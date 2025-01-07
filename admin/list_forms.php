<?php
include '../database/dbcon.php';

// Fetch all forms
$forms = $conn->query("SELECT * FROM forms");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Forms</title>
</head>
<body>
    <h1>Forms</h1>
    <ul>
        <?php while ($form = $forms->fetch_assoc()): ?>
            <li>
                <a href="fill_form.php?form_id=<?= $form['id']; ?>"><?= $form['form_name']; ?></a>
            </li>
        <?php endwhile; ?>
    </ul>
</body>
</html>
