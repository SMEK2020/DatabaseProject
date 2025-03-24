<?php
include('../connect.php');

if (isset($_POST['semid'])) {
    $semid = $_POST['semid'];

    // Query to fetch courses based on the selected semester
    $query = "SELECT DISTINCT courseid FROM enrollment WHERE semid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $semid); // 'i' for integer
    $stmt->execute();
    $result = $stmt->get_result();

    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = ['courseid' => $row['courseid']];
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($courses);
}
?>
