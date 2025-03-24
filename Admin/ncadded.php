<?php
include('../connect.php');
session_start();

if (!isset($_SESSION['userid'])) {
    header('location: ../Alogin.php');
    exit;
}

$userid = $_SESSION['userid'];

// Fetch admin details if not already in session
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

// Set variables for display
$fullname = $_SESSION['fullname'];
$profilePic = $_SESSION['profilepicture'];

// Set default profile picture if necessary
if (isset($_SESSION['profilepicture']) && !empty($_SESSION['profilepicture'])) {
    if (is_resource($_SESSION['profilepicture'])) {
        header("Content-type: image/jpeg");
        echo $_SESSION['profilepicture'];
        exit;
    }
    if (file_exists($_SESSION['profilepicture'])) {
        $profilePic = $_SESSION['profilepicture'];
    } else {
        $profilePic = 'image/default.png';
    }
} else {
    $profilePic = 'image/default.png';
}

$message = "";

// Fetch available sessions from session_table
$sessions = [];
$sql_sessions = "SELECT session FROM session_table";
$stmt_sessions = $conn->prepare($sql_sessions);
$stmt_sessions->execute();
$result_sessions = $stmt_sessions->get_result();

if ($result_sessions->num_rows > 0) {
    while ($row = $result_sessions->fetch_assoc()) {
        $sessions[] = $row['session'];
    }
}

$stmt_sessions->close();

// Fetch the semid for the selected session
$semid = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['session'])) {
    $session = trim($_POST['session']);

    // Get the semid for the selected session
    if (!empty($session)) {
        $sql = "SELECT semid FROM semester WHERE session = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $session);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $semid = $row['semid']; // Display the current semid
        } else {
            $message = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                            No semid found for the selected session.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
        }
        $stmt->close();
    }
}

// Insert new course into database
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['courseid'], $_POST['coursename'], $_POST['coursecredit'], $_POST['semid'])) {
    $courseid = trim($_POST['courseid']);
    $coursename = trim($_POST['coursename']);
    $coursecredit = trim($_POST['coursecredit']);
    $semid = trim($_POST['semid']);

    if (!empty($courseid) && !empty($coursename) && !empty($coursecredit) && !empty($semid)) {
        // Prepare the query to insert the new course
        $sql_insert = "INSERT INTO courses (courseid, coursename, coursecredit, semid) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("sssi", $courseid, $coursename, $coursecredit, $semid);

        if ($stmt_insert->execute()) {
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            New course added successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
        } else {
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Error adding course: ' . $stmt_insert->error . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
        }
        $stmt_insert->close();
    } else {
        $message = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Please fill in all fields.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Course Added</title>
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
                <div class="row">
                    <!-- Left Section (Course Addition Form) -->
                    <div class="col-md-8">
                        <?php echo $message; ?>
                        <div class="card shadow p-4">
                            <h3 class="text-center mb-4">Add New Course</h3>
                            <form method="post">
                                <div class="mb-3">
                                    <label for="courseid" class="form-label">Course ID:</label>
                                    <input type="text" name="courseid" id="courseid" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="coursename" class="form-label">Course Name:</label>
                                    <input type="text" name="coursename" id="coursename" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="coursecredit" class="form-label">Course Credit:</label>
                                    <input type="text" name="coursecredit" id="coursecredit" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label for="semid" class="form-label">Semester ID:</label>
                                    <input type="text" name="semid" id="semid" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Add Course</button>
                            </form>
                        </div>
                    </div>

                    <!-- Right Sidebar (Links) -->
                    <div class="col-md-4">
                        <div class="card shadow text-center"> 
                            <a href="stuenroll.php" class="btn btn-secondary m-3">Course Enroll</a>
                            <a href="upgrade.php" class="btn btn-secondary m-3">Upgrade Semester</a>
                            <a href="nsessadded.php" class="btn btn-secondary m-3">New Session Added</a>
                            <a href="nsemadded.php" class="btn btn-secondary m-3">New Semester Added</a>
                            <a href="ncadded.php" class="btn btn-primary m-3">New Course Added</a>
                            <a href="retake.php" class="btn btn-secondary m-3">Retake/Improvement Registration</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
