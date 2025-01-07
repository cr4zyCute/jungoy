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

// Fetch submitted responses
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

$delete_responses_query = $conn->prepare("DELETE FROM form_responses WHERE field_id = ?");
$delete_responses_query->bind_param('i', $field_id);
$delete_responses_query->execute();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Responses</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        h1 {
            text-align: center;
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table th, table td {
            padding: 12px;
            text-align: left;
        }

        table th {
            background-color: #007bff;
            color: #fff;
        }

        table tr:nth-child(odd) {
            background-color: #f2f2f2;
        }

        table tr:nth-child(even) {
            background-color: #fff;
        }

        input[type="text"] {
            width: 90%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            background-color: #007bff;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        button:hover {
            background-color: #0056b3;
        }

        .delete-button {
            background-color: #dc3545;
        }

        .delete-button:hover {
            background-color: #c82333;
        }

        a {
            display: block;
            margin: 20px auto;
            text-align: center;
            text-decoration: none;
            color: #007bff;
            font-size: 1rem;
        }

        a:hover {
            text-decoration: underline;
        }

        .no-responses {
            text-align: center;
            font-size: 1rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Responses for <?= htmlspecialchars($form['form_name']); ?></h1>
        <?php if ($responses->num_rows > 0): ?>
            <form action="update_responses.php" method="POST">
                <input type="hidden" name="form_id" value="<?= $form_id; ?>">
                <table>
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Response</th>
                           
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
                            <!-- <td>
                                <form action="delete_field.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="field_id" value="<?= $response['response_id']; ?>">
                                    <input type="hidden" name="form_id" value="<?= $form_id; ?>">
                                    <button 
                                        type="submit" 
                                        class="delete-button" 
                                        onclick="return confirm('Are you sure you want to delete this field?');">
                                        Delete
                                    </button>
                                </form>
                            </td> -->
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <button type="submit">Save Changes</button>
            </form>
        <?php else: ?>
            <p class="no-responses">No responses submitted yet.</p>
        <?php endif; ?>
        <a href="studentProfile.php">Back to Dashboard</a>
    </div>
</body>
</html>
