<?php
include('../connect.php');
session_start();

// কোর্স এবং সেমেস্টার আইডি পাওয়া যাচ্ছে কি না তা চেক করা
if (isset($_GET['courseid']) && isset($_GET['semid'])) {
    $courseId = $_GET['courseid'];
    $semesterId = $_GET['semid'];

    // কোর্স এবং সেমেস্টার অনুযায়ী ছাত্রদের তথ্য নেওয়া
    $sql = "SELECT s.studentid, s.fullname 
            FROM student s 
            JOIN enrollment e ON s.studentid = e.studentid 
            WHERE e.courseid = ? AND e.semid = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $courseId, $semesterId);
    $stmt->execute();
    $result = $stmt->get_result();

    // ছাত্রদের লিস্ট প্রিন্ট করা
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['studentid'] . "</td>";
            echo "<td>" . $row['fullname'] . "</td>";
            echo "<td><input type='checkbox' name='students[]' value='" . $row['studentid'] . "'></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='3'>No students found for this course and semester.</td></tr>";
    }

    $stmt->close();
} else {
    echo "<tr><td colspan='3'>Invalid course or semester.</td></tr>";
}
?>
