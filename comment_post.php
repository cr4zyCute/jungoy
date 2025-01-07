<?php
session_start();
include 'database/dbcon.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Something went wrong'];

if (isset($_POST['post_id'], $_POST['comment']) && isset($_SESSION['student_id'])) {
    $post_id = intval($_POST['post_id']);
    $student_id = intval($_SESSION['student_id']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    if (!empty($comment)) {
        $query = "INSERT INTO comments (post_id, student_id, content) VALUES ($post_id, $student_id, '$comment')";
        if (mysqli_query($conn, $query)) {
            // Fetch student details for the response
            $studentQuery = "SELECT firstname, lastname, image AS profile_image FROM student WHERE id = $student_id";
            $studentResult = mysqli_query($conn, $studentQuery);
            $student = mysqli_fetch_assoc($studentResult);

            $response = [
                'status' => 'success',
                'firstname' => $student['firstname'],
                'lastname' => $student['lastname'],
                'profile_image' => $student['profile_image']
            ];
        } else {
            $response['message'] = mysqli_error($conn);
        }
    } else {
        $response['message'] = 'Comment cannot be empty.';
    }
} else {
    $response['message'] = 'Invalid input or session expired.';
}

echo json_encode($response);
exit();
