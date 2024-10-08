<?php
session_start();
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $advocate_id = intval($_POST['advocate_id']);
    $rating = intval($_POST['rating']);
    $user_id = $_SESSION['user_id'];  // Assuming you have the user's ID stored in the session after login

    if ($advocate_id > 0 && $rating > 0 && $rating <= 5) {
        // Check if the user has already rated this advocate
        $check_sql = "SELECT * FROM ratings WHERE advocate_id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $advocate_id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $_SESSION['error_message'] = "You have already rated this advocate.";
        } else {
            // Insert the new rating
            $insert_sql = "INSERT INTO ratings (advocate_id, user_id, rating) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("iii", $advocate_id, $user_id, $rating);

            if ($insert_stmt->execute()) {
                // Update the advocate's average rating
                $update_sql = "UPDATE advocates SET rating = (SELECT AVG(rating) FROM ratings WHERE advocate_id = ?) WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $advocate_id, $advocate_id);
                $update_stmt->execute();

                $_SESSION['success_message'] = "Rating submitted successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to submit rating.";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } else {
        $_SESSION['error_message'] = "Invalid rating.";
    }
} else {
    $_SESSION['error_message'] = "Invalid request.";
}

header("Location: view_advocates.php");
exit();
?>
