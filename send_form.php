<?php
include 'database/dbcon.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_id = filter_input(INPUT_POST, 'form_id', FILTER_VALIDATE_INT);
    $student_id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);

    if (!$form_id || !$student_id) {
        die("Invalid form_id or student_id.");
    }

    // Handle responses
    if (isset($_POST['responses']) && is_array($_POST['responses'])) {
        foreach ($_POST['responses'] as $field_id => $response) {
            $field_id = intval($field_id);
            $response = $conn->real_escape_string($response);

            // Insert into a table to save responses (e.g., form_responses)
            $stmt = $conn->prepare("INSERT INTO form_responses (form_id, field_id, student_id, response) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $form_id, $field_id, $student_id, $response);

            if (!$stmt->execute()) {
                echo "Error saving response for field $field_id: " . $stmt->error;
            }
        }
    }

    // Send email (existing code)
    $student_result = $conn->query("SELECT email FROM credentials WHERE id = $student_id");
    if ($student_result && $student = $student_result->fetch_assoc()) {
        $to = $student['email'];
        $subject = "New Form Assigned";
        $message = "You have been assigned a new form. Please log in to your dashboard to view it.";
        $headers = "From: admin@yourdomain.com";

        if (mail($to, $subject, $message, $headers)) {
            echo "Form has been sent to the student.";
        } else {
            echo "Error sending email.";
        }
    } else {
       
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }

        #popup-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .popup {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 300px;
            text-align: center;
            transform: translateY(-50px);
            animation: slideDown 0.3s forwards;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
            }
            to {
                transform: translateY(0);
            }
        }

        .popup img {
            width: 80px; /* Adjust size as needed */
            height: 80px;
            margin: 20px auto; /* Add spacing around the image */
        }

        .popup p {
            font-size: 16px;
            margin: 10px 0;
        }

        .close-btn {
            background: #4CAF50; /* Green for success */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .close-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div id="popup-container">
        <div class="popup">
            <h2>Success</h2>
            <img src="images/check.png" alt="Check Mark">
            <p>Your operation was successful!</p>
            <a href="./studentProfile.php">
            <button class="close-btn" onclick="closePopup()">OK</button>
            </a>
        </div>
    </div>

    <script>
        function closePopup() {
            document.getElementById("popup-container").style.display = "none";
        }
    </script>
</body>
</html>
v