<?php
    include('../connect.php');
    session_start();
    if( !isset($_SESSION['id'])  ){
        header('location: Slogin.php');
        exit;
    }
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InCourse Mark</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../dashboard.css">
    <link rel="stylesheet" href="update.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    

</head>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div class="bg-light border-end" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 primary-text"> 
                <img src="../image/my pic.png" class="rounded-circle" width="80" alt="Profile Picture">
                <h6>SAZID MAHMUD EMON KHAN</h6>
            </div>
            <div class="list-group list-group-flush">
                <a href="index.php" class="list-group-item list-group-item-action">Dashboard</a>
                <a href="updateprofile.php" class="list-group-item list-group-item-action">Update Profile</a>
                <a href="downloadresult.php" class="list-group-item list-group-item-action  ">Download Result</a>
                <a href="dailyattendance.php" class="list-group-item list-group-item-action">Daily Attendance</a>
                <a href="incourse.php" class="list-group-item list-group-item-action active">InCourse Mark</a>
                <a href="certificaterequest.php" class="list-group-item list-group-item-action">Certificate Application</a>
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

            <div class="container mt-4 ">
            <div class="col-md-12 " style="margin-top:40px ;">
                    <div class="card shadow p-3">
                        <h4 class="text-center">InCourse Mark</h4>
                        <div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead >
            <tr >
                <th>Course</th>
                <th>First Incourse</th>
                <th>Second Incourse</th>
                <th>Average</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Database</td>
                <td>80%</td>
                <td>85%</td>
                <td>82.5%</td>
            </tr>
            <tr>
                <td>Theory of Computing</td>
                <td>75%</td>
                <td>78%</td>
                <td>76.5%</td>
            </tr>
            <tr>
                <td>Numerical Method</td>
                <td>70%</td>
                <td>75%</td>
                <td>72.5%</td>
            </tr>
            <tr>
                <td>Accounting</td>
                <td>70%</td>
                <td>75%</td>
                <td>72.5%</td>
            </tr>
            <tr>
                <td>Data Structure and Algorithm-II</td>
                <td>70%</td>
                <td>75%</td>
                <td>72.5%</td>
            </tr>
        </tbody>
    </table>
</div>

                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
