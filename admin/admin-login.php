<?php
session_start();
include '../database/dbcon.php';
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $email_check_sql = "SELECT * FROM admin WHERE admin_email = '$email'";
    $email_check_result = mysqli_query($conn, $email_check_sql);

    if (mysqli_num_rows($email_check_result) === 0) {
        $error_message = "Admin does not exist!";
    } else {
        $user = mysqli_fetch_assoc($email_check_result);
        if ($user['admin_password'] === $password) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_email'] = $user['admin_email'];
            header("Location: admin-dashboard.php");

            exit();
        } else {
            $error_message = "Wrong password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet" />
    <title>Login Page</title>
</head>

<body>
    <div class="container" id="container">

        <div class="sign-in">
            < <form action="admin-login.php" method="post">
                <section id="formContainer" class="<?= !empty(trim($error_message)) ? 'show' : '' ?>">
                    <div class="ring">
                        <i style="--clr:#d7dbdd;"></i>
                        <i style="--clr:#d7dbdd;"></i>
                        <i style="--clr:#d7dbdd;"></i>
                        <div class="login">
                            <div class="inputBx">
                                <?php if (!empty($error_message)) : ?>
                                    <p style="color: red;" id="errorMessage" style="color: white;"><?= $error_message; ?></p>
                                <?php endif; ?>
                                <input type="text" name="email" placeholder="Username" required>
                            </div>
                            <div class="inputBx">
                                <input type="password" name="password" placeholder="Password" required>
                            </div>
                            <div class="inputBx">
                                <input type="submit" name="login" value="Log in">
                            </div>
                            <div class="links">
                            </div>
                        </div>
                    </div>
                </section>
                </form>
        </div>
        <div class="toogle-container">
            <div class="toogle">
                <div class="toogle-panel toogle-right">
                    <img style="width: 45%;" src="../images/bsitlogo.png" alt="">
                    <p>Welcome Back!</p>
                    <h1>Hello, Admin!</h1>

                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const errorMessage = document.getElementById('errorMessage');
            if (errorMessage) {
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 3000);
            }
        });
    </script>

</body>

</html>

</html>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
    }

    body {
        margin: 0;
        padding: 0;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(45deg, rgb(0, 0, 0), rgb(70, 70, 70), rgb(175, 175, 175), rgb(255, 255, 255));
        background-size: 600% 600%;
        animation: abstractBackground 12s ease infinite;
        color: #fff;
    }

    @keyframes abstractBackground {
        0% {
            background-position: 0% 50%;
        }

        25% {
            background-position: 100% 50%;
        }

        50% {
            background-position: 100% 100%;
        }

        75% {
            background-position: 0% 100%;
        }

        100% {
            background-position: 0% 50%;
        }
    }


    .container {
        background-color: #fff;
        border-radius: 150px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
        position: relative;
        overflow: hidden;
        width: 768px;
        max-width: 100%;
        min-height: 480px;
    }

    .container p {
        font-size: 14px;
        line-height: 20px;
        letter-spacing: 0.3px;
        margin: 20px 0;
    }


    .container span {
        font-size: 12px;
    }

    .container a {
        color: #333;
        font-size: 13px;
        text-decoration: none;
        margin: 15px 0 10px;
    }

    .container button {
        background-color: #a82d2d;
        color: #fff;
        padding: 10px 45px;
        border: 1px solid transparent;
        border-radius: 8px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-top: 10px;
        cursor: pointer;
    }

    .container button.hidden {
        background-color: transparent;
        border-color: #fff;
    }

    .container form {
        background-color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        padding: 0 40px;
        height: 100%;
    }

    .container input {
        background-color: #eee;
        border: none;
        margin: 8px 0;
        padding: 10px 15px;
        font-size: 13px;
        border-radius: 8px;
        width: 100%;
        outline: none;
    }

    .sign-up,
    .sign-in {
        position: absolute;
        top: 0;
        height: 100%;
        transition: all 0.6s ease-in-out;
    }

    .sign-in {
        left: 0;
        width: 50%;
        z-index: 2;
    }

    .container.active .sign-in {
        transform: translateX(100%);
    }

    .sign-up {
        left: 0;
        width: 50%;
        z-index: 1;
        opacity: 0;
    }

    .container.active .sign-up {
        transform: translateX(100%);
        opacity: 1;
        z-index: 5;
        animation: move 0.6s;
    }

    @keyframes move {

        0%,
        49.99% {
            opacity: 0;
            z-index: 1;
        }

        50%,
        100% {
            opacity: 1;
            z-index: 5;
        }
    }

    .icons {
        margin: 20px 0;
    }

    .icons a {
        border: 1px solid #ccc;
        border-radius: 20%;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        margin: 0 3px;
        width: 40px;
        height: 40px;
    }

    .toogle-container {
        position: absolute;
        top: 0;
        left: 50%;
        width: 50%;
        height: 100%;
        overflow: hidden;
        border-radius: 150px;
        z-index: 1000;
        transition: all 0.6s ease-in-out;
    }

    .container.active .toogle-container {
        transform: translateX(-100%);
        border-radius: 150px;
    }

    .toogle {
        background-color: rgb(45, 104, 168);
        height: 100%;
        background: linear-gradient(to right, rgb(92, 125, 192), rgb(30, 61, 132));
        color: #fff;
        position: relative;
        left: -100%;
        width: 200%;
        transform: translateX(0);
        transition: all 0.6s ease-in-out;
    }

    .container.active .toogle {
        transform: translateX(50%);
    }

    .toogle-panel {
        position: absolute;
        width: 50%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        padding: 0 30px;
        text-align: center;
        top: 0;
        transform: translateX(0);
        transition: all 0.6s ease-in-out;
    }

    .toogle-left {
        transform: translateX(-200%);
    }

    .container.active .toogle-left {
        transform: translateX(0);
    }

    .toogle-right {
        right: 0;
        transform: translateX(0);
    }

    .container.active .toogle-right {
        transform: translateX(200%);
    }
</style>