<?php
include_once('../AutoLoader.php');
AutoLoader::registerDirectory('../src/classes');

require("config.php");
require('fpdf17/fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',22);
$pdf->Cell($pdf->w,40,'Billing Receipt',0,1,'C');
$pdf->SetFont('Arial','',12);

$query = '
        SELECT *
        FROM diagnosis
        WHERE
            id = :id
          ';
$query_params = array(
    ':id' => $_GET['id']
);
try {
    $stmt = $db->prepare($query);
    $stmt->execute($query_params);
} catch(PDOException $ex) {
    die("Failed to run query: " . $ex->getMessage());
}
$diagnosisInfo = $stmt->fetch();

$pdf->MultiCell($pdf->w-20,10,'Thank you, ' . $diagnosisInfo['patient_name'] . ', for scheduling and attending your appointment with ' . $diagnosisInfo['doctor_name']
        . '. The doctor had the following observations:',0,1);
$pdf->MultiCell($pdf->w-60,10,$diagnosisInfo['observations'],0,1,'C');
$pdf->Write(10,'These observations led to the following diagnosis: ');
$pdf->SetFont('Arial','B');
$pdf->Cell(30,10,$diagnosisInfo['diagnosis'],0,1);
$pdf->SetFont('Arial','');
$pdf->Write(10,'You have therefore been given this medication: ');
$pdf->SetFont('Arial','B');
$pdf->Cell(30,10,'SOME MEDICATION',0,1);
$pdf->SetFont('Arial','');
$pdf->Write(10,'Please submit you payment soon by clicking on the Pay Bills link on the home page of your'
        . ' account or by clicking ',0,1);
$pdf->SetTextColor(0,0,255);
$pdf->SetFont('','U');
$pdf->Write(30,10,'here','http://wal-engproject.rhcloud.com/src/pay_bill.php?id=' . $_GET['id']);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(50,20,'',0,1);
$pdf->SetFont('Arial','B',16);
$pdf->Cell($pdf->w-20,10,'Billing Details:',0,1,'C');
$pdf->SetFont('Arial','',12);
$pdf->Cell($pdf->w-20,10,'Doctor Services: ' . $diagnosisInfo['amount_due'],0,1,'C');
$pdf->Cell($pdf->w-20,10,'Prescription: $0','B',1,'C');
$pdf->SetFont('Arial','B');
$pdf->Cell(50,10,'Total: ' . $diagnosisInfo['amount_due'],0,1);
$pdf->Output();
?>