<?php
session_start();
require 'database.php';

// Check if the advocate is logged in and has the correct role
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'advocate') {
    header('Location: login.php');
    exit();
}

// Ensure advocate ID is set in the session
if (!isset($_SESSION['id'])) {
    echo "Error: Advocate ID not found in session.";
    exit();
}

// Optional: Set a default name if not set
if (!isset($_SESSION['name'])) {
    $_SESSION['name'] = 'Advocate'; // Set a default value if name isn't available
}

// Get advocate ID from session
$advocate_id = $_SESSION['id'];

// Retrieve advocate's profile image and other details
$sql = "SELECT profile_image FROM advocates WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param('i', $advocate_id);
    $stmt->execute();
    $stmt->bind_result($profile_image);
    $stmt->fetch();
    $stmt->close();
} else {
    echo "Error fetching profile image.";
    exit();
}

// Fetch accepted appointments or those with a suggested date for this advocate
$sql_appointments = "SELECT appointments.id, appointments.user_id, register.username, appointments.service_needed, 
                     appointments.appointment_date, appointments.status, appointments.suggested_date
                     FROM appointments 
                     JOIN register ON appointments.user_id = register.id
                     WHERE appointments.advocate_id = ? AND (appointments.status = 'accepted' OR appointments.suggested_date IS NOT NULL)";
$stmt_appointments = $conn->prepare($sql_appointments);
if ($stmt_appointments) {
    $stmt_appointments->bind_param("i", $advocate_id);
    $stmt_appointments->execute();
    $result_appointments = $stmt_appointments->get_result();
    $accepted_appointments = $result_appointments->fetch_all(MYSQLI_ASSOC);
    $stmt_appointments->close();
} else {
    echo "Error fetching appointments.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advocate Dashboard</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.6/css/unicons.css">
    <link rel="stylesheet" href="css/advo.css">
</head>
<body>
    <nav>
        <div class="container">
            <h2 class="log">Advocon.</h2>
            <div class="case">
                <div class="profile-photo">
                    <a href="profiles/profile_adv.php">
                        <img src="<?php echo !empty($profile_image) ? './assets/images/' . htmlspecialchars($profile_image) : './assets/images/default-profile.png'; ?>" alt="Profile">
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">
            <!-- Left Section -->
            <div class="left">
                <div class="profile-photo">
                    <a href="profiles/profile_adv.php">
                        <img src="<?php echo !empty($profile_image) ? './assets/images/' . htmlspecialchars($profile_image) : './assets/images/default-profile.png'; ?>" alt="Profile">
                    </a>
                </div>
                <div class="handle">
                    <h4><?php echo htmlspecialchars($_SESSION['name']); ?></h4>
                    <p class="text-muted">@advocate</p>
                </div>

                <!-- Sidebar -->
                <div class="sidebar">
                    <a class="menu-item active" href="advocate_dashboard.php">
                        <span><i class="uil uil-home"></i></span><h3>Home</h3>
                    </a>
                    <a class="menu-item" href="view_appoint.php">
                        <span><i class="uil uil-bell"></i></span><h3>Appointments</h3>
                    </a>
                    <a class="menu-item" href="appointments.php">
                        <span><i class="uil uil-envelope"></i></span><h3>Case Request</h3>
                    </a>
                </div>
            </div>

            <!-- Right Section -->
            <div class="right">
                <h2>Welcome to Your Dashboard</h2>
                <p>Here you can manage your appointments and case requests.</p>

                <h3>Your Appointments:</h3>
                <?php if (!empty($accepted_appointments)): ?>
                    <ul>
                        <?php foreach ($accepted_appointments as $appointment): ?>
                            <li>
                                <strong>Client:</strong> <?php echo htmlspecialchars($appointment['username']); ?> <br>
                                <strong>Service Needed:</strong> <?php echo htmlspecialchars($appointment['service_needed']); ?> <br>
                                <strong>Appointment Date:</strong> <?php echo htmlspecialchars($appointment['appointment_date']); ?> <br>
                                <?php if ($appointment['suggested_date']): ?>
                                    <strong>Suggested Date:</strong> <span>🕒 <?php echo htmlspecialchars($appointment['suggested_date']); ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No accepted appointments found.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="js/dashboard.js"></script>
</body>
</html>
