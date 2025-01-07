<?php
session_start();
include '../database/dbcon.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'approve' && isset($_POST['approve_users'])) {
        $approved_user_ids = $_POST['approve_users'];
        $approved_user_ids = array_map('intval', $approved_user_ids);

        foreach ($approved_user_ids as $student_id) {
            // Approve user
            $update_query = $conn->prepare("UPDATE student SET approved = 1, rejected = 0 WHERE id = ?");
            $update_query->bind_param('i', $student_id);
            if (!$update_query->execute()) {
                die("Error updating student approval: " . $update_query->error);
            }

            // Add approval notification
            $message = "Your account has been approved by the admin.";
            $insert_query = $conn->prepare("INSERT INTO notifications (student_id, message, created_at, is_read) VALUES (?, ?, NOW(), 0)");
            $insert_query->bind_param('is', $student_id, $message);
            if (!$insert_query->execute()) {
                die("Error inserting approval notification: " . $insert_query->error);
            }
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'reject' && isset($_POST['reject_users'])) {
        $rejected_user_ids = $_POST['reject_users'];
        $rejected_user_ids = array_map('intval', $rejected_user_ids);

        foreach ($rejected_user_ids as $student_id) {
            // Reject user
            $update_query = $conn->prepare("UPDATE student SET rejected = 1, approved = 0 WHERE id = ?");
            $update_query->bind_param('i', $student_id);
            if (!$update_query->execute()) {
                die("Error updating student rejection: " . $update_query->error);
            }

            // Add rejection notification
            $message = "Your account has been rejected by the admin.";
            $insert_query = $conn->prepare("INSERT INTO notifications (student_id, message, created_at, is_read) VALUES (?, ?, NOW(), 0)");
            $insert_query->bind_param('is', $student_id, $message);
            if (!$insert_query->execute()) {
                die("Error inserting rejection notification: " . $insert_query->error);
            }
        }
    }

    // Redirect after processing
    header('Location: admin-dashboard.php');
    exit;
}
