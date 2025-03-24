<?php
include('../connect.php');
session_start();

if (!isset($_SESSION['userid'])) {
    header('location: Slogin.php');
    exit;
}

if (!isset($_SESSION['fullname']) || !isset($_SESSION['profilepicture'])) {
    // Fetch student details if not already in session
    $userid = $_SESSION['userid'];  // Make sure $userid is set
    $sql = "SELECT profilepicture, fullname FROM student WHERE userid=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error preparing the SQL statement: " . $conn->error);
    }
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

$fullname = $_SESSION['fullname'];
$profilePic = $_SESSION['profilepicture'];

// Fetch studentid from student table based on the logged-in user's userid
$userid = $_SESSION['userid'];  // Assuming 'userid' is stored in the session after login

// Query to get the studentid from the student table
$query = "SELECT studentid FROM student WHERE userid = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing the SQL statement: " . $conn->error);
}
$stmt->bind_param("i", $userid);  // 'i' for integer (assuming userid is integer)
$stmt->execute();
$result = $stmt->get_result();

// Check if studentid exists
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $studentid = $row['studentid'];  // Fetch studentid from the result
} else {
    echo "Student ID not found!";
    exit;
}

$stmt->close();

// Fetching the data from the cgpa and reshis tables for the specific student
$query = "SELECT c.semid, r.date AS publish_date, c.file_path, c.cgpa
          FROM cgpa c
          JOIN reshis r ON c.studentid = r.studentid 
          WHERE c.studentid = ?
          ORDER BY r.date DESC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing the SQL statement: " . $conn->error);  // Check for SQL preparation error
}
$stmt->bind_param("s", $studentid);  // 's' for string because studentid is varchar
$stmt->execute();
$result = $stmt->get_result();

// Check for SQL error
if (!$result) {
    die("Error executing query: " . $conn->error);
}



$stmt->close();
$conn->close();
?>











<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Result</title>
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
                <a href="downloadresult.php" class="list-group-item list-group-item-action  active">Download Result</a>
                <a href="dailyattendance.php" class="list-group-item list-group-item-action">Daily Attendance</a>
                <a href="incourse.php" class="list-group-item list-group-item-action">InCourse Mark</a>
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
            <div class="container">
            <h2 class="text-center">Download Result</h2><br><br>
            <?php
// Check for SQL error
if (!$result) {
    die("Error executing query: " . $conn->error);
}

if ($result->num_rows > 0) {
    echo '<table class="table table-hover">
            <thead>
                <tr>
                    <th scope="col">Serial No.</th>
                    <th scope="col">Semester</th>
                    <th scope="col">Published Date</th>
                    <th style="text-align: center;">Download</th>
                </tr>
            </thead>
            <tbody>';

    $serialNo = 1;
    $baseUrl = 'http://localhost/Database/result/';  // আপনার লোকাল হোস্ট ডিরেক্টরি পাথ
    while ($row = $result->fetch_assoc()) {
        $semid = $row['semid'];
        $publishDate = date("d F, Y", strtotime($row['publish_date']));
        
        $filePathFull = $row['file_path'];  // Full file system path
        $filePathFull = str_replace('\\', '/', $filePathFull);  // Ensure forward slashes
    
        $relativePath = str_replace("C:/xampp/htdocs/", "", $filePathFull);  
        $fileUrl = "http://localhost/" . $relativePath;
    
        $semesterName = "Semester " . $semid;
    
        echo '<tr>
                <th scope="row">' . $serialNo++ . '</th>
                <td>' . $semesterName . '</td>
                <td>' . $publishDate . '</td>
                <td style="text-align: center;">
    <a href="' . $fileUrl . '" download style="display: inline-block;">
        <i class="fa-solid fa-download"></i>
    </a>
</td>

              </tr>';
    }
    
    

    echo '</tbody>
        </table>';
} else {
    echo "<script>
    alert('No Result Found');
    window.location.href = document.referrer;  // Redirects to the previous page
  </script>";
}
?>



            </div>
                
            </div>
        </div>
    </div>
</div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
