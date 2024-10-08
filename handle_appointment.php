<?php
session_start();
require 'database.php';

// Check if user is logged in and has the correct role
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'advocate') {
    header('Location: login.php');
    exit();
}

// Check if appointment ID and action are set
if (isset($_GET['id']) && isset($_GET['action'])) {
    $appointment_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    // Validate action
    if ($action !== 'approve' && $action !== 'deny') {
        header('Location: appointments.php?error=invalid_action');
        exit();
    }

    try {
        // Prepare SQL statement to update appointment status
        $status = ($action === 'approve') ? 'Approved' : 'Denied';
        $sql = "UPDATE appointments SET status = ? WHERE id = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters
            $stmt->bind_param("si", $status, $appointment_id);
            
            // Execute the statement
            if ($stmt->execute()) {
                // Redirect to appointments page with success message
                header('Location: appointments.php?success=appointment_updated');
                exit();
            } else {
                // Redirect to appointments page with error message
                header('Location: appointments.php?error=update_failed');
                exit();
            }
        } else {
            // Redirect to appointments page with error message
            header('Location: appointments.php?error=prepare_failed');
            exit();
        }
    } catch (Exception $e) {
        // Redirect to appointments page with error message
        header('Location: appointments.php?error=' . urlencode($e->getMessage()));
        exit();
    }
} else {
    // Redirect to appointments page if parameters are missing
    header('Location: appointments.php?error=missing_parameters');
    exit();
}

$conn->close();
?>
