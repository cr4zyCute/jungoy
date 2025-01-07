<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

include 'database/dbcon.php';
$student_id = $_SESSION['student_id'];

// Get the form ID from the query string
if (!isset($_GET['form_id']) || empty($_GET['form_id'])) {
    echo "No form selected.";
    exit;
}

$form_id = intval($_GET['form_id']);

// Fetch form name
$form_query = $conn->prepare("SELECT form_name FROM forms WHERE id = ?");
$form_query->bind_param('i', $form_id);
$form_query->execute();
$form_result = $form_query->get_result();

if ($form_result->num_rows === 0) {
    echo "Form not found.";
    exit;
}

$form = $form_result->fetch_assoc();

// Fetch form fields
$fields_query = $conn->prepare("SELECT id, field_name FROM form_fields WHERE form_id = ?");
$fields_query->bind_param('i', $form_id);
$fields_query->execute();
$fields = $fields_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fill Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 20px 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        input[type="text"] {
            padding: 10px;
            border: none;
            border-radius: 5px;
            width: 100%;
        }

        button {
            background-color: #4caf50;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #45a049;
        }

        a {
            text-decoration: none;
            color: #fff;
            display: block;
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
        }

        a:hover {
            text-decoration: underline;
        }

        p {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Fill Out Form: <?= htmlspecialchars($form['form_name']); ?></h1>
        <?php if ($fields->num_rows > 0): ?>
            <form action="send_form.php" method="POST">
                <input type="hidden" name="form_id" value="<?= $form_id; ?>">
                <input type="hidden" name="student_id" value="<?= $student_id; ?>"> <!-- Include student ID -->
                <?php while ($field = $fields->fetch_assoc()): ?>
                    <div>
                        <label for="field_<?= $field['id']; ?>"><?= htmlspecialchars($field['field_name']); ?>:</label>
                        <input
                            type="text"
                            id="field_<?= $field['id']; ?>"
                            name="responses[<?= $field['id']; ?>]"
                            required>
                    </div>
                <?php endwhile; ?>
                <button type="submit">Submit</button>
            </form>
        <?php else: ?>
            <p>No fields available for this form.</p>
        <?php endif; ?>
        <a href="studentProfile.php">Back to Dashboard</a>
    </div>
</body>

</html>