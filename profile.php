<?php
include 'database/dbcon.php';
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
}

$student_id = $_SESSION['student_id'];
$query = "SELECT * FROM student WHERE id = '$student_id'";
$result = mysqli_query($conn, $query);
$querypost = "SELECT * FROM posts WHERE id = '$student_id'";
$content = $_POST['content'] ?? '';
$mediaFiles = $_FILES['media'] ?? [];

if ($result && mysqli_num_rows($result) > 0) {
    $student = mysqli_fetch_assoc($result);
} else {
    echo "Student profile not found.";
    exit();
}

if ($content || !empty($mediaFiles['name'][0])) {
    // Save post content to database
    $query = "INSERT INTO posts (student_id, content, created_at) VALUES ('$student_id', '$content', NOW())";
    mysqli_query($conn, $query);
    $postId = mysqli_insert_id($conn); // Get the ID of the newly created post

    // Handle media upload if any media files are provided
    if (!empty($mediaFiles['name'][0])) {
        foreach ($mediaFiles['tmp_name'] as $key => $tmp_name) {
            if ($mediaFiles['error'][$key] === 0) {
                $filePath = 'uploads/' . basename($mediaFiles['name'][$key]);
                move_uploaded_file($tmp_name, $filePath);

                // Update the post with media path
                $query = "UPDATE posts SET media = '$filePath' WHERE id = '$postId'";
                mysqli_query($conn, $query);
            }
        }
    }
}
if (!empty($post['media'])) {
    $mediaType = mime_content_type($post['media']);
    if (strpos($mediaType, 'image') !== false) {
        echo '<img src=".' . htmlspecialchars($post['media']) . '" alt="Post Media" class="post-image">';
    } elseif (strpos($mediaType, 'video') !== false) {
        echo '<video controls class="post-video">
        <source src="' . htmlspecialchars($post['media']) . '" type="' . $mediaType . '">
    </video>';
    }
} elseif (!empty($post['content'])) {
    echo '<p>' . htmlspecialchars($post['content']) . '</p>';
}

$posts = [];

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$sql = "SELECT * FROM posts ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
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

    <title>Profile Layout</title>
