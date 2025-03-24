<?php
include('../connect.php');
session_start();

// সেশন চেক
if (!isset($_SESSION['userid'])) {
    header('location: ../Alogin.php');
    exit;
}

$userid = $_SESSION['userid'];

// সেশন থেকে তথ্য লোড
if (!isset($_SESSION['fullname']) || !isset($_SESSION['profilepicture'])) {
    $sql = "SELECT profilepicture, fullname FROM admin WHERE userid=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['fullname'] = $row['fullname'];
        $_SESSION['profilepicture'] = $row['profilepicture'] ?: 'image/default.png';
    } else {
        $_SESSION['fullname'] = 'Unknown User';
        $_SESSION['profilepicture'] = 'image/default.png';
    }
    $stmt->close();
}

$fullname = $_SESSION['fullname'];
$profilePic = $_SESSION['profilepicture'];

// সেশন ও সেমিস্টার লোড
$sessionQuery = "SELECT DISTINCT session FROM enrollment ORDER BY session DESC";
$sessionResult = $conn->query($sessionQuery);

$semesterQuery = "SELECT DISTINCT semid FROM enrollment ORDER BY semid ASC";
$semesterResult = $conn->query($semesterQuery);

// ফর্ম সাবমিট হলে রেজাল্ট পাবলিশ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['publish'])) {
    $session = $_POST['session'];
    $semid = $_POST['semid'];

    // Step 1: সেশন অনুযায়ী স্টুডেন্ট আইডি বের করা
    $query = "SELECT DISTINCT f.studentid 
              FROM finalmark f 
              JOIN courses c ON f.courseid = c.courseid 
              WHERE f.session = ? AND c.semid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $session, $semid);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $studentid = $row['studentid'];

        // স্টুডেন্টের নাম বের করা
        $nameQuery = "SELECT fullname FROM student WHERE studentid=?";
        $nameStmt = $conn->prepare($nameQuery);
        $nameStmt->bind_param("s", $studentid);
        $nameStmt->execute();
        $nameResult = $nameStmt->get_result();
        $nameRow = $nameResult->fetch_assoc();
        $stuname = $nameRow['fullname'] ?? 'Unknown';
        $nameStmt->close();

        // মার্ক ও কোর্স ক্রেডিট বের করা
        $gradeQuery = "SELECT f.courseid, f.totalmark, c.coursecredit 
                       FROM finalmark f 
                       JOIN courses c ON f.courseid = c.courseid 
                       WHERE f.studentid = ? AND f.session = ? AND c.semid = ?";
        $gradeStmt = $conn->prepare($gradeQuery);
        $gradeStmt->bind_param("ssi", $studentid, $session, $semid);
        $gradeStmt->execute();
        $gradeResult = $gradeStmt->get_result();

        $total_credit = 0;
        $total_gpa_credit = 0;
        $gradeDetails = "";

        while ($gradeRow = $gradeResult->fetch_assoc()) {
            $marks = $gradeRow['totalmark'];
            $credit = $gradeRow['coursecredit'];
            $grade_name = "";
            $gpa_credit = 0;

            // গ্রেড নির্ধারণ
            if ($marks >= 80) {
                $grade_name = "A+";
                $gpa = 4.00;
            } elseif ($marks >= 75) {
                $grade_name = "A";
                $gpa = 3.75;
            } elseif ($marks >= 70) {
                $grade_name = "A-";
                $gpa = 3.50;
            } elseif ($marks >= 65) {
                $grade_name = "B+";
                $gpa = 3.25;
            } elseif ($marks >= 60) {
                $grade_name = "B";
                $gpa = 3.00;
            } elseif ($marks >= 55) {
                $grade_name = "B-";
                $gpa = 2.75;
            } elseif ($marks >= 50) {
                $grade_name = "C+";
                $gpa = 2.50;
            } elseif ($marks >= 45) {
                $grade_name = "C";
                $gpa = 2.25;
            } elseif ($marks >= 40) {
                $grade_name = "D";
                $gpa = 2.00;
            } else {
                $grade_name = "F";
                $gpa = 0.00;
            }

            $gpa_credit = $credit * $gpa;

            $total_credit += $credit;
            $total_gpa_credit += $gpa_credit;

            // গ্রেড বিস্তারিত যুক্ত করা
            $gradeDetails .= "Course ID: {$gradeRow['courseid']}, Grade: $grade_name\n";
        }

        $gradeStmt->close();

        // CGPA ক্যালকুলেশন
        $cgpa = $total_credit > 0 ? round($total_gpa_credit / $total_credit, 2) : 0.00;

        // CGPA টেবিলে ইনসার্ট
        $updateCgpaQuery = "INSERT INTO cgpa (studentid, semid, total_credit, total_gained_credit, total_gpa_credit, cgpa) 
                            VALUES (?, ?, ?, ?, ?, ?)";
        $updateCgpaStmt = $conn->prepare($updateCgpaQuery);
        $updateCgpaStmt->bind_param("siiddd", $studentid, $semid, $total_credit, $total_gpa_credit, $total_gpa_credit, $cgpa);
        $updateCgpaStmt->execute();
        $updateCgpaStmt->close();

        // রেজাল্ট হিস্ট্রি টেবিলে ইনসার্ট
        $reshisQuery = "INSERT INTO reshis (cgpa, adminid, studentid, date) VALUES (?, ?, ?, ?)";
        $reshisStmt = $conn->prepare($reshisQuery);
        $date = date("Y-m-d H:i:s");
        $reshisStmt->bind_param("diss", $cgpa, $userid, $studentid, $date);
        $reshisStmt->execute();
        $reshisStmt->close();

        // PDF তৈরি
        require_once('tcpdf.php');
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Write(0, 'CGPA Report for Student ID: ' . $studentid, '', 0, 'L', true);
        $pdf->Write(0, 'Student Name: ' . $stuname, '', 0, 'L', true);
       
        $pdf->Write(0, 'Session: ' . $session, '', 0, 'L', true); // সেশন প্রিন্ট করা
        $pdf->Write(0, 'Semester ID: ' . $semid, '', 0, 'L', true); // সেমিস্টার আইডি প্রিন্ট করা
        $pdf->Write(0, 'CGPA: ' . $cgpa, '', 0, 'L', true);
        $pdf->Write(0, "Grade Details-\n$gradeDetails", '', 0, 'L', true);
        $pdf->Write(0, 'Generated by Admin: ' . $fullname, '', 0, 'L', true);

        if (!file_exists('../result')) {
            mkdir('../result', 0777, true);
        }

        $filePath = realpath('../') . DIRECTORY_SEPARATOR . 'result' . DIRECTORY_SEPARATOR . $studentid . '_cgpa.pdf';
        $pdf->Output($filePath, 'F');

        // ফাইল পাথ আপডেট
        $updateFilePathQuery = "UPDATE cgpa SET file_path = ? WHERE studentid = ? AND semid = ?";
        $updateFilePathStmt = $conn->prepare($updateFilePathQuery);
        $updateFilePathStmt->bind_param("ssi", $filePath, $studentid, $semid);
        $updateFilePathStmt->execute();
        $updateFilePathStmt->close();
    }

    $stmt->close();
    $conn->close();

    echo "<script>
        alert('Result Published for Session: $session, Semester: $semid');
        window.location.href = document.referrer;
      </script>";
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Publish</title>
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
                <a href="newadminregister.php" class="list-group-item list-group-item-action">Admin Registration</a>
                <a href="newtearegister.php" class="list-group-item list-group-item-action">Teacher Registration</a>
                <a href="newsturegister.php" class="list-group-item list-group-item-action">Student Registration</a>
                <a href="certificaterequest.php" class="list-group-item list-group-item-action">Certificate Approval</a>
                <a href="#" class="list-group-item list-group-item-action active">Result Publish</a>
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
                <div class="card shadow-lg p-4">
                    <h3 class="text-center mb-4">Publish Results</h3>
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="session" class="form-label">Select Session</label>
                            <select class="form-select" id="session" name="session" required>
                                <option value="">-- Select Session --</option>
                                <?php while ($row = $sessionResult->fetch_assoc()): ?>
                                    <option value="<?= $row['session']; ?>"><?= $row['session']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="semid" class="form-label">Select Semester</label>
                            <select class="form-select" id="semid" name="semid" required>
                                <option value="">-- Select Semester --</option>
                                <?php while ($row = $semesterResult->fetch_assoc()): ?>
                                    <option value="<?= $row['semid']; ?>"><?= $row['semid']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary" name="publish">Publish</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
