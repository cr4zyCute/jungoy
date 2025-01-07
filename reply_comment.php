<?php
include 'database/dbcon.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$post_id = intval($_POST['post_id']);
$parent_comment_id = isset($_POST['parent_comment_id']) ? intval($_POST['parent_comment_id']) : null;
$content = mysqli_real_escape_string($conn, $_POST['content']);

$query = "INSERT INTO comments (post_id, student_id, parent_comment_id, content, created_at) 
          VALUES ('$post_id', '$student_id', " . ($parent_comment_id === null ? 'NULL' : "'$parent_comment_id'") . ", '$content', NOW())";

if (mysqli_query($conn, $query)) {
    header("Location: post.php?post_id=$post_id"); // Redirect to the post
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
