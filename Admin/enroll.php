<?php
include('../connect.php');

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




// Fetch teacher IDs
$teachers = $conn->query("SELECT teacherid FROM teacher");

// Fetch courses
$courses = $conn->query("SELECT courseid FROM courses");

// Fetch semesters
$semesters = $conn->query("SELECT semid FROM session_table");

// Fetch sessions
$sessions = $conn->query("SELECT session FROM session_table");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacherid = $_POST['teacherid'];
    $courseid = $_POST['courseid'];
    $semid = $_POST['semid'];
    $session = $_POST['session'];

    // Validate input
    if (empty($teacherid) || empty($courseid) || empty($semid) || empty($session)) {
        echo "<script>alert('All fields are required!');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO tenrollment (teacherid, courseid, semid, session) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $teacherid, $courseid, $semid, $session);
        
        if ($stmt->execute()) {
            echo "<script>alert('Enrollment successful!'); window.location.href='enroll.php';</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
        
        $stmt->close();
    }
}

?>








<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Enroll</title>
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
                <a href="#" class="list-group-item list-group-item-action active">Enroll Course</a>
                <a href="newadminregister.php" class="list-group-item list-group-item-action ">Admin Registration</a>
                <a href="newtearegister.php" class="list-group-item list-group-item-action">Teacher Registration</a>
                <a href="newsturegister.php" class="list-group-item list-group-item-action">Student Registration</a>
                <a href="certificaterequest.php" class="list-group-item list-group-item-action">Crrtificate Approval</a>
                <a href="resultpublish.php" class="list-group-item list-group-item-action">Result Publish</a>
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
            <div class="card p-4 shadow-lg">
        <h2 class="text-center mb-4">Teacher Enrollment</h2>
        <form method="post" action="">
            <div class="mb-3">
                <label class="form-label">Teacher ID:</label>
                <select name="teacherid" class="form-select" required>
                    <option value="">Select Teacher</option>
                    <?php while ($row = $teachers->fetch_assoc()) { ?>
                        <option value="<?php echo $row['teacherid']; ?>"><?php echo $row['teacherid']; ?></option>
                    <?php } ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Course ID:</label>
                <select name="courseid" class="form-select" required>
                    <option value="">Select Course</option>
                    <?php while ($row = $courses->fetch_assoc()) { ?>
                        <option value="<?php echo $row['courseid']; ?>"><?php echo $row['courseid']; ?></option>
                    <?php } ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Semester ID:</label>
                <select name="semid" class="form-select" required>
                    <option value="">Select Semester</option>
                    <?php while ($row = $semesters->fetch_assoc()) { ?>
                        <option value="<?php echo $row['semid']; ?>"><?php echo $row['semid']; ?></option>
                    <?php } ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Session:</label>
                <select name="session" class="form-select" required>
                    <option value="">Select Session</option>
                    <?php while ($row = $sessions->fetch_assoc()) { ?>
                        <option value="<?php echo $row['session']; ?>"><?php echo $row['session']; ?></option>
                    <?php } ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Enroll</button>
        </form>
    </div>

    
    
            </div>

        </div>
    </div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
