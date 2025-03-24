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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['retakeid'])) {
        $retakeid = intval($_POST['retakeid']);

        if (isset($_POST['approve'])) {
            $updateQuery = "UPDATE retakecourse SET status = 'APPROVED' WHERE retakeid = ?";
        } elseif (isset($_POST['reject'])) {
            $updateQuery = "UPDATE retakecourse SET status = 'REJECTED' WHERE retakeid = ?";
        }

        $stmt_update = $conn->prepare($updateQuery);
        $stmt_update->bind_param("i", $retakeid);
        if ($stmt_update->execute()) {
            echo "<script>alert('Status updated successfully.'); window.location.href='retake.php';</script>";
        } else {
            echo "<script>alert('Error updating status.');</script>";
        }
        $stmt_update->close();
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retake/Improvement Request</title>
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
                    <div class="container mt-4">

<h3 class="mb-4 mt-5">Pending Retake Requests (Admin/Teacher View)</h3>

<table class="table table-bordered table-hover">
<thead class="table-light">
<tr>
<th>Student ID</th>
<th>Course ID</th>
<th>Course Name</th>
<th>Semester</th>
<th>Session</th>

<th>Action</th>
</tr>
</thead>
<tbody>
<?php
// Pending রিকোয়েস্ট ফেচ করো
$query = "SELECT rc.retakeid, rc.studentid, rc.courseid, rc.semid, rc.session, rc.status, c.coursename 
      FROM retakecourse rc 
      JOIN courses c ON rc.courseid = c.courseid 
      WHERE rc.status = 'PENDING' 
      ORDER BY rc.retakeid DESC";
$result = $conn->query($query);

if ($result->num_rows > 0) {
while ($row = $result->fetch_assoc()) {
    $retakeid = $row['retakeid'];
    $studentid = htmlspecialchars($row['studentid']);
    $courseid = htmlspecialchars($row['courseid']);
    $coursename = htmlspecialchars($row['coursename']);
    $semid = htmlspecialchars($row['semid']);
    $session = htmlspecialchars($row['session']);
    $status = htmlspecialchars($row['status']);

    echo "<tr>";
    echo "<td>$studentid</td>";
    echo "<td>$courseid</td>";
    echo "<td>$coursename</td>";
    echo "<td>Semester $semid</td>";
    echo "<td>$session</td>";
    
    echo "<td>
            <form method='post' action='' style='display:inline-block;'>
                <input type='hidden' name='retakeid' value='$retakeid'>
                <button type='submit' name='approve' class='btn btn-success btn-sm'>Approve</button>
            </form>
            <form method='post' action='' style='display:inline-block; margin-left:5px;'>
                <input type='hidden' name='retakeid' value='$retakeid'>
                <button type='submit' name='reject' class='btn btn-danger btn-sm'>Reject</button>
            </form>
        </td>";
    echo "</tr>";
}
} else {
echo "<tr><td colspan='7' class='text-center text-muted'>No pending requests found.</td></tr>";
}
?>
</tbody>
</table>

    
</div>
                    </div>

                    <!-- Right Sidebar (Links) -->
                    <div class="col-md-4">
                        <div class="card shadow text-center"> 
                            <a href="stuenroll.php" class="btn btn-secondary m-3">Course Enroll</a>
                            <a href="upgrade.php" class="btn btn-secondary m-3">Upgrade Semester</a>
                            <a href="nsessadded.php" class="btn btn-secondary m-3">New Session Added</a>
                            <a href="nsemadded.php" class="btn btn-secondary m-3">New Semester Added</a>
                            <a href="ncadded.php" class="btn btn-secondary m-3">New Course Added</a>
                            <a href="retake.php" class="btn btn-primary m-3">Retake/Improvement Registration</a>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
