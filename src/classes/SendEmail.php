<?php

class SendEmail {
    
    private $attachmentPath;
    
    function SendEmail($to,$subject,$msg,$attachment) {
        $mail = new PHPMailer();
        $mail->isSMTP();                  
        $mail->Host = 'smtp.mailgun.org'; 
        $mail->SMTPAuth = true;                               
        $mail->Username = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->Password = 'f285bbdde02a408823b9283cdd8d6958';                           
        $mail->From = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->FromName = 'No-reply Wal Consulting';
        $mail->addAddress($to);
        if ($attachment) {
            $mail->AddAttachment($this->attachmentPath);
        }
        $mail->isHTML(true);
        $mail->WordWrap = 70;
        $mail->Subject = $subject;
        $mail->Body    = $msg;
        return $mail->send();
    }

    function SendEmailToMultipleUsers($to,$subject,$msg,$attachment) {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.mailgun.org';
        $mail->SMTPAuth = true;
        $mail->Username = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->Password = 'f285bbdde02a408823b9283cdd8d6958';
        $mail->From = 'postmaster@sandboxb958ed499fee4346ba3efcec39208a74.mailgun.org';
        $mail->FromName = 'No-reply Wal Consulting';
        foreach ($to as $value) {
            $mail->addAddress($value);
        }
        if ($attachment) {
            $mail->AddAttachment($this->attachmentPath);
        }
        $mail->isHTML(true);
        $mail->WordWrap = 70;
        $mail->Subject = $subject;
        $mail->Body    = $msg;
        return $mail->send();
    }
    
    function SendEmailWithAttachment($prescriptionID,$db,$patientName,$doctorName,
            $observations,$diagnosis,$medication,$amount_due,$to,$subject,$msg) {
        $pdf = new FPDF();
        $pdf->AddPage();
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
            ':id' => $prescriptionID
        );
        try {
            $stmt = $db->prepare($query);
            $stmt->execute($query_params);
        } catch(PDOException $ex) {
            die("Failed to run query: " . $ex->getMessage());
        }
        $prescriptionInfo = $stmt->fetch();

        $pdf->MultiCell($pdf->w-20,10,'Thank you, ' . $patientName . ', for scheduling and attending your appointment with ' . $doctorName
                . ' at ' . $time . ' on ' . $date . '. The doctor had the following observations:',0);
        $pdf->SetFont('Arial','B');
        $pdf->MultiCell($pdf->w-60,8,$observations,0,'C');
        $pdf->SetFont('Arial','',12);
        $pdf->Write(10,'These observations led to the following diagnosis: ');
        $pdf->SetFont('Arial','B');
        $pdf->Cell(30,10,$diagnosis,0,1);
        $pdf->SetFont('Arial','');
        if (!empty($prescriptionInfo)) {
            $pdf->Write(10,'You have therefore been given this medication: ');
            $pdf->SetFont('Arial','B');
            $pdf->Cell(30,10,$medication,0,1);
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
            $doctorServices = intval($amount_due) - intval($prescriptionInfo['price']);
            $pdf->Cell($pdf->w-20,10,'Doctor Services: $' . $doctorServices,0,1,'C');
            $pdf->Cell($pdf->w-20,10,'Prescription: $' . $prescriptionInfo['price'],'B',1,'C');
            $pdf->SetFont('Arial','B');
            $pdf->Cell($pdf->w-20,10,'Total: $' . $amount_due,0,1,'C');
        } else {
            $pdf->Cell($pdf->w-20,10,'Doctor Services: $' . $amount_due,'B',1,'C');
            $pdf->SetFont('Arial','B');
            $pdf->Cell($pdf->w-20,10,'Total: $' . $amount_due,0,1,'C');
        }
        $firstLastName = explode(" ", $patientName);
        $this->attachmentPath = $firstLastName[1] . "_Bill.pdf";
        $pdf->Output($this->attachmentPath,'F');
        return $this->SendEmail($to,$subject,$msg,true);
    }
}