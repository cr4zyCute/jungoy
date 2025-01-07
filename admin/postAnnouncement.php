<?php
include '../database/dbcon.php';
session_start(); // Start the session

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Check if the admin ID is set in the session
    if (isset($_SESSION['admin_id'])) {
        $admin_id = $_SESSION['admin_id']; // Get the logged-in admin ID
    } else {
        // Handle the case where the admin ID is not set
        echo "Error: Admin not logged in.";
        exit;
    }

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO announcements (title, content, admin_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $title, $content, $admin_id);

    if ($stmt->execute()) {
        echo "Announcement posted successfully!";
        header("Location: admin-dashboard.php"); // Redirect to dashboard
        exit; // Make sure to exit after redirecting
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>