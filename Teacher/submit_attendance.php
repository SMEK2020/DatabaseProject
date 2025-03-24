<?php
include('../connect.php');
session_start();

if (!isset($_SESSION['userid'])) {
    header('location: ../Tlogin.php');
    exit;
}

// Fetch student details if not already in session
if (!isset($_SESSION['teacherid']) || !isset($_SESSION['fullname']) || !isset($_SESSION['profilepicture'])) {
    $userid = $_SESSION['userid'];
    $sql = "SELECT teacherid, profilepicture, fullname FROM teacher WHERE userid=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['teacherid'] = $row['teacherid']; 
        $_SESSION['fullname'] = $row['fullname'];
        $_SESSION['profilepicture'] = !empty($row['profilepicture']) ? $row['profilepicture'] : 'image/default.png';
    } else {
        $_SESSION['fullname'] = 'Unknown User';
        $_SESSION['profilepicture'] = 'image/default.png';
    }
    $stmt->close();
}
$fullname = $_SESSION['fullname'];

// BLOB ইমেজকে `base64_encode()` করে সেট করা
if (isset($_SESSION['profilepicture']) && !empty($_SESSION['profilepicture'])) {
    $profilePic = 'data:image/jpeg;base64,' . base64_encode($_SESSION['profilepicture']);
} else {
    $profilePic = 'image/default.png'; // যদি ছবি না থাকে, ডিফল্ট ইমেজ দেখাবে
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $attendance_date = $_POST['attendance_date'];
    $selected_students = isset($_POST['attendance']) ? $_POST['attendance'] : [];

    $studentQuery = $conn->query("SELECT * FROM students");
    
    while ($student = $studentQuery->fetch_assoc()) {
        $student_id = $student['roll'];
        $status = isset($selected_students[$student_id]) ? 'Present' : 'Absent';

        // Insert into attendance table
        $stmt = $conn->prepare("INSERT INTO attendance (student_id, course_id, attendance_date, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $student_id, $course_id, $attendance_date, $status);
        $stmt->execute();
    }

    echo "Attendance recorded successfully!";
    header("refresh:2;url=dailyattendance.php");
    exit;
}
?>