</head>
<style>
    body {
        margin: 0;
        font-family: Arial, sans-serif;
        background-color: #333;
        color: white;
    }

    img {
        max-width: 100%;
        height: auto;
        /* Maintain aspect ratio */
        display: block;
    }

    .profile-pic {
        width: 100px;
        /* Adjusted size */
        height: 100px;
        border-radius: 50%;
        object-fit: cover;
        /* Ensures the image fits within the container */
        background-color: #000;
    }

    .post-media img {
        max-width: 100%;
        height: auto;
        border-radius: 10px;
    }

    .comment .profile-pic {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }

    .header .profile-pic {
        width: 40px;
        height: 40px;
        object-fit: cover;
    }

    .profile-section img {
        width: 120px;
        height: 120px;
        object-fit: cover;
    }

    .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #444;
        padding: 10px 20px;
        border-bottom: 2px solid #7e57c2;
    }

    .header .logo {
        font-size: 20px;
        font-weight: bold;
    }

    .header .icons {
        display: flex;
        align-items: center;
    }

    .header .icons .icon {
        margin-left: 20px;
        width: 30px;
        height: 30px;
        background-color: white;
        border-radius: 50%;
    }

    .container {
        display: flex;
        padding: 20px;
        gap: 20px;
    }

    .profile-section {
        background-color: #555;
        padding: 20px;
        border-radius: 10px;
        flex: 1;
        text-align: center;
    }

    .profile-section img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background-color: black;
    }

    .profile-section h2 {
        margin: 10px 0 5px;
    }

    .profile-section .status {

        margin-top: 15px;
        background-color: black;
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        display: inline-block;
    }

    .info-section {
        background-color: #555;
        padding: 20px;
        border-radius: 10px;
        flex: 2;
        height: 300px;
        /* Set a specific height */
        overflow-y: auto;
        /* Enable vertical scrolling */
        scrollbar-width: thin;
        /* For custom thin scrollbars (optional, Firefox) */
        scrollbar-color: #888 #555;
        /* Scrollbar colors (optional, Firefox) */
    }

    /* Optional: Customize the scrollbar for WebKit browsers (Chrome, Edge, Safari) */
    .info-section::-webkit-scrollbar {
        width: 8px;
        /* Width of the scrollbar */
    }

    .info-section2::-webkit-scrollbar-track {
        background: #555;
        /* Track background color */
        border-radius: 10px;
        /* Optional rounded track corners */
    }

    .info-section::-webkit-scrollbar-thumb {
        background: #888;
        /* Scrollbar thumb color */
        border-radius: 10px;
        /* Optional rounded thumb corners */
    }



    .info-section2 {
        background-color: #555;
        padding: 20px;
        border-radius: 10px;
        flex: 2;
        height: 300px;
        /* Set a specific height */
        overflow-y: auto;
        /* Enable vertical scrolling */
        scrollbar-width: thin;
        /* For custom thin scrollbars (optional, Firefox) */
        scrollbar-color: #888 #555;
        /* Scrollbar colors (optional, Firefox) */
    }

    /* Optional: Customize the scrollbar for WebKit browsers (Chrome, Edge, Safari) */
    .info-section2::-webkit-scrollbar {
        width: 8px;
        /* Width of the scrollbar */
    }

    .info-section2::-webkit-scrollbar-track {
        background: #555;
        /* Track background color */
        border-radius: 10px;
        /* Optional rounded track corners */
    }

    .info-section2::-webkit-scrollbar-thumb {
        background: #888;
        /* Scrollbar thumb color */
        border-radius: 10px;
        /* Optional rounded thumb corners */
    }

    .info-box2 {
        margin-bottom: 20px;
    }

    .info-box2 h3 {
        background-color: black;
        display: inline-block;
        padding: 5px 10px;
        border-radius: 5px;
        margin-bottom: 10px;
    }

    .info-box2 p {
        margin: 5px 0;
    }


    .footer-section {
        background-color: #555;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
        text-align: center;
    }

    .info-section2 .form-content {
        background-color: #444;
        padding: 15px;
        border-radius: 8px;
        color: #fff;
    }

    .info-section2 .form-content h1 {
        font-size: 18px;
        margin-bottom: 10px;
        text-align: center;
        color: #7e57c2;
        font-weight: bold;
    }

    .info-section2 .form-content .form-count {
        font-size: 22px;
        font-weight: bold;
        color: #f1c40f;
        display: block;
        text-align: center;
        margin-bottom: 5px;
    }

    .info-section2 .forms-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .info-section2 .form-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #333;
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .info-section2 .form-item .form-details {
        flex: 1;
    }

    .info-section2 .form-item .form-name {
        font-size: 16px;
        font-weight: bold;
        margin: 0;
    }

    .info-section2 .form-item .form-timestamp {
        font-size: 12px;
        color: #aaa;
        margin: 5px 0 0;
    }

    .info-section2 .form-item .form-actions {
        display: flex;
        gap: 10px;
    }

    .info-section2 .form-item .btn-fill,
    .info-section2 .form-item .btn-view {
        padding: 5px 10px;
        text-decoration: none;
        font-size: 14px;
        font-weight: bold;
        border-radius: 5px;
        color: white;
        background-color: #7e57c2;
        transition: background-color 0.3s ease;
    }

    .info-section2 .form-item .btn-fill:hover {
        background-color: #5e3ba8;
    }

    .info-section2 .form-item .btn-view:hover {
        background-color: #5e3ba8;
    }

    .info-section2 .no-forms-message {
        text-align: center;
        color: #ccc;
        font-style: italic;
        margin-top: 20px;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #f9f9f9;
        margin: 0;
        padding: 0;
    }

    .footer-section {
        margin: 20px auto;
        max-width: 800px;
    }

    .post {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .post-header {
        display: flex;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #eee;
        position: relative;
    }

    .profile-pic {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 10px;
    }

    .post-user-info {
        display: flex;
        flex-direction: column;
    }

    .post-user-info strong {
        font-size: 16px;
        color: #333;
    }

    .post-user-info span {
        font-size: 12px;
        color: #888;
    }

    .delete-container {
        position: absolute;
        right: 10px;
        top: 15px;
    }

    .delete-button {
        background: transparent;
        border: none;
        cursor: pointer;
        color: #e74c3c;
        font-size: 18px;
    }

    /* Post content */
    .post-content {
        padding: 15px;
        font-size: 14px;
        color: #555;
    }

    .post-media img {
        width: 100%;
        max-height: 400px;
        object-fit: cover;
        border-bottom: 1px solid #eee;
    }

    /* Post footer */
    .post-footer {
        padding: 15px;
        border-top: 1px solid #eee;
    }

    .comment-form textarea {
        width: calc(100% - 20px);
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 4px;
        border: 1px solid #ccc;
        resize: none;
        font-size: 14px;
    }

    .comment-form button {
        background-color: #3498db;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }

    .comment-form button:hover {
        background-color: #2980b9;
    }

    /* Actions (like and comment) */
    .post-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
    }

    .like-button,
    .comment-button {
        background: transparent;
        border: none;
        cursor: pointer;
        color: #555;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .like-button:hover,
    .comment-button:hover {
        color: #3498db;
    }

    .like-count,
    .comment-count {
        font-weight: bold;
        color: #333;
    }

    /* Comments section */
    .comments {
        padding: 15px;
        border-top: 1px solid #eee;
        background: #f7f7f7;
    }

    .comment {
        display: flex;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .comment .profile-pic {
        margin-right: 10px;
        width: 30px;
        height: 30px;
    }

    .comment strong {
        color: #333;
    }

    .comment p {
        margin: 0;
        font-size: 13px;
        color: #555;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .post {
            margin: 10px;
        }

        .post-header,
        .post-footer,
        .comments {
            padding: 10px;
        }

        .comment-form textarea {
            font-size: 13px;
        }

        .comment-form button {
            font-size: 13px;
        }
    }
</style>

<body>
    <div class="header">
        <div class="logo" style="width: 45px;"><img src="./images/bsitlogo.png" alt=""></div>BSIT
        <div class="icons">
            <a href="home.php" style="margin-right: 35px;"><i style="color: white;" class="bi bi-shop"></i></a>
            <img src="images-data/<?= htmlspecialchars($student['image']) ?>" alt="Profile Image" class="profile-pic" />
        </div>
    </div>
    <div class="container">
        <div class="profile-section">
            <center>
                <?php
                $imagePath = 'images-data/' . htmlspecialchars($student['image']);
                if (!empty($student['image']) && file_exists($imagePath)) {
                    echo '<img src="' . $imagePath . '?v=' . time() . '" style="width:120px; height:120px;" alt="Profile Image">';
                } else {
                    echo '<img src="images-data/default-image.png" style="width:120px; height:520px;" alt="Default Image">';
                }
                ?>
            </center>
            <h2>
                <center>
                    <?php
                    echo htmlspecialchars($student['firstname']) . ' ' .
                        htmlspecialchars($student['middlename']) . ' ' .
                        htmlspecialchars($student['lastname']);
                    ?></center>
            </h2>
            <p>iD:<?php echo $student['id'] ?></p>
            <div class="status">
                <center>
                    <?php if ($student['approved']): ?>
                        <p style="color:green">Your account has been approved.</p>
                    <?php elseif ($student['rejected']): ?>
                        <p style="color:red">Your account has been rejected by the admin.</p>
                    <?php else: ?>
                        <p style="color:yellow">Your account is awaiting approval.</p>
                    <?php endif; ?>
                </center>
            </div>
        </div>

        <div class="info-section2">
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

        </div>

        <div class="info-section">
            <div class="info-box">
                <h3>Information <a href="studentUpdate.php"><i class='bi bi-pencil-square'></i></a> </h3>
                <p>First Name : <?php echo htmlspecialchars($student['firstname']) ?></p>
                <p>Middle Name : <?php echo htmlspecialchars($student['middlename']) ?></p>
                <p>Last Name : <?php echo htmlspecialchars($student['lastname']) ?></p>
            </div>
            <div class="info-box">
                <h3>Information</h3>
                <?php
                if (!empty($_SESSION['student_id'])) {
                    $student_id = $_SESSION['student_id'];

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

                                //echo "<a href='view_responses.php?form_id=" . $form['form_id'] . "'>edit<i class='bi bi-pencil-square'></i></a>";


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
                }
                ?>
            </div>
        </div>


    </div>

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

    </div>


    <script src="./js/studentProfile.js"></script>

</body>

</html>