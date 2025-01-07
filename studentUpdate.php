<?php
require 'database/dbcon.php';
session_start();

if (!empty($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];

    $query = "
        SELECT student.*, credentials.email, credentials.password
        FROM student 
        JOIN credentials ON student.id = credentials.student_id 
        WHERE student.id = '$student_id'
    ";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $student = mysqli_fetch_assoc($result);
    } else {
        echo "Student profile not found.";
        exit();
    }
} else {
    header("Location: student.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname'] ?? $student['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename'] ?? $student['middlename']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname'] ?? $student['lastname']);
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? $student['email']);
    $password = mysqli_real_escape_string($conn, isset($_POST['password']) ? $_POST['password'] : $student['password']);

    $imageQueryPart = "";
    $imageName = basename($_FILES['profileImage']['name']);
    $imagePath = 'images-data/' . $imageName;

    $imageQueryPart = "";
    if (!empty($_FILES['profileImage']['name'])) {
        $imageName = basename($_FILES['profileImage']['name']);
        $imagePath = 'images-data/' . $imageName;

        if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $imagePath)) {
            $imageQueryPart = ", student.image = '$imageName'";
        } else {
            echo "Failed to upload image.";
            exit();
        }
    }
    $updateQuery = "
        UPDATE student 
        JOIN credentials ON student.id = credentials.student_id
        SET 
            student.firstname = '$firstname',
            student.middlename = '$middlename',
            student.lastname = '$lastname',

            credentials.email = '$email',
            credentials.password = '$password'
               $imageQueryPart
        WHERE student.id = '$student_id'
    ";
    if (mysqli_query($conn, $updateQuery)) {
        header("Location: studentProfile.php");
        exit();
    } else {
        echo "Error updating profile: " . mysqli_error($conn);
    }
}
$student_query = $conn->prepare("SELECT approved FROM student WHERE id = ?");
$student_query->bind_param('i', $student_id);
$student_query->execute();
$student_query->bind_result($approved);
$student_query->fetch();
$student_query->close();

$notifications_query = $conn->prepare("SELECT message FROM notifications WHERE student_id = ? AND is_read = 0");
$notifications_query->bind_param('i', $student_id);
$notifications_query->execute();
$notifications_result = $notifications_query->get_result();

$mark_read_query = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE student_id = ? AND is_read = 0");
$mark_read_query->bind_param('i', $student_id);
$mark_read_query->execute();

$forms_query = $conn->prepare("SELECT f.id AS form_id, f.form_name FROM student_forms sf
                               JOIN forms f ON sf.form_id = f.id
                               WHERE sf.student_id = ?");

$forms_query->bind_param('i', $student_id);
$forms_query->execute();
$forms_result = $forms_query->get_result();
$forms_query->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Document</title>
</head>
<style>
    /* General Body Styling */
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f9;
        color: #333;
    }

    /* Popup Edit Styling */
    .popup-edit {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;
        max-width: 800px;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 1000;
        padding: 20px;
    }

    .popup-edit .close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 20px;
        cursor: pointer;
        color: #999;
    }

    .popup-edit .close:hover {
        color: #000;
    }

    /* Grid Layout */
    .grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    /* Card Styling */
    .card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 15px;
        border: 1px solid #ddd;
        text-align: left;
    }

    .card h3 {
        margin-top: 0;
        color: #007bff;
        font-size: 18px;
    }

    /* Profile Picture Styling */
    .profile-picture {
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-bottom: 15px;
    }

    .profile-picture img {
        border-radius: 50%;
        object-fit: cover;
        width: 120px;
        height: 120px;
        margin-bottom: 10px;
    }

    .profile-picture .edit-btn {
        background: #007bff;
        color: #fff;
        padding: 5px 10px;
        border-radius: 5px;
        cursor: pointer;
        text-align: center;
    }

    .profile-picture .edit-btn:hover {
        background: #0056b3;
    }

    /* Form Input Styling */
    form label {
        font-weight: bold;
        display: block;
        margin-top: 10px;
        margin-bottom: 5px;
    }

    form input[type="text"],
    form input[type="password"] {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        margin-bottom: 10px;
        font-size: 14px;
    }

    form input[type="text"]:focus,
    form input[type="password"]:focus {
        border-color: #007bff;
        outline: none;
    }

    /* Button Styling */
    button[type="submit"] {
        display: block;
        width: 100%;
        background: #28a745;
        color: #fff;
        border: none;
        padding: 10px 15px;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        text-align: center;
    }

    button[type="submit"]:hover {
        background: #218838;
    }

    .btn-green {
        background-color: #28a745;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-green:hover {
        background-color: #218838;
    }

    /* Toggle Password Eye Icon */
    #togglePassword {
        position: relative;
    }

    #eyeIcon {
        font-size: 16px;
        color: #666;
    }

    #eyeIcon:hover {
        color: #333;
    }

    /* Responsive Design */
    @media screen and (max-width: 600px) {
        .grid-container {
            grid-template-columns: 1fr;
        }

        .popup-edit {
            width: 100%;
            max-width: 400px;
        }

        .card {
            padding: 10px;
        }

        button[type="submit"] {
            font-size: 14px;
        }
    }
