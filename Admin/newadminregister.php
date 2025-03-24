
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

?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Admin Registration</title>
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
                <a href="updateprofile.php" class="list-group-item list-group-item-action">Update Profile</a>
                <a href="finalmark.php" class="list-group-item list-group-item-action">Input Final Mark</a>
                <a href="enroll.php" class="list-group-item list-group-item-action">Enroll Course</a>
                <a href="#" class="list-group-item list-group-item-action active">Admin Registration</a>
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
        <h2 class="text-center">Register New Admin</h2>
        <form action="inserthelper.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="userid" value="<?php echo $_SESSION['userid']; ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" name="name" id="name" placeholder="Enter your name" required>
            </div>
            <div class="mb-3">
                <label for="fathersName" class="form-label">Father's Name</label>
                <input type="text" class="form-control" name="fathersName" id="fatherName" placeholder="Enter father's name" required>
            </div>
            
            <div class="mb-3">
                <label for="mothersName" class="form-label">Mother's Name</label>
                <input type="text" class="form-control" name="mothersName" id="motherName" placeholder="Enter mother's name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="email" placeholder="Enter Email" required>
            </div>
            <div class="mb-3">
                <label for="phnnumber" class="form-label">Phone Number</label>
                <input type="text" class="form-control" name="phone" id="phone" placeholder="Enter Phone Number" required>
            </div>
            <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-control" name="role" id="role" required>
                        
                        
                        <option value="admin">Admin</option>
                        
                    </select>
                </div>
            

                <div class="mb-3">
                    <label for="department" class="form-label">Department</label>
                    <select class="form-control" name="department" id="department" required>
                        
                        <option value="Computer Science and Engineering">Computer Science and Engineering</option>
                        <option value="Accounting">Accounting</option>
                        <option value="English">English</option>
                        <option value="Mathematics">Mathematics</option>
                    </select>
                </div>

            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" id="password" placeholder="Enter A Password" required>
            </div>
            <div class="mb-3">
                <label for="village_road" class="form-label">Village/Road</label>
                <input type="text" class="form-control" name="village_road" id="villageRoad" placeholder="Enter village/road" required>
            </div>
            <div class="mb-3">
                <label for="thana_upazilla" class="form-label">Thana/Upazilla</label>
                <input type="text" class="form-control" name="thana_upazilla" id="thanaUpazilla" placeholder="Enter thana/upazilla" required>
            </div>
            <div class="mb-3">
                <label for="district" class="form-label">District</label>
                <input type="text" class="form-control" name="district" id="district" placeholder="Enter district" required>
            </div>
            <div class="mb-3">
                <label for="profilePicture" class="form-label">Insert Profile Picture</label>
                <input type="file" class="form-control" name="profilePicture" id="profilePicture">
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary w-50">Register Student</button>
            </div>
        </form>
             </div>
        </div>
    </div>
</div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
