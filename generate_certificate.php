<?php
require 'fpdf.php';

$file = "pdf/" . time() . ".pdf";
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 20);
$pdf->Cell(190, 20, "Certificate of Achievement", 0, 1, 'C');
$pdf->Cell(190, 20, "Awarded to: $name", 0, 1, 'C');
$pdf->Output("F", $file);

// মেইল পাঠানো
require 'PHPMailer/PHPMailer.php';
$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your_email@gmail.com';
$mail->Password = 'your_password';
$mail->setFrom('your_email@gmail.com', 'Admin');
$mail->addAddress($email);
$mail->Subject = "Your Certificate";
$mail->Body = "Dear $name, your certificate is attached.";
$mail->addAttachment($file);
$mail->send();
?>
