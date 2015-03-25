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

    function makePasswordChange($db, $newPassword, $salt, $id) {
        $query = "
            UPDATE users
            SET
                password = :password
            WHERE
                id = :id
        ";

        $query_params = array(
            ':password' => $this->hashPassword($newPassword, $salt),
            ':id' => $id
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
}