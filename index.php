<?php
session_start();
include 'database/dbcon.php';

$error_message = "";
$show_popup = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $show_popup = true;
    if (!empty($_POST['email']) && !empty($_POST['password'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);

        $email_check_sql = "SELECT * FROM credentials WHERE email = '$email'";
        $email_check_result = mysqli_query($conn, $email_check_sql);

        if (mysqli_num_rows($email_check_result) === 0) {
            $error_message = "User does not exist!";
        } else {
            $user = mysqli_fetch_assoc($email_check_result);
            if ($user['password'] === $password) {
                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['email'] = $user['email'];
                header("Location: studentProfile.php");

                exit();
            } else {
                $error_message = "Wrong password!";
            }
        }
    } else {
        $error_message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Webpage Design</title>
    <link rel="stylesheet" href="./css/index.css">
</head>

<body>

    <div class="main">
        <div class="navbar">
            <div class="icon">
                <h2 class="logo">BSIT</h2>
            </div>

            <div class="menu">

            </div>
        </div>
        <div class="content">
            <h1>BSIT<br><span>Bachelor in Science</span> <br>Information Tec</h1>
            <p class="par">A Bachelor of Science in Information Technology (B.Sc IT)<br>degree program typically takes three to four years depending on the country.<br>This degree is primarily focused on subjects such as software, databases,<br> and networking.
                The degree is a Bachelor of Science degree with<br> institutions conferring degrees in the fields of information technology<br> and related fields.
            </p>

            <button class="cn"><a href="https://www.ctu.edu.ph/">JOIN US</a></button>

            <div class="form">
                <form action="" method="post">


                    <div class="title">Log in</div>
                    <p id="error-message" style="color:red"><?php echo $error_message; ?></p>

                    <input type="text" name="email" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>

                    <div class="register__here">
                        <p>Don't have an account?</p>
                        <a href="RegistrationForm.php">Register Here!</a>
                    </div>
                    <button type="submit" name="login" class="submit button">Login Now</button>

                </form>


            </div>
        </div>
    </div>
    </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const errorMessage = document.getElementById("error-message");

            if (errorMessage) {
                // Set a timeout to hide the error message after 5 seconds
                setTimeout(() => {
                    errorMessage.style.display = "none";
                }, 3000);
            }
        });
    </script>
    </script>
</body>

</html>