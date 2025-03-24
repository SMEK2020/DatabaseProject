
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $session_name = trim($_POST['session']);

    if (!empty($session_name)) {
        // Prepare the SQL statement
        $sql = "INSERT INTO session_table (session) VALUES (?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $session_name);

            if ($stmt->execute()) {
                // Redirect using JavaScript
                echo "<script>
                        alert('New session added successfully!');
                        window.location.href = 'nsessadded.php';
                      </script>";
                exit;
            } else {
                $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                Error executing query: ' . $stmt->error . '
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>';
            }

            $stmt->close();
        } else {
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Error preparing query: ' . $conn->error . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
        }
    } else {
        $message = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Session name cannot be empty.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
    }
}

// সেশন লিস্ট বের করো (ড্রপডাউন এর জন্য)
$sql_session = "SELECT DISTINCT session FROM session_table ORDER BY session DESC";  // টেবিলের নাম বদলাও
$result_session = $conn->query($sql_session);

// ডেটা সাবমিট করলে আপডেট করো
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $selected_session = $_POST['session'];
    $new_semid = intval($_POST['semid']);

    $update_sql = "UPDATE session_table SET semid = ? WHERE session = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("is", $new_semid, $selected_session);
    if ($stmt->execute()) {
        echo "<script>alert('Semester updated successfully');window.location.href='upgrade.php';</script>";
    } else {
        echo "<script>alert('Error: Unsuccessfull');window.location.href='upgrade.php';</script>";
    }
    $stmt->close();
}

?>















<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade Semester</title>
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
            <a href="index.php" class="list-group-item list-group-item-action ">Dashboard</a>
                <a href="updateprofile.php" class="list-group-item list-group-item-action">Update Profile</a>
                <a href="finalmark.php" class="list-group-item list-group-item-action">Input Final Mark</a>
                <a href="enroll.php" class="list-group-item list-group-item-action">Enroll Course</a>
                <a href="newadminregister.php" class="list-group-item list-group-item-action">Admin Registration</a>
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
    <div class="row">
        <!-- Left Section (Result & Certificate + Table) -->
        <div class="col-md-8">
           
        <form method="post" action="">
    <div class="mb-3">
        <label for="session" class="form-label">Select Session</label>
        <select class="form-select" id="session" name="session" required>
            <option value="">Select</option>
            <?php while ($row = $result_session->fetch_assoc()) { ?>
                <option value="<?php echo htmlspecialchars($row['session']); ?>">
                    <?php echo htmlspecialchars($row['session']); ?>
                </option>
            <?php } ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="semid" class="form-label">Upgrade Semester</label>
        <input type="number" class="form-control" id="semid" name="semid" value="$row['semid']" required>
    </div>

    <button type="submit" name="update" class="btn btn-primary">Upgrade Semester</button>
</form>

         
            
        </div>


        

        <!-- Right Sidebar (Attendance Card) -->
        <div class="col-md-4">
            <div class="card shadow text-center"> 
            <a href="stuenroll.php" class="btn btn-secondary m-3">Course Enroll</a>
            <a href="upgrade.php" class="btn btn-primary active m-3">Upgrade Semester</a>
            <a href="nsessadded.php" class="btn btn-secondary m-3">New Session Added</a>
            <a href="nsemadded.php" class="btn btn-secondary m-3">New Semester Added</a>
            <a href="ncadded.php" class="btn btn-secondary m-3">New Course Added</a>
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

