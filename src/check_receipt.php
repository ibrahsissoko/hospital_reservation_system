<?php
include_once('../AutoLoader.php');
AutoLoader::registerDirectory('../src/classes');

require("config.php");
require('fpdf17/fpdf.php');

$logo = 'http://walphotobucket.s3.amazonaws.com/logo.jpg';
$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image($logo, 5, $pdf->GetY(), 33.78);
$pdf->SetFont('Arial','B',22);
$pdf->Cell($pdf->w-20,40,'Check Receipt',0,1,'C');
$pdf->SetFont('Arial','',12);

$query = '
        SELECT *
        FROM users
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
$userInfo = $stmt->fetch();

$query2 = '
        SELECT *
        FROM payout
        WHERE
            doctor_id = :doctor_id
          ';
$query_params2 = array(
    ':doctor_id' => $_GET['id']
);
try {
    $stmt2 = $db->prepare($query2);
    $stmt2->execute($query_params2);
} catch(PDOException $ex) {
    die("Failed to run query: " . $ex->getMessage());
}
$payInfo = $stmt2->fetch();

$pdf->MultiCell($pdf->w-20,10,'Thank you, ' . $userInfo['first_name'] . ' ' 
        . $userInfo['last_name'] . ', for seeing patients during this pay period.'
        . ' This is your receipt of payment in the amount of: $' . $payInfo['amount_due']
        . '. Thanks again for all of your hard work!',0);
$pdf->Output();
?>