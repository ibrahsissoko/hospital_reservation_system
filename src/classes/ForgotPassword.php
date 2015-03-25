<?php

class ForgotPassword {

    public $noEmail;
    public $success;
    public $email;
    
    function _construct() {
        $this->noEmail =
        $this->success =
        $this->email = "";
    }
    
    function checkEmail($email, $db) {
        // Check if the email is in the database.
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
            $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }

        if($stmt->rowCount() == 0){
            $this->noEmail = "This email is not recognized.";
        } else {
            // Set the email if it was recognized.
            $this->email = $email;
        }
    }
    
    function sendNewPassword($newPassword) {
        $mail = new PHPMailer();
        $mail->isSMTP();                  
        $mail->Host = 'smtp.mailgun.org'; 
        $mail->SMTPAuth = true;                               
        $mail->Username = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->Password = 'f285bbdde02a408823b9283cdd8d6958';                           
        $mail->From = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->FromName = 'No-reply Wal Consulting';
        $mail->addAddress($this->email);
        $mail->isHTML(true);
        $mail->WordWrap = 70;
        $mail->Subject = "Password Retrieval";
        $mail->Body    = 'Hello!<br/><br/>'
                . 'You recently requested a password retrieval.<br/><br/>'
                . 'Here is a new password use it to login.<br/><br/>'
                . 'Password: '. $newPassword
                . '<br/><br/>Thank you,<br/>Wal Consulting';
        return $mail->send();
    }
    
    function updateTables($password, $salt, $db) {
        $query = "
            UPDATE users
            SET 
                password = :password,
                salt = :salt
            WHERE
                email = :email
        ";

        $query_params = array(
            ':password' => $password,
            ':salt' => $salt,
            ':email' => $this->email
        );

        try {
            $stmt = $db->prepare($query);
            $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }
    }
}