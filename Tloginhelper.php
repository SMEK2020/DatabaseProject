<?php
ob_start(); // Output buffering শুরু করো
session_start();
include "connect.php"; // ডাটাবেস কানেকশন

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['e']; 
    $password = $_POST['p'];

    // ইমেইল এবং পাসওয়ার্ড ঠিক মত লিনেন করে দেয়া (Security)
    $email = trim($email);
    $password = trim($password);

    // SQL statement
    $sql = "SELECT * FROM users WHERE email = ?";

    // Prepare the statement
    $stmt = $conn->prepare($sql);

    // Check if the preparation was successful
    if ($stmt === false) {
        // If the statement preparation failed, show the error
        die('SQL Error: ' . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param("s", $email);

    // Execute the query
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // পাসওয়ার্ড যাচাই
        if (password_verify($password, $row['password'])) {
            // সেশন সেট করা হচ্ছে
            $_SESSION['userid'] = $row['userid'];
            $_SESSION['role'] = $row['role'];

            // রোল অনুসারে ডেটা ফেচ করা হচ্ছে
            switch ($row['role']) {
                case 'Student':
                    $sql2 = "SELECT fullname, profilepicture FROM student WHERE userid = ?";
                    break;
                case 'Teacher':
                    $sql2 = "SELECT fullname, profilepicture FROM teacher WHERE userid = ?"; // সংশোধিত
                    break;
                case 'admin':
                    $sql2 = "SELECT fullname, profilepicture FROM admin WHERE userid = ?";
                    break;
                default:
                    // Invalid role case
                    die('Invalid role!');
            }

            if ($sql2) {
                $stmt2 = $conn->prepare($sql2);
                // Check if the preparation was successful
                if ($stmt2 === false) {
                    die('SQL Error: ' . $conn->error);
                }

                $stmt2->bind_param("s", $row['userid']);
                $stmt2->execute();
                $result2 = $stmt2->get_result();

                if ($result2->num_rows == 1) {
                    $userData = $result2->fetch_assoc();
                    $_SESSION['fullname'] = $userData['fullname'];
                    $_SESSION['profilepicture'] = $userData['profilepicture']; 
                } else {
                    $_SESSION['fullname'] = "Unknown User";
                    $_SESSION['profilepicture'] = "image/default.png";
                }
            }

            // রোল অনুযায়ী ড্যাশবোর্ডে রিডাইরেক্ট
            ob_end_clean(); // Unwanted output মুছে ফেলো
            switch ($row['role']) {
                case 'admin':
                    header("Location: Admin/index.php");
                    break;
                case 'Student':
                    header("Location: students/index.php");
                    break;
                case 'Teacher':
                    header("Location: Teacher/index.php");
                    break;
                default:
                    header("Location: Tlogin.php");
                    break;
            }
            exit();
        } else {
            echo "<script>alert('❌ ভুল পাসওয়ার্ড!'); window.location.href='Tlogin.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('⚠️ এই ইমেইল দিয়ে কোনো অ্যাকাউন্ট পাওয়া যায়নি!'); window.location.href='Tlogin.php';</script>";
        exit();
    }
}
?>
