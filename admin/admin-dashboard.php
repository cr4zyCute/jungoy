<?php
include '../database/dbcon.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit();
}
$admin_id = $_SESSION['admin_id'];

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

$forms = $conn->query("SELECT * FROM forms")->fetch_all(MYSQLI_ASSOC);
$students = $conn->query("SELECT * FROM student")->fetch_all(MYSQLI_ASSOC);
$approved_students = $conn->query("SELECT * FROM student WHERE approved = 1")->fetch_all(MYSQLI_ASSOC);
$new_students = $conn->query("SELECT * FROM student WHERE is_approved = 0 AND admin_notified = 0")->fetch_all(MYSQLI_ASSOC);


$conn->query("UPDATE student SET admin_notified = 1 WHERE is_approved = 0 AND admin_notified = 0");

$students = $conn->query("SELECT * FROM student")->fetch_all(MYSQLI_ASSOC);
$forms = $conn->query("SELECT * FROM forms")->fetch_all(MYSQLI_ASSOC);
include '../database/dbcon.php';

$unread_notifications_query = "SELECT COUNT(*) AS unread_count FROM notifications WHERE is_read = 0";
$unread_notifications_result = $conn->query($unread_notifications_query);

if (!$unread_notifications_result) {
    die("Error fetching unread notifications: " . $conn->error);
}

$unread_notifications_count = $unread_notifications_result->fetch_assoc()['unread_count'] ?? 0;

$unapproved_students_query = "SELECT COUNT(*) AS unapproved_count FROM student WHERE approved = 0";
$unapproved_students_result = $conn->query($unapproved_students_query);

if (!$unapproved_students_result) {
    die("Error fetching unapproved students: " . $conn->error);
}

