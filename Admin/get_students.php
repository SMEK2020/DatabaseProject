<?php
include('../connect.php');

if (isset($_POST['semid']) && isset($_POST['courseid'])) {
    $semesterId = $_POST['semid'];
    $courseId = $_POST['courseid'];

    // Query to fetch students based on the selected semester and course,
    // joining student table to get fullname
    $query = "SELECT e.studentid, s.fullname 
              FROM enrollment e 
              JOIN student s ON e.studentid = s.studentid 
              WHERE e.semid = ? AND e.courseid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $semesterId, $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($students);
}
?>

