<?php
include('../connect.php');

// Query to fetch all unique semester_ids from the enrollment table
$query = "SELECT DISTINCT semid FROM enrollment";
$result = $conn->query($query);

$semesters = [];
while ($row = $result->fetch_assoc()) {
    $semesters[] = ['semid' => $row['semid']];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($semesters);
?>
