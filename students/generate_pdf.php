<?php
require_once('../fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);
$pdf->Write(0, "Daily Attendance Report", '', 0, 'C', true, 0, false, false, 0);
$pdf->Output('attendance_report.pdf', 'D');
?>
