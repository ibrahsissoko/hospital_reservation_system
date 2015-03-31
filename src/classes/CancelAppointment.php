<?php

class CancelAppointment {

    public $success;
    public $error;
    
    function sendEmailToUser($email, $name) {
    
        $mail = new PHPMailer();
        $mail->isSMTP();                  
        $mail->Host = 'smtp.mailgun.org'; 
        $mail->SMTPAuth = true;                               
        $mail->Username = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->Password = 'f285bbdde02a408823b9283cdd8d6958';                           
        $mail->From = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->FromName = 'No-reply Wal Consulting';
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->WordWrap = 70;
        $mail->Subject = "Appointment Deleted";
        $mail->Body    = 'Hello, ' . $name . '!<br/><br/>'
                . 'You recently deleted an appointment.<br/><br/>Thank you,<br/>Wal Consulting';
        return $mail->send();
    }

    function updateAppointmentTable($db, $id) {             
        $query = "
            DELETE
            FROM appointment
            WHERE
              id = :id
        ";
        $query_params = array(
            ':id' => $id
        );
        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $ex) {
                die("Failed to run query: " . $ex->getMessage());
        }
        return $result;
    }
}