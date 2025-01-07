<?php
include '../database/dbcon.php';

$search = $_GET['search'] ?? '';

if (!empty($search)) {
    $query = $conn->prepare("SELECT * FROM forms WHERE form_name LIKE ?");
    $searchTerm = '%' . $search . '%';
    $query->bind_param('s', $searchTerm);
    
    if ($query->execute()) {
        $result = $query->get_result();
        $forms = $result->fetch_all(MYSQLI_ASSOC);
if (!empty($forms)) {
    foreach ($forms as $form) {
        echo '<li>
            <a href="view_form.php?form_id=' . htmlspecialchars($form['id']) . '">' . htmlspecialchars($form['form_name']) . '</a>
            <button class="sendbtn" onclick="showSendModal(' . htmlspecialchars($form['id']) . ')">
                <i class="bi bi-send-fill"></i>
            </button>
            <form action="delete_form.php" method="POST" style="display: inline;">
                <input type="hidden" name="form_id" value="' . htmlspecialchars($form['id']) . '">
                <button type="submit" class="deletebtn" onclick="return confirm(\'Are you sure you want to delete this form?\')">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </form>
        </li>';
    }
} else {
    echo '<li>No forms found.</li>';
}

    } else {
        echo "Error: " . $query->error;
    }
} else {
    echo '<li>Please enter a search term.</li>';
}
?>
