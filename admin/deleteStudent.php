<?php

require_once '../database/dbcon.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    $query = "DELETE FROM student WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
    
        header("Location: adminStudentSection.php?msg=Student deleted successfully");
        exit;
    } else {
      
        echo "Error deleting student: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
?>
