<?php
require_once "database.php";

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $sql = "SELECT * FROM register WHERE reset_token = ? AND reset_token_expires > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if (isset($_POST['submit'])) {
            $password = trim($_POST['password']);
            $confirm_password = trim($_POST['confirm_password']);

            if (empty($password) || empty($confirm_password)) {
                echo "<div class='alert alert-danger'>Please fill all fields</div>";
            } elseif ($password !== $confirm_password) {
                echo "<div class='alert alert-danger'>Passwords do not match</div>";
            } else {
                $passwordhash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("UPDATE register SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE reset_token = ?");
                $stmt->bind_param("ss", $passwordhash, $token);
                if ($stmt->execute()) {
                    echo "<div class='alert alert-success'>Password has been reset. You can now <a href='login.html'>login</a></div>";
                } else {
                    die("Execute failed: " . $stmt->error);
                }
            }
        }
    } else {
        echo "<div class='alert alert-danger'>Invalid or expired token</div>";
    }
    $stmt->close();
} else {
    echo "<div class='alert alert-danger'>No token provided</div>";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/style_log.css">
</head>
<body>
<div class="body-reset-password">
    <div class="wrapper">
        <div class="form-box">
            <h2>Reset Password</h2>
            <form action="reset_password.php" method="POST">
                <div class="input-box">
                    <input type="password" name="password" id="password" required>
                    <label for="password">New Password</label>
                </div>
                <div class="input-box">
                    <input type="password" name="confirm_password" id="confirm_password" required>
                    <label for="confirm_password">Confirm New Password</label>
                </div>
                <button type="submit" class="log" name="submit">Reset Password</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
