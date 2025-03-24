<?php
include('../connect.php');
session_start();

if (!isset($_SESSION['userid'])) {
    header('location: Slogin.php');
    exit;
}

// Fetch student details if not already in session
if (!isset($_SESSION['studentid']) || !isset($_SESSION['fullname']) || !isset($_SESSION['profilepicture'])) {
    $userid = $_SESSION['userid'];
    $sql = "SELECT studentid, profilepicture, fullname FROM student WHERE userid=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['studentid'] = $row['studentid']; 
        $_SESSION['fullname'] = $row['fullname'];
        $_SESSION['profilepicture'] = !empty($row['profilepicture']) ? $row['profilepicture'] : 'image/default.png';
    } else {
        $_SESSION['fullname'] = 'Unknown User';
        $_SESSION['profilepicture'] = 'image/default.png';
    }
    $stmt->close();
}


$fullname = $_SESSION['fullname'];
$profilePic = $_SESSION['profilepicture'];
$studentid = $_SESSION['studentid']; // Student ID from session

// Process certificate request form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['certificate_type']) && isset($_POST['reason'])) {
    $requestdate = date('Y-m-d');
    $type = $_POST['certificate_type'];
    $reason = $_POST['reason'];
    
    $check_table = "SHOW TABLES LIKE 'certificaterequest'";
    $table_result = $conn->query($check_table);
    
    if ($table_result->num_rows == 0) {
        $error_message = "Error: Table 'certificaterequest' doesn't exist.";
        echo "<script>alert('$error_message'); window.location.href='certificaterequest.php';</script>";
        exit();
    } else {
        // Check if there is an existing pending request for the same certificate type
        $check_existing = "SELECT * FROM certificaterequest WHERE studentid = '$studentid' AND type = '$type' AND status = 'pending'";
        $existing_request = $conn->query($check_existing);
        
        if ($existing_request->num_rows > 0) {
            $error_message = "You already have a pending request for this certificate type.";
            echo "<script>alert('$error_message'); window.location.href='certificaterequest.php';</script>";
            exit();
        } else {
            // If no existing request, insert the new request
            $insert_sql = "INSERT INTO certificaterequest (studentid, requestdate, type, reason, status) 
                           VALUES ('$studentid', '$requestdate', '$type', '$reason', 'pending')";
            
            if ($conn->query($insert_sql)) {
                $success_message = "Certificate request submitted successfully!";
                echo "<script>alert('$success_message'); window.location.href='certificaterequest.php';</script>";
                exit();
            } else {
                $error_message = "Error submitting request.";
                echo "<script>alert('$error_message'); window.location.href='certificaterequest.php';</script>";
                exit();
            }
        }
    }
}
$fetch_requests = "
    SELECT cr.studentid, cr.requestdate, cr.type AS type, cr.status, 
           CONCAT(cr.studentid, '_', cr.requestdate) AS id, c.pdf_path
    FROM certificaterequest cr
    LEFT JOIN certificates c 
        ON cr.studentid = c.studentid 
        AND DATE(cr.requestdate) = DATE(c.created_at)
        AND cr.type = c.type
    WHERE cr.studentid = '$studentid'
    ORDER BY cr.requestdate DESC
";

$requests = $conn->query($fetch_requests);

if (!$requests) {
    $error_message = "Error fetching requests: " . $conn->error;
    $requests = new class {
        public function fetch_assoc() { return null; }
        public function num_rows() { return 0; }
    };
}

while ($row = $requests->fetch_assoc()) {
    // Check if type is set in the row
    if (isset($row['type']) && !empty($row['type'])) {
        $certificate_type = $row['type'];
    } else {
        $certificate_type = 'Not Available'; // Handle case when type is not available
    }

    // Check if pdf_path is set
    if (isset($row['pdf_path']) && !empty($row['pdf_path'])) {
        $pdf_path = $row['pdf_path'];
    } else {
        $pdf_path = 'Not Available'; // Handle case when pdf_path is not available
    }

    // echo "Certificate Type: " . htmlspecialchars($certificate_type) . "<br>";
    // echo "PDF Path: " . htmlspecialchars($pdf_path) . "<br>";
}
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
        <img src="<?php echo htmlspecialchars($profilePic); ?>" class="rounded-circle" width="90" height="90" alt="Profile Picture">
        <h6><?php echo htmlspecialchars($fullname); ?></h6>
        </div>
        <div class="list-group list-group-flush">
            <a href="index.php" class="list-group-item list-group-item-action">Dashboard</a>
            <a href="updateprofile.php" class="list-group-item list-group-item-action">Update Profile</a>
            <a href="downloadresult.php" class="list-group-item list-group-item-action">Download Result</a>
            <a href="dailyattendance.php" class="list-group-item list-group-item-action">Daily Attendance</a>
            <a href="incourse.php" class="list-group-item list-group-item-action">InCourse Mark</a>
            <a href="certificaterequest.php" class="list-group-item list-group-item-action active">Certificate Application</a>
            <a href="retake.php" class="list-group-item list-group-item-action">Retake/Improvement Course</a>
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
            <?php if (isset($success_message)) { ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success_message; ?>
                </div>
            <?php } ?>
            <?php if (isset($error_message)) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php } ?>

            <form method="POST">
                <h5>Certificate Type:</h5>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="certificate_type" value="Attestation Certificate" id="attestation" required>
                    <label class="form-check-label" for="attestation">
                        Attestation Certificate
                    </label>
                </div>
                <!-- <div class="form-check">
                    <input class="form-check-input" type="radio" name="certificate_type" value="Testimonial Certificate" id="testimonial">
                    <label class="form-check-label" for="testimonial">
                        Testimonial Certificate
                    </label>
                </div><br> -->

                <div class="mb-3">
                    <textarea class="form-control" name="reason" rows="3" placeholder="Write the reason for requesting the certificate..." required></textarea>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary w-50">Send Request</button>
                </div>
            </form>
        </div>

        <!-- Previous Requests Table -->
        

    </div>
</div>
<div class="text-center mt-4">
            <a href="previous_requests.php" class="btn btn-secondary">View Previous Requests</a>
        </div>
    


    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
