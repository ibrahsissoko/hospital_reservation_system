<?php

class ForgotPassword {

    public $noEmail;
    public $success;
    public $email;
    public $password;
    public $salt;
    
    function _construct() {
        $this->noEmail =
        $this->success =
        $this->email =
        $this->password = 
        $this->salt = "";
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
    
    function makeNewPassword() {
        $this->password = substr(md5(rand(0,2147483647)),10,10);
    }
    
    function sendNewPassword() {
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
                . 'Password: '. $this->password
                . '<br/><br/>Thank you,<br/>Wal Consulting';
        return $mail->send();
    }
    
    function hashNewPassword() {
        // Re-hash the password and create new salt.
        $this->salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
        $newPassword = hash('sha256', $this->password . $this->salt);
        for($round = 0; $round < 65536; $round++){
            $newPassword = hash('sha256', $newPassword . $this->salt);
        }
        $this->password = $newPassword;
    }
    
    function updateTables($db) {
        $query = "
            UPDATE users
            SET 
                password = :password,
                salt = :salt
            WHERE
                email = :email
        ";

        $query_params = array(
            ':password' => $this->password,
            ':salt' => $this->salt,
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