<?php
include 'database/dbcon.php';
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve POST data
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; // Hash the password
    $admin_notified = 0; // Default value for admin notification
    $approved = 0; // Default value for student approval

    // File upload handling
    $file_name = '';
    if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == 0) {
        $file_name = basename($_FILES['profilePicture']['name']);
        $tempname = $_FILES['profilePicture']['tmp_name'];
        $folder = 'images-data/' . $file_name;

        // Validate and move uploaded file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array(mime_content_type($tempname), $allowed_types)) {
            if (!move_uploaded_file($tempname, $folder)) {
                echo "Failed to upload image.";
                exit();
            }
        } else {
            echo "Invalid image file type.";
            exit();
        }
    }

    // Insert student data
    $sql = "INSERT INTO student (firstname, middlename, lastname, image, approved, admin_notified) 
            VALUES ('$firstname', '$middlename', '$lastname', '$file_name', '$approved', '$admin_notified')";

    if (mysqli_query($conn, $sql)) {
        $student_id = mysqli_insert_id($conn); // Get the last inserted ID

        // Insert credentials data
        $credentials_sql = "INSERT INTO credentials (student_id, email, password) 
                            VALUES ('$student_id', '$email', '$password')";

        if (mysqli_query($conn, $credentials_sql)) {
            // Add a notification for the admin
            $notification_message = "A new student has registered: $firstname ($email)";
            $notification_sql = "INSERT INTO notifications (message, student_id, created_at, is_read) 
                                 VALUES ('$notification_message', '$student_id', NOW(), 0)";

            if (mysqli_query($conn, $notification_sql)) {
                // Redirect with success message
                header("Location: RegistrationForm.php?update=success");
                exit();
            } else {
                echo "Error in adding notification: " . mysqli_error($conn);
            }
        } else {
            echo "Error in adding credentials: " . mysqli_error($conn);
        }
    } else {
        echo "Error in adding student: " . mysqli_error($conn);
    }

    mysqli_close($conn); // Close the database connection
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi-Step Form</title>
    <link rel="icon" href="./images/bsitlogo.png">
    <!-- <link rel="stylesheet" href="./css/registration.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #eaeaea;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        color: #333;
    }

    .main-container {
        display: flex;
        background: linear-gradient(135deg, #ffffff, #f7f9fc);
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        width: 90%;
        max-width: 1000px;
    }

    .left-section {
        flex: 1;
        background-color: #ff7200;
        color: white;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 40px;
    }

    .left-section h1 {
        font-size: 28px;
        margin-bottom: 20px;
        text-align: center;
    }

    .left-section img {
        max-width: 150px;
        margin-bottom: 20px;
        border-radius: 50%;
        border: 3px solid white;
    }

    .left-section .login-btn {
        margin-top: 20px;
        background-color: white;
        color: #ff7200;
        border: none;
        border-radius: 25px;
        padding: 12px 30px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .left-section .login-btn:hover {
        background-color: #ff7200;
        color: white;
    }

    .right-section {
        flex: 2;
        padding: 40px;
    }

    .form-container {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    label {
        font-weight: bold;
        color: #555;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 10px;
        border: 2px solid #ddd;
        border-radius: 10px;
        font-size: 16px;
        transition: border-color 0.3s ease;
    }

    input:focus {
        border-color: #ff7200;
        outline: none;
    }

    .profile-pic {
        text-align: center;
        position: relative;
    }

    .profile-pic img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 2px solid #ddd;
        margin-bottom: 10px;
    }

    .edit-btn {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background-color: #ff7200;
        color: white;
        border: none;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
    }

    .edit-btn:hover {
        background-color: #ff7200;
    }

    .next-btn,
    .prev-btn,
    .register-btn {
        background-color: #ff7200;
        color: white;
        border: none;
        border-radius: 25px;
        padding: 12px 20px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        align-self: flex-end;
        transition: all 0.3s ease;
    }

    .next-btn:hover,
    .prev-btn:hover,
    .register-btn:hover {
        background-color: #ff7200;
    }

    button i {
        margin-right: 5px;
    }

    @media (max-width: 768px) {
        .main-container {
            flex-direction: column;
        }

        .left-section {
            width: 100%;
            text-align: center;
            padding: 20px;
        }

        .right-section {
            width: 100%;
            padding: 20px;
        }
    }

    .modal-section {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background: rgba(0, 0, 0, 0.5);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .modal-section.active {
        opacity: 1;
        visibility: visible;
    }

    .modal-box {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .modal-box .fa-circle-check {
        font-size: 48px;
        color: #28a745;
        margin-bottom: 10px;
    }

    .modal-box h2 {
        margin-bottom: 10px;
        font-size: 24px;
        color: #333;
    }

    .modal-box h3 {
        margin-bottom: 20px;
        font-size: 18px;
        color: #555;
    }

    .modal-box .buttons button {
        padding: 10px 20px;
        margin: 5px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .modal-box .close-btn {
        background: #ff7200;
        color: #fff;
    }

    .profile-section {
        flex: 1 1 35%;
        padding: 20px;
        text-align: center;
        position: relative;
    }

    .profile-pic {
        position: relative;
        bottom: 55%;
        display: inline-block;
        width: 150px;
        height: 150px;
        margin: 0 auto 20px;
        border-radius: 50%;

        background-color: #e6e6e6;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border: #ff7200 solid 5px;
    }

    .profile-pic img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .button-group {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
    }

    button.prev-btn,
    button.next-btn {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: none;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #ff7200;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    button.prev-btn:hover,
    button.next-btn:hover {
        background-color: #ff7200;
    }

    button.prev-btn i,
    button.next-btn i {
        pointer-events: none;
    }
</style>

<body>
    <div class="main-container">
        <div class="left-section">
            <h1>Please Fill up the Following</h1>
            <img src="images/bsitlogo.png" alt="Image Description" style="width: 200px; height: auto; border-radius: 10px; margin-top: 20px;"><br>
            <a href="index.php">
                <button class="login-btn">Login</button>
            </a>
        </div>
        <div class="right-section">
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-container">
                    <div class="form-step active">
                        <div class="form-group">
                            <label for="firstname">Firstname:</label>
                            <input type="text" id="firstname" name="firstname" required>
                            <label for="middlename">Middlename:</label>
                            <input type="text" id="middlename" name="middlename" required>
                        </div>
                        <div class="form-group">
                            <label for="lastname">Lastname:</label>
                            <input type="text" id="lastname" name="lastname" required>
                        </div>
                        <!-- <button type="button" class="next-btn"><i class="fas fa-chevron-right"></i></button> -->
                    </div>
                    <div class="form-step">
                        <div class="form-group">
                            <div class="profile-pic">
                                <img id="profileImage" src="./images/defaultProfile.jpg">
                                <label for="profilePicture">Profile Picture:</label>
                                <button type="button" class="edit-btn" id="editButton">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <input type="file" id="profilePicture" name="profilePicture" accept="image/*" style="display: none;" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email">
                            <label for="password">Password:</label>
                            <input type="password" id="password" name="password">
                        </div>
                        <!-- <button type="button" class="prev-btn"><i class="fas fa-chevron-left"></i></button> -->
                        <button type="submit" name="submit" class="register-btn">Register</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <section class="modal-section">
        <span class="overlay"></span>
        <div class="modal-box">
            <!-- <i class="fa-regular fa-circle-check" style="font-size: 50px; color:green;"></i> -->
            <h2>Success</h2>
            <h3>You have successfully registered!</h3>
            <div class="buttons">
                <a href="index.php">
                    <button class="close-btn">OK</button>
                </a>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const steps = document.querySelectorAll('.form-step');
            const nextBtns = document.querySelectorAll('.next-btn');
            const prevBtns = document.querySelectorAll('.prev-btn');
            let currentStep = 0;

            const updateSteps = () => {
                steps.forEach((step, index) => {
                    step.classList.toggle('active', index === currentStep);
                });
            };

            const validateStep = (stepIndex) => {
                const inputs = steps[stepIndex].querySelectorAll('input[required]');
                let isValid = true;

                inputs.forEach((input) => {
                    if (!input.value.trim() || (input.type === 'radio' && !document.querySelector(`input[name="${input.name}"]:checked`))) {
                        input.classList.add('error');
                        isValid = false;
                    } else {
                        input.classList.remove('error');
                    }
                });

                return isValid;
            };

            nextBtns.forEach((btn) => {
                btn.addEventListener('click', () => {
                    if (validateStep(currentStep) && currentStep < steps.length - 1) {
                        currentStep++;
                        updateSteps();
                    }
                });
            });

            prevBtns.forEach((btn) => {
                btn.addEventListener('click', () => {
                    if (currentStep > 0) {
                        currentStep--;
                        updateSteps();
                    }
                });
            });

            updateSteps();
        });

        const profileInput = document.getElementById('profilePicture');
        const profileImage = document.getElementById('profileImage');
        const editButton = document.getElementById('editButton');

        editButton.addEventListener('click', function() {
            profileInput.click();
        });

        profileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const section = document.querySelector(".modal-section"),
                overlay = document.querySelector(".overlay"),
                closeBtn = document.querySelector(".close-btn");
            if (urlParams.get('update') === 'success') {
                section.classList.add("active");
            }
            overlay.addEventListener("click", () => section.classList.remove("active"));
            closeBtn.addEventListener("click", () => section.classList.remove("active"));

            window.history.replaceState({}, document.title, window.location.pathname);
        });
    </script>
</body>

</html>