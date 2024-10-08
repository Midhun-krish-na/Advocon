<?php
session_start();
require_once "database.php"; // Your database connection

// Check if the user is logged in
$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

if (!$user_id) {
    echo "You must be logged in to view your appointments.";
    exit;
}

// Fetch appointments for this user (client who requested the appointment)
$sql = "SELECT appointments.id, appointments.advocate_id, advocates.name AS advocate_name, 
               appointments.service_needed, appointments.description, 
               appointments.appointment_date, appointments.status, appointments.suggested_date
        FROM appointments 
        JOIN advocates ON appointments.advocate_id = advocates.id
        WHERE appointments.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Appointments</title>
    <link rel="stylesheet" href="css/appointments.css">
</head>
<body>
<?php include 'templates/navbar.php'; ?>

<div class="appointments-container">
    <h2>Your Appointments</h2>

    <!-- Display any success or error messages (optional) -->
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

    <!-- Check if appointments exist -->
    <?php if (!empty($appointments)): ?>
        <table>
            <thead>
                <tr>
                    <th>Advocate</th>
                    <th>Service Needed</th>
                    <th>Description</th>
                    <th>Requested Date</th>
                    <th>Suggested Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <!-- Loop through each appointment and display its details -->
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($appointment['advocate_name']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['service_needed']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['description']); ?></td>
                        <td><?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($appointment['appointment_date']))); ?></td>
                        <td><?php echo $appointment['suggested_date'] ? htmlspecialchars(date('F j, Y, g:i a', strtotime($appointment['suggested_date']))) : 'N/A'; ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($appointment['status'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No appointments found.</p>
    <?php endif; ?>
</div>

</body>
</html>
