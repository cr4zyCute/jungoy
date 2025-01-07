<?php
session_start();
include '../database/dbcon.php';
$notifications = $conn->query("SELECT * FROM notifications WHERE is_read = 0");

if (!$notifications) {
    die("Error fetching notifications: " . $conn->error);
}

$unapproved_users = $conn->query("
    SELECT 
        student.id AS student_id, 
        student.firstname, 
        credentials.email 
    FROM 
        student 
    INNER JOIN 
        credentials 
    ON 
        student.id =credentials.student_id 
    WHERE 
        student.approved = 0
");

if (!$unapproved_users) {
    die("Error fetching unapproved users: " . $conn->error);
}


$mark_read = $conn->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");

if (!$mark_read) {
    die("Error updating notifications: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Notifications</title>
</head>

<body>
    <h1>Admin Notifications</h1>

    <h2>New Notifications</h2>
    <?php if ($notifications->num_rows > 0): ?>
        <ul>
            <?php while ($notification = $notifications->fetch_assoc()): ?>
                <li><?= htmlspecialchars($notification['message']); ?> (<?= $notification['created_at']; ?>)</li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No new notifications.</p>
    <?php endif; ?>

    <h2>Unapproved Students</h2>
    <?php if ($unapproved_users->num_rows > 0): ?>
        <form method="POST" action="approve_users.php">
            <table border="1">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Approve</th>
                        <th>Reject</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = $unapproved_users->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['firstname']); ?></td>
                            <td><?= htmlspecialchars($student['email']); ?></td>
                            <td>
                                <input type="checkbox" name="approve_users[]" value="<?= $student['student_id']; ?>">
                            </td>
                            <td>
                                <input type="checkbox" name="reject_users[]" value="<?= $student['student_id']; ?>">
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit" name="action" value="approve">Approve Selected</button>
            <button type="submit" name="action" value="reject">Reject Selected</button>
        </form>

    <?php else: ?>
        <p>No students awaiting approval.</p>
    <?php endif; ?>


</body>

</html>