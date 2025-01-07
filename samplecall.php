<?php
require 'database/dbcon.php';
session_start();

if (!empty($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];

    // Query to fetch student details
    $query = "
        SELECT student.*, credentials.email
        FROM student
        JOIN credentials ON student.id = credentials.student_id
        WHERE student.id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $student = $result->fetch_assoc();
    } else {
        echo "Student profile not found.";
        exit();
    }

    // Query to fetch forms assigned to the student
    $forms_query = $conn->prepare("
        SELECT f.id AS form_id, f.form_name 
        FROM student_forms sf
        JOIN forms f ON sf.form_id = f.id
        WHERE sf.student_id = ?
    ");
    $forms_query->bind_param('i', $student_id);
    $forms_query->execute();
    $forms_result = $forms_query->get_result();

    if ($forms_result->num_rows > 0) {
        echo "<h1>Forms for " . htmlspecialchars($student['firstname']) . " " . htmlspecialchars($student['lastname']) . "</h1>";
        while ($form = $forms_result->fetch_assoc()) {
            $form_id = $form['form_id'];
            echo "<h2>Form Name: " . htmlspecialchars($form['form_name']) . "</h2>";

            // Fetch fields
            $fields_query = $conn->prepare("SELECT * FROM form_fields WHERE form_id = ?");
            $fields_query->bind_param('i', $form_id);
            $fields_query->execute();
            $fields_result = $fields_query->get_result();

            if ($fields_result->num_rows > 0) {
                echo "<h3>Fields:</h3><ul>";
                while ($field = $fields_result->fetch_assoc()) {
                    echo "<li>" . htmlspecialchars($field['field_name']) . " (Type: " . htmlspecialchars($field['field_type']) . ", Required: " . ($field['is_required'] ? "Yes" : "No") . ")</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No fields found for this form.</p>";
            }

            // Fetch responses
            $responses_query = $conn->prepare("
                SELECT fr.response, ff.field_name 
                FROM form_responses fr
                JOIN form_fields ff ON fr.field_id = ff.id
                WHERE fr.form_id = ? AND fr.student_id = ?
            ");
            $responses_query->bind_param('ii', $form_id, $student_id);
            $responses_query->execute();
            $responses_result = $responses_query->get_result();

            if ($responses_result->num_rows > 0) {
                echo "<h3>Responses:</h3><ul>";
                while ($response = $responses_result->fetch_assoc()) {
                    echo "<li>Field: " . htmlspecialchars($response['field_name']) . " - Response: " . htmlspecialchars($response['response']) . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No responses found for this form.</p>";
            }
        }
    } else {
        echo "<p>No forms found for this student.</p>";
    }

} else {
    header("Location: student.php");
    exit();
}
?>
