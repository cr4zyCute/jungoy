<?php
session_start();
include 'database/dbcon.php';
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

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
<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #f9f9f9;
    color: #333;
}

/* Header Styles */
header {
    background-color: #4CAF50;
    color: white;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

header nav a {
    color: white;
    text-decoration: none;
    font-size: 1.2rem;
    margin-right: 15px;
    display: flex;
    align-items: center;
}

header nav a i {
    margin-right: 5px;
}

header .logo {
    font-size: 1.5rem;
    font-weight: bold;
}

/* Profile Section */
.profile-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 20px;
    padding: 20px;
    background-color: white;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

.profile-picture img {
    border-radius: 50%;
    border: 4px solid #4CAF50;
    margin-right: 20px;
}

.profile-info h1 {
    margin: 0;
    font-size: 1.8rem;
    color: #333;
}

.profile-info p {
    margin: 5px 0;
    font-size: 1rem;
    color: #666;
}

/* Forms Section */
.forms-container {
    margin: 20px;
    padding: 20px;
    background-color: white;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

.forms-container h2 {
    margin-bottom: 20px;
    font-size: 1.5rem;
    color: #333;
    border-bottom: 2px solid #4CAF50;
    padding-bottom: 5px;
}

.form-section {
    margin-bottom: 20px;
}

.form-section h3 {
    font-size: 1.2rem;
    color: #4CAF50;
    margin-bottom: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

table th, table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}

table th {
    background-color: #f4f4f4;
    color: #333;
    font-weight: bold;
}

table td input[type="text"] {
    width: 100%;
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 5px;
}

button[type="submit"] {
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
}

button[type="submit"]:hover {
    background-color: #45a049;
}

/* Responsive Design */
@media (max-width: 768px) {
    .profile-container {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .profile-picture {
        margin-bottom: 15px;
    }

    table {
        font-size: 0.9rem;
    }
}

</style>
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
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button type="submit">Save Changes</button>
        </form>
    <?php else: ?>
        <p>No responses submitted yet.</p>
    <?php endif; ?>
    <a href="user_profile.php">Back to Dashboa
        d</a>
</body>
</html>
