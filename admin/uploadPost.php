<?php
include '../database/dbcon.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}

$admin_id = $_SESSION['id']; // Correctly fetch admin_id
$content = mysqli_real_escape_string($conn, $_POST['content']);
$mediaPaths = [];

// Check if media is uploaded
if (isset($_FILES['media']['name'][0]) && $_FILES['media']['name'][0] != "") {
    foreach ($_FILES['media']['tmp_name'] as $key => $tmp_name) {
        $fileName = basename($_FILES['media']['name'][$key]);
        $targetDir = "../uploads/";
        $targetFilePath = $targetDir . time() . "_" . $fileName;

        // Ensure the directory exists
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Move uploaded file to the target directory
        if (move_uploaded_file($tmp_name, $targetFilePath)) {
            $mediaPaths[] = $targetFilePath;
        }
    }
}

// Insert post data into the database
if (!empty($content) || !empty($mediaPaths)) {
    if (empty($mediaPaths)) {
        // Insert text-only post
        $query = "INSERT INTO posts (admin_id, content) VALUES ('$admin_id', '$content')";
        mysqli_query($conn, $query);
    } else {
        // Insert post with media
        foreach ($mediaPaths as $mediaPath) {
            $query = "INSERT INTO posts (admin_id, content, media) VALUES ('$admin_id', '$content', '$mediaPath')";
            mysqli_query($conn, $query);
        }
    }
}

header("Location: admin-dashboard.php");
exit();
?>
