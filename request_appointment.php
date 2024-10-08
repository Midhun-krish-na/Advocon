<?php
session_start();
require_once "database.php";

if (!isset($_GET['id'])) {
    echo "Advocate ID not specified.";
    exit;
}

$advocate_id = intval($_GET['id']);
$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null; // Assuming user is logged in and their ID is stored in session

// Check if the user is logged in
if (!$user_id) {
    echo "You must be logged in to book an appointment.";
    exit;
}

// Fetch advocate details for display purposes
$sql = "SELECT id, name FROM advocates WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $advocate_id);
$stmt->execute();
$result = $stmt->get_result();
$advocate = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $service_needed = $_POST['service_needed'];
    $description = $_POST['description'];
    $appointment_date = $_POST['appointment_date'];

    // Insert appointment into the database
    $sql_insert = "INSERT INTO appointments (advocate_id, user_id, service_needed, description, appointment_date) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iisss", $advocate_id, $user_id, $service_needed, $description, $appointment_date);
    
    if ($stmt_insert->execute()) {
        $_SESSION['success_message'] = "Appointment request submitted successfully.";
        header("Location: advocate_profile.php?id=" . $advocate_id);
        exit;
    } else {
        $_SESSION['error_message'] = "Failed to submit appointment request.";
    }
    
    $stmt_insert->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Appointment</title>
    <link rel="stylesheet" href="css/req_appointments.css">
</head>
<body>
<?php include 'templates/navbar.php'; ?>

<div class="appointment-container">
    <?php
    if (isset($_SESSION['success_message'])) {
        echo "<div class='alert alert-success'>" . htmlspecialchars($_SESSION['success_message']) . "</div>";
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
        echo "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['error_message']) . "</div>";
        unset($_SESSION['error_message']);
    }
    ?>

    <?php if ($advocate): ?>
        <h2>Request Appointment with <?php echo htmlspecialchars($advocate['name']); ?></h2>
        <form action="" method="post">
            <label for="service_needed">Service Needed:</label>
            <input type="text" id="service_needed" name="service_needed" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="appointment_date">Preferred Appointment Date:</label>
            <input type="datetime-local" id="appointment_date" name="appointment_date" required>

            <button type="submit">Submit Appointment Request</button>
        </form>
    <?php else: ?>
        <p>Advocate not found.</p>
    <?php endif; ?>
</div>
</body>
</html>
