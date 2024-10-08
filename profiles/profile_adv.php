<?php
session_start();
require '../database.php'; // Ensure this connects to the database

// Check if the advocate is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'advocate') {
    header('Location: login.php');
    exit();
}

// Retrieve advocate's details from the database using the session ID
$advocate_id = $_SESSION['id'];
$sql = "SELECT name, email, phone, address, cases_won, fees, specialized_field, certifications FROM advocates WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param('i', $advocate_id);
    $stmt->execute();
    $stmt->bind_result($name, $email, $phone, $address, $cases_won, $fees, $specialized_field, $certifications);
    if (!$stmt->fetch()) {
        // Handle the case where the advocate is not found
        echo "<p>Profile not found.</p>";
        exit();
    }
    $stmt->close();
} else {
    // Handle SQL error
    error_log("SQL Error: " . $conn->error);
    echo "<p>Error in SQL query.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Profile</title>
    <link rel="stylesheet" href="../css/view_profile.css">
</head>
<body>
<?php include '../templates/nav_prof.php'; ?>
    <div class="container">
        <h2>Your Profile</h2>
        <div class="profile-info">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($name); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
            <p><strong>Specialized Field:</strong> <?php echo htmlspecialchars($specialized_field); ?></p>
            <p><strong>Cases Won:</strong> <?php echo htmlspecialchars($cases_won); ?></p>
            <p><strong>Fees:</strong> $<?php echo htmlspecialchars($fees); ?></p>
            <p><strong>Certifications:</strong> 
                <?php 
                $certification_links = explode(',', $certifications);
                foreach ($certification_links as $cert) {
                    echo "<a href='" . htmlspecialchars(trim($cert)) . "' target='_blank'>Certification</a> ";
                }
                ?>
            </p>
        </div>
        <a href="edit_profile_adv.php">Edit Profile</a>
    </div>
</body>
</html>
