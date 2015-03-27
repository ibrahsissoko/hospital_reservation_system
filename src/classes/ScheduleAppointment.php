<?php

class ScheduleAppointment {

    public $doctorEmail;
    public $patientEmail;
    private $doctorName;
    private $patientName;
    private $date;
    private $time;
    public $success;
    public $error;
    
    function __construct($doctorName, $patientName, $patientEmail, $date, $time, $db) {
        $this->doctorName = $doctorName;
        $this->patientName = $patientName;
        $this->patientEmail = $patientEmail;
        $this->time = $time;
        $this->date = $date;
        
        $query = "SELECT * FROM users WHERE user_type_id=2";
        try {
            $stmt = $db->prepare($query);
            $result = $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Currently assuming no doctors will have the same first name, last
                // name, and degree.
                $string1 = str_replace(' ', '', $row["first_name"] . $row["last_name"] . $row["degree"]);
                $string2 = str_replace(' ', '', $doctorName);
                if(strcmp($string1, $string2) == 0) {
                    $this->doctorEmail = $row["email"];
                    break;
                }
            }
        } catch(PDOException $e) {
            die("Failed to gather doctor's email address.");
        }
        if (empty($this->doctorEmail)) {
            $this->error = "An internal error occurred acquiring the doctor's information.";
        }
    }
    
    function sendEmailToPatient() {
    
        $mail = new PHPMailer();
        $mail->isSMTP();                  
        $mail->Host = 'smtp.mailgun.org'; 
        $mail->SMTPAuth = true;                               
        $mail->Username = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->Password = 'f285bbdde02a408823b9283cdd8d6958';                           
        $mail->From = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->FromName = 'No-reply Wal Consulting';
        $mail->addAddress($this->patientEmail);
        $mail->isHTML(true);
        $mail->WordWrap = 70;
        $mail->Subject = "Appointment Confirmation";
        $mail->Body    = 'Hello, ' . $this->patientName . '!<br/><br/>'
                . 'You recently scheduled an appointment with ' . $this->doctorName
                . ' on ' . $this->date . ' at ' . $this->time . '. The doctor will confirm that this time will'
                . ' work as well.<br/><br/>Thank you,<br/>Wal Consulting';
        return $mail->send();
    }
    
    function sendEmailToDoctor() {
    
        $mail = new PHPMailer();
        $mail->isSMTP();                  
        $mail->Host = 'smtp.mailgun.org'; 
        $mail->SMTPAuth = true;                               
        $mail->Username = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->Password = 'f285bbdde02a408823b9283cdd8d6958';                           
        $mail->From = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->FromName = 'No-reply Wal Consulting';
        $mail->addAddress($this->doctorEmail);
        $mail->isHTML(true);
        $mail->WordWrap = 70;
        $mail->Subject = "Appointment Confirmation";
        $mail->Body    = 'Hello!<br/><br/>'
                . $this->patientName . ' requested an appointment with you on '
                . $this->date . ' at ' . $this->time . '. Hopefully this time can work for you...' 
                . '<br/><br/>Thank you,<br/>Wal Consulting';
        return $mail->send();
    }
}