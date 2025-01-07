<?php
include '../database/dbcon.php';

$forms = $conn->query("SELECT * FROM forms")->fetch_all(MYSQLI_ASSOC);
$students = $conn->query("SELECT * FROM student")->fetch_all(MYSQLI_ASSOC);
$approved_students = $conn->query("SELECT id, firstname, image FROM student WHERE approved = 1")->fetch_all(MYSQLI_ASSOC);
$new_students = $conn->query("SELECT * FROM student WHERE is_approved = 0 AND admin_notified = 0")->fetch_all(MYSQLI_ASSOC);

$conn->query("UPDATE student SET admin_notified = 1 WHERE is_approved = 0 AND admin_notified = 0");


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Admin Dashboard</title>
    <style>
        /* General Reset */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            background-color: #f9f9f9;
            color: #333;
        }

        /* Main Container */
        main {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }

        .container {
            flex: 1 1 40%;
            margin: 20px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .container h2 {
            font-size: 24px;
            margin-bottom: 15px;
        }

        .container a {
            color: #007bff;
            text-decoration: none;
            font-size: 18px;
        }

        .container a:hover {
            color: #0056b3;
        }

        /* Search Input */
        #searchInput {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Form List */
        #formList {
            list-style-type: none;
            padding: 0;
        }

        #formList li {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        #formList li a {
            flex: 1;
            color: #333;
            font-weight: bold;
        }

        #formList li a:hover {
            color: #007bff;
        }

        /* Buttons */
        button {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .sendbtn {
            background-color: #28a745;
            color: #fff;
            margin-left: 10px;
        }

        .sendbtn:hover {
            background-color: #218838;
        }

        .deletebtn {
            background-color: #dc3545;
            color: #fff;
            margin-left: 10px;
        }

        .deletebtn:hover {
            background-color: #c82333;
        }

        /* Content Section */
        .content {
            flex: 1 1 40%;
            margin: 20px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .content h2 {
            font-size: 24px;
            margin-bottom: 15px;
        }

        /* Form Styling */
        #createForm {
            margin-top: 15px;
        }

        #form_name {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Button Container */
        .button-container {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .fieldBtn {
            background-color: #007bff;
            color: #fff;
        }

        .fieldBtn:hover {
            background-color: #0056b3;
        }

        .createBtn {
            background-color: #ffc107;
            color: #fff;
        }

        .createBtn:hover {
            background-color: #e0a800;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            main {
                flex-direction: column;
                align-items: center;
            }

            .container,
            .content {
                flex: 1 1 100%;
            }
        }

        /* General Reset */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Overlay */
        #overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
            /* Initially hidden */
        }

        /* Modal Container */
        #sendModal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 400px;
            max-width: 90%;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
            /* Initially hidden */
        }

        /* Close Button */
        #sendModal button {
            background: transparent;
            border: none;
            font-size: 20px;
            color: #333;
            cursor: pointer;
            position: absolute;
            top: 10px;
            right: 10px;
        }

        #sendModal button:hover {
            color: #dc3545;
        }

        /* Modal Header */
        #sendModal h3 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 20px;
            color: #333;
        }

        /* Form Elements */
        #sendModal form {
            display: flex;
            flex-direction: column;
        }

        #sendModal label {
            margin-bottom: 8px;
            font-size: 14px;
            color: #555;
        }

        #sendModal input[type="text"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        /* Student List */
        .student-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .student-list li {
            padding: 10px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
        }

        .student-list li:last-child {
            border-bottom: none;
        }

        .student-list input[type="radio"] {
            margin-right: 10px;
        }

        .profile-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            border: 1px solid #ddd;
            object-fit: cover;
        }

        .student-name {
            font-size: 14px;
            color: #333;
        }

        /* Submit Button */
        #sendModal button[type="submit"] {
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 15px;
            font-size: 16px;
        }

        #sendModal button[type="submit"]:hover {
            background-color: #0056b3;
        }

        /* No Students Message */
        .student-list li {
            color: #777;
            text-align: center;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            #sendModal {
                width: 90%;
            }
        }
    </style>

</head>

