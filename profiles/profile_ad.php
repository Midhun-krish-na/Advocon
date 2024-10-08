<?php
session_start();
require_once "../database.php";

// Check if admin is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION["id"];
$sql = "SELECT name, email FROM admins WHERE admin_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($name, $email);
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
    <title>Admin Profile</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">
<?php include '../templates/nav_prof.php'; ?>
    <div style="background-color: white; padding: 20px; width: 350px; box-shadow: 0 0 15px rgba(0,0,0,0.1); border-radius: 8px;">
        <h2 style="text-align: center; color: #333;">Admin Profile</h2>
        
        <div style="margin-top: 20px;">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
        </div>
    </div>

</body>
</html>
