<?php
session_start();
include "connect.php"; // ডাটাবেস কানেকশন

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['e']; 
    $password = $_POST['p'];

    // `users` টেবিল থেকে তথ্য বের করা
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // পাসওয়ার্ড যাচাই করা
        if (password_verify($password, $row['password'])) {
            $_SESSION['userid'] = $row['userid'];
            $_SESSION['role'] = $row['role'];

            // ✅ STUDENT হলে student টেবিল থেকে fullname এবং profilepicture আনবে
            if ($row['role'] == 'Student') {
                $sql2 = "SELECT fullname, profilepicture FROM student WHERE userid = ?";
            } 
            // ✅ TEACHER হলে teacher টেবিল থেকে আনবে
            elseif ($row['role'] == 'Teacher') {
                $sql2 = "SELECT fullname, profilepicture FROM teacher WHERE userid = ?";
            } 
            // ✅ ADMIN হলে admin টেবিল থেকে আনবে
            elseif ($row['role'] == '') {
                $sql2 = "SELECT fullname, profilepicture FROM admin WHERE userid = ?";
            }

            // যদি `fullname` ও `profilepicture` এর কুয়েরি থাকে, তাহলে তা চালানো হবে
            if (isset($sql2)) {
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param("i", $row['userid']);
                $stmt2->execute();
                $result2 = $stmt2->get_result();

                if ($result2->num_rows == 1) {
                    $userData = $result2->fetch_assoc();
                    $_SESSION['fullname'] = $userData['fullname'];
                    $_SESSION['profilepicture'] = $userData['profilepicture']; 
                } else {
                    $_SESSION['fullname'] = "Unknown User"; // যদি নাম না পাওয়া যায়
                    $_SESSION['profilepicture'] = "image/default.png"; // ডিফল্ট প্রোফাইল ছবি
                }
            }

            // ✅ ইউজার রোল অনুযায়ী রিডাইরেক্ট করা
            if ($row['role'] == '') {
                header("Location: Admin/index.php");
            } elseif ($row['role'] == 'Student') {
                header("Location: students/index.php");
            } elseif ($row['role'] == 'Teacher') {
                header("Location: Teacher/index.php");
            } else {
                header("Location: Alogin.php");
            }
            exit();
        } else {
            echo "<script>alert('ভুল পাসওয়ার্ড!'); window.location.href='Alogin.php';</script>";
        }
    } else {
        echo "<script>alert('এই ইমেইল দিয়ে কোনো অ্যাকাউন্ট পাওয়া যায়নি!'); window.location.href='Alogin.php';</script>";
    }
}
?>
