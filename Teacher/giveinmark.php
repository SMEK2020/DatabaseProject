<?php
include('../connect.php');
session_start();

if (!isset($_SESSION['userid'])) {
    header('location: ../Tlogin.php');
    exit;
}

$userid = $_SESSION['userid'];

// **Teacher ID Fetch করা হচ্ছে**
$sql_teacher = "SELECT teacherid FROM teacher WHERE userid = ?";
$stmt_teacher = $conn->prepare($sql_teacher);
$stmt_teacher->bind_param("s", $userid);
$stmt_teacher->execute();
$result_teacher = $stmt_teacher->get_result();

if ($result_teacher->num_rows > 0) {
    $row_teacher = $result_teacher->fetch_assoc();
    $teacher_id = $row_teacher['teacherid']; // **সঠিক teacher_id সেট করা হলো**
} else {
    echo "<script>alert('⚠️ Teacher ID not found!'); window.location.href='marks_entry_page.php';</script>";
    exit();
}

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

// If the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course']; // Fixed course field name
    $in_course = $_POST['in_course'];
    $marks = $_POST['marks']; // Marks array
    
    foreach ($marks as $student_id => $mark) {
        if (!empty($mark)) {
            if ($in_course == "First Incourse") {
                // Update only finmark for existing row
                $stmt = $conn->prepare("UPDATE incousemark SET finmark = ? WHERE courseid = ? AND studentid = ? AND teacherid = ?");
            } elseif ($in_course == "Second Incourse") {
                // Update only sinmark for existing row
                $stmt = $conn->prepare("UPDATE incousemark SET sinmark = ? WHERE courseid = ? AND studentid = ? AND teacherid = ?");
            }

            if ($stmt === false) {
                echo "Error in SQL prepare: " . $conn->error;
                continue;
            }

            $stmt->bind_param("ssss", $mark, $course_id, $student_id, $teacher_id);

            if (!$stmt->execute()) {
                echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
            }

            // If no row was updated, insert a new row with default values
            if ($stmt->affected_rows == 0) {
                $stmt = $conn->prepare("INSERT INTO incousemark (courseid, studentid, teacherid, finmark, sinmark) 
                                        VALUES (?, ?, ?, ?, ?)");

                $default_finmark = ($in_course == "First Incourse") ? $mark : 0.00;
                $default_sinmark = ($in_course == "Second Incourse") ? $mark : 0.00;

                $stmt->bind_param("sssss", $course_id, $student_id, $teacher_id, $default_finmark, $default_sinmark);
                $stmt->execute();
            }
        }
    }

    echo "<script>alert('Marks successfully submitted!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InCourse Mark</title>
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
            <a href="dailyattendance.php" class="list-group-item list-group-item-action">Daily Attendance</a>
            <a href="giveinmark.php" class="list-group-item list-group-item-action active">InCourse Mark</a>
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
            <div class="card">
                <div class="card-header bg-primary text-white">In-course Marks Entry</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Select Semester:</label>
                            <select class="form-select" name="semid" id="semester" required>
                                <option value="">Select Semester</option>
                                <?php
                                    $stmt = $conn->prepare("SELECT DISTINCT semid FROM tenrollment WHERE teacherid = ?");
                                    $stmt->bind_param("s", $teacher_id);
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
                            <label class="form-label">Select InCourse:</label>
                            <select class="form-select" name="in_course" required>
                                <option value="">Select InCourse</option>
                                <option value="First Incourse">First Incourse</option>
                                <option value="Second Incourse">Second Incourse</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Student Marks:</label>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Marks</th>
                                    </tr>
                                </thead>
                                <tbody id="studentList">
                                    <!-- Student list will be populated here using AJAX -->
                                </tbody>
                            </table>
                        </div>

                        <button type="submit" class="btn btn-primary">Submit Marks</button>
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
    // সেমেস্টার সিলেক্ট করার সময় কোর্স লোড হবে
    $('#semester').change(function() {
        var semesterId = $(this).val();
        if (semesterId != "") {
            $.ajax({
                url: 'get_course.php',
                method: 'POST',
                data: { semesterId: semesterId }, 
                success: function(data) {
                    $('#course').html(data); // কোর্সগুলি ফিল্ডে লোড হবে
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
                url: 'get_in_stu.php',
                method: 'GET',
                data: { courseid: courseId, semid: semesterId },
                success: function(data) {
                    $('#studentList').html(data); // ছাত্রদের টেবিল ফিল্ডে লোড হবে
                }
            });
        }
    });
});
</script>
</body>
</html>
