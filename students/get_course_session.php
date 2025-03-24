<?php
include('../connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['courseid'])) {
    $courseid = $_POST['courseid'];

    // Step 1: Get semid from courses table
    $semQuery = "SELECT semid FROM courses WHERE courseid = ?";
    $stmt1 = $conn->prepare($semQuery);
    $stmt1->bind_param("s", $courseid);
    $stmt1->execute();
    $stmt1->bind_result($semid);
    
    if ($stmt1->fetch()) {
        $stmt1->close();

        // Step 2: Get session from session_table using semid
        $sessionQuery = "SELECT session FROM session_table WHERE semid = ?";
        $stmt2 = $conn->prepare($sessionQuery);
        $stmt2->bind_param("i", $semid);
        $stmt2->execute();
        $stmt2->bind_result($session);

        if ($stmt2->fetch()) {
            echo htmlspecialchars($session);
        } else {
            echo "Session Not Found";
        }

        $stmt2->close();
    } else {
        echo "SemID Not Found";
        $stmt1->close();
    }
}
?>