$unapproved_students_count = $unapproved_students_result->fetch_assoc()['unapproved_count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="./admin-css/dashboard.css">
    <link rel="stylesheet" href="./admin-css/admin-home.css">

    <title>Admin Page</title>
</head>

<body>

    <div class="main-content">
        <div style="background-color: #c0392b;" class="header">
            <div class="logo">
                <img src="../images/bsitlogo.png" alt="Logo">
                <span>BSIT</span>
            </div>
            <div class="icons">
                <a href="#" onclick="showSection('dashboard')" aria-controls="dashboard" aria-selected="true"><i style="margin: 10px;" class="bi bi-shop-window"></i></a>
                <a href="#" onclick="showSection('student')" aria-controls="student"><i style="margin: 10px;" class="bi bi-people-fill"></i></a>
                <a href="#" onclick="showSection('announcements')" aria-controls="announcements"><i style="margin: 10px;" class="bi bi-megaphone-fill"></i></a>
                <a href="adminForm.php" onclick="showSection('form')" aria-controls="form"><i style="margin: 10px;" class="bi bi-ui-checks-grid"></i></a>

                <!-- <a href="#" onclick="showSection('home')" aria-controls="home"><i class="bi bi-house-door-fill"></i></a> -->
                <!-- <a href="#" onclick="showSection('notifications')" aria-controls="notifications">
                    <i class="bi bi-envelope-fill"></i>
                    <span class="notification-count">
                        <?= htmlspecialchars($unapproved_students_count); ?>
                    </span>
                </a> -->

                <div class="dropdown">
                    <a href="./adminProfile.php">
                        <img src="../images-data/<?= htmlspecialchars($admin['adminProfile']) ?>" alt="Profile Image" class="profile-image">
                        <div class="dropdown-content">
                            <a href="./logout.php"><i style="padding-right: 5px; color: red; font-size: 20px;" class="bi bi-power"></i>Log out</a>
                        </div>
                </div>
            </div>
        </div>
        <section id="dashboard" class="active">
            <?php
            include '../database/dbcon.php';

            // Function to get student count based on year
            function getStudentCount($conn, $year)
            {
                $query = "SELECT COUNT(*) AS count FROM form_responses WHERE response = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("s", $year);
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_assoc()['count'] ?? 0;
            }

            // Fetch total students
            $total_students_query = "SELECT COUNT(*) AS total_students FROM student";
            $total_students_result = $conn->query($total_students_query);
            if (!$total_students_result) {
                die("Error fetching total students: " . $conn->error);
            }
            $total_students = $total_students_result->fetch_assoc()['total_students'] ?? 0;

            // Fetch student counts by year
            $first_year_students = getStudentCount($conn, 'First Year');
            $second_year_students = getStudentCount($conn, 'Second Year');
            $third_year_students = getStudentCount($conn, 'Third Year');
            $fourth_year_students = getStudentCount($conn, 'Fourth Year');

            // Fetch approved students
            $approved_students_query = "SELECT COUNT(*) AS approved_students FROM student WHERE approved = 1";
            $approved_students_result = $conn->query($approved_students_query);
            $approved_students = $approved_students_result->fetch_assoc()['approved_students'] ?? 0;
            ?>

            <h1>Dashboard</h1>
            <div class="dashboard-boxes">
                <div class="dashboard-box">
                    <h3>Total Students</h3>
                    <p><?= htmlspecialchars($total_students) ?></p>
                </div>
                <div class="dashboard-box">
                    <h3>First Year Students</h3>
                    <p><?= htmlspecialchars($first_year_students) ?></p>
                </div>
                <div class="dashboard-box">
                    <h3>Second Year Students</h3>
                    <p><?= htmlspecialchars($second_year_students) ?></p>
                </div>
                <div class="dashboard-box">
                    <h3>Third Year Students</h3>
                    <p><?= htmlspecialchars($third_year_students) ?></p>
                </div>
                <div class="dashboard-box">
                    <h3>Fourth Year Students</h3>
                    <p><?= htmlspecialchars($fourth_year_students) ?></p>
                </div>
                <!-- <div class="dashboard-box">
                    <h3>Approved Students</h3>
                    <p><?= htmlspecialchars($approved_students) ?></p>
                </div> -->
            </div>

            <?php
            $notifications = $conn->query("SELECT * FROM notifications WHERE is_read = 0");
            if (!$notifications) {
                die("Error fetching notifications: " . $conn->error);
            }
            $unapproved_users_query = "
        SELECT 
            student.id AS student_id, 
            student.firstname, 
            student.image, 
            credentials.email 
        FROM 
            student 
        INNER JOIN 
            credentials 
        ON 
            student.id = credentials.student_id 
        WHERE 
            student.approved = 0
    ";
            $unapproved_users = $conn->query($unapproved_users_query);
            if (!$unapproved_users) {
                die("Error fetching unapproved users: " . $conn->error);
            }

            // Mark notifications as read
            $mark_read = $conn->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
            if (!$mark_read) {
                die("Error updating notifications: " . $conn->error);
            }

            // Handle image upload
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profileImage'])) {
                $imageName = basename($_FILES['profileImage']['name']);
                $imagePath = 'images-data/' . $imageName;

                if (!empty($imageName)) {
                    if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $imagePath)) {
                        echo "Image uploaded successfully.";
                    } else {
                        echo "Failed to upload image.";
                        exit();
                    }
                }
            }
            ?>

            <h2 style="text-align: center; margin-top: 10px; font-family: Arial, sans-serif; color: #333;">Unapproved Students
                <span class="notification-count">
                    <?= htmlspecialchars($unapproved_students_count); ?>
                </span>
            </h2>
            <?php if ($unapproved_users->num_rows > 0): ?>
                <form method="POST" action="approve_users.php" style="margin: 20px auto; width: 80%; text-align: center;">
                    <button type="submit" name="action" value="approve" style="background-color: #4CAF50; color: white; border: none; padding: 10px 20px; margin-right: 10px; cursor: pointer; font-size: 14px; border-radius: 5px;">Approve Selected</button>
                    <button type="submit" name="action" value="reject" style="background-color: #f44336; color: white; border: none; padding: 10px 20px; cursor: pointer; font-size: 14px; border-radius: 5px;">Reject Selected</button>

                    <table style="width: 100%; border-collapse: collapse; margin-top: 20px; text-align: left; font-family: Arial, sans-serif; font-size: 14px;">
                        <thead>
                            <tr style="background-color: #f2f2f2; border-bottom: 2px solid #ddd;">
                                <th style="padding: 10px; text-align: center;">Profile</th>
                                <th style="padding: 10px;">Username</th>
                                <th style="padding: 10px;">Email</th>
                                <th style="padding: 10px; text-align: center;">Approve</th>
                                <th style="padding: 10px; text-align: center;">Reject</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = $unapproved_users->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #ddd;">
                                    <td style="padding: 10px; text-align: center;">
                                        <img src="../images-data/<?= htmlspecialchars($student['image'] ?: 'default-profile.jpg'); ?>"
                                            alt="Profile Picture" style="width: 90px; height: 80px; border-radius: 10%; object-fit: cover;">
                                    </td>
                                    <td style="padding: 10px;"><?= htmlspecialchars($student['firstname']); ?></td>
                                    <td style="padding: 10px;"><?= htmlspecialchars($student['email']); ?></td>
                                    <td style="padding: 10px; text-align: center;">
                                        <input type="checkbox" name="approve_users[]" value="<?= $student['student_id']; ?>" style="cursor: pointer;">
                                    </td>
                                    <td style="padding: 10px; text-align: center;">
                                        <input type="checkbox" name="reject_users[]" value="<?= $student['student_id']; ?>" style="cursor: pointer;">
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </form>
            <?php else: ?>
                <p style="text-align: center; margin-top: 20px; font-family: Arial, sans-serif; color: #666;">No students awaiting approval.</p>
            <?php endif; ?>

        </section>



        <section id="home">
            <div class="left-section">


                <div class="left-section">
                    <?php
                    date_default_timezone_set("Asia/Manila");

                    function timeAgo($time, $tense = 'ago')
                    {
                        static $periods = array('year', 'month', 'day', 'hour', 'minute');

                        if ((strtotime($time) <= 0)) {
                            trigger_error("Wrong time format: $time", E_USER_ERROR);
                        }

                        $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
                        $then = new DateTime($time, new DateTimeZone('Asia/Manila'));
                        $diff = $now->diff($then)->format('%y %m %d %h %i');
                        $diff = explode(' ', $diff);
                        $diff = array_combine($periods, $diff);
                        $diff = array_filter($diff);

                        $period = key($diff);
                        $value = current($diff);

                        if ($period === 'minute' && $value == 0) {
                            $value = 1;
                        }

                        if ($value) {
                            if ($value == 1) {
                                $period = rtrim($period, 's');
                            }
                            return "$value $period $tense";
                        }

                        return "just now";
                    }
                    $query = "SELECT p.*, s.firstname, s.lastname, s.image AS profile_image, 
                (SELECT COUNT(*) FROM likes WHERE post_id = p.id) AS like_count,
                (SELECT COUNT(*) FROM comments WHERE post_id = p.id) AS comment_count
              FROM posts p 
              JOIN student s ON p.student_id = s.id 
              ORDER BY p.created_at DESC";
                    $result = mysqli_query($conn, $query);

                    if ($result) {
                        while ($post = mysqli_fetch_assoc($result)) {
                            echo '<div class="post">';
                            echo '<div class="post-header">';
                            echo '<div class="delete-container">';
                            // echo '<button class="delete-button" onclick="deletePost(' . htmlspecialchars($post['id']) . ')"><i class="bi bi-trash3-fill"></i></button>';
                            echo "<button class='delete-button' onclick='deletePost(" . htmlspecialchars($post['id']) . ")'><i class='bi bi-trash3-fill'></i></button>";
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
                                echo '<img src="' . '../' . htmlspecialchars($post['media']) . '" alt="Post Media">';
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
                </div>
            </div>
        </section>

        <section id="student">
            <h2>Manage Students</h2>
            <h3>Student List</h3>

            <input
                type="text"
                id="searchInput"
                placeholder="Search by ID, First Name, or Last Name..."
                style="width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px; font-size: 16px;">

            <table class="dashboard-table" id="student-table">
                <thead>
                    <tr>
                        <th>Profile Picture</th>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Manage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td>
                                    <center>
                                        <img src="../images-data/<?= !empty($student['image']) ? htmlspecialchars($student['image']) : 'default-profile.jpg'; ?>"
                                            alt="Profile Picture"
                                            style="width: 150px; height: 150px; object-fit: cover; border: 1px solid #ccc; border-radius: 10px;">
                                    </center>
                                </td>
                                <td><?= htmlspecialchars($student['id']); ?></td>
                                <td><?= htmlspecialchars($student['firstname']); ?></td>
                                <td><?= htmlspecialchars($student['lastname']); ?></td>
                                <td>
                                    <!-- Edit Button -->
                                    <a href="studentUpdate.php?id=<?= htmlspecialchars(string: $student['id']); ?>"
                                        style="display: inline-block; background-color: #3bd20f; color: #fff; padding: 10px 15px; font-size: 16px; text-align: center; text-decoration: none; border-radius: 5px; border: 1px solid transparent; transition: background-color 0.3s ease;"
                                        onmouseover="this.style.backgroundColor='#00ff00 ';"
                                        onmouseout="this.style.backgroundColor='#5dff2e';">
                                        <i class="bi bi-pencil-square" style="font-size: 16px;"></i>
                                    </a>


                                    <!-- Delete Button -->
                                    <form action="deleteStudent.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($student['id']); ?>">
                                        <button type="submit" style="display: inline-block; background-color:rgb(255, 0, 0); color: #fff; padding: 10px 15px; font-size: 16px; text-align: center; text-decoration: none; border-radius: 5px; border: 1px solid transparent; transition: background-color 0.3s ease;"
                                            onmouseover="this.style.backgroundColor='#ff7860';"
                                            onmouseout="this.style.backgroundColor='#c11d00';"
                                            onclick="return confirm('Are you sure you want to delete this student?');"><i class="bi bi-trash3-fill"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>

                        <h2 style="display: flex; align-items:center; justify-content: center; color: red ">No students found.</h2>

                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <script>
            document.getElementById('searchInput').addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                const rows = document.querySelectorAll('#student-table tbody tr');

                rows.forEach(row => {
                    const id = row.cells[1].textContent.toLowerCase();
                    const firstName = row.cells[2].textContent.toLowerCase();
                    const lastName = row.cells[3].textContent.toLowerCase();

                    if (id.includes(filter) || firstName.includes(filter) || lastName.includes(filter)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        </script>

        <section id="announcements">

            <div style="display: flex; justify-content: space-between; gap: 20px; align-items: flex-start;">

                <form action="postAnnouncement.php" method="POST" style="border: 1px solid #ddd; padding: 20px; border-radius: 5px; width: 48%; background-color: #f9f9f9;">
                    <label for="title" style="display: block; font-family: Arial, sans-serif; font-size: 14px; margin-bottom: 5px;">Title:</label>
                    <input type="text" id="title" name="title" required style="width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px;">

                    <label for="content" style="display: block; font-family: Arial, sans-serif; font-size: 14px; margin-bottom: 5px;">Content:</label>
                    <textarea id="content" name="content" required style="width: 100%; padding: 8px; height: 100px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px;"></textarea>

                    <button type="submit" style="background-color: #007bff; color: white; border: none; padding: 10px 20px; font-family: Arial, sans-serif; font-size: 14px; border-radius: 4px; cursor: pointer;">Post Announcement</button>
                </form>

                <?php
                $sql = "SELECT a.id, a.title, a.content, a.created_at, ad.admin_username, ad.admin_name 
                FROM announcements a 
                JOIN admin ad ON a.admin_id = ad.id 
                ORDER BY a.created_at DESC";
                $result = mysqli_query($conn, $sql);

                ?>
                <div class="announcement" style="flex-grow: 1; margin-top: 0;">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <div class="card" style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; background-color: #fff; margin-bottom: 15px;">
                                <div class="profile-info">
                                    <strong style="font-family: Arial, sans-serif; font-size: 16px;">
                                        <?php echo htmlspecialchars($row['admin_name']); ?>
                                    </strong>
                                    <small class="role" style="display: block; color: #555; font-size: 12px;">
                                        <i class="bi bi-people-fill"></i>
                                        <small style="margin: 0;"> <?php echo htmlspecialchars($row['admin_username']); ?></small>
                                    </small>
                                    <small class="time" style="display: block; color: #999; font-size: 12px; margin-top: 5px;">
                                        <?php echo date("F j, Y, g:i a", strtotime($row['created_at'])); ?>
                                    </small>
                                    <p style="margin-top: 10px; font-family: Arial, sans-serif; font-size: 14px; color: #333;">
                                        <?php echo htmlspecialchars($row['content']); ?>
                                    </p>
                                    <form action="" method="POST">
                                        <input type="hidden" name="announcement_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete" style="background-color: red; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="font-family: Arial, sans-serif; font-size: 14px; color: #555;">No announcements found.</p>
                    <?php endif; ?>

                    <?php
                    if (isset($_POST['delete']) && isset($_POST['announcement_id'])) {
                        $announcement_id = mysqli_real_escape_string($conn, $_POST['announcement_id']);

                        $delete_sql = "DELETE FROM announcements WHERE id = '$announcement_id'";
                        if (mysqli_query($conn, $delete_sql)) {
                            echo "<p style='color: green;'>Announcement deleted successfully!</p>";
                        } else {
                            echo "<p style='color: red;'>Error deleting announcement: " . mysqli_error($conn) . "</p>";
                        }


                        echo "<script>window.location = admin-dashboard.php;</script>";
                    }
                    ?>
                </div>
            </div>
        </section>

        <section id="notifications">
            <?php

            $notifications = $conn->query("SELECT * FROM notifications WHERE is_read = 0");
            if (!$notifications) {
                die("Error fetching notifications: " . $conn->error);
            }

            $unapproved_users = $conn->query("
    SELECT 
        student.id AS student_id, 
        student.firstname, 
        student.image, 
        credentials.email 
    FROM 
        student 
    INNER JOIN 
        credentials AS credentials 
    ON 
        student.id = credentials.student_id 
    WHERE 
        student.approved = 0
");
            if (!$unapproved_users) {
                die("Error fetching unapproved users: " . $conn->error);
            }


            $mark_read = $conn->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
            if (!$mark_read) {
                die("Error updating notifications: " . $conn->error);
            }


            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profileImage'])) {
                $imageName = basename($_FILES['profileImage']['name']);
                $imagePath = 'images-data/' . $imageName;

                if (!empty($imageName)) {
                    if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $imagePath)) {
                        echo "Image uploaded successfully.";
                    } else {
                        echo "Failed to upload image.";
                        exit();
                    }
                }
            }
            ?>
            <h2 style="display: flex; align-items:center; justify-content:center; margin-top: 10px; ">Unapproved Students</h2>
            <?php if ($unapproved_users->num_rows > 0): ?>
                <form method="POST" action="approve_users.php">
                    <button type="submit" name="action" value="approve" style="background-color: #007bff; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 5px; cursor: pointer; margin-right: 10px; transition: background-color 0.3s ease;">Approve Selected</button>
                    <button type="submit" name="action" value="reject" style="background-color: #dc3545; color: #fff; border: none; padding: 10px 20px; font-size: 14px; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease;">Reject Selected</button>

                    <table style="width: 100%; border-collapse: collapse; margin-top: 20px; background-color: #fff; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); border: 1px solid #ddd;">
                        <thead>
                            <tr style="background-color: #f4f4f4; font-weight: bold;">
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd; font-size: 14px; color: #333;">Profile</th>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd; font-size: 14px; color: #333;">Username</th>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd; font-size: 14px; color: #333;">Email</th>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd; font-size: 14px; color: #333;">Approve</th>
                                <th style="padding: 12px 15px; text-align: left; border: 1px solid #ddd; font-size: 14px; color: #333;">Reject</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = $unapproved_users->fetch_assoc()): ?>
                                <tr style="background-color: #f9f9f9; border: 1px solid #ddd;">
                                    <td style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;">
                                        <center>
                                            <img src="../images-data/<?= !empty($student['image']) ? htmlspecialchars($student['image']) : 'default-profile.jpg'; ?>"
                                                alt="Profile Picture"
                                                style="width: 150px; height: 150px;object-fit: cover; border: 1px solid #ccc;">
                                        </center>
                                    </td>

                                    <td style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;"><?= htmlspecialchars($student['firstname']); ?></td>
                                    <td style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;"><?= htmlspecialchars($student['email']); ?></td>
                                    <td style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;">
                                        <input type="checkbox" name="approve_users[]" value="<?= $student['student_id']; ?>">
                                    </td>
                                    <td style="padding: 12px 15px; text-align: left; border: 1px solid #ddd;">
                                        <input type="checkbox" name="reject_users[]" value="<?= $student['student_id']; ?>">
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </form>
            <?php else: ?>
                <p style="position:absolute; top:50%; left: 51%; ">No students awaiting approval.</p>
            <?php endif; ?>
        </section>

        <section id="form">
            <h2>Create a New Form</h2>
            <form id="createForm" method="POST" action="create_form.php">
                <label for="form_name">Form Name:</label>
                <input type="text" name="form_name" id="form_name" required><br><br>
                <button type="button" onclick="addField()">Add Field</button>
                <button type="submit">Create Form</button>
                <div id="fields"></div>
            </form>


            <section>
                <h2>Available Forms</h2>
                <ul>
                    <?php foreach ($forms as $form): ?>
                        <li>
                            <?= htmlspecialchars($form['form_name']); ?>
                            <button onclick="showSendModal(<?= $form['id']; ?>)">Send to student</button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>


            <div id="sendModal" style="display:none;">
                <h3>Send Form to Student</h3>
                <form method="POST" action="send_form.php">
                    <input type="hidden" name="form_id" id="modalFormId">
                    <label for="student_id">Select Student:</label>
                    <select name="student_id" id="student_id" required>
                        <?php if (!empty($approved_students)): ?>
                            <?php foreach ($approved_students as $student): ?>
                                <option value="<?= htmlspecialchars($student['id']); ?>">
                                    <?= htmlspecialchars($student['firstname']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option disabled>No approved students available</option>
                        <?php endif; ?>

                    </select><br><br>
                    <button type="submit">Send Form</button>
                </form>
                <button onclick="document.getElementById('sendModal').style.display = 'none';">Close</button>
            </div>
        </section>

        <script>
            fieldCount = 0;

            function showSendModal(formId) {
                document.getElementById('modalFormId').value = formId;
                document.getElementById('sendModal').style.display = 'block';
            }

            function addField() {
                const fieldsDiv = document.getElementById('fields');
                const fieldHTML = `
            <div class="field">
                <label>Field Name:</label>
                <input type="text" name="fields[${fieldCount}][name]" required>
                <label>Field Type:</label>
                <select name="fields[${fieldCount}][type]">
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="email">Email</option>
                    <option value="textarea">Textarea</option>
                </select>
                <label>Required:</label>
                <input type="checkbox" name="fields[${fieldCount}][required]">
            </div>
        `;
                fieldsDiv.insertAdjacentHTML('beforeend', fieldHTML);
                fieldCount++;
            }
        </script>
        </section>
    </div>
    <script src="../js/home.js"></script>
    <script>
        function showSection(sectionId) {
            const sections = document.querySelectorAll('.main-content section');
            sections.forEach(section => {
                section.classList.remove('active');
            });
            const activeSection = document.getElementById(sectionId);
            activeSection.classList.add('active');
            localStorage.setItem('activeSection', sectionId);
        }
    </script>

</body>

</html>