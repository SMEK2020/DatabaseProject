<?php
include('../connect.php');
session_start();

if (!isset($_SESSION['userid'])) {
    header('location: ../Tlogin.php');
    exit;
}

// ডায়নামিক studentid বের করা
$userid = $_SESSION['userid'];
$studentid = '';  // studentid খুঁজে এনে সেট করবো

$stu_query = "SELECT studentid FROM student WHERE userid = ?";
$stmt_stu = $conn->prepare($stu_query);
$stmt_stu->bind_param("i", $userid);
$stmt_stu->execute();
$result_stu = $stmt_stu->get_result();

if ($result_stu->num_rows == 1) {
    $row_stu = $result_stu->fetch_assoc();
    $studentid = $row_stu['studentid'];
} else {
    die("Student ID খুঁজে পাওয়া যায়নি।");
}
$stmt_stu->close();

$selectedSemid = isset($_POST['semid']) ? $_POST['semid'] : '';
$courses = [];

// সেমিস্টার অনুযায়ী ফেল করা কোর্স বের করা
if (!empty($selectedSemid)) {
    $courseListQuery = "SELECT courseid, coursename FROM courses WHERE semid = ?";
    $stmt_list = $conn->prepare($courseListQuery);
    if (!$stmt_list) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt_list->bind_param("i", $selectedSemid);
    $stmt_list->execute();
    $result_list = $stmt_list->get_result();

    while ($row = $result_list->fetch_assoc()) {
        $courseid = $row['courseid'];
        $coursename = $row['coursename'];

        $checkFailQuery = "SELECT totalmark FROM finalmark WHERE studentid = ? AND courseid = ? AND totalmark < 54";
        $stmt_check = $conn->prepare($checkFailQuery);
        if (!$stmt_check) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt_check->bind_param("ss", $studentid, $courseid);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $courses[] = [
                'courseid' => $courseid,
                'coursename' => $coursename
            ];
        }

        $stmt_check->close();
    }
    $stmt_list->close();
}

// সেমিস্টার লিস্ট বের করা
$sql_sem = "SELECT DISTINCT semid FROM courses WHERE semid IS NOT NULL ORDER BY semid ASC";
$result_sem = $conn->query($sql_sem);

// রিটেক রিকোয়েস্ট সাবমিশন হ্যান্ডলিং
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $courseid = $_POST['courseid'];
    $session_input = $_POST['session'];

    $checkQuery = "SELECT * FROM retake WHERE studentid = ? AND courseid = ? AND session = ?";
    $stmt_check = $conn->prepare($checkQuery);
    $stmt_check->bind_param("sss", $studentid, $courseid, $session_input);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo "<script>alert('তুমি ইতোমধ্যে এই কোর্সের রিটেক রিকোয়েস্ট দিয়েছো।');</script>";
    } else {
        $insertQuery = "INSERT INTO retake (studentid, courseid, session) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($insertQuery);
        $stmt_insert->bind_param("sss", $studentid, $courseid, $session_input);

        if ($stmt_insert->execute()) {
            echo "<script>alert('রিটেক রিকোয়েস্ট সফলভাবে সাবমিট হয়েছে।');</script>";
        } else {
            echo "<script>alert('এরর হয়েছে। দয়া করে পরে চেষ্টা করো।');</script>";
        }

        $stmt_insert->close();
    }
    $stmt_check->close();
}
?>

<!-- HTML শুরু -->
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>রিটেক রিকোয়েস্ট</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">রিটেক রিকোয়েস্ট ফর্ম</h2>

    <form method="post" action="">
        <!-- সেমিস্টার নির্বাচন -->
        <div class="mb-3">
            <label for="semid" class="form-label">সেমিস্টার নির্বাচন করুন</label>
            <select class="form-select" id="semid" name="semid" onchange="this.form.submit()" required>
                <option value="">-- নির্বাচন করুন --</option>
                <?php while ($row = $result_sem->fetch_assoc()) { ?>
                    <option value="<?php echo $row['semid']; ?>" <?php if ($selectedSemid == $row['semid']) echo 'selected'; ?>>
                        <?php echo 'Semester ' . $row['semid']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <!-- ফেল করা কোর্স দেখাবে -->
        <?php if (!empty($courses)) { ?>
            <div class="mb-3">
                <label for="courseid" class="form-label">ফেল করা কোর্স নির্বাচন করুন</label>
                <select class="form-select" id="courseid" name="courseid" required>
                    <option value="">-- কোর্স নির্বাচন করুন --</option>
                    <?php foreach ($courses as $course) { ?>
                        <option value="<?php echo htmlspecialchars($course['courseid']); ?>">
                            <?php echo htmlspecialchars($course['courseid'] . ' - ' . $course['coursename']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <!-- সেশন ইনপুট -->
            <div class="mb-3">
                <label for="session" class="form-label">সেশন</label>
                <input type="text" class="form-control" id="session" name="session" placeholder="উদাহরণ: 2021-22" required>
            </div>

            <button type="submit" name="submit" class="btn btn-primary">রিকোয়েস্ট সাবমিট করুন</button>
        <?php } elseif (!empty($selectedSemid)) { ?>
            <div class="alert alert-warning">এই সেমিস্টারে তোমার কোনো ফেল করা কোর্স নেই।</div>
        <?php } ?>
    </form>
</div>
</body>
</html>
