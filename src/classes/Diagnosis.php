<?php

class Diagnosis {

    public $doctorEmail;
    public $patientEmail;
    private $doctorName;
    private $patientName;
    private $drug_name;
    private $date;
    private $time;
    private $db;
    private $prescriptionID;
    public $patientInfo;
    private $diagnosis;
    private $observations;
    private $amount_due;
    public $success;
    public $error;

    function __construct($doctorName, $patientName, $doctorEmail, $diagnosis, $observations,$date,$time,$db,$medication) {
        $this->doctorName = preg_replace('/([a-z])([A-Z])/s','$1 $2', $doctorName);
        $this->patientName = $patientName;
        $this->doctorEmail = $doctorEmail;
        $this->db = $db;
        $this->date = $date;
        $this->time = $time;
        $this->amount_due = 500.00;
        if (!empty($diagnosis) || !empty($observations)) {
            $this->diagnosis = $diagnosis;
            $this->observations = $observations;
            $query = "SELECT * FROM users WHERE user_type_id=1";
            try {
                $stmt = $this->db->prepare($query);
                $stmt->execute();
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
                die("Failed to gather patient's email address. " . $e->getMessage());
            }
            if (empty($this->patientEmail)) {
                $this->error = "An internal error occurred acquiring the doctor's information.";
            }
            if (!empty($medication) && $medication != "Medication") {
                $query = "SELECT * FROM prescription WHERE drug_name = :drug_name";
                $query_params = array(
                    ':drug_name' => $medication
                );
                try {
                    $stmt = $this->db->prepare($query);
                    $stmt->execute($query_params);
                } catch(PDOException $e) {
                    die("Failed to gather prescription information. " . $e->getMessage());
                }
                $this->drug_name = $medication;
                $row = $stmt->fetch();
                $this->prescriptionID = $row['id'];
                $this->amount_due += intval($row['price']);
            } else {
                $this->prescriptionID = 0;
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
                        return true;
                    } else {
                        $this->error = "An error occurred sending confirmation emails. Try again soon.";
                        return false;
                    }
                case 2:
                    $this->updateBillTable();
                    if($this->sendEmailToPatient()) {
                        $this->updateDiagnosisTable();
                        $this->success = "A diagnosis confirmation email was sent to the patient!";
                        return true;
                    } else {
                        $this->error = "An error occurred sending the patient's confirmation email. Try again soon.";
                        return false;
                    }
                case 3:
                    $this->updateBillTable();
                    if($this->sendEmailToDoctor($session["user"]["email"])) {
                        $this->updateDiagnosisTable();
                        $this->success = "You were sent a confirmation email regarding this diagnosis!";
                        return true;
                    } else {
                        $this->error = "An error occurred sending your confirmation email. Try again soon.";
                        return false;
                    }
                case 4:
                    $this->updateBillTable();
                    $this->updateDiagnosisTable();
                    $this->success = "Diagnosis saved!";
                    return true;
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
            ':amount_due' => $this->amount_due,
            ':patient_name' => $this->patientName,
            ':patient_email' => $this->patientEmail,
            ':doctor_name' => $this->doctorName,
            ':doctor_email' => $this->doctorEmail
        );
        try {
                $stmt = $this->db->prepare($query);
                $result = $stmt->execute($query_params);
            } catch(PDOException $e) {
                die("Failed to update bill table. " . $e->getMessage());
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
                    doctor_email,
                    prescription_id,
                    date,
                    time,
                    amount_due
                ) VALUES (
                    :observations,
                    :diagnosis,
                    :patient_name,
                    :patient_email,
                    :doctor_name,
                    :doctor_email,
                    :prescription_id,
                    :date,
                    :time,
                    :amount_due
                )
                ";    
        $query_params = array(
        ':observations' => $this->observations,
        ':diagnosis' => $this->diagnosis,
        ':patient_name' => $this->patientName,
        ':patient_email' => $this->patientEmail,
        ':doctor_name' => $this->doctorName,
        ':doctor_email' => $this->doctorEmail,
        ':prescription_id' => $this->prescriptionID,
        ':date' => $this->date,
        ':time' => $this->time,
        ':amount_due' => $this->amount_due
    );
    try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($query_params);
        } catch(PDOException $e) {
            die("Failed to update diagnosis table. " . $e->getMessage());
        }
    }
       
    function sendEmailToPatient() {
        // Generate the pdf attachment.
        $pdf = new FPDF();
        $logo = 'http://walphotobucket.s3.amazonaws.com/logo.jpg';
        $pdf->AddPage();
        $pdf->Image($logo, 5, $pdf->GetY(), 33.78);
        $pdf->SetFont('Arial','B',22);
        $pdf->Cell($pdf->w-20,40,'Billing Receipt',0,1,'C');
        $pdf->SetFont('Arial','',12);
        
        $query = '
                SELECT *
                FROM prescription
                WHERE
                    id = :id
                  ';
        $query_params = array(
            ':id' => $this->prescriptionID
        );
        try {
            $stmt = $db->prepare($query);
            $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }
        $prescriptionInfo = $stmt->fetch();

        $pdf->MultiCell($pdf->w-20,10,'Thank you, ' . $this->patientName . ', for scheduling and attending your appointment with ' . $this->doctorName
                . '. The doctor had the following observations:',0);
        $pdf->SetFont('Arial','B');
        $pdf->MultiCell($pdf->w-60,8,$this->observations,0,'C');
        $pdf->SetFont('Arial','',12);
        $pdf->Write(10,'These observations led to the following diagnosis: ');
        $pdf->SetFont('Arial','B');
        $pdf->Cell(30,10,$this->diagnosis,0,1);
        $pdf->SetFont('Arial','');
        if (!empty($prescriptionInfo)) {
            $pdf->Write(10,'You have therefore been given this medication: ');
            $pdf->SetFont('Arial','B');
            $pdf->Cell(30,10,$this->medication,0,1);
            $pdf->SetFont('Arial','');
            $pdf->Write(10,'General information: ');
            $pdf->SetFont('Arial','B');
            $pdf->MultiCell(60,8,$prescriptionInfo['property'],0);
            $pdf->SetFont('Arial','');
            $pdf->Write(10,'Directions of usage: ');
            $pdf->SetFont('Arial','B');
            $pdf->MultiCell(60,8,$prescriptionInfo['usage_directions'],0);
            $pdf->SetFont('Arial','');
        }
        $pdf->Write(10,'Please submit you payment soon by clicking on the Pay link next to your bill on the view bills page'
                . ' or by clicking ',0,1);
        $pdf->SetTextColor(0,0,255);
        $pdf->SetFont('','U');
        $pdf->Write(10,'here','http://wal-engproject.rhcloud.com/src/pay_bill.php?id=' . $_GET['id']);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(50,20,'',0,1);
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell($pdf->w-20,10,'Billing Details:',0,1,'C');
        $pdf->SetFont('Arial','',12);
        if (!empty($prescriptionInfo)) {
            $doctorServices = intval($this->amount_due) - intval($prescriptionInfo['price']);
            $pdf->Cell($pdf->w-20,10,'Doctor Services: $' . $doctorServices,0,1,'C');
            $pdf->Cell($pdf->w-20,10,'Prescription: $' . $prescriptionInfo['price'],'B',1,'C');
            $pdf->SetFont('Arial','B');
            $pdf->Cell($pdf->w-20,10,'Total: $' . $this->amount_due,0,1,'C');
        } else {
            $pdf->Cell($pdf->w-20,10,'Doctor Services: $' . $this->amount_due,'B',1,'C');
            $pdf->SetFont('Arial','B');
            $pdf->Cell($pdf->w-20,10,'Total: $' . $this->amount_due,0,1,'C');
        }
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
        $logo = 'http://walphotobucket.s3.amazonaws.com/logo.jpg';
        $pdf->AddPage();
        $pdf->Image($logo, 5, $pdf->GetY(), 33.78);
        $pdf->SetFont('Arial','B',22);
        $pdf->Cell($pdf->w-20,40,'Billing Receipt',0,1,'C');
        $pdf->SetFont('Arial','',12);
        
        $query = '
                SELECT *
                FROM prescription
                WHERE
                    id = :id
                  ';
        $query_params = array(
            ':id' => $this->prescriptionID
        );
        try {
            $stmt = $db->prepare($query);
            $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }
        $prescriptionInfo = $stmt->fetch();

        $pdf->MultiCell($pdf->w-20,10,'Thank you, ' . $this->patientName . ', for scheduling and attending your appointment with ' . $this->doctorName
                . '. The doctor had the following observations:',0);
        $pdf->SetFont('Arial','B');
        $pdf->MultiCell($pdf->w-60,8,$this->observations,0,'C');
        $pdf->SetFont('Arial','',12);
        $pdf->Write(10,'These observations led to the following diagnosis: ');
        $pdf->SetFont('Arial','B');
        $pdf->Cell(30,10,$this->diagnosis,0,1);
        $pdf->SetFont('Arial','');
        if (!empty($prescriptionInfo)) {
            $pdf->Write(10,'You have therefore been given this medication: ');
            $pdf->SetFont('Arial','B');
            $pdf->Cell(30,10,$this->medication,0,1);
            $pdf->SetFont('Arial','');
            $pdf->Write(10,'General information: ');
            $pdf->SetFont('Arial','B');
            $pdf->MultiCell(60,8,$prescriptionInfo['property'],0);
            $pdf->SetFont('Arial','');
            $pdf->Write(10,'Directions of usage: ');
            $pdf->SetFont('Arial','B');
            $pdf->MultiCell(60,8,$prescriptionInfo['usage_directions'],0);
            $pdf->SetFont('Arial','');
        }
        $pdf->Write(10,'Please submit you payment soon by clicking on the Pay link next to your bill on the view bills page'
                . ' or by clicking ',0,1);
        $pdf->SetTextColor(0,0,255);
        $pdf->SetFont('','U');
        $pdf->Write(10,'here','http://wal-engproject.rhcloud.com/src/pay_bill.php?id=' . $_GET['id']);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell(50,20,'',0,1);
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell($pdf->w-20,10,'Billing Details:',0,1,'C');
        $pdf->SetFont('Arial','',12);
        if (!empty($prescriptionInfo)) {
            $doctorServices = intval($this->amount_due) - intval($prescriptionInfo['price']);
            $pdf->Cell($pdf->w-20,10,'Doctor Services: $' . $doctorServices,0,1,'C');
            $pdf->Cell($pdf->w-20,10,'Prescription: $' . $prescriptionInfo['price'],'B',1,'C');
            $pdf->SetFont('Arial','B');
            $pdf->Cell($pdf->w-20,10,'Total: $' . $this->amount_due,0,1,'C');
        } else {
            $pdf->Cell($pdf->w-20,10,'Doctor Services: $' . $this->amount_due,'B',1,'C');
            $pdf->SetFont('Arial','B');
            $pdf->Cell($pdf->w-20,10,'Total: $' . $this->amount_due,0,1,'C');
        }
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
    
    function updateAppointment($appointmentID) {
        $query = "
                UPDATE appointment
                SET
                    completed = :completed
                WHERE
                    id = :id
                ";    
        $query_params = array(
            ':completed' => 1,
            ':id' => $appointmentID
        );
        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute($query_params);
        } catch(PDOException $e) {
            die("Failed to update appointment information. " . $e->getMessage());
        }
    }
}