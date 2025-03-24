<?php
include('../connect.php');
session_start();

// যদি ইউজার লগইন না করে থাকে, তাহলে লগইন পেইজে পাঠাও
if (!isset($_SESSION['userid'])) {
    header('location: Slogin.php');
    exit;
}

$userid = $_SESSION['userid'];

// প্রোফাইল ইনফো না থাকলে সেট করো
if (!isset($_SESSION['fullname']) || !isset($_SESSION['profilepicture'])) {
    $sql = "SELECT profilepicture, fullname FROM student WHERE userid=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
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

// ডায়নামিক studentid বের করো
$studentid = '';
$stu_query = "SELECT studentid FROM student WHERE userid = ?";
$stmt_stu = $conn->prepare($stu_query);
$stmt_stu->bind_param("i", $userid);
$stmt_stu->execute();
$result_stu = $stmt_stu->get_result();
if ($result_stu->num_rows == 1) {
    $row_stu = $result_stu->fetch_assoc();
    $studentid = $row_stu['studentid'];
} else {
    die("Student ID Not Found");
}
$stmt_stu->close();

$selectedSemid = isset($_POST['semid']) ? intval($_POST['semid']) : '';
$courses = [];

// সেমিস্টার অনুযায়ী ফেল করা কোর্স বের করো
if (!empty($selectedSemid)) {
    $courseListQuery = "SELECT courseid, coursename FROM courses WHERE semid = ?";
    $stmt_list = $conn->prepare($courseListQuery);
    $stmt_list->bind_param("i", $selectedSemid);
    $stmt_list->execute();
    $result_list = $stmt_list->get_result();
    while ($row = $result_list->fetch_assoc()) {
        $courseid = $row['courseid'];
        $coursename = $row['coursename'];

        $checkFailQuery = "SELECT totalmark FROM finalmark WHERE studentid = ? AND courseid = ? AND totalmark < 54";
        $stmt_check = $conn->prepare($checkFailQuery);
        $stmt_check->bind_param("ss", $studentid, $courseid);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) {
            $courses[] = ['courseid' => $courseid, 'coursename' => $coursename];
        }
        $stmt_check->close();
    }
    $stmt_list->close();
}

