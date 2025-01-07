<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

include '../database/dbcon.php';
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

// Fetch submitted responses
$responses_query = $conn->prepare("
    SELECT fr.id as response_id, ff.field_name, fr.response 
    FROM form_responses fr 
    JOIN form_fields ff ON fr.field_id = ff.id 
    WHERE fr.student_id = ? AND fr.form_id = ?
");
$responses_query->bind_param('ii', $student_id, $form_id);
$responses_query->execute();
$responses = $responses_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Responses</title>
</head>

<body>
    <h1>Edit Responses for <?= htmlspecialchars($form['form_name']); ?></h1>
    <?php if ($responses->num_rows > 0): ?>
        <form action="update_responses.php" method="POST">
            <input type="hidden" name="form_id" value="<?= $form_id; ?>">
            <table border="1">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Response</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($response = $responses->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($response['field_name']); ?></td>
                            <td>
                                <input
                                    type="text"
                                    name="responses[<?= $response['response_id']; ?>]"
                                    value="<?= htmlspecialchars($response['response']); ?>">
                            </td>
                            <td>
                                <form action="delete_field.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="response_id" value="<?= $response['response_id']; ?>">
                                    <input type="hidden" name="form_id" value="<?= $form_id; ?>">
                                    <button type="submit" onclick="return confirm('Are you sure you want to delete this response?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit">Save Changes</button>
        </form>
    <?php else: ?>
        <p>No responses submitted yet.</p>
    <?php endif; ?>
    <a href="studentUpdate.php">Back to Dashboard</a>
</body>

</html>
<style>
    /* General body styling */
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f7fc;
        color: #333;
        margin: 0;
        padding: 0;
    }

    h1 {
        color: #4a90e2;
        text-align: center;
        margin: 20px 0;
    }

    /* Container for the main content */
    .container {
        width: 80%;
        margin: 0 auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Table styling */
    table {
        width: 100%;
        margin: 20px 0;
        border-collapse: collapse;
    }

    table th,
    table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    table th {
        background-color: #4a90e2;
        color: white;
    }

    table tr:hover {
        background-color: #f1f1f1;
    }

    /* Form and input fields */
    input[type="text"],
    input[type="hidden"] {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-sizing: border-box;
        font-size: 14px;
    }

    input[type="text"]:focus {
        border-color: #4a90e2;
        outline: none;
    }

    /* Buttons */
    button {
        background-color: #4a90e2;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: #357ab7;
    }

    /* Action buttons for delete */
    form button[type="submit"] {
        background-color: #e74c3c;
        padding: 5px 15px;
        font-size: 12px;
    }

    form button[type="submit"]:hover {
        background-color: #c0392b;
    }

    /* Back link */
    a {
        display: inline-block;
        margin-top: 20px;
        text-decoration: none;
        color: #4a90e2;
        font-size: 16px;
    }

    a:hover {
        text-decoration: underline;
    }

    /* Responsive design for mobile */
    @media (max-width: 768px) {
        .container {
            width: 95%;
        }

        table th,
        table td {
            padding: 10px;
        }

        button {
            width: 100%;
            padding: 12px;
        }
    }
</style>