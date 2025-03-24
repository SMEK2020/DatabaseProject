<?php
include('../connect.php');
session_start();

if (!isset($_SESSION['userid'])) {
    header('location: ../Alogin.php');
    exit;
}

$userid = $_SESSION['userid'];

// Fetch admin details if not already in session
if (!isset($_SESSION['fullname']) || !isset($_SESSION['profilepicture'])) {
    $sql = "SELECT profilepicture, fullname FROM admin WHERE userid=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['fullname'] = $row['fullname'];
        $_SESSION['profilepicture'] = !empty($row['profilepicture']) ? $row['profilepicture'] : 'image/default.png';
    } else {
        $_SESSION['fullname'] = 'Unknown User';
        $_SESSION['profilepicture'] = 'image/default.png';
    }
    $stmt->close();
}

// Set variables for display
$fullname = $_SESSION['fullname'];
$profilePic = $_SESSION['profilepicture'];

// Set default profile picture if necessary
if (isset($_SESSION['profilepicture']) && !empty($_SESSION['profilepicture'])) {
    if (is_resource($_SESSION['profilepicture'])) {
        header("Content-type: image/jpeg");
        echo $_SESSION['profilepicture'];
        exit;
    }
    if (file_exists($_SESSION['profilepicture'])) {
        $profilePic = $_SESSION['profilepicture'];
    } else {
        $profilePic = 'image/default.png';
    }
} else {
    $profilePic = 'image/default.png';
}

// Initialize pending_count
$pending_count = 0;

// Prepare SQL query to count pending requests
$sql_pending = "SELECT COUNT(*) AS pending_count FROM certificaterequest WHERE status = 'pending'";
$stmt_pending = $conn->prepare($sql_pending);

if ($stmt_pending) {
    $stmt_pending->execute();
    $result_pending = $stmt_pending->get_result();

    if ($result_pending->num_rows > 0) {
        $row_pending = $result_pending->fetch_assoc();
        $pending_count = $row_pending['pending_count'];
    }

    $stmt_pending->close();
} else {
    die("Query preparation error: " . $conn->error);
}

?>















<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../dashboard.css">
    

</head>
<body>
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-light border-end" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 primary-text"> 
            <img src="<?php echo htmlspecialchars($profilePic); ?>" class="rounded-circle" width="90" height="90" alt="Profile Picture">
<h6><?php echo htmlspecialchars($fullname); ?></h6>
            </div>
            <div class="list-group list-group-flush">
            <a href="index.php" class="list-group-item list-group-item-action active">Dashboard</a>
                <a href="updateprofile.php" class="list-group-item list-group-item-action">Update Profile</a>
                <a href="finalmark.php" class="list-group-item list-group-item-action">Input Final Mark</a>
                <a href="enroll.php" class="list-group-item list-group-item-action">Enroll Course</a>
                <a href="newadminregister.php" class="list-group-item list-group-item-action">Admin Registration</a>
                <a href="newtearegister.php" class="list-group-item list-group-item-action">Teacher Registration</a>
                <a href="newsturegister.php" class="list-group-item list-group-item-action">Student Registration</a>
                <a href="certificaterequest.php" class="list-group-item list-group-item-action">Crrtificate Approval</a>
                <a href="resultpublish.php" class="list-group-item list-group-item-action">Result Publish</a>
                <a href="changepass.php" class="list-group-item list-group-item-action">Change Password</a>
                <a href="logouthelper.php" class="list-group-item list-group-item-action">Logout</a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <nav  class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3">
                <div class="container-fluid d-flex align-items-center" >
                    <img src="../image/bsmru_logo.png" alt="Logo" width="70" class="me-2">
                    <h5 class="m-0">Bangabandhu Sheikh Mujibur Rahman University, Kishoreganj</h5>
                </div>
            </nav>

            <div class="container mt-4">
    <div class="row">
        <!-- Left Section (Result & Certificate + Table) -->
        <div class="col-md-8">
            <div class="row">
                <!-- Result Card -->
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-body">
                            <h5 class="card-title">Make Class Scedule</h5>
                            <a href="classschedule.php" class="btn btn-primary">Make</a>
                        </div>
                    </div>
                </div>

                <!-- Certificate Card -->
                <div class="col-md-6">
    <div class="card shadow">
        <div class="card-body">
            <h5 class="card-title">Certificate Request</h5>
            <a href="certificaterequest.php" class="btn btn-primary position-relative">
                Request
                <?php if ($pending_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?= $pending_count ?>
                        <span class="visually-hidden">unread messages</span>
                    </span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</div>

            </div>

           


            <!-- Table Below Result & Certificate -->
            <div class="row mt-4">
                <div class="col-md-12">
                <div class="card shadow p-3">
    <h4 class="text-center">Teacher List</h4>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Teacher Name</th>
                    <th>Course</th>
                    <th>Session</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // প্রতি পেজে কতটি রেকর্ড দেখাবেন
                $limit = 5;

                // বর্তমান পেজ নাম্বার
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                if ($page < 1) $page = 1;

                $offset = ($page - 1) * $limit;

                // মোট রেকর্ড সংখ্যা নির্ণয়
                $countSql = "SELECT COUNT(*) AS total FROM tenrollment";
                $countResult = $conn->query($countSql);
                $totalRecords = $countResult->fetch_assoc()['total'];
                $totalPages = ceil($totalRecords / $limit);

                // ডেটা ফেচ
                $sql = "SELECT t.teacherid, t.fullname, c.courseid, e.session 
                        FROM tenrollment e
                        JOIN teacher t ON e.teacherid = t.teacherid
                        JOIN courses c ON e.courseid = c.courseid
                        LIMIT $limit OFFSET $offset";

                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['fullname']}</td>
                                <td>{$row['courseid']}</td>
                                <td>{$row['session']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='text-center text-danger'>No records found!</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Links -->
    <nav>
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a></li>
            <?php endif; ?>

            <?php
            for ($i = 1; $i <= $totalPages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo "<li class='page-item $active'><a class='page-link' href='?page=$i'>$i</a></li>";
            }
            ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">Next</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

                </div>
            </div>
        </div>


        

        <!-- Right Sidebar (Attendance Card) -->
        <div class="col-md-4">
            <div class="card shadow text-center"> 
            <a href="stuenroll.php" class="btn btn-secondary m-3">Course Enroll (Student)</a>
            <a href="upgrade.php" class="btn btn-secondary m-3">Upgrade Semester</a>
            <a href="nsessadded.php" class="btn btn-secondary m-3">New Session Added</a>
            <a href="nsemadded.php" class="btn btn-secondary m-3">New Semester Added</a>
            <a href="ncadded.php" class="btn btn-secondary m-3">New Course Added</a>
            <a href="retake.php" class="btn btn-secondary m-3">Retake/Improvement Registration</a>
                
            </div>
        </div>
    </div>
    
</div>

        </div>
    </div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
