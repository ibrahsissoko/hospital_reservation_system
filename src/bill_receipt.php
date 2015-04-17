<?php
include_once('../AutoLoader.php');
AutoLoader::registerDirectory('../src/classes');

require("config.php");
require('fpdf17/fpdf.php');

$logo = 'http://walphotobucket.s3.amazonaws.com/logo.jpg';
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',22);
$pdf->Cell($pdf->w-20,40,'Billing Receipt',0,1,'C');
$pdf->Cell(40, 40, $pdf->Image($logo, $pdf->GetX(), $pdf->GetY(), 33.78),0,0,'L', false);

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

$query = '
        SELECT *
        FROM prescription
        WHERE
            id = :id
          ';
$query_params = array(
    ':id' => $diagnosisInfo['prescription_id']
);
try {
    $stmt = $db->prepare($query);
    $stmt->execute($query_params);
} catch(PDOException $ex) {
    die("Failed to run query: " . $ex->getMessage());
}
$prescriptionInfo = $stmt->fetch();
$pdf->MultiCell($pdf->w-20,10,'Thank you, ' . $diagnosisInfo['patient_name'] . ', for scheduling and attending your appointment with ' . $diagnosisInfo['doctor_name']
        . '. The doctor had the following observations:',0);
$pdf->SetFont('Arial','B');
$pdf->MultiCell($pdf->w-60,8,$diagnosisInfo['observations'],0,'C');
$pdf->SetFont('Arial','',12);
$pdf->Write(10,'These observations led to the following diagnosis: ');
$pdf->SetFont('Arial','B');
$pdf->Cell(30,10,$diagnosisInfo['diagnosis'],0,1);
$pdf->SetFont('Arial','');
if (!empty($prescriptionInfo)) {
    $pdf->Write(10,'You have therefore been given this medication: ');
    $pdf->SetFont('Arial','B');
    $pdf->Cell(30,10,$prescriptionInfo['drug_name'],0,1);
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
    $doctorServices = intval($diagnosisInfo['amount_due']) - intval($prescriptionInfo['price']);
    $pdf->Cell($pdf->w-20,10,'Doctor Services: $' . $doctorServices,0,1,'C');
    $pdf->Cell($pdf->w-20,10,'Prescription: $' . $prescriptionInfo['price'],'B',1,'C');
    $pdf->SetFont('Arial','B');
    $pdf->Cell($pdf->w-20,10,'Total: $' . $diagnosisInfo['amount_due'],0,1,'C');
} else {
    $pdf->Cell($pdf->w-20,10,'Doctor Services: $' . $diagnosisInfo['amount_due'],'B',1,'C');
    $pdf->SetFont('Arial','B');
    $pdf->Cell($pdf->w-20,10,'Total: $' . $diagnosisInfo['amount_due'],0,1,'C');
}
$pdf->Output();
?>