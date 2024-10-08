<?php
session_start();
require_once "../database.php";

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION["id"];
$role = $_SESSION["role"];

if ($role == "user") {
    $sql = "SELECT username, phone, email FROM register WHERE id = ?";
} elseif ($role == "admin") {
    $sql = "SELECT name, email FROM admins WHERE admin_id = ?";
} else {
    // Handle other roles or redirect
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($username, $phone, $email);
    $stmt->fetch();
    $stmt->close();
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="../css/pro_use.css">
</head>
<body>
<?php include '../templates/nav_prof.php'; ?>
    </header>
    <div class="profile-container">
        <h2>User Profile</h2>
        <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        <a href="edit_profile_user.php">Edit Profile</a>
    </div>
</body>
</html>
