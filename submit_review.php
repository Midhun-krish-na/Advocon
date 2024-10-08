<?php
require 'database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $advocate_id = intval($_POST['advocate_id']);
    $reviewer_name = trim($_POST['reviewer_name']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    $sql = "INSERT INTO reviews (advocate_id, reviewer_name, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("isis", $advocate_id, $reviewer_name, $rating, $comment);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Review submitted successfully!";
        } else {
            $_SESSION['error_message'] = "Error submitting review: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Error preparing statement: " . $conn->error;
    }
} else {
    $_SESSION['error_message'] = "Invalid request method.";
}

header("Location: advocate_profile.php?id=$advocate_id");
exit();
?>
