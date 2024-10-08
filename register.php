<?php
require_once "database.php";

function validate_phone($phone) {
    return preg_match('/^[6-9]\d{9}$/', $phone);
}

$errors = array(); // Initialize errors array

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $phone = trim($_POST["phone"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Validate form data
    if (empty($username) || empty($phone) || empty($email) || empty($password)) {
        $errors[] = "All fields are required";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email is not valid";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    if (!validate_phone($phone)) {
        $errors[] = "Phone number is not valid. Please enter a 10-digit number starting with 6-9.";
    }

    // Check if email already exists
    if (empty($errors)) {
        $sql = "SELECT * FROM register WHERE email = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errors[] = "Email already exists";
            }
            $stmt->close();
        } else {
            $errors[] = "Database error: " . $conn->error;
        }
    }

    // Proceed with registration if no errors
    if (empty($errors)) {
        $passwordhash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO register (username, phone, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssss", $username, $phone, $email, $passwordhash);
            if ($stmt->execute()) {
                echo "<div class='alert alert-success'>Registration successful</div>";
            } else {
                $errors[] = "Something went wrong: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Something went wrong: " . $conn->error;
        }
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Font -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/reg.css">
    <!-- AOS -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <script>
        function togglePassword() {
            var passwordField = document.getElementById("password");
            var passwordFieldType = passwordField.getAttribute("type");
            var toggleIcon = document.querySelector(".toggle-password");

            if (passwordFieldType === "password") {
                passwordField.setAttribute("type", "text");
                toggleIcon.classList.remove("fa-eye");
                toggleIcon.classList.add("fa-eye-slash");
            } else {
                passwordField.setAttribute("type", "password");
                toggleIcon.classList.remove("fa-eye-slash");
                toggleIcon.classList.add("fa-eye");
            }
        }
    </script>

    <title>Advocon</title>
</head>
<body class="body-login">
    <header>
        <nav class="navbar">
            <img src="img/icon/logoadvocon.png" alt="logo" class="logo">
            <ul class="nav-list">
                <li><a href="index.html">Home</a></li>
                <li><a href="register_advocate.php">Advocate Register</a></li>
            </ul>
        </nav>
    </header>
    
    <div class="wrapper-reg">
        <span class="icon-close">
            <ion-icon name="close"></ion-icon>
        </span>
        <div class="form-box register">
            <h2>Register</h2>
            <form action="register.php" method="post">
                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="person"></ion-icon>
                    </span>
                    <input type="text" name="username" id="username" required>
                    <label for="username">Username</label>
                </div>
                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="call"></ion-icon>
                    </span>
                    <input type="tel" name="phone" id="phone" required>
                    <label for="phone">Phone</label>
                </div>                    
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
                    <input type="password" name="password" id="password" required>
                    <label for="password">Password</label>
                </div>
                <div class="terms">
                    <label for="terms">
                        <input type="checkbox" name="terms" id="terms" required>
                        <a href="#">I agree to the terms & conditions</a>
                    </label>
                </div>

                <!-- Display errors -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                                <!-- Display success message -->
                                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($successMessage); ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="reg" name="submit">Register</button>
                <div class="login-register">
                    <p>
                        Already have an Account? <a href="login.php" class="login-link">Login</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
