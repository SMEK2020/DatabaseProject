<?php
include('../config.php');
session_start();

if (!isset($_SESSION['id'])) {
    header('location: Alogin.php');
    exit;
}

// পেন্ডিং সার্টিফিকেট রিকুয়েস্ট লোড করা হচ্ছে
$requests = $conn->query("
    SELECT r.id, s.name, s.email, r.status, r.request_date
    FROM certificate_requests r
    JOIN students s ON r.student_id = s.id
    WHERE r.status = 'Pending'
");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action == "approve") {
        $stmt = $conn->prepare("SELECT s.name, s.email FROM certificate_requests r JOIN students s ON r.student_id = s.id WHERE r.id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->bind_result($name, $email);
        $stmt->fetch();
        $stmt->close();

        include "generate_certificate.php"; // সার্টিফিকেট তৈরি ও মেইল পাঠানো

        $stmt = $conn->prepare("UPDATE certificate_requests SET status = 'Approved' WHERE id = ?");
    } else {
        $stmt = $conn->prepare("UPDATE certificate_requests SET status = 'Rejected' WHERE id = ?");
    }

    if ($stmt) {
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();
    }

    echo "<script>window.location.href='teacher_portal.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Pending Certificate Requests</h2>
        <table class="table table-striped">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Request Date</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $requests->fetch_assoc()) { ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td><?= htmlspecialchars($row['email']); ?></td>
                    <td><?= htmlspecialchars($row['request_date']); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="request_id" value="<?= $row['id']; ?>">
                            <button name="action" value="approve" class="btn btn-success">Approve</button>
                            <button name="action" value="reject" class="btn btn-danger">Reject</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
