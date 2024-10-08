<?php
session_start();
require_once "database.php";

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        // Function to check login credentials
        function checkCredentials($conn, $sql, $email, &$id, &$name, &$hashed_password) {
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("s", $email);
                if ($stmt->execute()) {
                    $stmt->store_result();
                    if ($stmt->num_rows === 1) {
                        $stmt->bind_result($id, $name, $hashed_password);
                        $stmt->fetch();
                        $stmt->close();
                        return true;
                    }
                }
                $stmt->close();
            }
            return false;
        }

        // Function to check advocate login credentials (including verification)
        function checkAdvocateCredentials($conn, $sql, $email, &$id, &$name, &$hashed_password, &$verified) {
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("s", $email);
                if ($stmt->execute()) {
                    $stmt->store_result();
                    if ($stmt->num_rows === 1) {
                        $stmt->bind_result($id, $name, $hashed_password, $verified);
                        $stmt->fetch();
                        $stmt->close();
                        return true;
                    }
                }
                $stmt->close();
            }
            return false;
        }

        // Check Advocates table first (requires verification)
        $sql = "SELECT id, name, password, verified FROM advocates WHERE email = ?";
        if (checkAdvocateCredentials($conn, $sql, $email, $id, $name, $hashed_password, $verified)) {
            if (password_verify($password, $hashed_password)) {
                if (!$verified) {
                    $errors[] = "Your account is not approved yet. Please wait for admin approval.";
                } else {
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["name"] = $name;
                    $_SESSION["role"] = "advocate";
                    header("Location: adv_dash.php");
                    exit();
                }
            } else {
                $errors[] = "Invalid password.";
            }
        } else {
            // Check clients if not found in advocates
            $sql = "SELECT id, username, password FROM register WHERE email = ?";
            if (checkCredentials($conn, $sql, $email, $id, $username, $hashed_password)) {
                if (password_verify($password, $hashed_password)) {
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["name"] = $username;
                    $_SESSION["role"] = "user";
                    header("Location: user_dash.php");
                    exit();
                } else {
                    $errors[] = "Invalid password.";
                }
            } else {
                // Check admins if not found in clients
                $sql = "SELECT admin_id, name, password FROM admins WHERE email = ?";
                if (checkCredentials($conn, $sql, $email, $admin_id, $name, $hashed_password)) {
                    if (password_verify($password, $hashed_password)) {
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $admin_id;
                        $_SESSION["name"] = $name;
                        $_SESSION["role"] = "admin";
                        header("Location: admin_dashboard.php");
                        exit();
                    } else {
                        $errors[] = "Invalid password.";
                    }
                } else {
                    $errors[] = "No account found with that email.";
                }
            }
        }
    } else {
        $errors[] = "Please fill in both fields.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login page for Advocon">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <title>Advocon Login</title>
    <script>
        function togglePassword() {
            var passwordField = document.getElementById("password");
            var toggleIcon = document.querySelector(".toggle-password");
            passwordField.type = (passwordField.type === "password") ? "text" : "password";
            toggleIcon.classList.toggle("fa-eye");
            toggleIcon.classList.toggle("fa-eye-slash");
        }
    </script>
</head>
<body class="body-login">
    <header>
        <nav class="navbar">
            <img src="img/icon/logoadvocon.png" alt="Advocon Logo" class="logo">
            <ul class="nav-list">
                <li><a href="index.html">Home</a></li>
            </ul>
        </nav>
    </header>
    <div class="wrapper">
        <div class="form-box login">
            <h2>Login</h2>
            <form action="login.php" method="post">
                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="mail"></ion-icon>
                    </span>
                    <input type="email" name="email" id="email" required>
                    <label for="email">Email</label>
                </div>
                <div class="input-box">
                    <span class="icon">
                        <i class="fa fa-eye toggle-password" onclick="togglePassword()"></i>
                    </span>
                    <input type="password" name="password" id="password" required autocomplete="off">
                    <label for="password">Password</label>
                </div>
                <div class="remember-forgot">
                    <label>
                        <input type="checkbox" name="remember-password">
                        Remember me
                    </label>
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>
                <button type="submit" class="log" name="login">Login</button>
                <div class="login-register">
                    <p>
                        Don't have an account? <a href="register.php" class="register-link">Register</a>
                    </p>
                </div>
                <!-- Display errors -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>
</html>
