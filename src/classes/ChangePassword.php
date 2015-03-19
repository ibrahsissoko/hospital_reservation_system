<?php

class ChangePassword {
    public $errorMessage;

    function checkFieldsFilled($post) {
        if (empty($post['current_password']) || empty($post['new_password']) || empty($post['confirm_password'])) {
            $this->errorMessage = "Please fill all fields.";
            return false;
        } else {
            return true;
        }
    }

    function checkMatchingPasswords($newPassword, $confirmed) {
        if ($newPassword == $confirmed) {
            return true;
        } else {
            $this->errorMessage = "Passwords do not match";
            return false;
        }
    }

    function changePassword($db, $newPassword, $salt) {
        $query = "
            UPDATE users
            SET
                password = :password
            WHERE
                id = :id
        ";

        $query_params = array(
            ':password' => $this->hashPassword($newPassword, $salt)
        );

        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }

        if ($result) {
            header("Location: home.php");
            die("Redirecting to: home.php");
        }
    }

    function hashPassword($newPassword, $salt) {
        $password = hash('sha256', $newPassword . $salt);

        for($round = 0; $round < 65536; $round++) {
            $password = hash('sha256', $password . $salt);
        }

        return $password;
    }
}