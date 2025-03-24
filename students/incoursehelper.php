<?php
session_start();
include('../connect.php'); // ডাটাবেজ কানেকশন

// ✅ ম্যানুয়ালি studentid সেট করা
$studentid = "202122104005"; // এখানে তোমার studentid বসাও

// ✅ ইনকোর্স মার্ক রেকর্ড বের করো
$query = "SELECT Course_id, fincoursemark, sincoursemark, tincoursemark FROM incoursemark WHERE studentid = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $studentid);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-4">
    <div class="col-md-12" style="margin-top:40px;">
        <div class="card shadow p-3">
            <h4 class="text-center">InCourse Mark</h4>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Course ID</th>
                            <th>First Incourse</th>
                            <th>Second Incourse</th>
                            <th>Average</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['Course_id']) ?></td>
                            <td><?= htmlspecialchars($row['fincoursemark']) ?></td>
                            <td><?= htmlspecialchars($row['sincoursemark']) ?></td>
                            <td><?= htmlspecialchars($row['tincoursemark']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
?>
