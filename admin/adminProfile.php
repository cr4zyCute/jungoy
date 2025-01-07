<?php
include '../database/dbcon.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}
$admin_id = $_SESSION['admin_id'];
$query = "SELECT * FROM admin WHERE id = '$admin_id'";
$result = mysqli_query($conn, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $admin = mysqli_fetch_assoc($result);
} else {
    echo "Admin profile not found.";
    exit();
}
$query = "SELECT * FROM admin WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
} else {
    echo "Admin profile not found.";
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_admin'])) {
    $username = $_POST['admin_username'];
    $email = $_POST['admin_email'];
    $password = $_POST['admin_password'];
    $name = $_POST['admin_name'];
    $profile_path = $admin['adminProfile'];

    if (!empty($_FILES['admin_profile']['name'])) {
        $target_dir = "../uploads/admin_profiles/";
        $file_name = uniqid() . '_' . basename($_FILES['admin_profile']['name']);
        $target_file = $target_dir . $file_name;

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        if (move_uploaded_file($_FILES['admin_profile']['tmp_name'], $target_file)) {
            $profile_path = $target_file;
        } else {
            echo "Error uploading file.";
        }
    }
    $update_query = $conn->prepare("
        UPDATE admin 
        SET admin_username = ?, admin_email = ?, admin_password = ?, admin_name = ?, adminProfile = ? 
        WHERE id = ?
    ");
    $update_query->bind_param("sssssi", $username, $email, $password, $name, $profile_path, $admin_id);

    if ($update_query->execute()) {
        header("Location: admin-dashboard.php");
        exit();
    } else {
        echo "Error updating admin details.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="./admin-css/adminProfile.css">
</head>

<body>
    <div class="header">
        <div class="logo">
            <img src="../images/bsitlogo.png" alt="Logo">
            <span>BSIT</span>
        </div>
        <div class="icons">
            <a href="./admin-dashboard.php"><i class="bi bi-house-door-fill"></i></a>
            <a href="#home"><i class="bi bi-envelope-fill"></i></a>
            <a href="admin-dashboard.php"><i class="bi bi-megaphone-fill announcement-icon"></i></a>
            <div class="dropdown">
                <a href="./adminProfile.php">
                    <?php if (!empty($admin['image'])): ?>
                        <img src="../images-data/<?= htmlspecialchars($admin['adminProfile']) ?>" alt="Profile Image" class="profile-image">
                    <?php else: ?>
                        <img src="../images-data/<?= htmlspecialchars($admin['adminProfile']) ?>" alt="Profile Image" class="profile-image">
                    <?php endif; ?>
                </a>
                <div class="dropdown-content">
                    <a href="#profile">Profile Settings</a>
                    <a href="./includes/logout.php">Log out</a>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard">
        <div class="left-section">
            <div class="profile">
                <?php if (!empty($admin['image'])): ?>
                    <img src="../images-data/<?= htmlspecialchars($admin['adminProfile']) ?>" alt="Profile Image" class="profile-image">
                <?php else: ?>
                    <img src="../images-data/<?= htmlspecialchars($admin['adminProfile']) ?>" alt="Profile Image" class="profile-image">
                <?php endif; ?>
                <button class="modal-trigger">Open Settings</button>

                <p><?= htmlspecialchars($admin['admin_username']) ?></p>
                <p>Email: <?= htmlspecialchars($admin['admin_email']) ?></p>

            </div>

        </div>
    </div>

    <div id="settingsModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" id="closeModalBtn">&times;</span>
            <h3 class="modal-title">Edit Admin Details</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="admin_profile">Profile Image:</label>
                    <input type="file" name="admin_profile" id="admin_profile">
                    <?php if (!empty($admin['adminProfile'])): ?>
                        <img src="<?= htmlspecialchars($admin['adminProfile']); ?>" alt="Admin Profile" class="profile-preview">
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="admin_username">Username:</label>
                    <input type="text" name="admin_username" id="admin_username" value="<?= htmlspecialchars($admin['admin_username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="admin_email">Email:</label>
                    <input type="email" name="admin_email" id="admin_email" value="<?= htmlspecialchars($admin['admin_email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="admin_name">Name:</label>
                    <input type="text" name="admin_name" id="admin_name" value="<?= htmlspecialchars($admin['admin_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="admin_password">Password:</label>
                    <div class="password-container">
                        <input type="password" name="admin_password" id="admin_password" value="<?= htmlspecialchars($admin['admin_password']); ?>" required>
                        <button id="togglePassword" type="button" class="toggle-password-btn">
                            <i id="eyeIcon" class="bi bi-eye-fill"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="modal-submit-btn">Save Changes</button>
            </form>
        </div>

        <script>
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('admin_password');
            const eyeIcon = document.getElementById('eyeIcon');

            togglePassword.addEventListener('click', () => {

                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';


                eyeIcon.classList.toggle('bi-eye-fill');
                eyeIcon.classList.toggle('bi-eye-slash-fill');
            });

            const modalTrigger = document.querySelector('.modal-trigger');
            const modal = document.getElementById('settingsModal');
            const closeModalBtn = document.getElementById('closeModalBtn');

            modalTrigger.addEventListener('click', () => {
                modal.style.display = 'flex';
            });


            closeModalBtn.addEventListener('click', () => {
                modal.style.display = 'none';
                l
            });

            window.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        </script>
</body>

</html>