</style>

<body>
    <div class="popup-edit" id="loginPopup">
        <a href="studentProfile.php"><i class="bi bi-arrow-left-circle-fill"></i></a>
        <span class="close">&times;</span>
        <div class="grid-container">
            <!-- Profile Card -->
            <div class="card">
                <form action="studentProfile.php" method="POST" enctype="multipart/form-data">
                    <div class="profile-picture">
                        <?php
                        $imagePath = 'images-data/' . htmlspecialchars($student['image']);
                        if (!empty($student['image']) && file_exists($imagePath)) {
                            echo '<img src="' . $imagePath . '?v=' . time() . '" style="width:120px; height:120px;" alt="Profile Image" id="profileDisplay"  >';
                        } else {
                            echo '<img src="images-data/default-image.png" style="width:120px; height:120px;" alt="Default Image" id="profileDisplay">';
                        }
                        ?>
                        <input type="file" id="profileImageUpload" name="profileImage" accept="image/*" onchange="previewImage(event)" hidden>
                        <div class="edit-btn" onclick="document.getElementById('profileImageUpload').click()">Edit</div>
                    </div>
                    <h3>Information</h3>

                    <label for="firstname">First Name</label>
                    <input type="text" id="firstname" name="firstname" value="<?= htmlspecialchars($student['firstname']) ?>" required>

                    <label for="middlename">Middle Name</label>
                    <input type="text" id="middlename" name="middlename" value="<?= htmlspecialchars($student['middlename']) ?>" required>

                    <label for="lastname">Last Name</label>
                    <input type="text" id="lastname" name="lastname" value="<?= htmlspecialchars($student['lastname']) ?>" required>
            </div>


            <div class="card">
                <h3>Additional Information</h3>
                <?php
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
                        while ($form = $forms_result->fetch_assoc()) {
                            $form_id = $form['form_id'];
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

                                echo "<a href='view_responses.php?form_id=" . $form['form_id'] . "'>edit<i class='bi bi-pencil-square'></i></a>";


                                while ($response = $responses_result->fetch_assoc()) {
                                    echo "<li style = 'list-style-type: none;'> " . htmlspecialchars($response['field_name']) . ":  " . htmlspecialchars($response['response']) . "</li>";
                                }
                                echo "</ul>";
                            } else {
                            }
                        }
                    } else {
                        echo "<p>No forms found for this student.</p>";
                    }
                } else {
                    header("Location: studentProfile.php");
                    exit();
                }
                ?>
            </div>
            <div class="card">
                <h3>Credentials</h3>
                <label for="email">Address</label>
                <input type="text" id="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>
                <?php

                if (!empty($_SESSION['student_id'])) {
                    $student_id = $_SESSION['student_id'];

                    $query = "
        SELECT student.*, credentials.email, credentials.password
        FROM student 
        JOIN credentials ON student.id = credentials.student_id 
        WHERE student.id = '$student_id'
    ";
                    $result = mysqli_query($conn, $query);

                    if ($result && mysqli_num_rows($result) > 0) {
                        $student = mysqli_fetch_assoc($result);
                    }
                }
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $password = mysqli_real_escape_string($conn, isset($_POST['password']) ? $_POST['password'] : $student['password']);
                    $updateQuery = "
                        UPDATE student 
                        JOIN credentials ON student.id = credentials.student_id
                        SET
                            credentials.password = '$password'
                        WHERE student.id = '$student_id'
                    ";
                    if (mysqli_query($conn, $updateQuery)) {
                        header("Location: studentProfile.php");
                        exit();
                    }
                }
                ?>
                <p>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" value="<?= htmlspecialchars($student['password']) ?>">
                    <button id="togglePassword" style="background: none; border: none; cursor: pointer; margin-left: 10px;">
                        <i id="eyeIcon" class="bi bi-eye-fill"></i>
                    </button>
                </p>

            </div>
        </div>
        <button type="submit" name="updatePersonalInfo" class="btn btn-green">Save Changes</button>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const passwordField = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const eyeIcon = document.getElementById('eyeIcon');

            togglePassword.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent form submission when the button is clicked

                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    eyeIcon.className = 'bi bi-eye-slash-fill'; // Change icon to "eye-slash" when visible
                } else {
                    passwordField.type = 'password';
                    eyeIcon.className = 'bi bi-eye-fill'; // Change icon back to "eye" when hidden
                }
            });
        });
    </script>
</body>

</html>