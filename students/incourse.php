<?php
include('../connect.php');
session_start();

if (!isset($_SESSION['userid'])) {
    header('location: Slogin.php');
    exit;
}

$userid = $_SESSION['userid'];


if (!isset($_SESSION['fullname']) || !isset($_SESSION['profilepicture'])) {
    // Fetch student details if not already in session
    $sql = "SELECT profilepicture, fullname FROM student WHERE userid=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Store in session
        $_SESSION['fullname'] = $row['fullname'];
        $_SESSION['profilepicture'] = !empty($row['profilepicture']) ? $row['profilepicture'] : 'image/default.png';
    } else {
        $_SESSION['fullname'] = 'Unknown User';
        $_SESSION['profilepicture'] = 'image/default.png'; // Default image
    }
    $stmt->close();
}

// Set variables for display
$fullname = $_SESSION['fullname'];
$profilePic = $_SESSION['profilepicture'];





// Fetch student ID and session
$studentid = "";
$sql_student = "SELECT studentid, session FROM student WHERE userid = ?";
$stmt_student = $conn->prepare($sql_student);
$stmt_student->bind_param("i", $userid);
$stmt_student->execute();
$result_student = $stmt_student->get_result();

if ($result_student->num_rows == 1) {
    $row_student = $result_student->fetch_assoc();
    $studentid = $row_student['studentid'];
    $session = $row_student['session'];
}
$stmt_student->close();

// Fetch enrolled courses
$sql_courses = "
    SELECT c.courseid, c.coursename 
    FROM courses c
    JOIN enrollment e ON c.courseid = e.courseid
    WHERE e.studentid = ?
";
$stmt_courses = $conn->prepare($sql_courses);
$stmt_courses->bind_param("s", $studentid);
$stmt_courses->execute();
$result_courses = $stmt_courses->get_result();

// Fetch attendance
$sql_attendance = "SELECT courseid, percentage FROM attendance WHERE studentid = ?";
$stmt_attendance = $conn->prepare($sql_attendance);
$stmt_attendance->bind_param("s", $studentid);
$stmt_attendance->execute();
$result_attendance = $stmt_attendance->get_result();
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InCourse Mark</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="update.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    

</head>

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
                <a href="downloadresult.php" class="list-group-item list-group-item-action  ">Download Result</a>
                <a href="dailyattendance.php" class="list-group-item list-group-item-action">Daily Attendance</a>
                <a href="incourse.php" class="list-group-item list-group-item-action active">InCourse Mark</a>
                <a href="certificaterequest.php" class="list-group-item list-group-item-action">Certificate Application</a>
                <a href="retake.php" class="list-group-item list-group-item-action">Retake/Improvement Course</a>
                <a href="changepass.php" class="list-group-item list-group-item-action">Change Password</a>
                <a href="logouthelper.php" class="list-group-item list-group-item-action">Logout</a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav  class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3">
                <div class="container-fluid d-flex align-items-center" >
                    <img src="../image/bsmru_logo.png" alt="Logo" width="70" class="me-2">
                    <h5 class="m-0">Bangabandhu Sheikh Mujibur Rahman University, Kishoreganj</h5>
                </div>
            </nav>

            <div class="container mt-4">
    <div class="col-md-12" style="margin-top:40px;">
    <div class="card shadow p-3">
            <h4 class="text-center">InCourse Mark</h4>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>First Incourse</th>
                            <th>Second Incourse</th>
                            <th>Average</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $seen_courses = []; // ডুপ্লিকেট চেকের জন্য অ্যারে

                        while ($row_course = $result_courses->fetch_assoc()) { 
                            $courseid = $row_course['courseid'];
                            $coursename = $row_course['coursename'];

                            // যদি এই কোর্স আগে প্রিন্ট হয়ে থাকে, তাহলে স্কিপ করুন
                            if (in_array($courseid, $seen_courses)) {
                                continue;
                            }
                            $seen_courses[] = $courseid; // নতুন কোর্স ট্র্যাক করুন

                            $sql_marks = "SELECT finmark, sinmark, intmark FROM incousemark WHERE courseid = ? AND studentid = ?";
                            $stmt_marks = $conn->prepare($sql_marks);
                            $stmt_marks->bind_param("ss", $courseid, $studentid);
                            $stmt_marks->execute();
                            $result_marks = $stmt_marks->get_result();
                            
                            if ($result_marks->num_rows == 1) {
                                $row_marks = $result_marks->fetch_assoc();
                                $finmark = $row_marks['finmark'];
                                $sinmark = $row_marks['sinmark'];
                                $avgmark = $row_marks['intmark'];
                            } else {
                                $finmark = "N/A";
                                $sinmark = "N/A";
                                $avgmark = "N/A";
                            }
                            $stmt_marks->close();
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($coursename); ?></td>
                                <td><?php echo htmlspecialchars($finmark); ?></td>
                                <td><?php echo htmlspecialchars($sinmark); ?></td>
                                <td><?php echo htmlspecialchars($avgmark); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
        </div>
    </div>
</div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
