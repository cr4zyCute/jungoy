<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

include '../database/dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_id'])) {
    $form_id = intval($_POST['form_id']);

    // Delete the form from the database
    $delete_query = $conn->prepare("DELETE FROM forms WHERE id = ?");
    $delete_query->bind_param('i', $form_id);

    if ($delete_query->execute()) {
        // Redirect back to the admin page with success message
        header('Location: adminForm.php?message=Form deleted successfully');
        exit;
    } else {
        // Redirect back with error message
        header('Location: adminForm.php?error=Failed to delete form');
        exit;
    }
}

// Redirect if accessed directly
header('Location: adminForm.php');
exit;
