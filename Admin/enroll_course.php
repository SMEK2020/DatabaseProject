<?php
include('../connect.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courseid = $_POST['courseid'];
    $session = $_POST['session'];
    $studentids = $_POST['studentid'];
    $semid = $_POST['semid'];  // সেম আইডি

    if (empty($courseid) || empty($session) || empty($studentids) || empty($semid)) {
        echo "<script>alert('All fields are required!');</script>";
    } else {
        // Insert data into enrollment table with semid
        $stmt_enroll = $conn->prepare("INSERT INTO enrollment (studentid, courseid, session, semid) VALUES (?, ?, ?, ?)");
        $stmt_enroll->bind_param("ssss", $studentid, $courseid, $session, $semid);

        $successCount = 0;
        $failCount = 0;

        foreach ($studentids as $studentid) {
            if ($stmt_enroll->execute()) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        $stmt_enroll->close();

        echo "<script>alert('$successCount students enrolled successfully.'); window.location.href='enroll.php';</script>";
        if ($failCount > 0) {
            echo "<script>alert('$failCount enrollments failed.');</script>";
        }
    }
}
?>
