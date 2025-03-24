<?php
include('../connect.php'); // ডাটাবেস কানেকশন

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Composer-generated autoload ফাইল
require 'smtp/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentid = $_POST['studentid']; // স্টুডেন্ট আইডি
    $requestdate = $_POST['requestdate']; // রিকুয়েস্ট ডেট

    if (isset($_POST['action']) && $_POST['action'] == 'approve') {
        // অ্যাপ্রুভ করলে সার্টিফিকেট তৈরি হবে
        $sql = "UPDATE certificaterequest SET status = 'approved' WHERE studentid = '$studentid' AND requestdate = '$requestdate'";
        $conn->query($sql);

        // student টেবিল থেকে student's name ফেচ করা
        $sql_name = "SELECT fullname FROM student WHERE studentid = '$studentid'";
        $result_name = $conn->query($sql_name);
        if ($result_name) {
            $row_name = $result_name->fetch_assoc();
            $name = $row_name['fullname'];
        } else {
            die("Error fetching student name: " . $conn->error);
        }

        // users টেবিল থেকে email ফেচ করা
        $sql_email = "SELECT email FROM users WHERE userid = (SELECT userid FROM student WHERE studentid = '$studentid')";
        $result_email = $conn->query($sql_email);
        if ($result_email) {
            $row_email = $result_email->fetch_assoc();
            $email = $row_email['email'];
        } else {
            die("Error fetching email: " . $conn->error);
        }

        // সার্টিফিকেট জেনারেশন কোড
        $font = "../fonts/arial.ttf"; // Custom font for text on the certificate
        if (!file_exists($font)) {
            die("Font file not found!");
        }

        if (!is_dir("pdf")) {
            mkdir("pdf", 0777, true);  // Create directory for storing PDFs if it doesn't exist
        }

        $image = imagecreatefromjpeg("certificate.jpg"); // Certificate template image
        $color = imagecolorallocate($image, 19, 21, 22); // Text color
        $image_width = imagesx($image);  // Get the image width

        // Generate unique certificate ID (UUID)
        $unique_id = strtoupper(bin2hex(random_bytes(8))); // 16-character UUID
        $text_id = "ID: " . $unique_id;

        // Add name to the center of the certificate
        $size = 40;  // Font size
        $angle = 0;
        $bbox = imagettfbbox($size, $angle, $font, $name);
        $text_width = $bbox[2] - $bbox[0];
        $x = ($image_width - $text_width) / 2;  // Center the text horizontally
        $y = 650;  // Vertical position for the name
        imagettftext($image, $size, $angle, $x, $y, $color, $font, $name);

        // Add the unique ID on the top left corner
        $size_id = 20; 
        $x_id = 50;  // Left offset
        $y_id = 100; // Top offset
        imagettftext($image, $size_id, 0, $x_id, $y_id, $color, $font, $text_id);

        // Save image as JPG
        $file = time();  // Use the current timestamp for the filename
        $image_path = "pdf/".$file.".jpg";
        imagejpeg($image, $image_path);
        imagedestroy($image);

        // Convert image to PDF using FPDF
        require('fpdf.php');
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->Image($image_path, 0, 0, 210, 150);  // Add image to PDF
        $pdf_path = "pdf/".$file.".pdf";
        $pdf->Output("F", $pdf_path);  // Save PDF to file

        // Send the email with the certificate as an attachment
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';
        $mail->Username = 'sazid.bsmru.cse1@gmail.com';  // Your email address
        $mail->Password = 'curdznnekpspmcpz';  // Your email app password

        $mail->setFrom('sazid.bsmru.cse1@gmail.com', 'Sazid Mahmud');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Certificate Generated';
        $mail->Body = "Your certificate is ready! Please find the attached PDF. <br> <b>Certificate ID:</b> $unique_id";

        // Attach the PDF
        if (file_exists($pdf_path)) {
            $mail->addAttachment($pdf_path);
        } else {
            die("Error: PDF not found!");
        }

        // Send the email
        if ($mail->send()) {
            echo "<script>alert('Certificate Approved and Email Sent!'); window.location.href = 'certificaterequest.php';</script>";
        } else {
            echo "<script>alert('Mailer Error: " . $mail->ErrorInfo . "'); window.location.href = 'certificaterequest.php';</script>";
        }

    } elseif (isset($_POST['action']) && $_POST['action'] == 'reject') {
        // রিকুয়েস্ট রিজেক্ট করলে স্ট্যাটাস আপডেট হবে
        $sql = "UPDATE certificaterequest SET status = 'rejected' WHERE studentid = '$studentid' AND requestdate = '$requestdate'";
        $conn->query($sql);

        echo "<script>alert('Request Rejected!'); window.location.href = 'certificaterequest.php';</script>";
    }
}
?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Approval</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../dashboard.css">
</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-light border-end" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 primary-text"> 
                <img src="../image/my pic.png" class="rounded-circle" width="80" alt="Profile Picture">
                <h6>SAZID MAHMUD EMON KHAN</h6>
            </div>
            <div class="list-group list-group-flush">
                <a href="index.php" class="list-group-item list-group-item-action">Dashboard</a>
                <a href="updateprofile.php" class="list-group-item list-group-item-action">Update Profile</a>
                <a href="finalmark.php" class="list-group-item list-group-item-action">Input Final Mark</a>
                <a href="enroll.php" class="list-group-item list-group-item-action">Enroll Course</a>
                <a href="newadminregister.php" class="list-group-item list-group-item-action ">Admin Registration</a>
                <a href="newtearegister.php" class="list-group-item list-group-item-action">Teacher Registration</a>
                <a href="newsturegister.php" class="list-group-item list-group-item-action">Student Registration</a>
                <a href="#" class="list-group-item list-group-item-action active">Certificate Approval</a>
                <a href="resultpublish.php" class="list-group-item list-group-item-action">Result Publish</a>
                <a href="changepass.php" class="list-group-item list-group-item-action">Change Password</a>
                <a href="logouthelper.php" class="list-group-item list-group-item-action">Logout</a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3">
                <div class="container-fluid d-flex align-items-center">
                    <img src="../image/bsmru_logo.png" alt="Logo" width="70" class="me-2">
                    <h5 class="m-0">Bangabandhu Sheikh Mujibur Rahman University, Kishoreganj</h5>
                </div>
            </nav>

            <div class="container mt-4">
    <h3>Certificate Requests</h3>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Serial No.</th>
                <th>Certificate Type</th>
                <th>Student ID</th>
                <th>Request Date</th>
                <th>Cause</th>
                <th style="text-align: center;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            include('../connect.php');
            $serial = 1;
            $requests = $conn->query("SELECT * FROM certificaterequest WHERE status = 'pending' ORDER BY requestdate DESC");
            while ($row = $requests->fetch_assoc()) {
            ?>
                <tr>
                    <th scope="row"><?= $serial++; ?></th>
                    <td><?= htmlspecialchars($row['type']); ?></td>
                    <td><?= htmlspecialchars($row['studentid']); ?></td>
                    <td><?= htmlspecialchars($row['requestdate']); ?></td>
                    <td><?= htmlspecialchars($row['reason']); ?></td>
                    <td style="text-align: center;">
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="studentid" value="<?= htmlspecialchars($row['studentid']); ?>">
                            <input type="hidden" name="requestdate" value="<?= htmlspecialchars($row['requestdate']); ?>">
                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                        </form>
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="studentid" value="<?= htmlspecialchars($row['studentid']); ?>">
                            <input type="hidden" name="requestdate" value="<?= htmlspecialchars($row['requestdate']); ?>">
                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

