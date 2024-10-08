<?php
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $case_id = $_POST['case_id'];
    $action = $_POST['action'];

    $status = ($action == 'accept') ? 'Reviewed' : 'Declined';

    $sql = "UPDATE cases SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $case_id);
    if ($stmt->execute()) {
        header("Location: view_cases.php");
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
    $conn->close();
}
?>
	