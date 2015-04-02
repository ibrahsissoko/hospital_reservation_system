<?php

class Diagnosis {

    public $doctorEmail;
    public $patientEmail;
    private $doctorName;
    private $patientName;
    public $patientInfo;
    private $diagnosis;
    private $observations;
    private $amount_due = 500;
    public $error;

  function __construct($doctorName, $patientName, $doctorEmail, $diagnosis, $observations,$db) {
        $this->doctorName = $doctorName;
        $this->patientName = $patientName;
        $this->doctorEmail = $doctorEmail;

        if (!empty($diagnosis) || !empty($observations)) {
            $this->diagnosis = $diagnosis;
            $this->observations = $observations;
            $query = "SELECT * FROM users WHERE user_type_id=1";
            try {
                $stmt = $db->prepare($query);
                $result = $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $string1 = str_replace(' ', '', $row["first_name"] . $row["last_name"]);
                    $string2 = str_replace(' ', '', $patientName);
                    if(strcmp($string1, $string2) == 0) {
                        $this->patientInfo = $row;
                        $this->patientEmail = $this->patientInfo["email"];
                        break;
                    }
                }
            } catch(PDOException $e) {
                die("Failed to gather patient's email address.");
            }
            if (empty($this->patientEmail)) {
                $this->error = "An internal error occurred acquiring the doctor's information.";
            }
        } else {
            $this->error = "Please fill out all fields.";
        }
  }

    function sendEmailToPatient()) {
    
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
        $mail->Subject = "Diagnosis and Billing";
        $mail->Body    = 'Hello, ' . $this->patientName . '!<br/><br/>'
                . 'You recently scheduled an appointment with ' . $this->doctorName
                . '. Here are some details of your appointment:'
                . 'Your total is $'. $this->amount_due . '<br/><br/>Thank you,<br/>Wal Consulting';
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
        $mail->Subject = "Diagnosis and Billing";
        $mail->Body    = 'Hello!<br/><br/>'
                . 'You recently had an appointment with ' . $this->patientName . 'Here is'
                . ' the receipt of the diagnosis form that you submitted:' . $this->amount_due .
                . '<br/><br/>Thank you,<br/>Wal Consulting';
        return $mail->send();
    }

    function updateBillTable($db) {
        $query = "
                    INSERT INTO bill (
                        amount_due,
                        patient_name,
                        patient_email,
                        doctor_name,
                        doctor_email
                    ) VALUES (
                        :amount_due,
                        :patient_name,
                        :patient_email,
                        :doctor_name,
                        :doctor_email
                    )
                    ";
        $query_params = array(
            ':amount_due' => $this->amount_due,
            ':patient_name' => $this->patientName,
            ':patient_email' => $this->patientEmail,
            ':doctor_name' => $this->doctorName,
            ':doctor_email' => $this->doctorEmail
        );
        try {
                $stmt = $db->prepare($query);
                $result = $stmt->execute($query_params);
            } catch(PDOException $e) {
                die("Failed to updated tables.");
            }
    }
}