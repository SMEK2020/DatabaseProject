<?php
include('../config.php');
session_start();

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
