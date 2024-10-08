<?php
session_start();
require 'database.php';

// Check if user is logged in and has the correct role
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

// Retrieve user ID from session
$user_id = $_SESSION['id'];

// Initialize variables
$cases = array();
$error = '';

try {
    // Prepare SQL statement to fetch cases for the logged-in user
    $sql = "SELECT c.id, adv.name AS advocate_name, c.subject, c.description, c.submission_date, c.status 
            FROM cases c
            JOIN advocates adv ON c.advocate_id = adv.id
            WHERE c.client_name = (SELECT username FROM register WHERE id = ?)"; // Assuming 'client_name' matches 'username' in the register table
    
    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("i", $user_id);
        
        // Execute the statement
        $stmt->execute();
        
        // Store result
        $stmt->store_result();
        
        // Bind result variables
        $stmt->bind_result($id, $advocate_name, $subject, $description, $submission_date, $status);
        
        // Fetch results
        while ($stmt->fetch()) {
            $cases[] = array(
                'id' => $id,
                'advocate_name' => $advocate_name,
                'subject' => $subject,
                'description' => $description,
                'submission_date' => $submission_date,
                'status' => $status
            );
        }
        
        // Close the statement
        $stmt->close();
    } else {
        $error = 'Failed to prepare SQL statement.';
    }
} catch (Exception $e) {
    $error = 'An error occurred: ' . $e->getMessage();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cases</title>
    <link rel="stylesheet" href="css/appointments_user.css">
</head>
<body>
<?php include 'templates/navbar.php'; ?>


    <main>
        <div class="container">
            <h1>Your Cases</h1>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (empty($cases)): ?>
                <p>You have not submitted any cases yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Advocate Name</th>
                            <th>Subject</th>
                            <th>Description</th>
                            <th>Submission Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cases as $case): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($case['id']); ?></td>
                                <td><?php echo htmlspecialchars($case['advocate_name']); ?></td>
                                <td><?php echo htmlspecialchars($case['subject']); ?></td>
                                <td><?php echo htmlspecialchars($case['description']); ?></td>
                                <td><?php echo htmlspecialchars($case['submission_date']); ?></td>
                                <td><?php echo htmlspecialchars($case['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
