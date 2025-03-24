<?php
session_start();
include(realpath(__DIR__ . '/../connect.php')); // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $fullname = $_POST['name'];
    $fathersname = $_POST['fathersName'];
    $mothersname = $_POST['mothersName'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = strtolower($_POST['role']); // Role কে lowercase করা হলো
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $village_road = $_POST['village_road'];
    $thana_upazilla = $_POST['thana_upazilla'];
    $district = $_POST['district'];
    $department = $_POST['department'];

    // Student-specific data
    $studentid = $_POST['studentid'] ?? null;
    $session = $_POST['session'] ?? null;

    // Handle profile picture upload
    $profilepicture = 'image/default.png';  // Default image
    if (!empty($_FILES['profilePicture']['tmp_name'])) {
        // File upload settings
        $uploadDir = '../uploads/profile_pictures/';  // Folder to save the images
        $fileName = basename($_FILES['profilePicture']['name']);
        $filePath = $uploadDir . uniqid('profile_') . '.' . pathinfo($fileName, PATHINFO_EXTENSION);  // Unique file name

        // Ensure the folder exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);  // Create folder if it doesn't exist
        }

        // Check if file is an image
        $fileType = mime_content_type($_FILES['profilePicture']['tmp_name']);
        if (strpos($fileType, 'image') === false) {
            die("❌ Error: Only image files are allowed.");
        }

        // Move the file to the desired folder
        if (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $filePath)) {
            $profilepicture = $filePath;  // Save the file path
        } else {
            die("❌ Error: File upload failed.");
        }
    }

    // Insert into users table
    $user_insert_query = "INSERT INTO users (email, password, phone, role) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($user_insert_query);
    if (!$stmt) {
        die("❌ Error preparing users insert statement: " . $conn->error);
    }
    $stmt->bind_param("ssss", $email, $password, $phone, $role);
    if (!$stmt->execute()) {
        die("❌ Error inserting into users: " . $stmt->error);
    }

    $userid = $stmt->insert_id; // Get last inserted user ID
    $stmt->close();

    // Role-based registration
    switch ($role) {
        case "student":
            $student_insert_query = "INSERT INTO student (userid, fullname, fathersname, mothersname, studentid, session, department, village_road, thana_upazilla, district, profilepicture) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($student_insert_query);
            if (!$stmt) {
                die("❌ Error preparing student insert statement: " . $conn->error);
            }
            $stmt->bind_param("issssssssss", $userid, $fullname, $fathersname, $mothersname, $studentid, $session, $department, $village_road, $thana_upazilla, $district, $profilepicture);
            
            if (!$stmt->execute()) {
                die("❌ Error inserting into student: " . $stmt->error);
            }
            $stmt->close();
            break;

        case "teacher":
            $teacherid = "TCH-" . strtoupper(substr($fullname, 0, 3)) . rand(100, 999);
            $teacher_insert_query = "INSERT INTO teacher (teacherid, userid, fullname, fathersname, mothersname, village_road, thana_upazilla, district, department, profilepicture) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($teacher_insert_query);
            if (!$stmt) {
                die("❌ Error preparing teacher insert statement: " . $conn->error);
            }
            $stmt->bind_param("sissssssss", $teacherid, $userid, $fullname, $fathersname, $mothersname, $village_road, $thana_upazilla, $district, $department, $profilepicture);

            if (!$stmt->execute()) {
                die("❌ Error inserting into teacher: " . $stmt->error);
            }
            $stmt->close();
            break;

        case "admin":
            $admin_insert_query = "INSERT INTO admin (userid, fullname, fathersname, mothersname, village_road, thana_upazilla, district, department, profilepicture) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($admin_insert_query);
            if (!$stmt) {
                die("❌ Error preparing admin insert statement: " . $conn->error);
            }
            $stmt->bind_param("issssssss", $userid, $fullname, $fathersname, $mothersname, $village_road, $thana_upazilla, $district, $department, $profilepicture);

            if (!$stmt->execute()) {
                die("❌ Error inserting into admin: " . $stmt->error);
            }
            $stmt->close();
            break;
    }

    echo "<script>alert('✅ User Registered Successfully!'); window.location.href='index.php';</script>";
    $conn->close();
}
?>
