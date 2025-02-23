<?php
    include('../connect.php');
    session_start();
    if( !isset($_SESSION['id'])  ){
        header('location: ../Slogin.php');
        exit;
    }
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../dashboard.css">
    

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
                <a href="index.php" class="list-group-item list-group-item-action   active">Dashboard</a>
                <a href="updateprofile.php" class="list-group-item list-group-item-action">Update Profile</a>
                <a href="dailyattendance.php" class="list-group-item list-group-item-action">Daily Attendance</a>
                <a href="giveinmark.php" class="list-group-item list-group-item-action  ">InCourse Mark</a>
                
                
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
           

            <!-- Table Below Result & Certificate -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card shadow p-3">
                        <h4 class="text-center">Class Schedule</h4>
                        <div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead >
            <tr >
                <th></th>
                <th>8:30-9:50</th>
                <th>10:00-11:20</th>
                <th>11:30-12:50</th>
                <th>Break</th>
                <th>2:00-3:20</th>
                <th>3:30-4:50</th>
                
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Sunday</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>Monday</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>CSE 2205</td>
                <td>CSE 1103</td>
            </tr>
            <tr>
                <td>Tuesday</td>
                <td></td>
                <td>CSE 2104</td>
                <td>CSE 2104</td>
                <td></td>
                <td>CSE 2202</td>
                <td>CSE 2202</td>
            </tr>
            <tr>
                <td>Wednesday</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>CSE 2205</td>
                <td></td>
            </tr>
            <tr>
                <td>Thursday</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>CSE 1103</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar (Attendance Card) -->
        <div class="col-md-4">
            <div class="card shadow text-center"> 
                <h4 class="my-3">Exam Schedule</h4> 
                <div class="card-body text-start">
                    <h6>Session:2021-22</h6><hr>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead >
                                <tr >
                                    <th>Event</th>
                                    
                                    <th>Date</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                            <tr>
                               
                                <td>Start of Class</td>
                                
                                <td>21/10/24</td>
                            </tr>
                            <tr>
                               
                                <td>1st InCourse</td>
                                
                                <td>18/11/24</td>
                            </tr>
                            <tr>
                               
                                <td>2nd InCourse</td>
                                
                                <td>06/01/25</td>
                            </tr>
                            <tr>
                               
                                <td>End of class</td>
                                
                                <td>20/02/25</td>
                            </tr>
                            <tr>
                               
                                <td>Final Exam</td>
                                
                                <td>09/03/25</td>
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
    </div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
