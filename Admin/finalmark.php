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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['marks'])) {
    $semid = $_POST['semid'];
    $courseid = $_POST['courseid'];
    $session = $_POST['session'];
    $marks = $_POST['marks'];

    foreach ($marks as $studentid => $fmark) {
        if ($fmark >= 0 && $fmark <= 70) {
            // 🔥 FIX: LEFT JOIN ব্যবহার করে আলাদাভাবে attmark ও intmark ফেচ করছি
            $fetchQuery = "SELECT 
                            COALESCE(a.attmark, 0) AS attmark, 
                            COALESCE(i.intmark, 0) AS intmark
                           FROM attendance a
                           LEFT JOIN incousemark i ON a.studentid = i.studentid AND a.courseid = i.courseid
                           WHERE a.studentid = ? AND a.courseid = ?";

            $fetchStmt = $conn->prepare($fetchQuery);
            $fetchStmt->bind_param('ss', $studentid, $courseid);
            $fetchStmt->execute();
            $result = $fetchStmt->get_result();

            $attmark = 0; // ডিফল্ট মান
            $intmark = 0; // ডিফল্ট মান

            while ($row = $result->fetch_assoc()) {
                if ($row['attmark'] > 0) $attmark = $row['attmark'];
                if ($row['intmark'] > 0) $intmark = $row['intmark'];
            }

            $fetchStmt->close(); // বন্ধ করা হলো

            $totalmark = $attmark + $intmark + $fmark;

            // ✅ DEBUGGING: Check fetched values
            echo "Student: $studentid, Att: $attmark, Int: $intmark, Final: $fmark, Total: $totalmark <br>";

            // Insert or update finalmark table
            $insertQuery = "INSERT INTO finalmark (studentid, courseid, session, attmark, intmark, fmark, totalmark) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE attmark = VALUES(attmark), intmark = VALUES(intmark), fmark = VALUES(fmark), totalmark = VALUES(totalmark)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param('ssssddd', $studentid, $courseid, $session, $attmark, $intmark, $fmark, $totalmark);
            $insertStmt->execute();

            if ($insertStmt->error) {
                echo "Error: " . $insertStmt->error;
            }

            $insertStmt->close();
        }
    }

    $conn->close(); // Database সংযোগ বন্ধ

    echo "<script>
            alert('Marks successfully updated for all students!');
            window.location.href = 'finalmark.php';
          </script>";
}

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Mark Input</title>
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
                <a href="index.php" class="list-group-item list-group-item-action">Dashboard</a>
                <a href="updateprofile.php" class="list-group-item list-group-item-action">Update Profile</a>
                <a href="#" class="list-group-item list-group-item-action active">Input Final Mark</a>
                <a href="enroll.php" class="list-group-item list-group-item-action">Enroll Course</a>
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
                <div class="card">
                    <div class="card-header bg-primary text-white">Final Theory Marks Entry</div>
                    <div class="card-body">
                        <form id="markEntryForm" method="POST">


                        <div class="mb-3">
    <label class="form-label">Select Session:</label>
    <select class="form-control" name="session" id="session" required>
        <option value="">Select Session</option>
        <?php
        
        
        $sql = "SELECT session FROM session_table ORDER BY session DESC"; // সেশনগুলো DESCending এ
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($row['session']) . "'>" . htmlspecialchars($row['session']) . "</option>";
            }
        } else {
            echo "<option value=''>No sessions found</option>";
        }

        $conn->close();
        ?>
    </select>
</div>

                            <div class="mb-3">
                                <label class="form-label">Select Semester:</label>
                                <select class="form-select" name="semid" id="semid" required>
                                    <option value="">Select Semester</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Select Course:</label>
                                <select class="form-select" name="courseid" id="courseid" required>
                                    <option value="">Select Course</option>
                                </select>
                            </div>

                           

                            <div class="mb-3">
                                <label class="form-label">Student Marks:</label>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <th>Marks (0-70)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="studentList">
                                        <tr><td colspan="3" class="text-center">Please select semester and course</td></tr>
                                    </tbody>
                                </table>
                            </div>

                            <button type="submit" class="btn btn-primary">Submit Marks</button>
                        </form>
                    </div>
                </div>
            </div>

            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script>
            $(document).ready(function() {
                // Load semesters
                $.ajax({
                    url: 'get_semesters.php', 
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        console.log("Semesters Loaded: ", response);
                        if (response.length > 0) {
                            $('#semid').html('<option value="">Select Semester</option>');
                            $.each(response, function(index, semester) {
                                $('#semid').append('<option value="' + semester.semid + '">' + semester.semid + '</option>');
                            });
                        }
                    }
                });

                // Load courses based on selected semester
                $('#semid').change(function() {
                    var semid = $(this).val();
                    console.log("Selected Semester: ", semid);
                    
                    if (semid) {
                        $.ajax({
                            url: 'get_courses.php',
                            type: 'POST',
                            data: { semid: semid },
                            dataType: 'json',
                            success: function(response) {
                                console.log("Courses Loaded: ", response);
                                if (response.length > 0) {
                                    $('#courseid').html('<option value="">Select Course</option>');
                                    $.each(response, function(index, course) {
                                        $('#courseid').append('<option value="' + course.courseid + '">' + course.courseid + '</option>');
                                    });
                                    $('#courseid').prop('disabled', false);
                                }
                            }
                        });
                    }
                });

                // Load students based on selected semester & course
                $('#courseid').change(function() {
                    var courseid = $(this).val();
                    var semid = $('#semid').val();
                    console.log("Selected Course: ", courseid);

                    if (courseid && semid) {
                        $.ajax({
                            url: 'get_students.php',
                            type: 'POST',
                            data: { courseid: courseid, semid: semid },
                            dataType: 'json',
                            success: function(response) {
                                console.log("Students Loaded: ", response);
                                if (response.length > 0) {
                                    var studentListHtml = '';
                                    $.each(response, function(index, student) {
                                        studentListHtml += '<tr><td>' + student.studentid + '</td><td>' + student.fullname + '</td><td><input type="number" name="marks[' + student.studentid + ']" class="form-control" min="0" max="70" required></td></tr>';
                                    });
                                    $('#studentList').html(studentListHtml);
                                }
                            }
                        });
                    }
                });
            });
            </script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </div>
    </div>
</body>
</html>
