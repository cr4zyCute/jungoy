    <?php
    include 'database/dbcon.php';
    session_start();

    if (!isset($_SESSION['student_id'])) {
        header("Location: index.php");
        exit();
    }
    $student_id = $_SESSION['student_id'];

  
    if (isset($_POST['delete_post_id'])) {
        $postId = $_POST['delete_post_id'];
        $query = "DELETE FROM posts WHERE id = '$postId' AND student_id = '$student_id'";
        if (mysqli_query($conn, $query)) {
            echo "Post deleted successfully.";
        } else {
            echo "Error deleting post: " . mysqli_error($conn);
        }
    }
    exit();  
?>
