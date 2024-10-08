<?php
session_start();
require 'database.php';

// Check if user is logged in and has the correct role
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'advocate') {
    header('Location: login.php');
    exit();
}

// Retrieve advocate ID from session
$advocate_id = $_SESSION['id'];

// Initialize variables
$cases = array();
$error = '';

// Handle approval and rejection of cases
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve']) || isset($_POST['reject'])) {
        $case_id = intval($_POST['case_id']);
        $status = isset($_POST['approve']) ? 'approved' : 'declined';

        // Prepare and execute the update SQL statement
        $sql = "UPDATE cases SET status = ? WHERE id = ? AND advocate_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sii", $status, $case_id, $advocate_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Case $status successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to update case status.";
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = 'Failed to prepare SQL statement.';
        }
        header("Location: appointments.php"); // Redirect to refresh the page and show updated status
        exit();
    }
}

try {
    // Prepare SQL statement to fetch cases
    $sql = "SELECT c.id, r.username AS client_name, c.subject, c.description, c.submission_date, c.status
            FROM cases c
            JOIN register r ON c.client_name = r.username
            WHERE c.advocate_id = ?";
    
    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("i", $advocate_id);
        
        // Execute the statement
        $stmt->execute();
        
        // Store result
        $stmt->store_result();
        
        // Bind result variables
        $stmt->bind_result($id, $client_name, $subject, $description, $submission_date, $status);
        
        // Fetch results
        while ($stmt->fetch()) {
            $cases[] = array(
                'id' => $id,
                'client_name' => $client_name,
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
    <title>Cases</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.6/css/unicons.css">
    <link rel="stylesheet" href="css/appointments.css">
</head>
<body>
<?php include 'templates/navbar.php'; ?>


    <main>
        <div class="container">
            <h1>Cases</h1>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); ?></div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (empty($cases)): ?>
                <p>No cases found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client Name</th>
                            <th>Subject</th>
                            <th>Description</th>
                            <th>Submission Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cases as $case): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($case['id']); ?></td>
                                <td><?php echo htmlspecialchars($case['client_name']); ?></td>
                                <td><?php echo htmlspecialchars($case['subject']); ?></td>
                                <td><?php echo htmlspecialchars($case['description']); ?></td>
                                <td><?php echo htmlspecialchars($case['submission_date']); ?></td>
                                <td><?php echo htmlspecialchars($case['status']); ?></td>
                                <td>
                                    <?php if ($case['status'] == 'pending'): ?>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="case_id" value="<?php echo htmlspecialchars($case['id']); ?>">
                                            <button type="submit" name="approve">Approve</button>
                                            <button type="submit" name="reject">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span><?php echo htmlspecialchars($case['status']); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
