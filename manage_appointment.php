<?php
session_start();
require 'database.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'advocate') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = $_POST['appointment_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $new_status = 'Approved';
    } elseif ($action === 'reject') {
        $new_status = 'Rejected';
    } else {
        $new_status = 'Pending';
    }

    // Update appointment status
    try {
        $sql = "UPDATE appointments SET status = ? WHERE id = ? AND advocate_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sii", $new_status, $appointment_id, $_SESSION['id']);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }

    // Redirect back to the appointments page
    header('Location: appointments_advocate.php');
    exit();
}

$conn->close();
?>
