<?php
// সর্বপ্রথম include বা require করতে হবে
include('../connect.php'); 

// ডাটাবেস কানেকশন


session_start();

if( !isset($_SESSION['userid']) ){
    header('location: ../Alogin.php');
    exit;
}

$userid = $_SESSION['userid'];

// Fetch admin details if not already in session
if (!isset($_SESSION['fullname']) || !isset($_SESSION['profilepicture'])) {
    $sql = "SELECT profilepicture, fullname FROM admin WHERE userid=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userid); // Ensure userid is an integer
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Store in session
        $_SESSION['fullname'] = $row['fullname'];
        
        // Check if profile picture is available
        if (!empty($row['profilepicture'])) {
            $_SESSION['profilepicture'] = $row['profilepicture']; // Store file path or image data
        } else {
            $_SESSION['profilepicture'] = 'image/default.png'; // Default image
        }
    } else {
        $_SESSION['fullname'] = 'Unknown User';
        $_SESSION['profilepicture'] = 'image/default.png'; // Default image
    }
    $stmt->close();
}

// Set variables for display
$fullname = $_SESSION['fullname'];
$profilePic = $_SESSION['profilepicture'];

// If profile picture is stored as BLOB, display it properly
if (isset($_SESSION['profilepicture']) && !empty($_SESSION['profilepicture'])) {
    // Check if profile picture is stored as BLOB in the database
    if (is_resource($_SESSION['profilepicture'])) {
        // Output image data directly from BLOB
        header("Content-type: image/jpeg"); // or the actual MIME type of the image
        echo $_SESSION['profilepicture']; // Output the BLOB data
        exit;
    }
    // If it is stored as a valid image path
    if (file_exists($_SESSION['profilepicture'])) {
        $profilePic = $_SESSION['profilepicture'];
    } else {
        $profilePic = 'image/default.png'; // Default image if path is not valid
    }
} else {
    $profilePic = 'image/default.png'; // Default image
}

// Fetch admin ID
$adminid = "";
$sql_admin = "SELECT adminid FROM admin WHERE userid = ?";
if ($stmt_admin = $conn->prepare($sql_admin)) {
    $stmt_admin->bind_param("i", $userid);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result(); // Separate $result for admin info

    if ($result_admin->num_rows == 1) {
        $row_admin = $result_admin->fetch_assoc();
        $adminid = $row_admin['adminid'];
    }
    $stmt_admin->close();
} else {
    // Output error if prepare fails
    echo "Error preparing query: " . $conn->error;
}

// PHPMailer লাইব্রেরি এর জন্য `use` স্টেটমেন্ট শীর্ষে রাখুন
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// লাইব্রেরি ফাইলগুলো include করতে হবে
require 'smtp/src/Exception.php';
require 'smtp/src/PHPMailer.php';
require 'smtp/src/SMTP.php';

