<?php
require 'database.php';

// Admin data
$name = "Admin Name";
$email = "admin@example.com";
$password = "securepassword"; // Change to your desired password
$role = "admin";
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert admin data
$sql = "INSERT INTO admins (name, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
    if ($stmt->execute()) {
        echo "Admin data inserted successfully.";
    } else {
        echo "Error inserting admin data: " . $conn->error;
    }
} else {
    die("Something went wrong: " . $conn->error);
}

$conn->close();
?>
