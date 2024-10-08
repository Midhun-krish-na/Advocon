<?php
require 'database.php';
session_start();

// Check if the user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && !empty($_GET['id']) && isset($_GET['action'])) {
    $advocate_id = intval($_GET['id']); // Ensure the ID is an integer
    $action = $_GET['action'];
    
    // Determine the SQL query based on the action
    if ($action === 'verify') {
        $sql = "UPDATE advocates SET verified = 1 WHERE id = ?";
    } elseif ($action === 'revoke') {
        $sql = "UPDATE advocates SET verified = 0 WHERE id = ?";
    } else {
        // Invalid action
        header("Location: admin_manage_advocates.php");
        exit();
    }

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $advocate_id);
        if ($stmt->execute()) {
            header("Location: admin_manage_advocates.php?status=success");
            exit();
        } else {
            header("Location: admin_manage_advocates.php?status=error");
            exit();
        }
        $stmt->close();
    } else {
        header("Location: admin_manage_advocates.php?status=error");
        exit();
    }
} else {
    header("Location: admin_manage_advocates.php?status=error");
    exit();
}

$conn->close();
?>
