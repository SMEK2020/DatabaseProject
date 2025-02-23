<?php
include('../config.php');
session_start();
if (!isset($_SESSION['id'])) {
    header('location: ../Slogin.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = $_POST['date'];
    $course_id = $_POST['course'];
    $students = isset($_POST['students']) ? $_POST['students'] : [];

    // Mark all students as "Absent" by default
    $conn->query("INSERT INTO attendance (student_id, course_id, attendance_date, status) 
                  SELECT roll, '$course_id', '$date', 'Absent' FROM students");

    // Update "Present" students
    foreach ($students as $student_id) {
        $conn->query("UPDATE attendance SET status='Present' 
                      WHERE student_id='$student_id' AND course_id='$course_id' AND attendance_date='$date'");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="update.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="bg-light border-end" id="sidebar-wrapper">
        <div class="sidebar-heading text-center py-4 primary-text"> 
            <img src="../image/my pic.png" class="rounded-circle" width="80" alt="Profile Picture">
            <h6>SAZID MAHMUD EMON KHAN</h6>
        </div>
        <div class="list-group list-group-flush">
            <a href="index.php" class="list-group-item list-group-item-action">Dashboard</a>
            <a href="dailyattendance.php" class="list-group-item list-group-item-action active">Daily Attendance</a>
            <a href="logouthelper.php" class="list-group-item list-group-item-action">Logout</a>
        </div>
    </div>

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3">
            <div class="container-fluid d-flex align-items-center">
                <img src="../image/bsmru_logo.png" alt="Logo" width="70" class="me-2">
                <h5 class="m-0">Bangabandhu Sheikh Mujibur Rahman University, Kishoreganj</h5>
            </div>
        </nav>

        <div class="container mt-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">Daily Attendance</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Select Course:</label>
                            <select class="form-select" name="course">
                                <?php
                                $courses = $conn->query("SELECT * FROM courses");
                                while ($row = $courses->fetch_assoc()) {
                                    echo "<option value='{$row['id']}'>{$row['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date:</label>
                            <input type="date" class="form-control" name="date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Student List:</label>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Present</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $students = $conn->query("SELECT * FROM students");
                                    while ($row = $students->fetch_assoc()) {
                                        echo "<tr>
                                                <td>{$row['roll']}</td>
                                                <td>{$row['name']}</td>
                                                <td><input type='checkbox' name='students[]' value='{$row['roll']}'></td>
                                              </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <button type="submit" class="btn btn-success">Submit Attendance</button>
                    </form>
                    <form action="attendance_report.php" method="post">
                        <button type="submit" class="btn btn-danger mt-2">
                            <i class="fas fa-file-pdf"></i> Download Attendance Report
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
