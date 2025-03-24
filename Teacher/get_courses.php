<?php
include('../connect.php');
session_start();

// টিচার আইডি সংগ্রহ করা
$teacherId = $_SESSION['teacherid'];
if (!isset($_SESSION['teacherid'])) {
    echo "Teacher ID is not set in session.";
    exit;
} else {
    echo "Teacher ID: " . $_SESSION['teacherid'];  // পরীক্ষা করার জন্য
}

// সেমেস্টার আইডি আনা
if (isset($_POST['semesterId'])) {
    $semesterId = $_POST['semesterId'];

    // সেমেস্টার আইডি এবং টিচার আইডি অনুযায়ী কোর্স লোড করা
    $sql = "SELECT c.courseid, c.coursename 
            FROM courses c 
            JOIN tenrollment te ON c.courseid = te.courseid 
            WHERE te.teacherid = ? AND te.semid = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $teacherId, $semesterId); // টিচার আইডি এবং সেমেস্টার আইডি বাইন্ড করা
    $stmt->execute();
    $result = $stmt->get_result();

    // কোর্স পাওয়া গেলে
    if ($result->num_rows > 0) {
        echo "<option value=''>Select Course</option>";
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['courseid']}'>{$row['coursename']}</option>";
        }
    } else {
        echo "<option value=''>No courses available</option>";
    }
    $stmt->close();
} else {
    echo "<option value=''>Invalid semester</option>";
}
?>
