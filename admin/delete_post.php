<?php
include '../database/dbcon.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

if (isset($_POST['delete_post_id'])) {
    $postId = $_POST['delete_post_id'];

    // Delete the post
    $query = "DELETE FROM posts WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $postId);

    if ($stmt->execute()) {
        echo "Post deleted successfully.";
    } else {
        echo "Error deleting post: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
    exit();
}
