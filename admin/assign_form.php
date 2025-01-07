<?php
include '../database/dbcon.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $form_id = $_POST['form_id'];

   
   $conn->query("INSERT INTO student_forms (student_id, form_id) VALUES ($student_id, $form_id)");


    header('Location: adminpage.php');
}

if ($conn->query("INSERT INTO student_forms (student_id, form_id) VALUES ($student_id, $form_id)")) {
    echo "Form assigned successfully.";
} else {
    echo "Error assigning form: " . $conn->error;
}


$student_result = $conn->query("SELECT email FROM credentials WHERE id = $student_id");
$student= $student_result->fetch_assoc();


$to = $student['email'];
$subject = "New Form Assigned";
$message = "You have been assigned a new form. Please log in to your dashboard to view it.";
$headers = "From: admin@yourdomain.com";
mail($to, $subject, $message);

?>
