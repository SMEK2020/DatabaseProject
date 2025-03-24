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


    $sql = "SELECT a.fullname, a.fathersname, a.mothersname, u.email, u.phone, a.village_road, a.thana_upazilla, a.district 
        FROM teacher a
        JOIN users u ON a.userid = u.userid
        WHERE a.userid = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed (admin): " . $conn->error);
}
$stmt->bind_param("i", $userid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $fullname = $row['fullname'];
    $fathersname = $row['fathersname'];
    $mothersname = $row['mothersname'];
    $email = $row['email'];
    $phone = $row['phone'];
    $village_road = $row['village_road'];
    $thana_upazilla = $row['thana_upazilla'];
    $district = $row['district'];
} else {
    echo "User data not found.";
    $fullname = $fathersname = $mothersname = $email = $phone = $village_road = $thana_upazilla = $district = "";
}

   
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="../update.css">
    

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
                <a href="updateprofile.php" class="list-group-item list-group-item-action  active">Update Profile</a>
                <a href="dailyattendance.php" class="list-group-item list-group-item-action  ">Daily Attendance</a>
                <a href="giveinmark.php" class="list-group-item list-group-item-action  ">InCourse Mark</a>
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
        <h2 class="text-center">Update Your Profile</h2>
        <form action="updatehelper.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="userid" value="<?php echo $_SESSION['userid']; ?>">

    <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" class="form-control" name="name" id="name" placeholder="Enter full name" value="<?php echo $fullname; ?>" required>
    </div>
    <div class="mb-3">
        <label for="fatherName" class="form-label">Father's Name</label>
        <input type="text" class="form-control" name="fatherName" id="fatherName" placeholder="Enter father's name" value="<?php echo $fathersname; ?>" required>
    </div>
    
    <div class="mb-3">
        <label for="motherName" class="form-label">Mother's Name</label>
        <input type="text" class="form-control" name="motherName" id="motherName" placeholder="Enter mother's name" value="<?php echo $mothersname; ?>" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" name="email" id="email" placeholder="Enter Email" value="<?php echo $email; ?>" required>
    </div>
    <div class="mb-3">
        <label for="phnnumber" class="form-label">Phone Number</label>
        <input type="text" class="form-control" name="phone" id="phone" placeholder="Enter Phone Number" value="<?php echo $phone; ?>" required>
    </div>
    <div class="mb-3">
        <label for="villageRoad" class="form-label">Village/Road</label>
        <input type="text" class="form-control" name="villageRoad" id="villageRoad" placeholder="Enter village/road" value="<?php echo $village_road; ?>" required>
    </div>
    <div class="mb-3">
        <label for="thanaUpazilla" class="form-label">Thana/Upazilla</label>
        <input type="text" class="form-control" name="thanaUpazilla" id="thanaUpazilla" placeholder="Enter thana/upazilla" value="<?php echo $thana_upazilla; ?>" required>
    </div>
    <div class="mb-3">
        <label for="district" class="form-label">District</label>
        <input type="text" class="form-control" name="district" id="district" placeholder="Enter district" value="<?php echo $district; ?>" required>
    </div>
    <div class="mb-3">
        <label for="profilePicture" class="form-label">Profile Picture</label>
        <input type="file" class="form-control" name="profilePicture" id="profilePicture">
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-primary w-50">Update Profile</button>
    </div>
</form>
             </div>
        </div>
    </div>
</div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
