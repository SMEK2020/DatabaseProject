<?php
include('../connect.php');
session_start();

if (!isset($_SESSION['userid'])) {
    header('location: Slogin.php');
    exit;
}

if (!isset($_SESSION['fullname']) || !isset($_SESSION['profilepicture'])) {
    // Fetch student details if not already in session
    $sql = "SELECT profilepicture, fullname FROM student WHERE userid=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Store in session
        $_SESSION['fullname'] = $row['fullname'];
        $_SESSION['profilepicture'] = !empty($row['profilepicture']) ? $row['profilepicture'] : 'image/default.png';
    } else {
        $_SESSION['fullname'] = 'Unknown User';
        $_SESSION['profilepicture'] = 'image/default.png'; // Default image
    }
    $stmt->close();
}

$fullname = $_SESSION['fullname'];
$profilePic = $_SESSION['profilepicture'];






$student_id = $_SESSION['id']; // লগইন করা ছাত্রের আইডি

// কোর্স লোড করা
$courses = [];
$course_stmt = $conn->prepare("SELECT id, course_name FROM courses");
$course_stmt->execute();
$course_result = $course_stmt->get_result();
while ($row = $course_result->fetch_assoc()) {
    $courses[$row['id']] = $row['course_name'];
}

// যদি এটেন্ডেন্স সাবমিট করা হয়
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $course_id = $_POST['course_id'];
    $present_students = $_POST['present'] ?? [];

    // ছাত্রদের তালিকা বের করা
    $student_stmt = $conn->prepare("SELECT roll FROM students");
    $student_stmt->execute();
    $students_result = $student_stmt->get_result();

    while ($student = $students_result->fetch_assoc()) {
        $status = in_array($student['roll'], $present_students) ? 'Present' : 'Absent';

        // উপস্থিতি সংরক্ষণ
        $insert_stmt = $conn->prepare("INSERT INTO attendance (student_id, course_id, attendance_date, status) VALUES (?, ?, ?, ?)");
        $insert_stmt->bind_param("iiss", $student['roll'], $course_id, $date, $status);
        $insert_stmt->execute();
    }

    echo "<script>alert('Attendance Recorded Successfully');</script>";
}

// উপস্থিতির শতাংশ বের করা
$attendance_data = [];
foreach ($courses as $course_id => $course_name) {
    $stmt = $conn->prepare("SELECT 
            (SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) * 100) / COUNT(*) AS attendance_percentage 
        FROM attendance 
        WHERE student_id = ? AND course_id = ?");
    $stmt->bind_param("ii", $student_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $attendance_data[$course_name] = $row['attendance_percentage'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4>Daily Attendance</h4>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label>Select Course:</label>
                        <select name="course_id" class="form-select">
                            <?php foreach ($courses as $id => $name) : ?>
                                <option value="<?php echo $id; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Date:</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Student List:</label>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Present</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $student_stmt = $conn->prepare("SELECT roll, name FROM students");
                                $student_stmt->execute();
                                $students_result = $student_stmt->get_result();
                                while ($student = $students_result->fetch_assoc()) :
                                ?>
                                    <tr>
                                        <td><?php echo $student['roll']; ?></td>
                                        <td><?php echo $student['name']; ?></td>
                                        <td><input type="checkbox" name="present[]" value="<?php echo $student['roll']; ?>"></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-success">Submit Attendance</button>
                </form>
            </div>
        </div>

        <!-- উপস্থিতির শতাংশ -->
        <div class="card shadow mt-4">
            <div class="card-header bg-success text-white">
                <h4>Attendance Progress</h4>
            </div>
            <div class="card-body">
                <?php foreach ($attendance_data as $course_name => $percentage) : ?>
                    <h6><?php echo $course_name; ?></h6>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-info" style="width: <?php echo $percentage; ?>%">
                            <?php echo round($percentage, 2); ?>%
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <a href="generate_pdf.php" class="btn btn-danger mt-3">Download PDF</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
