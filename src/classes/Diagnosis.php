<?php

class ForgotPassword {

    public $doctorEmail;
    public $patientEmail;
    
    function sendEmailToPatient() {
    /*
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
        $mail->Subject = "Diagnosis";
        $mail->Body    = 'Hello!<br/><br/>'
                . 'You recently had an appointment with Dr. DOCTOR_NAME. Here are'
                . ' some of the details of your appointment: 
                . '<br/><br/>Thank you,<br/>Wal Consulting';
        return $mail->send();*/
    }
    
    function sendEmailToDoctor() {
    /*
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
        $mail->Subject = "Diagnosis";
        $mail->Body    = 'Hello!<br/><br/>'
                . 'You recently had an appointment with PATIENT_NAME. Here is'
                . ' the receipt of the diagnosis form that you submitted: 
                . '<br/><br/>Thank you,<br/>Wal Consulting';
        return $mail->send();*/
    }
}