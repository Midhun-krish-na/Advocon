<?php
require 'database.php';
session_start();

// Check if the user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all advocates
$sql = "SELECT id, name, verified FROM advocates";
$result = $conn->query($sql);

$advocates = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $advocates[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Advocates</title>
    <link rel="stylesheet" href="css/admin_manage_advocates.css">
</head>
<body>
<?php include 'templates/navbar.php'; ?>

    <div class="manage-container">
        <h2>Manage Advocates</h2>
        <?php if (count($advocates) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($advocates as $advocate): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($advocate['name']); ?></td>
                            <td><?php echo $advocate['verified'] ? 'Verified' : 'Unverified'; ?></td>
                            <td>
                                <?php if ($advocate['verified']): ?>
                                    <a href="verify_revoke.php?id=<?php echo $advocate['id']; ?>&action=revoke" class="btn revoke">Revoke Access</a>
                                <?php else: ?>
                                    <a href="verify_revoke.php?id=<?php echo $advocate['id']; ?>&action=verify" class="btn verify">Verify Advocate</a>
                                <?php endif; ?>
                                <a href="advocate_detail.php?id=<?php echo $advocate['id']; ?>" class="btn detail">Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No advocates found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
