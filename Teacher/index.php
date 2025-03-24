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
// সেশন থেকে teacherid আনছে
if (!isset($_SESSION['userid'])) {
    header('location: ../Tlogin.php');
    exit;
}

$userid = $_SESSION['userid'];

$sql = "SELECT teacherid FROM teacher WHERE userid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userid);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();
$teacherid = $teacher['teacherid'] ?? null;
$stmt->close();

if (!$teacherid) {
    die("Teacher ID not found!");
}


// ক্লাস রুটিন নিয়ে আসা
$sql = "SELECT * FROM classschedule WHERE teacherid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $teacherid);
$stmt->execute();
$result = $stmt->get_result();

$schedule = [];
while ($row = $result->fetch_assoc()) {
    $schedule[$row['day']][] = [
        'courseid' => $row['courseid'],
        'classstart' => $row['classstart'],
    ];
}


?>







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="update.css">
    

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
                <a href="index.php" class="list-group-item list-group-item-action   active">Dashboard</a>
                <a href="updateprofile.php" class="list-group-item list-group-item-action">Update Profile</a>
                <a href="dailyattendance.php" class="list-group-item list-group-item-action">Daily Attendance</a>
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
    <div class="row">
        <!-- Left Section (Result & Certificate + Table) -->
        <div class="col-md-8">
           

            <!-- Table Below Result & Certificate -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card shadow p-3">
                        <h4 class="text-center">Class Schedule</h4>
                        <div class="table-responsive">
                        <table class="table table-striped table-hover">
    <thead>
        <tr>
            <th></th>
            <th>8:30-9:50</th>
            <th>10:00-11:20</th>
            <th>11:30-12:50</th>
            <th>Break</th>
            <th>2:00-3:20</th>
            <th>3:30-4:50</th>
        </tr>
    </thead>
    <tbody>
        <?php
       

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];

        $time_slots = [
            '08:30:00' => '8:30-9:50',
            '10:00:00' => '10:00-11:20',
            '11:30:00' => '11:30-12:50',
            'Break'     => 'Break',
            '02:00:00' => '2:00-3:20', // ✅ **Fixed 02:00 PM**
            '03:30:00' => '3:30-4:50'  // ✅ **Fixed 03:30 PM**
        ];

        foreach ($days as $day) {
            echo "<tr>";
            echo "<td><strong>$day</strong></td>";

            foreach ($time_slots as $start_time => $slot_label) {
                if ($start_time === 'Break') {
                    echo "<td style='background-color:#f8f9fa; text-align:center;'><strong>Break</strong></td>";
                    continue;
                }

                $found = false;

                if (isset($schedule[$day])) {
                    foreach ($schedule[$day] as $class) {
                        $class_time = date("H:i:s", strtotime($class['classstart'])); // সময় ঠিক করা

                        if ($class_time == $start_time) { 
                            echo "<td><strong>{$class['courseid']}</strong></td>";
                            $found = true;
                            break;
                        }
                    }
                }

                if (!$found) {
                    echo "<td></td>";
                }
            }

            echo "</tr>";
        }
        ?>
    </tbody>
</table>





</div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar (Attendance Card) -->
        <div class="col-md-4">
            <div class="card shadow text-center"> 
                <h4 class="my-3">Exam Schedule</h4> 
                <div class="card-body text-start">
                    <h6>Session:2021-22</h6><hr>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead >
                                <tr >
                                    <th>Event</th>
                                    
                                    <th>Date</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                            <tr>
                               
                                <td>Start of Class</td>
                                
                                <td>21/10/24</td>
                            </tr>
                            <tr>
                               
                                <td>1st InCourse</td>
                                
                                <td>18/11/24</td>
                            </tr>
                            <tr>
                               
                                <td>2nd InCourse</td>
                                
                                <td>06/01/25</td>
                            </tr>
                            <tr>
                               
                                <td>End of class</td>
                                
                                <td>20/02/25</td>
                            </tr>
                            <tr>
                               
                                <td>Final Exam</td>
                                
                                <td>09/03/25</td>
                            </tr>

                            </tbody>


                        </table>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

        </div>
    </div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
