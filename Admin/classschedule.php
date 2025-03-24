<?php
include '../connect.php'; // আপনার ডাটাবেজ কানেকশন ফাইল

$message = "";

// Fetch teacher IDs
$teachers = [];
$sql_teachers = "SELECT teacherid FROM teacher";
$result_teachers = $conn->query($sql_teachers);
if ($result_teachers->num_rows > 0) {
    while ($row = $result_teachers->fetch_assoc()) {
        $teachers[] = $row['teacherid'];
    }
}

// Fetch course IDs
$courses = [];
$sql_courses = "SELECT courseid FROM courses";
$result_courses = $conn->query($sql_courses);
if ($result_courses->num_rows > 0) {
    while ($row = $result_courses->fetch_assoc()) {
        $courses[] = $row['courseid'];
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $teacherid = $_POST['teacherid'];
    $day = $_POST['day'];
    $courseid = $_POST['courseid'];
    $classstart = $_POST['classstart'];
    $classend = $_POST['classend'];
    $session = $_POST['session'];

    $sql_insert = "INSERT INTO classschedule (teacherid, day, courseid, classstart, classend, session) 
                   VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("ssssss", $teacherid, $day, $courseid, $classstart, $classend, $session);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['HTTP_REFERER']); // সফল হলে আগের পেজে নিয়ে যাবে
        exit;
    } else {
        $message = '<div class="alert alert-danger">Error: ' . $stmt->error . '</div>';
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedule Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

<h2 class="mb-4">Add Class Schedule</h2>

<?= $message; ?>

<form method="POST" class="card p-4 shadow">
    <div class="mb-3">
        <label class="form-label">Teacher ID</label>
        <select name="teacherid" class="form-select" required>
            <option value="">Select Teacher</option>
            <?php foreach ($teachers as $tid) { echo "<option value='$tid'>$tid</option>"; } ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Day</label>
        <select name="day" class="form-select" required>
            <option value="">Select Day</option>
            <option value="Sunday">Sunday</option>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
            <option value="Saturday">Saturday</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Course ID</label>
        <select name="courseid" class="form-select" required>
            <option value="">Select Course</option>
            <?php foreach ($courses as $cid) { echo "<option value='$cid'>$cid</option>"; } ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Class Start Time</label>
        <input type="time" name="classstart" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Class End Time</label>
        <input type="time" name="classend" class="form-control" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Session</label>
        <input type="text" name="session" class="form-control" required>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-grow-1">Add Schedule</button>
        <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
    </div>
</form>

</body>
</html>
