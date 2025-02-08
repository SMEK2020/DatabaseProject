<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher's Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Teacher's Course Management</h2>
        
        <!-- Attendance Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Daily Attendance</div>
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Select Course:</label>
                        <select class="form-select">
                            <option>Course 1</option>
                            <option>Course 2</option>
                            <option>Course 3</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date:</label>
                        <input type="date" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Student List:</label>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Present</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Student 1</td>
                                    <td><input type="checkbox"></td>
                                </tr>
                                <tr>
                                    <td>Student 2</td>
                                    <td><input type="checkbox"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-success">Submit Attendance</button>
                </form>
            </div>
        </div>
        
        <!-- In-course Marks Form -->
        <div class="card">
            <div class="card-header bg-secondary text-white">In-course Marks Entry</div>
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Select Course:</label>
                        <select class="form-select">
                            <option>Course 1</option>
                            <option>Course 2</option>
                            <option>Course 3</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Student Marks:</label>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Marks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Student 1</td>
                                    <td><input type="number" class="form-control" min="0" max="100"></td>
                                </tr>
                                <tr>
                                    <td>Student 2</td>
                                    <td><input type="number" class="form-control" min="0" max="100"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Marks</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
