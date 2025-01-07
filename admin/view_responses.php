<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

include '../database/dbcon.php';

if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);

    // Fetch student details
    $student_query = $conn->prepare("
        SELECT student.*, credentials.email 
        FROM student
        LEFT JOIN credentials ON student.id = credentials.student_id
        WHERE student.id = ?
    ");
    $student_query->bind_param('i', $student_id);
    $student_query->execute();
    $student_result = $student_query->get_result();

    if ($student_result->num_rows > 0) {
        $student = $student_result->fetch_assoc();
    } else {
        die("Student not found.");
    }

    // Fetch forms assigned to the student
    $forms_query = $conn->prepare("
        SELECT f.id AS form_id, f.form_name 
        FROM student_forms sf
        JOIN forms f ON sf.form_id = f.id
        WHERE sf.student_id = ?
    ");
    $forms_query->bind_param('i', $student_id);
    $forms_query->execute();
    $forms_result = $forms_query->get_result();
} else {
    die("No student ID provided.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $responses = $_POST['responses'] ?? [];

    foreach ($responses as $response_id => $response) {
        // Update each response in the database
        $update_query = $conn->prepare("
            UPDATE form_responses 
            SET response = ? 
            WHERE id = ?
        ");
        $update_query->bind_param('si', $response, $response_id);
        $update_query->execute();
    }

    header("Location: viewresponses.php?id=$student_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Student Profile</title>
    <link rel="stylesheet" href="./admin-css/studentProfile.css">
</head>
<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: #c0392b;
        color: #333;
    }

    /* Header Styles */
    header {
        background-color: #c0392b;
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
        border: 4px solid #c0392b;
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
        border-bottom: 2px solid #c0392b;
        padding-bottom: 5px;
    }

    .form-section {
        margin-bottom: 20px;
    }

    .form-section h3 {
        font-size: 1.2rem;
        color: #c0392b;
        margin-bottom: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    table th,
    table td {
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
        background-color: #c0392b;
        color: white;
        border: none;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
    }

    button[type="submit"]:hover {
        background-color: #c0392b;
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
    <header>
        <nav>
            <a href="./admin-dashboard.php"><i class="bi bi-arrow-90deg-left"></i></a>
            <div class="logo">BSIT</div>
        </nav>
    </header>

    <div class="profile-container">
        <div class="profile-picture">
            <img src="<?= file_exists('../images-data/' . htmlspecialchars($student['image'])) && !empty($student['image']) ? '../images-data/' . htmlspecialchars($student['image']) : '../images-data/default-image.png'; ?>"
                alt="Profile Image" class="profile-image" style="width: 120px; height: 120px;">
        </div>
        <div class="profile-info">
            <h1><?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']); ?></h1>
            <p><strong>ID:</strong> <?= htmlspecialchars($student['id']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($student['email']); ?></p>
        </div>
    </div>

    <div class="forms-container">
        <h2>Assigned Forms</h2>
        <form method="POST" action="">
            <?php if ($forms_result->num_rows > 0): ?>
                <?php while ($form = $forms_result->fetch_assoc()): ?>
                    <div class="form-section">
                        <h3><?= htmlspecialchars($form['form_name']); ?></h3>
                        <table border="1">
                            <thead>
                                <tr>
                                    <th>Field</th>
                                    <th>Response</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $responses_query = $conn->prepare("
                                SELECT fr.id as response_id, ff.field_name, fr.response 
                                FROM form_responses fr 
                                JOIN form_fields ff ON fr.field_id = ff.id 
                                WHERE fr.student_id = ? AND fr.form_id = ?
                            ");
                                $responses_query->bind_param('ii', $student_id, $form['form_id']);
                                $responses_query->execute();
                                $responses_result = $responses_query->get_result();

                                if ($responses_result->num_rows > 0):
                                    while ($response = $responses_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($response['field_name']); ?></td>
                                            <td>
                                                <input type="text" name="responses[<?= $response['response_id']; ?>]"
                                                    value="<?= htmlspecialchars($response['response']); ?>">
                                            </td>
                                        </tr>
                                    <?php endwhile;
                                else: ?>
                                    <tr>
                                        <td colspan="2">No responses found for this form.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endwhile; ?>
                <button type="submit">Save Changes</button>
            <?php else: ?>
                <p>No forms assigned to this student.</p>
            <?php endif; ?>
        </form>
    </div>

</body>

</html>