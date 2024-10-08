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

// Fetch the current user data
if ($role == "user") {
    $sql = "SELECT username, phone, email FROM register WHERE id = ?";
} elseif ($role == "admin") {
    $sql = "SELECT name, email FROM admins WHERE admin_id = ?";
} else {
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

// Handle form submission for updating profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = trim($_POST["username"]);
    $new_phone = trim($_POST["phone"]);
    $new_email = trim($_POST["email"]);

    $errors = array();

    // Validate inputs
    if (empty($new_username) || empty($new_phone) || empty($new_email)) {
        $errors[] = "All fields are required.";
    }

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if (!preg_match('/^[6-9]\d{9}$/', $new_phone)) {
        $errors[] = "Phone number is not valid. It should be a 10-digit number starting with 6-9.";
    }

    // If no errors, update the user's information
    if (empty($errors)) {
        if ($role == "user") {
            $sql = "UPDATE register SET username = ?, phone = ?, email = ? WHERE id = ?";
        } elseif ($role == "admin") {
            $sql = "UPDATE admins SET name = ?, email = ? WHERE admin_id = ?";
        }

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if ($role == "user") {
                $stmt->bind_param("sssi", $new_username, $new_phone, $new_email, $id);
            } elseif ($role == "admin") {
                $stmt->bind_param("ssi", $new_username, $new_email, $id);
            }

            if ($stmt->execute()) {
                echo "<div class='alert alert-success'>Profile updated successfully.</div>";
            } else {
                echo "<div class='alert alert-danger'>Something went wrong: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            echo "<div class='alert alert-danger'>Something went wrong: " . $conn->error . "</div>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../css/edit_profile_user.css">
</head>
<body>
<?php include '../templates/nav_prof.php'; ?>
    
    <div class="profile-container">
        <h2>Edit Profile</h2>
        <form action="edit_profile_user.php" method="post">
            <div class="input-box">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="input-box">
                <label for="phone">Phone:</label>
                <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>
            <div class="input-box">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <button type="submit">Update Profile</button>
        </form>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
