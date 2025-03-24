<?php
include('../connect.php');
session_start();

if (!isset($_SESSION['userid'])) {
    header('location: ../Alogin.php');
    exit;
}

$userid = $_SESSION['userid'];

if (!isset($_SESSION['fullname']) || !isset($_SESSION['profilepicture'])) {
    $sql = "SELECT profilepicture, fullname FROM admin WHERE userid=?";
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

$courses = $conn->query("SELECT courseid FROM courses");
$sessions = $conn->query("SELECT session FROM session_table");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courseid = $_POST['courseid'];
    $session = $_POST['session'];
    $studentids = $_POST['studentid'];
    $semid = $_POST['semid']; // সেম আইডি এখানে আসবে

    if (empty($courseid) || empty($semid) || empty($session) || empty($studentids)) {
        echo "<script>alert('All fields are required!');</script>";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        // Check if the course's semid matches the selected session's semid
        $stmt_check = $conn->prepare("SELECT semid FROM courses WHERE courseid=?");
        $stmt_check->bind_param("s", $courseid);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $course_row = $result_check->fetch_assoc();
            $courseSemid = $course_row['semid'];

            // Now check if the selected session matches the course's semid
            $stmt_session_check = $conn->prepare("SELECT semid FROM session_table WHERE session=?");
            $stmt_session_check->bind_param("s", $session);
            $stmt_session_check->execute();
            $result_session_check = $stmt_session_check->get_result();

            if ($result_session_check->num_rows > 0) {
                $session_row = $result_session_check->fetch_assoc();
                $sessionSemid = $session_row['semid'];

                if ($courseSemid != $sessionSemid) {
                    echo "<script>alert('The selected session does not match the course semester.');window.location.href='stuenroll.php';</script>";
                    exit;
                }
            }
        }

        // If all checks pass, proceed with enrollment
        $stmt_enroll = $conn->prepare("INSERT INTO enrollment (studentid, courseid, session, semid) VALUES (?, ?, ?, ?)");
        $stmt_enroll->bind_param("ssss", $studentid, $courseid, $session, $semid);

        $successCount = 0;
        $failCount = 0;

        foreach ($studentids as $studentid) {
            if ($stmt_enroll->execute()) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        $stmt_enroll->close();

        echo "<script>alert('$successCount students enrolled successfully.'); window.location.href='stuenroll.php';</script>";
        if ($failCount > 0) {
            echo "<script>alert('$failCount enrollments failed.');window.location.href='stuenroll.php';</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Enroll</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../dashboard.css">
</head>
<body>
<div class="d-flex" id="wrapper">
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
            <a href="certificaterequest.php" class="list-group-item list-group-item-action">Crrtificate Approval</a>
            <a href="resultpublish.php" class="list-group-item list-group-item-action">Result Publish</a>
            <a href="changepass.php" class="list-group-item list-group-item-action">Change Password</a>
            <a href="logouthelper.php" class="list-group-item list-group-item-action">Logout</a>
        </div>
    </div>

    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3">
            <div class="container-fluid d-flex align-items-center">
                <img src="../image/bsmru_logo.png" alt="Logo" width="70" class="me-2">
                <h5 class="m-0">Bangabandhu Sheikh Mujibur Rahman University, Kishoreganj</h5>
            </div>
        </nav>

        <div class="container mt-4">
            <div class="row">
                <div class="col-md-8">
                <form method="POST">
        <label for="courseid" class="form-label">Select Course</label>
        <select class="form-select" id="courseid" name="courseid" required>
            <option value="">-- Select Course --</option>
            <?php
            while ($row = $courses->fetch_assoc()) {
                echo "<option value='" . $row['courseid'] . "'>" . $row['courseid'] . "</option>";
            }
            ?>
        </select>

        <label for="session" class="form-label mt-3">Select Session</label>
        <select class="form-select" id="session" name="session" required>
            <option value="">-- Select Session --</option>
            <?php
            while ($row = $sessions->fetch_assoc()) {
                echo "<option value='" . $row['session'] . "'>" . $row['session'] . "</option>";
            }
            ?>
        </select>

        <div id="studentList" class="mt-4">
            <!-- Student list will load here -->
        </div>

        <input type="hidden" name="semid" id="semid" value="">

        <button type="submit" class="btn btn-primary mt-4">Enroll Students</button>
    </form>
                </div>

                <div class="col-md-4">
                    <div class="card shadow text-center">
                        <a href="#" class="btn btn-primary m-3">Course Enroll</a>
                        <a href="upgrade.php" class="btn btn-secondary m-3">Upgrade Semester</a>
                        <a href="nsessadded.php" class="btn btn-secondary m-3">New Session Added</a>
                        <a href="nsemadded.php" class="btn btn-secondary m-3">New Semester Added</a>
                        <a href="ncadded.php" class="btn btn-secondary m-3">New Course Added</a>
                        <a href="#" class="btn btn-secondary m-3">Retake/Improvement Registration</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#courseid').on('change', function() {
        var courseVal = $(this).val();
        if (courseVal !== "") {
            $.ajax({
                url: 'get_semid.php',
                type: 'POST',
                data: { courseid: courseVal },
                success: function(response) {
                    // Set the semid value from the response
                    $('#semid').val(response);
                }
            });
        }
    });
$('#session').on('change', function() {
    var sessionVal = $(this).val();

    if (sessionVal !== "") {
        $.ajax({
            url: 'get_student.php',
            type: 'POST',
            data: { session: sessionVal },
            success: function(response) {
                var data = JSON.parse(response);

                if (data.error) {
                    $('#studentList').html("<div class='alert alert-danger'>" + data.error + "</div>");
                } else {
                    $('#studentList').html(data.students);
                }
            },
            error: function() {
                $('#studentList').html("<div class='alert alert-danger'>Something went wrong. Please try again.</div>");
            }
        });
    } else {
        $('#studentList').html('');
    }
});
</script>

</body>
</html>
