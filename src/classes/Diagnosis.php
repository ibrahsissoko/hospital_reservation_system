<?php

class Diagnosis {

    public $doctorEmail;
    public $patientEmail;
    private $doctorName;
    private $patientName;
    private $db;
    public $patientInfo;
    private $diagnosis;
    private $observations;
    private $amount_due;
    public $success;
    public $error;

    function __construct($doctorName, $patientName, $doctorEmail, $diagnosis, $observations,$db) {
        $this->doctorName = preg_replace('/([a-z])([A-Z])/s','$1 $2', $doctorName);
        $this->patientName = $patientName;
        $this->doctorEmail = $doctorEmail;
        $this->db = $db;
        $this->amount_due = 500.00;
        if (!empty($diagnosis) || !empty($observations)) {
            $this->diagnosis = $diagnosis;
            $this->observations = $observations;
            $query = "SELECT * FROM users WHERE user_type_id=1";
            try {
                $stmt = $this->db->prepare($query);
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
    
    function initiate($session) {
        if(empty($this->error)){
            $emailPatient = ($this->patientInfo['diagnosis_confirm_email'] == "Yes" || $this->patientInfo['diagnosis_confirm_email'] == NULL);
            $emailDoctor = ($session['user']['diagnosis_confirm_email'] == "Yes" || $session['user']['diagnosis_confirm_email'] == NULL);
            if ($emailPatient && $emailDoctor) {
                $option = 1;
            } else if ($emailPatient && !$emailDoctor) {
                $option = 2;
            } else if (!$emailPatient && $emailDoctor) {
                $option = 3;
            } else if (!$emailPatient && !$emailDoctor) {
                $option = 4;
            }
            switch($option) {
                case 1:
                    $this->updateBillTable();
                    if($this->sendEmailToPatient() && $this->sendEmailToDoctor($session["user"]["email"])) {
                        $this->updateDiagnosisTable();
                        $this->success = "Diagnosis emails were sent to you and the patient you named!";
                    } else {
                        $this->error = "An error occurred sending confirmation emails. Try again soon.";
                    } 
                    break;
                case 2:
                    $this->updateBillTable();
                    if($this->sendEmailToPatient()) {
                        $this->updateDiagnosisTable();
                        $this->success = "A diagnosis confirmation email was sent to the patient!";
                    } else {
                        $this->error = "An error occurred sending the patient's confirmation email. Try again soon.";
                    } 
                    break;
                case 3:
                    $this->updateBillTable();
                    if($this->sendEmailToDoctor($session["user"]["email"])) {
                        $this->updateDiagnosisTable();
                        $this->success = "You were sent a confirmation email regarding this diagnosis!";
                    } else {
                        $this->error = "An error occurred sending your confirmation email. Try again soon.";
                    } 
                    break;
                case 4:
                    $this->updateBillTable();
                    $this->updateDiagnosisTable();
                    $this->error = "Diagnosis emails were NOT sent to you and the patient you named!";
                    break;
                default:
                    die("An internal error occurred.");
            }
        }
    }

    function updateBillTable() {
        $query1 = " SELECT * FROM bill WHERE patient_email = :patient_email";
        $query_params1 = array(':patient_email'  => $this->patientEmail);
        try{
        $stmt1 = $this->db->prepare($query1);
        $result1 = $stmt1->execute($query_params1);
        } catch(PDOException $e) {
            die("Failed to update bill table. " . $e->getMessage());
        }
        if(!empty($result1)){
            try {
            while($row = $stmt1->fetch(PDO::FETCH_ASSOC)){
            $this->patientInfo = $row;
            $this->amount_due = ($this->patientInfo["amount_due"]) + $this->amount_due;
            $query2 = "UPDATE bill SET amount_due = :amount_due WHERE patient_email = :patient_email";
            $query_params2 = array(':patient_email'  => $this->patientEmail,
                                    ':amount_due'=>$this->amount_due);
            $stmt2 = $this->db->prepare($query2);
            $result2 = $stmt2->execute($query_params2);
            break;
            }}catch(PDOException $e) {
                die("Failed to gather patient's amount due.");
           }
                
        
        }
    
        else{      

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
            ':amount_due' => 500,
            ':patient_name' => $this->patientName,
            ':patient_email' => $this->patientEmail,
            ':doctor_name' => $this->doctorName,
            ':doctor_email' => $this->doctorEmail
        );
        try {
                $stmt = $this->db->prepare($query);
                $result = $stmt->execute($query_params);
            } catch(PDOException $e) {
                die("Failed to update tables.");
            }
        }
    
    }

    
    function updateDiagnosisTable(){
        $query = "
                INSERT INTO diagnosis (
                    observations,
                    diagnosis,
                    patient_name,
                    patient_email,
                    doctor_name,
                    doctor_email
                ) VALUES (
                    :observations,
                    :diagnosis,
                    :patient_name,
                    :patient_email,
                    :doctor_name,
                    :doctor_email
                )
                ";    
        $query_params = array(
        ':observations' => $this->observations,
        ':diagnosis' => $this->diagnosis,
        ':patient_name' => $this->patientName,
        ':patient_email' => $this->patientEmail,
        ':doctor_name' => $this->doctorName,
        ':doctor_email' => $this->doctorEmail
    );
    try {
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($query_params);
        } catch(PDOException $e) {
            die("Failed to update tables.");
        }
    }
       
    function sendEmailToPatient() {
        // Generate the pdf attachment.
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',22);
        $pdf->Cell($pdf->w,40,'Billing Information',0,1,'C');
        $pdf->SetFont('Arial','',12);
        $pdf->MultiCell($pdf->w-20,10,'Thank you ' . $this->patientName . ' for scheduling and attending your appointment with ' . $this->doctorName
                . '. The doctor had the following observations:',0,1);
        $pdf->MultiCell(100,10,$this->observations,0,1);
        $pdf->Write(10,'These observations led to the following diagnosis: ');
        $pdf->SetFont('Arial','B');
        $pdf->Cell(30,10,$this->diagnosis,0,1);
        $pdf->SetFont('Arial','');
        $pdf->Write($pdf->w-20,10,'You have therefore been given this medication: ');
        $pdf->SetFont('Arial','B');
        $pdf->Cell(30,10,'SOME MEDICATION',0,1);
        $pdf->SetFont('Arial','');
        $pdf->MultiCell($pdf->w-20,10,'Please submit you payment soon by clicking on the Pay Bills link on the home page of your'
                . ' account or by clicking HERE.',0,1);
        $pdf->Cell(50,20,'',0,1);
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell($pdf->w-20,10,'Billing Details:',0,1,'C');
        $pdf->SetFont('Arial','',12);
        $pdf->Cell($pdf->w-20,10,'Doctor Services: $500',0,1,'C');
        $pdf->Cell($pdf->w-20,10,'Prescription: $200','B',1,'C');
        $pdf->SetFont('Arial','B');
        $pdf->Cell(50,10,'Total: $700',0,1);
        $firstLastName = explode(" ", $this->patientName);
        $fileName = $firstLastName[1] . "_Bill.pdf";
        $pdf->Output($fileName,'F');
        // Generate the email.
        $mail = new PHPMailer();
        $mail->isSMTP();                  
        $mail->Host = 'smtp.mailgun.org'; 
        $mail->SMTPAuth = true;                               
        $mail->Username = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->Password = 'f285bbdde02a408823b9283cdd8d6958';                           
        $mail->From = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->FromName = 'No-reply Wal Consulting';
        $mail->addAddress($this->patientEmail);
        $mail->AddAttachment($fileName);
        $mail->isHTML(true);
        $mail->WordWrap = 70;
        $mail->Subject = "Diagnosis and Billing";
        $mail->Body    = 'Hello, ' . $this->patientName . '!<br/><br/>'
                . 'You recently scheduled an appointment with ' . $this->doctorName
                . '. Here are some details of your appointment:'
                . '. Your observations by the doctor are: '. $this->observations
                . '. Your diagnosis by the doctor is: ' . $this->diagnosis
                . '. Your total is $'. $this->amount_due 
                . '. Attached is the official bill for the service'
                . '<br/><br/>Thank you,<br/>Wal Consulting';
        return $mail->send();
    }
    
    function sendEmailToDoctor($email) {
        // Generate the pdf attachment.
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',22);
        $pdf->Cell($pdf->w,40,'Billing Information',0,1,'C');
        $pdf->SetFont('Arial','',12);
        $pdf->MultiCell($pdf->w-20,10,'Thank you ' . $this->patientName . ' for scheduling and attending your appointment with ' . $this->doctorName
                . '. The doctor had the following observations:',0,1);
        $pdf->MultiCell(100,10,$this->observations,0,1);
        $pdf->Write(10,'These observations led to the following diagnosis: ');
        $pdf->SetFont('Arial','B');
        $pdf->Cell(30,10,$this->diagnosis,0,1);
        $pdf->SetFont('Arial','');
        $pdf->Write($pdf->w-20,10,'You have therefore been given this medication: ');
        $pdf->SetFont('Arial','B');
        $pdf->Cell(30,10,'SOME MEDICATION',0,1);
        $pdf->SetFont('Arial','');
        $pdf->MultiCell($pdf->w-20,10,'Please submit you payment soon by clicking on the Pay Bills link on the home page of your'
                . ' account or by clicking HERE.',0,1);
        $pdf->Cell(50,20,'',0,1);
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell($pdf->w-20,10,'Billing Details:',0,1,'C');
        $pdf->SetFont('Arial','',12);
        $pdf->Cell($pdf->w-20,10,'Doctor Services: $500',0,1,'C');
        $pdf->Cell($pdf->w-20,10,'Prescription: $200','B',1,'C');
        $pdf->SetFont('Arial','B');
        $pdf->Cell(50,10,'Total: $700',0,1);
        $firstLastName = explode(" ", $this->patientName);
        $fileName = $firstLastName[1] . "_Bill.pdf";
        $pdf->Output($fileName,'F');
        // Generate the email.
        $mail = new PHPMailer();
        $mail->isSMTP();                  
        $mail->Host = 'smtp.mailgun.org'; 
        $mail->SMTPAuth = true;                               
        $mail->Username = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->Password = 'f285bbdde02a408823b9283cdd8d6958';                           
        $mail->From = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->FromName = 'No-reply Wal Consulting';
        $mail->addAddress($email);
        $mail->AddAttachment($fileName);
        $mail->isHTML(true);
        $mail->WordWrap = 70;
        $mail->Subject = "Diagnosis and Billing";
        $mail->Body    = 'Hello!<br/><br/>'
                . 'You recently had an appointment with ' . $this->patientName . '. Email of patient is: '
                . $this->patientEmail . '. Here is'
                . ' the receipt of the diagnosis form that you submitted: $' . $this->amount_due
                . '. Attached is the official bill for the service'
                . '<br/><br/>Thank you,<br/>Wal Consulting';
        return $mail->send();
    }
}