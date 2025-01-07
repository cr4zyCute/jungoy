<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

include '../database/dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_id = intval($_POST['form_id']);
    $responses = $_POST['responses'] ?? [];

    if (empty($responses)) {
        echo "No responses to update.";
        exit;
    }

    foreach ($responses as $response_id => $response) {
        // Update each response in the database
        $update_query = $conn->prepare("
            UPDATE form_responses 
            SET response = ? 
            WHERE id = ? AND form_id = ? AND student_id = ?
        ");
        $update_query->bind_param('siii', $response, $response_id, $form_id, $_SESSION['student_id']);
        $update_query->execute();
    }

    echo "Responses updated successfully!";
    header("Location: edit_responses.php?form_id=$form_id");
    exit;
} else {
    echo "Invalid request method.";
}
?>