<body>
    <main>
        <div class="container">
            <a href="./admin-dashboard.php"><i class="bi bi-arrow-left-circle-fill"></i></a>
            <h2>Available Forms</h2>
            <input type="text" id="searchInput" placeholder="Search forms..." onkeyup="liveSearch()">
            <ul id="formList">
                <?php foreach ($forms as $form): ?>
                    <li>
                        <a href="view_form.php?form_id=<?= $form['id']; ?>"><?= htmlspecialchars($form['form_name']); ?></a>
                        <button class="sendbtn" onclick="showSendModal(<?= $form['id']; ?>)">Send</button>
                        <form action="delete_form.php" method="POST" style="display: inline;">
                            <input type="hidden" name="form_id" value="<?= $form['id']; ?>">
                            <button type="submit" class="deletebtn" onclick="return confirm('Are you sure you want to delete this form?')">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    </li>
                <?php endforeach; ?>

            </ul>
        </div>
        <div class="content">
            <section>
                <h2>Create a New Form</h2>
                <form id="createForm" method="POST" action="create_form.php">
                    <div class="button-container">
                        <button type="button" class="fieldBtn" onclick="addField()"><i class="bi bi-bookmark-plus-fill"></i></button>
                        <button type="submit" class="createBtn">Create Form</button>
                    </div>
                    <label for="form_name">Form Name:</label>
                    <input type="text" name="form_name" id="form_name" required><br><br>
                    <div id="fields"></div>
                </form>
            </section>
        </div>
    </main>


    <div id="overlay"></div>
    <div id="sendModal">
        <button style="position: relative;" onclick="closeSendModal()"><i class="bi bi-x-circle-fill"></i></button>
        <h3>Send Form to Student</h3>
        <form method="POST" action="send_form.php">
            <input type="hidden" name="form_id" id="modalFormId">

            <label for="studentSearch">Search Students:</label>
            <input type="text" id="studentSearch" placeholder="Type to search..." onkeyup="filterStudentList()">

            <label for="student_id">Select a Student:</label>
            <ul class="student-list" id="studentList">
                <?php if (!empty($approved_students)): ?>
                    <?php foreach ($approved_students as $student): ?>
                        <li>
                            <label>
                                <input type="radio" name="student_id" value="<?= htmlspecialchars($student['id']); ?>" required>
                                <img src="../images-data/<?= htmlspecialchars($student['image']); ?>" alt="Profile Image" class="profile-image">
                                <span class="student-name"><?= htmlspecialchars($student['firstname']); ?></span>
                            </label>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No approved students available. Please approve students first.</li>
                <?php endif; ?>
            </ul>
            <button type="submit">Send Form</button>
        </form>
    </div>

    <script>
        let fieldCount = 0;

        function addField() {
            const fieldsDiv = document.getElementById('fields');
            const fieldId = `field_${fieldCount}`;

            const fieldHTML = `

        <div class="field" id="${fieldId}">
            <label>Field Name:</label>
            <input type="text" name="fields[${fieldCount}][name]" required>
         
            
            <button type="button" style="background-color: red" onclick="removeField('${fieldId}')"> <i class="bi bi-x-lg"></i></button>
        </div>
       
    `;
            fieldsDiv.insertAdjacentHTML('beforeend', fieldHTML);
            fieldCount++;
        }

        function filterStudentList() {
            const searchInput = document.getElementById("studentSearch").value.toLowerCase();
            const studentList = document.getElementById("studentList");
            const students = studentList.getElementsByTagName("li");

            for (let i = 0; i < students.length; i++) {
                const studentName = students[i].getElementsByClassName("student-name")[0].innerText.toLowerCase();

                if (studentName.includes(searchInput)) {
                    students[i].style.display = "flex";
                } else {
                    students[i].style.display = "none";
                }
            }
        }

        function removeField(fieldId) {
            const field = document.getElementById(fieldId);
            if (field) {
                field.remove();
            }
        }
        document.getElementById('student_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const studentImage = selectedOption.getAttribute('data-image');
            const studentName = selectedOption.getAttribute('data-name');

            if (studentImage && studentName) {
                document.getElementById('studentImage').src = studentImage;
                document.getElementById('studentName').textContent = studentName;
                document.getElementById('studentPreview').style.display = 'block';
            } else {
                document.getElementById('studentPreview').style.display = 'none';
            }
        });


        function liveSearch() {
            const searchQuery = document.getElementById('searchInput').value;

            fetch(`live_search.php?search=${encodeURIComponent(searchQuery)}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('formList').innerHTML = html;
                })
                .catch(error => console.error('Error:', error));
        }


        function showSendModal(formId) {
            document.getElementById('modalFormId').value = formId;
            document.getElementById('sendModal').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function closeSendModal() {
            document.getElementById('sendModal').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }
    </script>

</body>

</html>