// সেমিস্টার লিস্ট বের করো
$sql_sem = "SELECT DISTINCT semid FROM courses WHERE semid IS NOT NULL ORDER BY semid ASC";
$result_sem = $conn->query($sql_sem);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $courseid = $_POST['courseid'] ?? '';
    $session_input = $_POST['session'] ?? '';

    if (empty($courseid) || empty($session_input) || empty($selectedSemid)) {
        die("Incomplete form data. CourseID: $courseid, Session: $session_input, SemID: $selectedSemid");
    }

    $checkQuery = "SELECT * FROM retakecourse WHERE studentid = ? AND courseid = ? AND session = ?";
    $stmt_check = $conn->prepare($checkQuery);
    $stmt_check->bind_param("sss", $studentid, $courseid, $session_input);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        echo "<script>alert('তুমি ইতোমধ্যে এই কোর্সের রিটেক রিকোয়েস্ট দিয়েছো।');window.location.href='retake.php';</script>";
    } else {
        $insertQuery = "INSERT INTO retakecourse (studentid, courseid, semid, session) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($insertQuery);
        if (!$stmt_insert) {
            die("Prepare failed: " . $conn->error . " Query: " . $insertQuery);
        }

        $stmt_insert->bind_param("ssis", $studentid, $courseid, $selectedSemid, $session_input);
        if ($stmt_insert->execute()) {
            echo "<script>alert('Retake Request Submitted Successfully');window.location.href='retake.php';</script>";
        } else {
            echo "<script>alert('Error!Try again');window.location.href='retake.php';</script>";
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
}

?>

<!-- HTML PART -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Retake/Improvement Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../dashboard.css">
</head>
<body>
<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="bg-light border-end" id="sidebar-wrapper">
        <div class="sidebar-heading text-center py-4">
            <img src="<?php echo htmlspecialchars($profilePic); ?>" class="rounded-circle" width="90" height="90" alt="Profile Picture">
            <h6><?php echo htmlspecialchars($fullname); ?></h6>
        </div>
        <div class="list-group list-group-flush">
            <a href="index.php" class="list-group-item">Dashboard</a>
            <a href="updateprofile.php" class="list-group-item">Update Profile</a>
            <a href="downloadresult.php" class="list-group-item">Download Result</a>
            <a href="dailyattendance.php" class="list-group-item">Daily Attendance</a>
            <a href="incourse.php" class="list-group-item ">InCourse Mark</a>
            <a href="certificaterequest.php" class="list-group-item">Certificate Application</a>
            <a href="retake.php" class="list-group-item list-group-item-action active">Retake/Improvement Course</a>
            <a href="changepass.php" class="list-group-item">Change Password</a>
            <a href="logouthelper.php" class="list-group-item">Logout</a>
        </div>
    </div>

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-light bg-white border-bottom px-3">
            <div class="container-fluid d-flex align-items-center">
                <img src="../image/bsmru_logo.png" alt="Logo" width="70" class="me-2">
                <h5 class="m-0">Bangabandhu Sheikh Mujibur Rahman University, Kishoreganj</h5>
            </div>
        </nav>

        <div class="container mt-5">
    <h2 class="mb-4">Retake/Improvement Request Form</h2>

    <!-- Semester Selection -->
    <form method="post" action="">
        <div class="mb-3">
            <label for="semid" class="form-label">Select Semester</label>
            <select class="form-select" id="semid" name="semid" onchange="this.form.submit()" required>
                <option value="">Select</option>
                <?php while ($row = $result_sem->fetch_assoc()) { ?>
                    <option value="<?php echo $row['semid']; ?>" <?php if ($selectedSemid == $row['semid']) echo 'selected'; ?>>
                        <?php echo 'Semester ' . $row['semid']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </form>

    <!-- Retake Form -->
    <?php if (!empty($courses)) { ?>
        <form method="post" action="">
            <!-- এখানে semid পাঠানো হচ্ছে -->
            <input type="hidden" name="semid" value="<?php echo $selectedSemid; ?>">

            <div class="mb-3">
                <label for="courseid" class="form-label">Select Your Retake/Improve Course</label>
                <select class="form-select" id="courseid" name="courseid" required onchange="fetchSession()">
                    <option value="">Select Your Course</option>
                    <?php foreach ($courses as $course) { ?>
                        <option value="<?php echo htmlspecialchars($course['courseid']); ?>">
                            <?php echo htmlspecialchars($course['courseid'] . ' - ' . $course['coursename']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="session" class="form-label">Session</label>
                <input type="text" class="form-control" id="session" name="session" readonly required>
            </div>

            <button type="submit" name="submit" class="btn btn-primary">Submit Your Request</button>
        </form>
    <?php } elseif (!empty($selectedSemid)) { ?>
        <div class="alert alert-warning mt-4">এই সেমিস্টারে তোমার কোনো রিটেক/ইম্প্রুভমেন্ট কোর্স নেই।</div>
    <?php } ?>
        </div>
        <hr class="my-5">
<h3 class="mb-4">Your Retake/Improvement Requests</h3>

<table class="table table-bordered table-hover">
    <thead class="table-light">
        <tr>
            <th>Course ID</th>
            <th>Course Name</th>
            <th>Semester</th>
            <th>Session</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // রিটেক রিকোয়েস্ট লোড করো
        $query = "SELECT rc.courseid, rc.semid, rc.session, rc.status, c.coursename 
                  FROM retakecourse rc 
                  JOIN courses c ON rc.courseid = c.courseid 
                  WHERE rc.studentid = ? 
                  ORDER BY rc.semid ASC";
        $stmt_retake = $conn->prepare($query);
        $stmt_retake->bind_param("s", $studentid);
        $stmt_retake->execute();
        $result_retake = $stmt_retake->get_result();

        if ($result_retake->num_rows > 0) {
            while ($row = $result_retake->fetch_assoc()) {
                $courseid = htmlspecialchars($row['courseid']);
                $coursename = htmlspecialchars($row['coursename']);
                $semid = htmlspecialchars($row['semid']);
                $session_text = htmlspecialchars($row['session']);
                $status = htmlspecialchars($row['status']);
        
                echo "<tr>";
                echo "<td>$courseid</td>";
                echo "<td>$coursename</td>";
                echo "<td>Semester $semid</td>";
                echo "<td>$session_text</td>";
                echo "<td>$status</td>";
        
                // স্ট্যাটাস চেক করে পেমেন্ট অপশন দেখাও
                if ($status === 'APPROVED') {
                    echo "<td><a href='payment.php?courseid=$courseid&semid=$semid' class='btn btn-success btn-sm'>Pay Now</a></td>";
                } elseif ($status === 'PENDING') {
                    echo "<td><span class='text-warning'>Awaiting Approval</span></td>";
                } elseif ($status === 'REJECTED') {
                    echo "<td><span class='text-danger'>Rejected</span></td>";
                } else {
                    echo "<td>-</td>";
                }
        
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6' class='text-center text-muted'>No retake requests found.</td></tr>";
        }
        

        $stmt_retake->close();
        ?>
    </tbody>
</table>


    </div>
</div>

<script>
function fetchSession() {
    const courseid = document.getElementById("courseid").value;
    if (courseid) {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "get_course_session.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (xhr.status === 200) {
                document.getElementById("session").value = xhr.responseText;
            }
        };
        xhr.send("courseid=" + encodeURIComponent(courseid));
    } else {
        document.getElementById("session").value = "";
    }
}
</script>
</body>
</html>
