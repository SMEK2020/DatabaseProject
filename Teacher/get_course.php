<?php
// ডিবাগিংয়ের জন্য Error Reporting অন করা হয়েছে
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../connect.php');
session_start();

// Session থেকে Teacher ID চেক ও সংগ্রহ করা
if (!isset($_SESSION['teacherid'])) {
    echo "<option value=''>Teacher ID not set in session</option>";
    exit;
}

$teacherId = $_SESSION['teacherid'];

// Semester ID এসেছে কিনা চেক করা
if (isset($_POST['semesterId'])) {
    $semesterId = $_POST['semesterId'];

    // কোর্স লোড করার SQL
    $sql = "SELECT c.courseid, c.coursename 
            FROM courses c 
            JOIN tenrollment te ON c.courseid = te.courseid 
            WHERE te.teacherid = ? AND te.semid = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "<option value=''>Query Error: " . htmlspecialchars($conn->error) . "</option>";
        exit;
    }

    $stmt->bind_param("si", $teacherId, $semesterId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<option value=''>Select Course</option>";
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . htmlspecialchars($row['courseid']) . "'>" . htmlspecialchars($row['coursename']) . "</option>";
        }
    } else {
        echo "<option value=''>No courses available</option>";
    }

    $stmt->close();
} else {
    echo "<option value=''>Invalid semester</option>";
}
?>
