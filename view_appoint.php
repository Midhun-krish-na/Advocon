<?php
session_start();
require_once "database.php";

// Check if the advocate is logged in
$advocate_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;

if (!$advocate_id) {
    echo "You must be logged in as an advocate to view appointments.";
    exit;
}

// Fetch appointments for this advocate, including all statuses
$sql = "SELECT appointments.id, appointments.user_id, register.username, 
               appointments.service_needed, appointments.description, 
               appointments.appointment_date, appointments.status, 
               appointments.suggested_date, appointments.suggested_status
        FROM appointments 
        JOIN register ON appointments.user_id = register.id
        WHERE appointments.advocate_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $advocate_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $_SESSION['error_message'] = "Could not fetch appointments.";
}

// Handle form submission for accepting, rejecting, or suggesting a new date
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointment_id = intval($_POST['appointment_id']);
    $action = $_POST['action'];

    if ($action == 'accept') {
        // Accept the appointment
        $sql_update = "UPDATE appointments SET status = 'approved', suggested_status = NULL WHERE id = ?";
    } elseif ($action == 'reject') {
        // Reject the appointment
        $suggested_date = isset($_POST['suggested_date']) ? $_POST['suggested_date'] : null;

        if ($suggested_date) {
            // Reject with a suggested date
            $sql_update = "UPDATE appointments SET status = 'declined', suggested_date = ?, suggested_status = 'rejected' WHERE id = ?";
        } else {
            // Reject without suggesting a new date
            $sql_update = "UPDATE appointments SET status = 'declined', suggested_status = NULL WHERE id = ?";
        }
    }

    // Prepare and execute the update statement
    $stmt_update = $conn->prepare($sql_update);
    if ($suggested_date) {
        $stmt_update->bind_param("si", $suggested_date, $appointment_id);
    } else {
        $stmt_update->bind_param("i", $appointment_id);
    }
    $stmt_update->execute();
    $stmt_update->close();

    $_SESSION['success_message'] = "Appointment status has been updated.";
    header("Location: view_appoint.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointments</title>
    <link rel="stylesheet" href="css/appointments.css">
</head>
<body>
<?php include 'templates/navbar.php'; ?>

<div class="appointments-container">
    <h2>Your Appointments</h2>

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

    <?php if (!empty($appointments)): ?>
        <table>
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Service Needed</th>
                    <th>Description</th>
                    <th>Requested Date</th>
                    <th>Suggested Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($appointment['username']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['service_needed']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['description']); ?></td>
                        <td><?php echo htmlspecialchars($appointment['appointment_date']); ?></td>
                        <td><?php echo $appointment['suggested_date'] ? htmlspecialchars($appointment['suggested_date']) : 'N/A'; ?></td>
                        <td>
                            <?php 
                            // Display status based on appointment and suggested status
                            switch ($appointment['status']) {
                                case 'approved':
                                    echo 'Accepted'; // Map 'approved' to 'Accepted'
                                    break;
                                case 'declined':
                                    if ($appointment['suggested_date']) {
                                        echo 'Rejected with suggested date: ' . htmlspecialchars($appointment['suggested_date']);
                                    } else {
                                        echo 'Rejected';
                                    }
                                    break;
                                case 'pending':
                                    echo 'Pending';
                                    break;
                                default:
                                    if ($appointment['suggested_status'] == 'accepted') {
                                        echo 'New date suggested: ' . htmlspecialchars($appointment['suggested_date']);
                                    } elseif ($appointment['suggested_status'] == 'rejected') {
                                        echo 'Rejected with new date suggestion: ' . htmlspecialchars($appointment['suggested_date']);
                                    } else {
                                        echo 'Unknown status';
                                    }
                                    break;
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($appointment['status'] == 'pending'): ?>
                                <!-- If status is pending, show the buttons -->
                                <form action="" method="post" class="appointment-action-form">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                    <button type="submit" name="action" value="accept">Accept</button>
                                    <button type="button" onclick="showRejectOptions(<?php echo $appointment['id']; ?>)">Reject</button>
                                </form>
                                
                                <!-- Form for suggesting a new date or confirming rejection -->
                                <form id="reject-options-<?php echo $appointment['id']; ?>" action="" method="post" style="display:none;">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                    <label for="suggested_date_<?php echo $appointment['id']; ?>">Suggest a New Date (optional):</label>
                                    <input type="date" name="suggested_date" id="suggested_date_<?php echo $appointment['id']; ?>">
                                    <button type="submit" name="action" value="suggest">Suggest New Date</button>
                                    <button type="submit" name="action" value="reject">Confirm Rejection</button>
                                </form>
                            <?php elseif ($appointment['status'] == 'approved'): ?>
                                <p>Appointment accepted</p>
                            <?php elseif ($appointment['status'] == 'declined'): ?>
                                <p>Appointment rejected</p>
                            <?php elseif ($appointment['suggested_status'] == 'rejected' && $appointment['suggested_date']): ?>
                                <p>Rejected with new date suggestion: <?php echo htmlspecialchars($appointment['suggested_date']); ?></p>
                            <?php elseif ($appointment['suggested_status'] == 'accepted'): ?>
                                <p>New date suggested: <?php echo htmlspecialchars($appointment['suggested_date']); ?></p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No appointments found.</p>
    <?php endif; ?>
</div>

<script>
function showRejectOptions(appointmentId) {
    var form = document.getElementById('reject-options-' + appointmentId);
    form.style.display = 'block';
}
</script>

</body>
</html>
