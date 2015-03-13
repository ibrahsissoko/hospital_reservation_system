<?php

class Register {
    public $noEmail;
    public $incorrectEmail;
    public $noPassword;
    public $registeredEmail;
    public $noConfirmPassword;
    public $noPasswordMatch;
    public $noAccessCode;
    public $registrationSuccess;
    public $badPassword;

    function _construct() {
        $this->noEmail =
            $this->incorrectEmail =
            $this->noPassword =
            $this->registeredEmail =
            $this->noConfirmPassword =
            $this->noPasswordMatch =
            $this->noAccessCode =
            $this->registrationSuccess =
                "";

    }

    function checkEmailExists($email, $db) {
        $query = "
                SELECT *
                FROM users
                WHERE
                    email = :email
            ";

        $query_params = array(
            ':email' => $email
        );

        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }

        if($stmt->rowCount() > 0){
            $this->registeredEmail = "This email address is already registered.";
        }
    }

    function checkNoFormErrors($_POST, $db) {
        $this->emailError($_POST['email']);
        $this->passwordError($_POST['password'], $_POST['confirmPassword']);

        $accessCode = $this->getAccessCode($_POST['user_type_id'], $db);
        $this->userTypeError($_POST['user_type_id'], $_POST['access_code'], $accessCode);

        return empty($this->noEmail) && empty($this->incorrectEmail) && empty($this->noPassword) &&
                empty($this->noConfirmPassword) && empty($this->noPasswordMatch) &&
                empty($this->noAccessCode) && empty($this->badPassword);
    }

    function emailError($email) {
        if (empty($email)) {
            $this->noEmail = "Please enter an email address.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->incorrectEmail = "Invalid E-Mail Address.";
        }
    }

    function passwordError($password, $confirm) {
        $this->badPassword = $this->testPassword($password);

        if (empty($password)) {
            $this->noPassword = "Please enter a password.";
        }
        if (empty($confirm)) {
            $this->noConfirmPassword = "Please confirm your password.";
        }
        if ($password != $confirm && empty($this->noPassword) && empty($this->noConfirmPassword) && empty($this->badPassword)) {
            $this->noPasswordMatch = "Passwords do not match.";
        }
    }

    function userTypeError($typeId, $userAccessCode, $dbAccessCode) {
        if ($typeId != 1) {
            if (empty($userAccessCode)) {
                $this->noAccessCode = "Enter an access code.";
            }
            if ($dbAccessCode != $userAccessCode) {
                $this->noAccessCode = "Invalid access code";
            }
        }

    }

    function getAccessCode($typeId, $db) {
        $query = "
                SELECT *
                FROM user_types
                WHERE
                  id = :type_id
            ";

        $query_params = array(
            ':type_id' => $typeId
        );

        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }
        $row = $stmt->fetch();

        return $row['access_code'];
    }

    function saveRegistration($_POST, $hash, $db) {
        // Store the results into the users table.
        $query = "
                    INSERT INTO users (
                        email,
                        password,
                        salt,
                        user_type_id,
                        hash
                    ) VALUES (
                        :email,
                        :password,
                        :salt,
                        :user_type_id,
                        :hash
                    )
                    ";

        // Security measures
        $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
        $password = hash('sha256', $_POST['password'] . $salt);

        for($round = 0; $round < 65536; $round++) {
            $password = hash('sha256', $password . $salt);
        }

        $query_params = array(
            ':email' => $_POST['email'],
            ':password' => $password,
            ':salt' => $salt,
            ':user_type_id' => $_POST['user_type_id'],
            ':hash' => $hash
        );

        try {
            $stmt = $db->prepare($query);
            $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }
    }

    function testPassword($password) {
        if (strlen($password) == 0) {
            // Already caught by empty password.
            return "";
        } elseif (strlen($password) > 20 ) {
            return "Password cannot be longer than 20 characters.";
        } elseif (preg_match("/\d/",$password) == 0) {
            return "Password must have at least one number.";
        } elseif (preg_match("/[A-Z,a-z]/",$password) == 0) {
            return "Password must have at least one letter.";
        }
        // If password passes all of the other tests, then there is no error message.
        return "";
    }
}
