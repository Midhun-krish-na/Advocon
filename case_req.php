<?php
session_start();
require 'database.php';

// Check if advocate is logged in and has the correct role
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'advocate') {
    header('Location: login.php');
    exit();
}

$advocate_id = $_SESSION['id'];

// Fetch all cases for this advocate
$sql = "SELECT id, client_name, email, phone, subject, description, submission_date 
        FROM cases WHERE advocate_id = ? ORDER BY submission_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $advocate_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Case Requests</title>
    <link rel="stylesheet" href="css/advo.css"> <!-- Link to your CSS file -->
    <link rel="stylesheet" href="css/case_req.css"> <!-- or your specific css file -->

</head>
<body>
    <div class="container">
        <h2>Case Requests</h2>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Subject</th>
                        <th>Description</th>
                        <th>Submission Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo htmlspecialchars($row['subject']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars($row['submission_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No case requests found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
