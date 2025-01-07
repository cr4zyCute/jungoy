<?php
include '../database/dbcon.php';
session_start();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_username = $_POST['admin_username'];
    $admin_name = $_POST['admin_name'];
    $admin_email = $_POST['admin_email'];
    $admin_password = $_POST['admin_password'];

    if (isset($_FILES['adminProfile']) && $_FILES['adminProfile']['error'] == 0) {

        $uploadDir = '../uploads/admin_profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); 
        }

        $fileName = uniqid() . '-' . basename($_FILES['adminProfile']['name']);
        $targetFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['adminProfile']['tmp_name'], $targetFilePath)) {
          
            $stmt = $conn->prepare("INSERT INTO admin (adminProfile, admin_username, admin_email, admin_password,admin_name) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $targetFilePath, $admin_username, $admin_email, $admin_password, $admin_name);

            if ($stmt->execute()) {
                echo "<p style='color: green;'>Admin registered successfully!</p>";
            } else {
                echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
            }

            $stmt->close();
        } else {
            echo "<p style='color: red;'>Error: Failed to upload the image.</p>";
        }
    } else {
        echo "<p style='color: red;'>Error: Please upload an image.</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 50%;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
            margin: 10px 0 5px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="file"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            margin-top: 20px;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Register Admin</h1>
        <form action="admin_registration.php" method="POST" enctype="multipart/form-data">
            <label for="admin_username">Role:</label>
            <select id="admin_username" name="admin_username" required>
                <option value="admin">Admin</option>
                <option value="teacher">Teacher</option>
            </select>
            <label for="admin_email">Email:</label>
            <input type="email" id="admin_email" name="admin_email" required>

            <label for="admin_name">Name:</label>
            <input type="text" id="admin_name" name="admin_name" required>

            <label for="admin_password">Password:</label>
            <input type="password" id="admin_password" name="admin_password" required>

            <label for="adminProfile">Profile Image:</label>
            <input type="file" id="adminProfile" name="adminProfile" accept="image/*" required>

            <button type="submit">Register</button>
        </form>
    </div>
</body>

</html>