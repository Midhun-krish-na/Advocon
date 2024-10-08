<?php
require 'database.php'; // Make sure this file sets up the $conn variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $fees = floatval($_POST['fees']);
    $cases_won = intval($_POST['cases_won']);
    $specialized_field = trim($_POST['specialized_field']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $errors = array();

    // Validate form data
    if (empty($name) || empty($email) || empty($password) || empty($phone) || empty($address) || empty($specialized_field)) {
        $errors[] = "All fields are required";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email is not valid";
    }
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    if (!validate_phone($phone)) {
        $errors[] = "Phone number is not valid";
    }

    // Check if email already exists
    $sql = "SELECT * FROM advocates WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errors[] = "Email already exists";
    }

    // Handle file uploads
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $certifications = array();

    if (!empty($_FILES['certifications']['name'][0])) {
        foreach ($_FILES['certifications']['tmp_name'] as $key => $tmp_name) {
            $file_name = basename($_FILES['certifications']['name'][$key]);
            $file_tmp = $_FILES['certifications']['tmp_name'][$key];
            $target_file = $upload_dir . $file_name;

            if (move_uploaded_file($file_tmp, $target_file)) {
                $certifications[] = $target_file;
            } else {
                $errors[] = "Failed to upload file: $file_name";
            }
        }
    } else {
        $errors[] = "At least one certification is required";
    }

    // Display errors or proceed with registration
    if (count($errors) > 0) {
        $error_message = implode(" | ", $errors);
        echo "<script>window.onload = function() { showPopup('error', '$error_message'); };</script>";
    } else {
        $certifications_str = implode(",", $certifications);
        $sql = "INSERT INTO advocates (name, email, password, phone, address, cases_won, fees, specialized_field, certifications, verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssssidss", $name, $email, $hashed_password, $phone, $address, $cases_won, $fees, $specialized_field, $certifications_str);
            if ($stmt->execute()) {
                echo "<script>window.onload = function() { showPopup('success', 'Registration successful. Awaiting admin approval.'); };</script>";
            } else {
                die("Something went wrong: " . $conn->error);
            }
        }
    }

    $stmt->close();
    $conn->close();
}

function validate_phone($phone) {
    return preg_match("/^\d{10,15}$/", $phone);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Registration page for advocates on Advocon">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/reg.css">
    <!-- AOS (Animate On Scroll) Library -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    <title>Advocon Advocate Registration</title>
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
</head>
<body class="body-login">
    <header>
        <nav class="navbar">
            <img src="img/icon/logoadvocon.png" alt="Advocon Logo" class="logo">
            <ul class="nav-list">
                <li><a href="index.html">Home</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </nav>
    </header>
    <div class="wrapper-reg">
        <div class="form-box register">
            <h2>Register</h2>
            <form action="register_advocate.php" method="post" enctype="multipart/form-data">
                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="person"></ion-icon>
                    </span>
                    <input type="text" name="name" id="name" required>
                    <label for="name">Name</label>
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
                        <ion-icon name="home"></ion-icon>
                    </span>
                    <input type="text" name="address" id="address" required>
                    <label for="address">Address</label>
                </div>
                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="cash"></ion-icon>
                    </span>
                    <input type="number" step="0.01" name="fees" id="fees" required>
                    <label for="fees">Minimum Fees</label>
                </div>
                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="trophy"></ion-icon>
                    </span>
                    <input type="number" name="cases_won" id="cases_won" required>
                    <label for="cases_won">Cases Won</label>
                </div>
                <div class="input-box">
                    <span class="icon">
                        <ion-icon name="school"></ion-icon>
                    </span>
                    <select class="dropdown" name="specialized_field" id="specialized_field" required>
                        <option value="" disabled selected></option>
                        <option value="criminal">Criminal</option>
                        <option value="business">Business</option>
                        <option value="environmental">Environmental</option>
                        <option value="civil">Civil</option>
                        <option value="constitutional">Constitutional</option>
                        <option value="family">Family</option>
                        <option value="intellectual_property">Intellectual Property</option>
                    </select>
                    <label for="specialized_field">Specialized Field</label>
                </div>
                <div class="input-box file-input">
                    <span class="icon">
                        <ion-icon name="document"></ion-icon>
                    </span>
                    <input type="file" class="attachfile" name="certifications[]" id="certifications" multiple required>
                    <label for="certifications">Certifications</label>
                </div>
                <div class="input-box">
                    <span class="icon">
                    <i class="fa fa-eye toggle-password" onclick="togglePassword()"></i>
                    </span>
                    <input type="password" name="password" id="password" required autocomplete="off">
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

                <button type="submit" class="reg">Register</button>
                <div class="login-register">
                    <p>
                        Already have an account? <a href="login.php" class="register-link">Login</a>
                    </p>
                </div>
               
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