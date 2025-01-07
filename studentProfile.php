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
    <title>Student Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="./images/bsitlogo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="./css/studentProfile.css">
</head>

<body>
    <header class="header">
        <div class="header-container">
        </div>
        <nav class="header-nav">
            <ul>
                <li><a href="home.php"><i style="position: relative; top:35% ;" class="bi bi-shop"></i></a></li>

                <li class="dropdown-profile">
                    <a href="studentProfile.php">
                        <img src="images-data/<?= htmlspecialchars($student['image']) ?>" alt="Profile Image" class="profile-pic" />
                    </a>
                    <div class="dropdown-content-profile">
                    </div>
                </li>
            </ul>
        </nav>
    </header>



    <div class="dashboard">
        <aside class="sidebar">
            <div class="logo">
                <img src="./images/bsitlogo.png" alt="BSIT Logo" style="width: 150px; height: 150px; display: block; margin: 0 auto;">
                BSIT
            </div>
            <nav>
                <ul>
                    <li class="active">My dashboard</li>
                    <li>Settings
                        <ul>
                            <li class="settings-btn">
                                <a href="studentUpdate.php">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit Profile
                                </a>
                            </li>

                        </ul>
                    </li>
                    <a href="index.php">
                        <li>Log Out </li>
                    </a>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <section class="profile">

                <div class="profile-info">
                    <div class="profile-container">
                        <?php
                        $imagePath = 'images-data/' . htmlspecialchars($student['image']);
                        if (!empty($student['image']) && file_exists($imagePath)) {
                            echo '<img src="' . $imagePath . '?v=' . time() . '" style="width:320px; height:320px;" alt="Profile Image">';
                        } else {
                            echo '<img src="images-data/default-image.png" style="width:120px; height:520px;" alt="Default Image">';
                        }
                        ?>
                    </div>
                    <div class="informative">
                        <center>
                            <p class="id-number">ID Number: <?php echo $student['id'] ?></p>
                            <p><strong>Full Name:</strong><br>
                        </center>
                        <center>
                            <?php
                            echo htmlspecialchars($student['firstname']) . ' ' .
                                htmlspecialchars($student['middlename']) . ' ' .
                                htmlspecialchars($student['lastname']);
                            ?></center>
                        </p>

                    </div>
                    <div class="approval-msg" style="margin-top: 20%;">
                        <center>
                            <?php if ($student['approved']): ?>
                                <p>Your account has been approved.</p>
                            <?php elseif ($student['rejected']): ?>
                                <p>Your account has been rejected by the admin.</p>
                            <?php else: ?>
                                <p>Your account is awaiting approval.</p>
                            <?php endif; ?>
                        </center>
                    </div>
                </div>

            </section>

            <div class="form-content">
                <?php $form_count = $forms_result->num_rows; ?>
                <!-- <button class="open-btn" id="openModalBtn"> -->
                <span class="form-count"><?= $form_count; ?></span>
                <h1>Notification</h1>
                <?php if ($forms_result->num_rows > 0): ?>
                    <ul class="forms-list">
                        <?php while ($form = $forms_result->fetch_assoc()): ?>
                            <li class="form-item">
                                <div class="form-details">
                                    <p class="form-name"><?= htmlspecialchars($form['form_name']); ?></p>
                                    <p class="form-timestamp"><?= date('M j, Y | g:i A'); ?></p>
                                </div>
                                <div class="form-actions">
                                    <a href="fill_form.php?form_id=<?= $form['form_id']; ?>" class="btn-fill">
                                        Fill
                                    </a>
                                    <a href="view_responses.php?form_id=<?= $form['form_id']; ?>" class="btn-view">
                                        View
                                    </a>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="no-forms-message">No Messages Yet</p>
                <?php endif; ?>
            </div>
            <section class="accounts">
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

                                //echo "Information:<a href='view_responses.php?form_id=" . $form['form_id'] . "'><i class='bi bi-pencil-square'></i></a>";


                                while ($response = $responses_result->fetch_assoc()) {
                                    echo "<li style='list-style-type: none; padding: 5px; '><strong>" . htmlspecialchars($response['field_name']) . ":</strong> " . htmlspecialchars($response['response']) . "</li>";
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
                <div class=" account">
                </div>
                <div class="account">

                </div>
            </section>

            <?php
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
                    echo '<img src="images-data/' . htmlspecialchars($post['profile_image']) . '" alt="Profile Image" class="profile-pic">';
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
                        echo '<img src="' . htmlspecialchars($post['media']) . '" alt="Post Media">';
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
                            echo '<img src="images-data/' . htmlspecialchars($comment['profile_image']) . '" alt="Profile Image" class="profile-pic">';
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

        </main>

    </div>




    <script>
        function printDiv(divId) {
            var printContents = document.getElementById(divId).innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }


        function closeModal() {
            document.querySelector('.modal-section.success').style.display = 'none';
        }

        function openEditProfileModal() {
            const modal = document.getElementById('editModal');
            if (modal) modal.classList.add('active');
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            if (modal) modal.classList.remove('active');
        }

        function openEditAccountModal() {
            const modal = document.getElementById('editcredentialsModal');
            if (modal) modal.classList.add('active');
        }

        function closeEditAccountModal() {
            const modal = document.getElementById('editcredentialsModal');
            if (modal) modal.classList.remove('active');
        }

        function previewImage(event) {
            const file = event.target.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profileDisplay').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
        const passwordInput = document.getElementById('password');
        const togglePasswordIcon = document.getElementById('togglePassword');
        togglePasswordIcon.addEventListener('click', () => {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            togglePasswordIcon.classList.toggle('fa-eye');
            togglePasswordIcon.classList.toggle('fa-eye-slash');
        });

        // Open the modal
        function openenrollmentForm() {
            document.getElementById('enrollmentForm').classList.add('active');
        }

        // Close the modal
        function closeenrollmentForm() {
            document.getElementById('enrollmentForm').classList.remove('active');
        }

        document.getElementById('studentForm').addEventListener('submit', function(event) {
            event.preventDefault();
            alert('Form submitted successfully!');
            closeenrollmentForm();
        });


        document.getElementById('studentForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            // Collect form data
            const year = document.getElementById('year').value;
            const studentType = document.getElementById('studentType').value;
            const studentStatus = document.getElementById('studentStatus').value;

            const formData = {
                year: year,
                studentType: studentType,
                studentStatus: studentStatus,
            };

            // Send data to the admin via an API (adjust the URL as needed)
            fetch('https://your-backend-endpoint/admin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData),
                })
                .then((response) => {
                    if (response.ok) {
                        alert('Form submitted successfully!');
                    } else {
                        alert('Failed to submit the form.');
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('An error occurred while submitting the form.');
                });
        });

        function closeenrollmentForm() {
            document.getElementById('enrollmentForm').style.display = 'none';
        }
    </script>

</body>

</html>