// বাকি কোড
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $studentid = $_POST['studentid'];  // Student ID
    $type = $_POST['type'];  // Certificate Type
    $action = $_POST['action'];  // Action: approve or reject

    // Get student details
    $sql_student = "SELECT fullname, fathersname, mothersname, village_road, thana_upazilla, district, session FROM student WHERE studentid = ?";
    $stmt = $conn->prepare($sql_student);
    $stmt->bind_param("s", $studentid); // Binding the student ID parameter
    $stmt->execute();
    $result_student = $stmt->get_result();
    if ($result_student->num_rows > 0) {
        $row_student = $result_student->fetch_assoc();
        $name = $row_student['fullname'];
        $father_name = $row_student['fathersname'];
        $mother_name = $row_student['mothersname'];
        $village = $row_student['village_road'];
        $upazilla = $row_student['thana_upazilla'];
        $district = $row_student['district'];
        $session = $row_student['session'];
    } else {
        die("Error fetching student details: " . $conn->error);
    }

    // users টেবিল থেকে email ফেচ করা
    $sql_email = "SELECT email FROM users WHERE userid = (SELECT userid FROM student WHERE studentid = ?)";
    $stmt = $conn->prepare($sql_email);
    $stmt->bind_param("s", $studentid); // Binding the student ID parameter
    $stmt->execute();
    $result_email = $stmt->get_result();
    if ($result_email->num_rows > 0) {
        $row_email = $result_email->fetch_assoc();
        $email = $row_email['email'];
    } else {
        die("Error fetching email: " . $conn->error); // যদি কোনো সমস্যা হয়
    }

    // সার্টিফিকেট জেনারেশন কোড
    $font = "arial.ttf";
    if (!file_exists($font)) {
        die("Font file not found!");
    }

    if (!is_dir("pdf")) {
        mkdir("pdf", 0777, true);
    }

    $image = imagecreatefromjpeg("certificate.jpg");
    $color = imagecolorallocate($image, 19, 21, 22);
    $image_width = imagesx($image);

    // Generate unique certificate ID
    $unique_id = strtoupper(bin2hex(random_bytes(8)));
    $text_id = "ID: " . $unique_id;

    // Name Position
    $size_name = 38;
    $angle = 0;
    $bbox_name = imagettfbbox($size_name, $angle, $font, $name);
    $text_width_name = $bbox_name[2] - $bbox_name[0];
    $x_name = (($image_width - $text_width_name) / 2) - 260;
    $y_name = 860;
    imagettftext($image, $size_name, $angle, $x_name, $y_name, $color, $font, $name);

    // Other Information Positions
    imagettftext($image, 20, 0, 50, 450, $color, $font, $text_id); // Unique ID
    imagettftext($image, 38, 0, 1600, 860, $color, $font, $father_name); // Father Name
    imagettftext($image, 38, 0, 170, 940, $color, $font, $mother_name); // Mother Name
    imagettftext($image, 38, 0, 900, 940, $color, $font, $village); // Village
    imagettftext($image, 38, 0, 1500, 940, $color, $font, $upazilla); // Upazilla
    imagettftext($image, 38, 0, 2100, 940, $color, $font, $district); // District
    imagettftext($image, 38, 0, 2000, 1080, $color, $font, $session); // Session
    imagettftext($image, 38, 0, 700, 1145, $color, $font, $studentid);
    imagettftext($image, 45, 0, 200, 3000, $color, $font, 'This is autometically generated certificate.To check the vality visit our website.');

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
    $pdf_path = "certificate/".$file.".pdf";
    $pdf->Output('F', $pdf_path);  // Save PDF to file (F means save to file)

    // Insert certificate data into the `certificates` table
    if ($action == 'approve') {
        // Get current date for requestdate (if it's not already set)
        $requestdate = date('Y-m-d'); // Current date in YYYY-MM-DD format

        // Add type and requestdate in the insert query
        $sql_insert_cert = "INSERT INTO certificates (studentid, unique_id, type, pdf_path, requestdate) 
                            VALUES (?, ?, ?, ?, ?)";
        
        // Prepared statement to insert data
        $stmt = $conn->prepare($sql_insert_cert);
        $stmt->bind_param("sssss", $studentid, $unique_id, $type, $pdf_path, $requestdate);

        // Execute the query
        if ($stmt->execute()) {
            echo "Certificate inserted successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

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

    // If the action is approve, send the email with the attachment
    if ($action == 'approve') {
        $mail->Subject = 'Certificate Generated';
        $mail->Body = "Your $type is ready! Please find the attached PDF. <br> <b>Certificate ID:</b> $unique_id";

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
    }


    // সার্টিফিকেট একশন প্রক্রিয়া (approve/reject) করার জন্য
if ($action == 'approve') {
    // Get current date for approved date
    $approvedate = date('Y-m-d'); // Current date in YYYY-MM-DD format
    
    // Insert certificate data into the `certificates` table
    $sql_insert_cert = "INSERT INTO certificates (studentid, unique_id, type, pdf_path, requestdate) 
                        VALUES (?, ?, ?, ?, ?)";
    
    // Prepared statement to insert data
    $stmt = $conn->prepare($sql_insert_cert);
    $stmt->bind_param("sssss", $studentid, $unique_id, $type, $pdf_path, $requestdate);

    // Execute the query
    if ($stmt->execute()) {
        // Update status to approved in the certificate request table
        $sql_update_status = "UPDATE certificaterequest SET status = 'approved', approvedate = ? WHERE studentid = ? AND requestdate = ?";
        $stmt_update = $conn->prepare($sql_update_status);
        $stmt_update->bind_param("sss", $approvedate, $studentid, $requestdate);

        if ($stmt_update->execute()) {
            echo "Certificate approved and status updated to 'approved'.";
        } else {
            echo "Error updating status: " . $stmt_update->error;
        }
    } else {
        echo "Error inserting certificate: " . $stmt->error;
    }
}

if ($action == 'reject') {
    // Update status to rejected in the certificate request table
    $sql_update_status = "UPDATE certificaterequest SET status = 'rejected' WHERE studentid = ? AND requestdate = ?";
    $stmt_update = $conn->prepare($sql_update_status);
    $stmt_update->bind_param("ss", $studentid, $requestdate);

    if ($stmt_update->execute()) {
        echo "Certificate request rejected and status updated to 'rejected'.";
    } else {
        echo "Error updating status: " . $stmt_update->error;
    }
}


    // If the action is reject, send a rejection email (without attachment)
    if ($action == 'reject') {
        $mail->Subject = 'Certificate Request Rejected';
        $mail->Body = "Sorry, your $type request has been rejected.";

        // Send rejection email without attachment
        if ($mail->send()) {
            echo "<script>alert('Request Rejected and Rejection Email Sent!'); window.location.href = 'certificaterequest.php';</script>";
        } else {
            echo "<script>alert('Mailer Error: " . $mail->ErrorInfo . "'); window.location.href = 'certificaterequest.php';</script>";
        }
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
            <img src="<?php echo htmlspecialchars($profilePic); ?>" class="rounded-circle" width="90" height="90" alt="Profile Picture">
            <h6><?php echo htmlspecialchars($fullname); ?></h6>
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
        <input type="hidden" name="type" value="<?= htmlspecialchars($row['type']); ?>">
        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
    </form>
    <form method="POST" style="display: inline-block;">
        <input type="hidden" name="studentid" value="<?= htmlspecialchars($row['studentid']); ?>">
        <input type="hidden" name="type" value="<?= htmlspecialchars($row['type']); ?>">
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
