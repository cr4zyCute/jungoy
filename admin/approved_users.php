<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_users'])) {
    $approved_user_ids = $_POST['approve_users'];

    foreach ($approved_user_ids as $user_id) {
        // Approve the user
        $conn->query("UPDATE users SET approved = 1 WHERE id = $user_id");

        // Add a notification for the user
        $message = "Your account has been approved by the admin.";
        $created_at = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at, is_read) VALUES (?, ?, ?, 0)");
        $stmt->bind_param('iss', $user_id, $message, $created_at);
        $stmt->execute();
    }

    header('Location: admin-dashboard.php');
    exit;
}
