<?php
include '../database/dbcon.php';

// Add a new form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_name = $_POST['form_name'];

    // Insert the new form and check if it succeeded
    if ($conn->query("INSERT INTO forms (form_name) VALUES ('$form_name')") === TRUE) {
        $form_id = $conn->insert_id; // Get the form ID of the inserted form
    } else {
        die("Error inserting form: " . $conn->error);
    }

    // Insert form fields
    foreach ($_POST['fields'] as $field) {
        $field_name = $field['name'];
        $field_type = $field['type'];
        $is_required = isset($field['required']) ? 1 : 0;

        $conn->query("INSERT INTO form_fields (form_id, field_name, field_type, is_required) 
                      VALUES ($form_id, '$field_name', '$field_type', $is_required)");
    }

    // Redirect after successful form creation
    header('Location: adminForm.php');
}

// If you want to retain the values after submitting, you can prepopulate the fields here
$existing_fields = isset($_POST['fields']) ? $_POST['fields'] : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Form</title>
</head>
<body>
    <h1>Create a New Form</h1>
    <form method="POST">
        <label for="form_name">Form Name:</label>
        <input type="text" name="form_name" id="form_name" required><br><br>
        <button type="button" onclick="addField()">Add Field</button>
        <button type="submit">Create Form</button>

        <div id="fields">
            <?php
            $fieldCount = 0;
            // Display existing fields
            foreach ($existing_fields as $field) {
                ?>
                <div class="field">
                    <label>Field Name:</label>
                    <input type="text" name="fields[<?php echo $fieldCount; ?>][name]" value="<?php echo htmlspecialchars($field['name']); ?>" required>
                    <label>Field Type:</label>
                    <select name="fields[<?php echo $fieldCount; ?>][type]">
                        <option value="text" <?php echo ($field['type'] === 'text') ? 'selected' : ''; ?>>Text</option>
                        <option value="number" <?php echo ($field['type'] === 'number') ? 'selected' : ''; ?>>Number</option>
                        <option value="email" <?php echo ($field['type'] === 'email') ? 'selected' : ''; ?>>Email</option>
                        <option value="textarea" <?php echo ($field['type'] === 'textarea') ? 'selected' : ''; ?>>Textarea</option>
                    </select>
                    <label>Required:</label>
                    <input type="checkbox" name="fields[<?php echo $fieldCount; ?>][required]" <?php echo ($field['required']) ? 'checked' : ''; ?>>
                </div>
                <?php
                $fieldCount++;
            }
            ?>
        </div>
    </form>

    <script>
        let fieldCount = <?php echo $fieldCount; ?>;

        function addField() {
            const fieldsDiv = document.getElementById('fields');
            fieldsDiv.innerHTML += `
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
            fieldCount++;
        }
    </script>
</body>
</html>
