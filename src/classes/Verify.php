<?php

class Verify {

    public $hash;
    public $email;
    public $db;

    public $status;

    function verifyUser($hash, $email, $db) {

        $this->email = $email;
        $this->hash = $hash;
        $this->db = $db;

        if(!empty($this->email) && !empty($this->hash)) {
            $stmt = $this->getUserStatement();

            if ($stmt != null) {
                $row = $stmt->fetch();
                if($stmt->rowCount() > 0) {
                    if ($row['active_user'] == 1) {
                        $this->status = "This email account has already been registered.";
                    } else {
                        $this->activateUser();
                        $this->status = "You are now registered!";
                    }
                } else {
                    $this->status = "The link you entered is invalid. Make sure that you copied it correctly.";
                }
            } else {
                $this->status = "Something went wrong :(";
            }
        } else {
            $this->status = "Invalid method for account verification.";
        }

    }

    private function getUserStatement() {
        $query = "
                SELECT *
                FROM users
                WHERE
                    email = :email
                AND
                    hash  = :hash
            ";

        $query_params = array(
            ':email' => $this->email,
            ':hash' => $this->hash
        );

        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }

        return $stmt;
    }

    private function activateUser() {
        $query = "
                    UPDATE users
                    SET
                        active_user = :active_user
                    WHERE
                        email = :email
                    ";

        $query_params = array(
            ':active_user' => 1,
            ':email' => $this->email
        );
        try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }
    }
}