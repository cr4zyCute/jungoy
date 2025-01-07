<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

include 'database/dbcon.php';

if (isset($_POST['response_id']) && isset($_POST['form_id'])) {
    $response_id = intval($_POST['response_id']);
    $form_id = intval($_POST['form_id']);

    // Delete the response
    $delete_query = $conn->prepare("DELETE FROM form_responses WHERE id = ? AND form_id = ?");
    $delete_query->bind_param('ii', $response_id, $form_id);
    if ($delete_query->execute()) {
        echo "Response deleted successfully!";
        header('Location: view_responses.php?form_id=' . $form_id);
        exit;
    } else {
        echo "Error deleting response.";
    }
} else {
    echo "Invalid request.";
}
