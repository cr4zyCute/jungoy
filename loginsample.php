<?php
session_start();
require 'database/dbcon.php';

if (empty($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}
$student_id = $_SESSION['student_id'];

// Use a prepared statement to avoid SQL injection
$query = "
    SELECT student.*, credentials.email, credentials.password
    FROM student 
    JOIN credentials ON student.id = credentials.student_id 
    WHERE student.id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id); // "i" for integer
$stmt->execute();
$result = $stmt->get_result();

if ($result && mysqli_num_rows($result) > 0) {
    $student = mysqli_fetch_assoc($result);
} else {
    echo "Student profile not found.";
    exit();
}

// Define default image path if not set
$profileImage = !empty($student['image']) && file_exists('images-data/' . $student['image']) 
    ? 'images-data/' . $student['image'] 
    : 'images-data/default-profile.png'; // Default image if not set or image not found
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <title>Student Profile</title>
  <style>
    /* Popup Styles */
    .popup {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background-color: white;
      padding: 20px;
      box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
      border-radius: 8px;
      z-index: 1000;
    }
    .popup.active {
      display: block;
    }
    .popup-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 999;
    }
    .popup-overlay.active {
      display: block;
    }
    .error-message {
      color: red;
      font-size: 0.9em;
    }
    .popup form input {
      display: block;
      width: 100%;
      margin: 10px 0;
      padding: 8px;
    }
    .popup form button {
      padding: 10px 20px;
      background-color: blue;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    /* Card Styles */
    .grid-container {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 20px;
    }
    .card {
      padding: 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
    }
    .card h3 {
      margin-top: 0;
    }
    /* Profile Image Styles */
    .profile-pic {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  
  <div class="popup-overlay" id="popupOverlay"></div>
  <div class="popup" id="loginPopup">
    <div class="grid-container">
      <div class="card">
        <a href="studentProfile.php">
          <img src="<?= $profileImage ?>" alt="Profile Image" class="profile-pic">
        </a>
        <h3>Information</h3>
        <form action="updateProfile.php" method="POST">
          <label for="firstname">First Name</label>
          <input type="text" id="firstname" name="firstname" value="<?= htmlspecialchars($student['firstname']) ?>" required>

          <label for="middlename">Middle Name</label>
          <input type="text" id="middlename" name="middlename" value="<?= htmlspecialchars($student['middlename']) ?>" required>

          <label for="lastname">Last Name</label>
          <input type="text" id="lastname" name="lastname" value="<?= htmlspecialchars($student['lastname']) ?>" required>

          <label for="age">Age</label>
          <input type="number" id="age" name="age" value="<?= htmlspecialchars($student['age']) ?>" required>

          <label for="gender">Gender</label>
          <select id="gender" name="gender">
            <option value="Male" <?= $student['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= $student['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= $student['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
          </select>

          <button type="submit" name="updateProfile" style="background-color: green; color: white;">Save Changes</button>
        </form>
      </div>
      <div class="card">
        <h3>Additional Information</h3>
        <form action="updateProfile.php" method="POST">
          <label for="yearlvl">Year Level</label>
          <input type="text" id="yearlvl" name="yearlvl" value="<?= htmlspecialchars($student['yearlvl']) ?>" required>

          <label for="section">Section</label>
          <input type="text" id="section" name="section" value="<?= htmlspecialchars($student['section']) ?>" required>

          <label for="contact">Contact</label>
          <input type="text" id="contact" name="contact" value="<?= htmlspecialchars($student['contact']) ?>" required>

          <label for="address">Address</label>
          <input type="text" id="address" name="address" value="<?= htmlspecialchars($student['address']) ?>" required>

          <button type="submit" name="updateProfile" style="background-color: green; color: white;">Save Changes</button>
        </form>
      </div>
      <div class="card">
        <h3>Credentials</h3>
        <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
        <p>
            <strong>Password:</strong>
            <span id="passwordField"><?= htmlspecialchars($student['password']) ?></span>
            <button id="togglePassword" style="background: none; border: none; cursor: pointer; margin-left: 10px;">
                <i id="eyeIcon" class="bi bi-eye-fill"></i>
            </button>
        </p>
      </div>
    </div>
  </div>

  <button onclick="openPopup()">Open Profile</button>

  <script>
    const popup = document.getElementById('loginPopup');
    const overlay = document.getElementById('popupOverlay');

    function openPopup() {
      popup.classList.add('active');
      overlay.classList.add('active');
    }

    function closePopup() {
      popup.classList.remove('active');
      overlay.classList.remove('active');
    }

    // Handle password visibility toggle
    document.getElementById('togglePassword').addEventListener('click', function () {
      const passwordField = document.getElementById('passwordField');
      const eyeIcon = document.getElementById('eyeIcon');
      if (passwordField.type === 'password') {
        passwordField.type = 'text';
        eyeIcon.classList.remove('bi-eye-fill');
        eyeIcon.classList.add('bi-eye-slash-fill');
      } else {
        passwordField.type = 'password';
        eyeIcon.classList.remove('bi-eye-slash-fill');
        eyeIcon.classList.add('bi-eye-fill');
      }
    });

    // Close popup when overlay is clicked
    overlay.addEventListener('click', closePopup);
  </script>
</body>
</html>
