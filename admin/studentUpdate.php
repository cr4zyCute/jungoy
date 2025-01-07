<?php
include '../database/dbcon.php';

if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);

    $query = "
        SELECT student.*, credentials.email, credentials.password 
        FROM student
        LEFT JOIN credentials ON student.id = credentials.student_id
        WHERE student.id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
    } else {
        die("Student not found.");
    }
} else {
    die("No student ID provided.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname'] ?? $student['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename'] ?? $student['middlename']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname'] ?? $student['lastname']);
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? $student['email']);
    $password = $_POST['password'] ?? $student['password'];
    $imageQueryPart = "";
    $parameters = [$firstname, $middlename, $lastname, $email, $password, $student_id];

    if (!empty($_FILES['profileImage']['name'])) {
        $imageName = basename($_FILES['profileImage']['name']);
        $imagePath = '../images-data/' . $imageName;

        if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $imagePath)) {
            $imageQueryPart = ", student.image = ?";
            $parameters = [$firstname, $middlename, $lastname, $email, $password, $imageName, $student_id];
        } else {
            die("Failed to upload image.");
        }
    }


    $updateQuery = "
        UPDATE student 
        JOIN credentials ON student.id = credentials.student_id
        SET 
            student.firstname = ?, 
            student.middlename = ?, 
            student.lastname = ?, 
            credentials.email = ?, 
            credentials.password = ?
            $imageQueryPart
        WHERE student.id = ?
    ";
    $stmt = $conn->prepare($updateQuery);

    $paramTypes = str_repeat('s', count($parameters) - 1) . 'i'; // 's' for strings, 'i' for integer
    $stmt->bind_param($paramTypes, ...$parameters);

    if ($stmt->execute()) {
        header("Location: studentUpdate.php?id=$student_id");
        exit();
    } else {
        die("Error updating profile: " . $stmt->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>User Profile</title>
    <link rel="stylesheet" href="./admin-css/studentUpdate.css">
</head>

<body>
    <header>
        <nav>
            <a href="./admin-dashboard.php"><i class="bi bi-arrow-90deg-left"></i></a>

        </nav>
    </header>

    <form action="studentUpdate.php?id=<?= htmlspecialchars($student['id']) ?>" method="POST" enctype="multipart/form-data">
        <div class="profile-container">
            <div class="profile-picture">
                <img src="<?= file_exists('../images-data/' . htmlspecialchars($student['image'])) && !empty($student['image']) ? '../images-data/' . htmlspecialchars($student['image']) . '?v=' . time() : '../images-data/default-image.png'; ?>"
                    alt="Profile Image"
                    class="profile-image"
                    id="profileDisplay"
                    style="width: 120px; height: 120px;">
                <input type="file" id="profileImageUpload" name="profileImage" accept="image/*" onchange="previewImage(event)" hidden>
                <div class="edit-btn" onclick="document.getElementById('profileImageUpload').click()">Edit</div>
            </div>
            <div class="profile-info">
                <h1><?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) ?></h1>
                <p><strong>ID:</strong> <?= htmlspecialchars($student['id']) ?></p>
                <h2><i class="bi bi-mortarboard-fill"></i> Student</h2>
            </div>
        </div>

        <div class="grid-container">
            <div class="card">
                <h3>Information</h3>
                <p>
                    <label for="firstname">First Name</label>
                    <input type="text" id="firstname" name="firstname" value="<?= htmlspecialchars($student['firstname']) ?>" required>
                </p>
                <p>
                    <label for="middlename">Middle Name</label>
                    <input type="text" id="middlename" name="middlename" value="<?= htmlspecialchars($student['middlename']) ?>" required>
                </p>
                <p>
                    <label for="lastname">Last Name</label>
                    <input type="text" id="lastname" name="lastname" value="<?= htmlspecialchars($student['lastname']) ?>" required>
                </p>
            </div>

            <div class="card">
                <h3>Additional Information</h3>

                <?php
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

                            echo "<a href='view_responses.php?id=" . htmlspecialchars($student['id']) . "'><i class='bi bi-pencil-square'></i></a>";
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

                ?>
            </div>
            <div class="card">
                <h3>Credentials</h3>
                <p>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($student['email'] ?? '') ?>" required>
                </p>
                <?php
                if (isset($_GET['id'])) {
                    $student_id = intval($_GET['id']);

                    $query = "
                        SELECT student.*, credentials.email, credentials.password 
                        FROM student
                        LEFT JOIN credentials ON student.id = credentials.student_id
                        WHERE student.id = ?
                    ";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param('i', $student_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $student = $result->fetch_assoc();
                    } else {
                        die("Student not found.");
                    }
                } else {
                    die("No student ID provided.");
                }

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                    $password = $_POST['password'] ?? $student['password'];
                    $imageQueryPart = "";
                    $parameters = [$firstname, $middlename, $lastname, $email, $password, $student_id];

                    $updateQuery = "
        UPDATE student 
        JOIN credentials ON student.id = credentials.student_id
        SET  
            credentials.password = ?

        WHERE student.id = ?
    ";
                    $stmt = $conn->prepare($updateQuery);

                    $paramTypes = str_repeat('s', count($parameters) - 1) . 'i'; // 's' for strings, 'i' for integer
                    $stmt->bind_param($paramTypes, ...$parameters);

                    if ($stmt->execute()) {
                        header("Location: studentUpdate.php?id=$student_id");
                        exit();
                    } else {
                        die("Error updating profile: " . $stmt->error);
                    }
                }
                ?>
                <p>
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" value="<?= htmlspecialchars($student['password']) ?>">
                    <button id="togglePassword" type="button" style="background: none; border: none; cursor: pointer; margin-left: 10px;">
                        <i id="eyeIcon" class="bi bi-eye-fill"></i>
                    </button>
                </p>

            </div>
        </div>
        <center>
            <button type="submit" name="updatePersonalInfo" class="btn btn-green">Save Changes</button>
        </center>
    </form>

    <div class="footer-section">

        <?php

        // Set the timezone to the Philippines
        date_default_timezone_set("Asia/Manila");

        function timeAgo($time, $tense = 'ago')
        {
            static $periods = array('year', 'month', 'day', 'hour', 'minute');

            if ((strtotime($time) <= 0)) {
                trigger_error("Wrong time format: $time", E_USER_ERROR);
            }

            $now = new DateTime('now', new DateTimeZone('Asia/Manila')); // Ensure timezone is set
            $then = new DateTime($time, new DateTimeZone('Asia/Manila'));
            $diff = $now->diff($then)->format('%y %m %d %h %i');
            $diff = explode(' ', $diff);
            $diff = array_combine($periods, $diff);
            $diff = array_filter($diff); // Remove zero values

            $period = key($diff); // Get the first non-zero period
            $value = current($diff); // Get the corresponding value

            if ($period === 'minute' && $value == 0) {
                // If less than 1 minute, show as "1 minute ago"
                $value = 1;
            }

            if ($value) {
                if ($value == 1) {
                    $period = rtrim($period, 's'); // Singular (remove 's')
                }
                return "$value $period $tense";
            }

            return "just now"; // Fallback for any unexpected cases
        }
        $query = "SELECT p.*, s.firstname, s.lastname, s.image AS profile_image, 
                (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
          FROM posts p 
          JOIN student s ON p.student_id = s.id 
          WHERE p.student_id = '$student_id' 
          ORDER BY p.created_at DESC";
        $result = mysqli_query($conn, $query);

        if ($result) {
            while ($post = mysqli_fetch_assoc($result)) {
                echo '<div class="post">';
                echo '<div class="post-header">';
                echo '<div class="delete-container">';
                echo '<button class="delete-button" onclick="deletePost(' . htmlspecialchars($post['id']) . ')"><i class="bi bi-trash3-fill"></i></button>';
                echo '</div>';
                echo '<img src="../images-data/' . htmlspecialchars($post['profile_image']) . '" alt="Profile Image" class="profile-pic">';
                echo '<div class="post-user-info">';
                echo '<strong>' . htmlspecialchars($post['firstname'] . ' ' . $post['lastname']) . '</strong>';
                echo '<span><i class="bi bi-mortarboard-fill"></i> Student</span>';
                echo '<span class="post-time">' . htmlspecialchars(timeAgo($post['created_at'])) . '</span>';

                echo '</div>';
                echo '</div>';

                echo '<div class="post-content">';
                echo '<p>' . htmlspecialchars($post['content']) . '</p>';
                echo '</div>';

                if (!empty($post['media'])) {
                    echo '<div class="post-media">';
                    echo '<img src="../' . htmlspecialchars($post['media']) . '" alt="Post Media" class="post-media">';

                    echo '</div>';
                }

                echo '<div class="post-footer">';
                echo '<form method="POST" action="comment_post.php" class="comment-form">';
                echo '<input type="hidden" name="post_id" value="' . htmlspecialchars($post['id']) . '">';
                echo '<textarea name="comment" placeholder="Write a comment..." required></textarea>';
                echo '<button type="submit">Post Comment</button>';
                echo '</form>';
                echo '<div class="post-actions">';
                echo '<form method="POST" action="like_post.php" class="like-form">';
                echo '<button class="like-button" onclick="toggleLike(this, ' . htmlspecialchars($post['id']) . ')">';
                echo '<i class="bi bi-balloon-heart-fill" style="color: red;"></i> ';
                echo '<span>Like</span> (<span class="like-count">' . htmlspecialchars($post['like_count']) . '</span>)';
                echo '</button>';
                echo '</form>';

                echo '<button class="comment-button" onclick="toggleComments(' . htmlspecialchars($post['id']) . ')">';
                echo '<i class="bi bi-chat-square-dots-fill" style="color: blue;"></i> ';
                echo '<span>Comment</span> (<span class="comment-count">' . htmlspecialchars($post['comment_count']) . '</span>)';
                echo '</button>';
                echo '</div>';

                $commentQuery = "SELECT c.*, s.firstname, s.lastname, s.image AS profile_image
                         FROM comments c 
                         JOIN student s ON c.student_id = s.id 
                         WHERE c.post_id = " . intval($post['id']) . " 
                         ORDER BY c.created_at ASC";
                $commentResult = mysqli_query($conn, $commentQuery);

                echo '<div class="comments" id="comments-' . htmlspecialchars($post['id']) . '">';
                echo '<h2>Comments</h2>';
                if ($commentResult && mysqli_num_rows($commentResult) > 0) {
                    while ($comment = mysqli_fetch_assoc($commentResult)) {
                        echo '<div class="comment">';
                        echo '<img src="../images-data/' . htmlspecialchars($comment['profile_image']) . '" alt="Profile Image" class="profile-pic">';
                        echo '<strong>' . htmlspecialchars($comment['firstname'] . ' ' . $comment['lastname']) . ':</strong> ';
                        echo '<p>' . htmlspecialchars($comment['content']) . '</p>';
                        echo '</div>';
                    }
                }
                echo '</div>';

                echo '</div>';
            }
        }
        ?>
        <script src="../js/studentProfile.js"></script>

    </div>





    <script src="./js/studentProfile.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
            } else {
                passwordField.type = 'password';
                eyeIcon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
            }
        });

        const modal = document.getElementById("messageModal");
        const openBtn = document.getElementById("openModalBtn");
        const closeBtn = document.getElementById("closeModalBtn");

        openBtn.addEventListener("click", () => {
            modal.style.display = "block";
        });

        closeBtn.addEventListener("click", () => {
            modal.style.display = "none";
        });

        function openPopup() {
            const popup = document.getElementById('loginPopup');
            const overlay = document.getElementById('popupOverlay-edit');
            popup.classList.add('active');
            overlay.classList.add('active');
        }

        function closePopup() {
            const popup = document.getElementById('loginPopup');
            const overlay = document.getElementById('popupOverlay-edit');
            popup.classList.remove('active');
            overlay.classList.remove('active');
        }

        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const preview = document.getElementById('profileDisplay');
                preview.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</body>

</html>