<?php
session_start();
require '../database.php'; // Ensure this connects to the database

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the advocate is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'advocate') {
    header('Location: login.php');
    exit();
}

// Retrieve advocate's details for the form
$advocate_id = $_SESSION['id'];
$sql = "SELECT name, phone, address, cases_won, fees, specialized_field, certifications FROM advocates WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $advocate_id);
$stmt->execute();
$stmt->bind_result($name, $phone, $address, $cases_won, $fees, $specialized_field, $certifications);
$stmt->fetch();
$stmt->close();

// Handle form submission for updating the profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $fees = floatval($_POST['fees']);
    $specialized_field = trim($_POST['specialized_field']);
    $errors = array();

    // Validation
    if (empty($name) || empty($phone) || empty($address) || empty($specialized_field)) {
        $errors[] = "All fields are required.";
    }

    if (!preg_match('/^\+?\d{10,15}$/', $phone)) {
        $errors[] = "Invalid phone number format.";
    }

    if ($fees < 0) {
        $errors[] = "Fees must be a positive number.";
    }

    if (count($errors) === 0) {
        // Update the advocate's details in the database
        $sql = "UPDATE advocates SET name = ?, phone = ?, address = ?, fees = ?, specialized_field = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sssisi", $name, $phone, $address, $fees, $specialized_field, $advocate_id);
            if ($stmt->execute()) {
                $_SESSION['name'] = $name; // Update session name
                header('Location: profile_adv.php?success=1');
                exit();
            } else {
                $errors[] = "Failed to update the profile. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../css/edit_profile.css">
</head>
<body>
<?php include '../templates/nav_prof.php'; ?>

<div class="container">
    <h2>Edit Profile</h2>
    <form action="edit_profile_adv.php" method="post">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone:</label>
            <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
        </div>
        <div class="form-group">
            <label for="address">Address:</label>
            <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($address); ?>" required>
        </div>
        <div class="form-group">
            <label for="fees">Minimum Fees:</label>
            <input type="number" step="0.01" name="fees" id="fees" value="<?php echo htmlspecialchars($fees); ?>" required>
        </div>
        <div class="form-group">
            <label for="specialized_field">Specialized Field:</label>
            <select name="specialized_field" id="specialized_field" required>
                <option value="criminal" <?php echo ($specialized_field == 'criminal') ? 'selected' : ''; ?>>Criminal</option>
                <option value="business" <?php echo ($specialized_field == 'business') ? 'selected' : ''; ?>>Business</option>
                <option value="environmental" <?php echo ($specialized_field == 'environmental') ? 'selected' : ''; ?>>Environmental</option>
                <option value="civil" <?php echo ($specialized_field == 'civil') ? 'selected' : ''; ?>>Civil</option>
                <option value="constitutional" <?php echo ($specialized_field == 'constitutional') ? 'selected' : ''; ?>>Constitutional</option>
                <option value="family" <?php echo ($specialized_field == 'family') ? 'selected' : ''; ?>>Family</option>
                <option value="intellectual_property" <?php echo ($specialized_field == 'intellectual_property') ? 'selected' : ''; ?>>Intellectual Property</option>
            </select>
        </div>
        <button type="submit">Save Changes</button>
    </form>
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['success'])): ?>
        <div class="success">
            <p>Your profile has been updated successfully!</p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
