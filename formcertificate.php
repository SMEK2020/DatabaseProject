<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'smtp/src/Exception.php';
require 'smtp/src/PHPMailer.php';
require 'smtp/src/SMTP.php';
require('fpdf.php');



$name = $_POST['name'];
$email = 'limon1230987@gmail.com';

$font = "arial.ttf";
if (!file_exists("certificate.jpg")) {
    die("Error: certificate.jpg not found!");
}
if (!file_exists($font)) {
    die("Error: Font file not found!");
}

// **ফোল্ডার তৈরি (যদি না থাকে)**
if (!is_dir("pdf")) {
    mkdir("pdf", 0777, true);
}

// **সার্টিফিকেট তৈরি**
$image = imagecreatefromjpeg("certificate.jpg");
$color = imagecolorallocate($image, 19, 21, 22);
$image_width = imagesx($image);

// 🔹 **সার্টিফিকেটের ইউনিক আইডি**
$unique_id = strtoupper(bin2hex(random_bytes(8)));
$text_id = "ID: " . $unique_id;
imagettftext($image, 20, 0, 50, 100, $color, $font, $text_id);

// 🔹 **স্টুডেন্টের নাম সংযুক্ত করা**
$size = 40;
$angle = 0;
$bbox = imagettfbbox($size, $angle, $font, $name);
$text_width = $bbox[2] - $bbox[0];
$x = ($image_width - $text_width) / 2;
$y = 650;
imagettftext($image, $size, $angle, $x, $y, $color, $font, $name);

// **সার্টিফিকেট ইমেজ সংরক্ষণ**
$file = time();
$image_path = "pdf/".$file.".jpg";
imagejpeg($image, $image_path);
imagedestroy($image);

// **সার্টিফিকেট PDF তৈরি করা**
$pdf = new FPDF();
$pdf->AddPage();
$pdf->Image($image_path, 0, 0, 210, 150);
$pdf_path = "pdf/".$file.".pdf";
$pdf->Output("F", $pdf_path);

if (!file_exists($pdf_path)) {
    die("Error: PDF file not generated!");
}

// **মেইল পাঠানোর সেটআপ**
$mail = new PHPMailer();
$mail->isSMTP();
$mail->SMTPDebug = 2;
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Port = 587;
$mail->SMTPSecure = 'tls';
$mail->Username = 'sazid.bsmru.cse1@gmail.com';
$mail->Password = 'curdznnekpspmcpz'; // আপনার Google অ্যাপে তৈরি করা App Password ব্যবহার করুন

$mail->setFrom('sazid.bsmru.cse1@gmail.com', 'Sazid Mahmud');
$mail->addAddress($email);
$mail->isHTML(true);
$mail->Subject = 'Certificate Generated';
$mail->Body = "Dear $name, <br><br> Your certificate is ready! <br> <b>Certificate ID:</b> $unique_id <br><br> Please find the attached PDF.";

if (file_exists($pdf_path)) {
    $mail->addAttachment($pdf_path);
} else {
    die("Error: PDF not found!");
}

if (!$mail->send()) {
    die("Mailer Error: " . $mail->ErrorInfo);
} else {
    echo "Email Sent Successfully!";
}
?>
