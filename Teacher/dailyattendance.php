<?php
include('../connect.php');
session_start();

if (!isset($_SESSION['userid'])) {
    header('location: ../Tlogin.php');
    exit;
}

$userid = $_SESSION['userid'];

// 🔹 এই অংশটা POST এর বাইরে নিয়ে আসা হয়েছে
if (!isset($_SESSION['teacherid'])) {
    $sql = "SELECT teacherid FROM teacher WHERE userid=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['teacherid'] = $row['teacherid'];
    } else {
        echo "❌ Teacher ID পাওয়া যায়নি!";
        exit;
    }
    $stmt->close();
}

$teacherid = $_SESSION['teacherid'];  // এখন সঠিকভাবে teacherid পাওয়া যাবে

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    $date = $_POST['date'];
    $course_id = $_POST['course'];
    $semester_id = $_POST['semid'];  // Change `session` to `semid`

    // ✅ 1️⃣ চেকবক্সে সিলেক্ট করা শিক্ষার্থীরা
    $students = isset($_POST['students']) ? $_POST['students'] : [];

    // ✅ 2️⃣ সকল ছাত্রদের লিস্ট বের করা (এই কোর্স + সেমিস্টারের জন্য)
    $all_students = [];
    $result = $conn->query("SELECT studentid FROM enrollment WHERE semid='$semester_id' AND courseid='$course_id'");

    while ($row = $result->fetch_assoc()) {
        $all_students[] = $row['studentid'];
    }

    // ✅ 3️⃣ পুরাতন ডাটা ডিলিট করা (যদি আগে ইনসার্ট হয়ে থাকে)
    $deleteQuery = "DELETE FROM dailyattendance WHERE courseid=? AND date=?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("ss", $course_id, $date);
    $stmt->execute();
    $stmt->close();

    // ✅ 4️⃣ নতুন ডাটা ইনসার্ট করা (Present / Absent)
    $stmt = $conn->prepare("INSERT INTO dailyattendance (studentid, courseid, date, status) VALUES (?, ?, ?, ?)");

    foreach ($all_students as $student_id) {
        $status = in_array($student_id, $students) ? 'present' : 'absent';
        $stmt->bind_param("ssss", $student_id, $course_id, $date, $status);
        $stmt->execute();
    }

    echo "<script>
    alert('✅ Attendance successfully recorded!');
    window.location.href='dailyattendance.php';
  </script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="update.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
            <a href="dailyattendance.php" class="list-group-item list-group-item-action active">Daily Attendance</a>
            <a href="giveinmark.php" class="list-group-item list-group-item-action">InCourse Mark</a>
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
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">Daily Attendance</div>
                <div class="card-body">
                    <form method="POST">
                    <div class="mb-3">
    <label class="form-label">Select Semester:</label>
    <select class="form-select" name="semid" id="semester" required>
        <option value="">Select Semester</option>
        <?php
         // ডাটাবেজ কানেকশন

        $teacherid = $_SESSION['teacherid'];  // টিচার আইডি সেশন থেকে

        // একই সেমেস্টার একাধিকবার না দেখাতে DISTINCT
        $stmt = $conn->prepare("SELECT DISTINCT semid FROM tenrollment WHERE teacherid = ?");
        $stmt->bind_param("s", $teacherid);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['semid']}'>{$row['semid']}</option>";
        }

        $stmt->close();
        ?>
    </select>
</div>


                        <div class="mb-3">
                            <label class="form-label">Select Course:</label>
                            <select class="form-select" name="course" id="course" required>
                                <option value="">Select a course</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" class="form-control" name="date" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Student List:</label>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="studentList">
                                    <!-- Student List will be loaded dynamically based on selected course -->
                                </tbody>
                            </table>
                        </div>

                        <button type="submit" class="btn btn-success">Submit Attendance</button>
                    </form>

                    
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // যখন সেমেস্টার সিলেক্ট করা হবে
    $('#semester').change(function() {
        var semesterId = $(this).val(); // সেমেস্টার আইডি
        console.log("Selected Semester ID: " + semesterId);  // ডিবাগিং
        if (semesterId != "") {
            // সেমেস্টার অনুযায়ী কোর্স লোড করা
            $.ajax({
                url: 'get_courses.php',
                method: 'POST',
                data: { semesterId: semesterId },
                success: function(data) {
                    console.log("Response from server: " + data); // Response দেখুন
                    $('#course').html(data); // কোর্স গুলি ফিল্ডে লোড হবে
                },
                error: function(xhr, status, error) {
                    console.log("Error: " + error); // যদি কোন ত্রুটি হয়
                }
            });
        } else {
            $('#course').html('<option value="">Please select a semester</option>');
        }
    });

    // কোর্স সিলেক্ট করা হলে ছাত্রদের লোড করা
    $('#course').change(function() {
        var courseId = $(this).val();
        var semesterId = $('#semester').val();
        if (courseId != "" && semesterId != "") {
            $.ajax({
                url: 'get_students.php',
                method: 'GET',
                data: { courseid: courseId, semid: semesterId },
                success: function(response) {
                    console.log("Student Data: " + response); // পরীক্ষা করার জন্য
                    $('#studentList').html(response);
                }
            });
        }
    });
});
</script>
</body>
</html>
