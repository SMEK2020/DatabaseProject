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
    $result = $stmt->get_result(); // Corrected from $res to $result

    // Generate table rows with student IDs and names
    while ($row = $result->fetch_assoc()) {  // Corrected from $res to $result
        echo "<tr>
                <td>{$row['studentid']}</td>
                <td>{$row['fullname']}</td>
                <td><input type='number' name='marks[{$row['studentid']}]' class='form-control' min='0' max='25'></td>
              </tr>";
    }
}
?>
