<?php
    include('../connect.php');
    session_start();

if (!isset($_SESSION['userid'])) {
    header('location: ../Tlogin.php');
    exit;
}

$userid = $_SESSION['userid'];

if (!isset($_SESSION['fullname']) || !isset($_SESSION['profilepicture'])) {
    // Fetch teacher details if not already in session
    $sql = "SELECT profilepicture, fullname FROM teacher WHERE userid=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userid); // Ensure userid is integer
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

// If profile picture is stored as file path, display it as is
if (isset($_SESSION['profilepicture']) && !empty($_SESSION['profilepicture'])) {
    // Check if it is a valid image path
    if (file_exists($_SESSION['profilepicture'])) {
        $profilePic = $_SESSION['profilepicture'];
    } else {
        $profilePic = 'image/default.png'; // Default image if path is not valid
    }
} else {
    $profilePic = 'image/default.png'; // Default image
}

?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="update.css">
    

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
                <a href="dailyattendance.php" class="list-group-item list-group-item-action  ">Daily Attendance</a>
                <a href="giveinmark.php" class="list-group-item list-group-item-action  ">InCourse Mark</a>
                
                
                <a href="changepass.php" class="list-group-item list-group-item-action  active">Change Password</a>
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

<div class="container">
<div class="form-container">
<h2 class="text-center">Update Your Password</h2>
<form method="POST" action="changepasshelper.php">
<div class="mb-3">
<label for="previous_password" class="form-label">Previous Password</label>
<input type="password" class="form-control" name="previous_password" placeholder="Enter your previous password" required>
</div>
<div class="mb-3">
<label for="new_password" class="form-label">New Password</label>
<input type="password" class="form-control" name="new_password" placeholder="Enter your new password" required>
</div>
<div class="text-center">
<button type="submit" class="btn btn-primary w-50">Update Password</button>
</div>
</form>


</div>
</div>


</div>
        </div>
    </div>
</div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
