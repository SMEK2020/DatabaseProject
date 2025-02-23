<?php
include('../config.php'); // Database Connection File
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header('location: Slogin.php');
    exit;
}

$student_id = $_SESSION['id']; // Current logged-in student ID

// Handle Certificate Request Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $certificate_type = $_POST['certificate_type'];
    $reason = $_POST['reason'];

    // Check if a pending request already exists
    $checkQuery = $conn->prepare("SELECT * FROM certificate_requests WHERE student_id = ? AND status = 'Pending'");
    $checkQuery->bind_param("i", $student_id);
    $checkQuery->execute();
    $result = $checkQuery->get_result();

    if ($result->num_rows == 0) {
        // Insert new request only if no pending request exists
        $stmt = $conn->prepare("INSERT INTO certificate_requests (student_id, certificate_type, reason, status, request_date) VALUES (?, ?, ?, 'Pending', NOW())");

        if ($stmt === false) {
            die("SQL Error: " . $conn->error);
        }

        $stmt->bind_param("iss", $student_id, $certificate_type, $reason);

        if ($stmt->execute()) {
            $success_message = "Certificate request submitted successfully!";
        } else {
            $error_message = "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $error_message = "You already have a pending request. Wait for approval or rejection before submitting another.";
    }
}

// Fetch previous certificate requests
$result = $conn->prepare("SELECT id, certificate_type, status, request_date FROM certificate_requests WHERE student_id = ?");
$result->bind_param("i", $student_id);
$result->execute();
$requests = $result->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Request</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="update.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
            <a href="downloadresult.php" class="list-group-item list-group-item-action">Download Result</a>
            <a href="dailyattendance.php" class="list-group-item list-group-item-action">Daily Attendance</a>
            <a href="incourse.php" class="list-group-item list-group-item-action">InCourse Mark</a>
            <a href="certificaterequest.php" class="list-group-item list-group-item-action active">Certificate Application</a>
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
            <div class="container">
                <div class="form-container">
                    <h2 class="text-center">Certificate Request</h2>

                    <!-- Success/Error Messages -->
                    <?php if (isset($success_message)) echo "<div class='alert alert-success'>$success_message</div>"; ?>
                    <?php if (isset($error_message)) echo "<div class='alert alert-danger'>$error_message</div>"; ?>

                    <form method="POST">
                        <h5>Certificate Type:</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="certificate_type" value="Attestation Certificate" id="attestation" required>
                            <label class="form-check-label" for="attestation">
                                Attestation Certificate
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="certificate_type" value="Testimonial Certificate" id="testimonial">
                            <label class="form-check-label" for="testimonial">
                                Testimonial Certificate
                            </label>
                        </div><br>

                        <div class="mb-3">
                            <textarea class="form-control" name="reason" rows="3" placeholder="Write the reason for requesting the certificate..." required></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary w-50">Send Request</button>
                        </div>
                    </form>
                </div>

                <!-- Previous Requests Table -->
                <h3 class="mt-5">Your Previous Requests</h3>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Serial No.</th>
                            <th>Certificate Type</th>
                            <th>Status</th>
                            <th>Request Date</th>
                            <th style="text-align: center;">Download</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $serial = 1; while ($row = $requests->fetch_assoc()) { ?>
                            <tr>
                                <th scope="row"><?= $serial++; ?></th>
                                <td><?= $row['certificate_type']; ?></td>
                                <td><?= $row['status']; ?></td>
                                <td><?= $row['request_date']; ?></td>
                                <td style="text-align: center;">
                                    <?php if ($row['status'] == 'Approved') { ?>
                                        <a href="download_certificate.php?id=<?= $row['id']; ?>" class="btn btn-success btn-sm">
                                            <i class="fa-solid fa-download"></i> Download
                                        </a>
                                    <?php } else { ?>
                                        <span class="text-muted">Not Available</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
