<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

include '../database/dbcon.php';

// Get the form ID from the query string
if (!isset($_GET['form_id']) || empty($_GET['form_id'])) {
    echo "No form selected.";
    exit;
}

$form_id = intval($_GET['form_id']);

// Fetch form details
$form_query = $conn->prepare("SELECT * FROM forms WHERE id = ?");
$form_query->bind_param('i', $form_id);
$form_query->execute();
$form_result = $form_query->get_result();

if ($form_result->num_rows === 0) {
    echo "Form not found.";
    exit;
}

$form = $form_result->fetch_assoc();

// Fetch form fields
$fields_query = $conn->prepare("SELECT * FROM form_fields WHERE form_id = ?");
$fields_query->bind_param('i', $form_id);
$fields_query->execute();
$fields_result = $fields_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            margin-bottom: 1rem;
            font-size: 1.5rem;
            color: #007bff;
        }
        .field {
            margin-bottom: 1rem;
        }
        .field label {
            display: block;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .field input, .field textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }
        .actions {
            margin-top: 2rem;
        }
        .actions button {
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .actions button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>View Form: <?= htmlspecialchars($form['form_name']); ?></h1>
        <form action="submit_form.php" method="POST">
            <input type="hidden" name="form_id" value="<?= $form_id; ?>">
            <?php if ($fields_result->num_rows > 0): ?>
                <?php while ($field = $fields_result->fetch_assoc()): ?>
                    <div class="field">
                        <label for="field_<?= $field['id']; ?>"><?= htmlspecialchars($field['field_name']); ?></label>
                        <?php if ($field['field_type'] === 'textarea'): ?>
                            <textarea id="field_<?= $field['id']; ?>" name="fields[<?= $field['id']; ?>]"></textarea>
                        <?php else: ?>
                            <input type="<?= htmlspecialchars($field['field_type']); ?>" 
                                   id="field_<?= $field['id']; ?>" 
                                   name="fields[<?= $field['id']; ?>]">
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No fields available for this form.</p>
            <?php endif; ?>
           
        </form>
    </div>
</body>
</html>
