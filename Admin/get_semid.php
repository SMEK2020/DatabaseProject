<?php
include('../connect.php');

if (isset($_POST['courseid'])) {
    $courseid = $_POST['courseid'];

    // কোর্সের সেম আইডি অনুসন্ধান করা
    $sql = "SELECT semid FROM courses WHERE courseid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $courseid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo $row['semid']; // সেম আইডি রিটার্ন
    } else {
        echo "Error"; // কোনো ফলাফল না থাকলে
    }
}
?>
