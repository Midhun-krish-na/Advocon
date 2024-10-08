<?php
session_start();
require_once 'database.php';
require 'PHPMailer/PHPMailer.php'; // Ensure PHPMailer is correctly included
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

if (isset($_POST['forgot_password'])) {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT * FROM register WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    if (!$result) {
        die("Get result failed: " . $stmt->error);
    }
    $user = $result->fetch_assoc();

    if ($user) {
        // Generate a unique token
        $token = bin2hex(random_bytes(50));
        $expires = date("U") + 1800; // 30 minutes expiry

        // Store the token in the database
        $stmt = $conn->prepare("UPDATE register SET reset_token = ?, reset_token_expires = ? WHERE email = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sis", $token, $expires, $email);
        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->error);
        }

        // Send the reset password email
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@gmail.com';
        $mail->Password = 'your_password'; // Use app password if 2FA is enabled
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your_email@gmail.com', 'Your Name');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Reset your password';
        $mail->Body    = 'Click <a href="http://localhost/reset_password.php?token=' . $token . '">here</a> to reset your password.';

        if ($mail->send()) {
            echo "Reset password link has been sent to your email.";
        } else {
            echo "Failed to send email. Mailer Error: " . $mail->ErrorInfo;
        }
    } else {
        echo "Email does not exist.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/style_log.css">
</head>
<body>
<div class="body-forgot-password">
    <div class="wrapper">
        <div class="form-box">
            <h2>Forgot Password</h2>
            <form action="forgot_password.php" method="POST">
                <div class="input-box">
                    <input type="email" name="email" id="email" required>
                    <label for="email">Email</label>
                </div>
                <button type="submit" class="log" name="forgot_password">Send Reset Link</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
