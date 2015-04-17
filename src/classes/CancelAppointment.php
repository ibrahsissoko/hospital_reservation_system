<?php

class CancelAppointment {

    public $success;
    public $error;
    
    function sendEmailToUser($userEmail, $name) {
        $message = 'Hello, ' . $name . '!<br/><br/>'
                . 'You recently deleted an appointment.<br/><br/>Thank you,<br/>Wal Consulting';
        $email = new SendEmail();
        return $email->SendEmail($userEmail,"Appointment Deleted",$message,false);
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