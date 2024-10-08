<?php
require 'database.php';
session_start();

// Check if the user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $advocate_id = intval($_GET['id']);

    // Fetch advocate details
    $sql = "SELECT id, name, phone, address, rating, cases_won, fees, profile_picture, specialized_field, certifications, verified FROM advocates WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $advocate_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $advocate = $result->fetch_assoc();
    $stmt->close();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['approve'])) {
            $update_sql = "UPDATE advocates SET verified = 1 WHERE id = ?";
        } elseif (isset($_POST['revoke'])) {
            $update_sql = "UPDATE advocates SET verified = 0 WHERE id = ?";
        }
        if (isset($update_sql)) {
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("i", $advocate_id);
            if ($stmt->execute()) {
                header("Location: advocate_detail.php?id=$advocate_id");
                exit();
            } else {
                echo "Failed to update status.";
            }
            $stmt->close();
        }
    }
} else {
    echo "Advocate ID not specified.";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advocate Detail</title>
    <link rel="stylesheet" href="css/advocate_detail.css">
</head>
<body>
<?php include 'templates/navbar.php'; ?>
    <div class="detail-container">
        <h2>Advocate Details</h2>
        <?php if ($advocate): ?>
            <div class="detail-box">
                <?php if (!empty($advocate['profile_picture'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($advocate['profile_picture']); ?>" alt="<?php echo htmlspecialchars($advocate['name']); ?>">
                <?php endif; ?>
                <h2><?php echo htmlspecialchars($advocate['name']); ?></h2>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($advocate['phone']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($advocate['address']); ?></p>
                <p><strong>Rating:</strong> <?php echo htmlspecialchars($advocate['rating']); ?> / 5</p>
                <p><strong>Cases Won:</strong> <?php echo htmlspecialchars($advocate['cases_won']); ?></p>
                <p><strong>Fees:</strong> <?php echo htmlspecialchars($advocate['fees']); ?></p>
                <p><strong>Specialized Field:</strong> <?php echo htmlspecialchars($advocate['specialized_field']); ?></p>

                <!-- Certifications -->
                <div class="certifications">
                    <h3>Certifications:</h3>
                    <?php if (!empty($advocate['certifications'])): ?>
                        <?php 
                        $certificates = explode(',', $advocate['certifications']);
                        foreach ($certificates as $certificate): 
                            $fileType = pathinfo($certificate, PATHINFO_EXTENSION);
                        ?>
                            <div class="certification">
                                <?php if (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($certificate); ?>" alt="Certification" class="cert-img">
                                <?php elseif (in_array($fileType, ['pdf'])): ?>
                                    <a href="uploads/<?php echo htmlspecialchars($certificate); ?>" target="_blank">View PDF Certification</a>
                                <?php else: ?>
                                    <a href="uploads/<?php echo htmlspecialchars($certificate); ?>" target="_blank">View Certification</a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No certifications available.</p>
                    <?php endif; ?>
                </div>

                <!-- Approve / Revoke Buttons -->
                <form method="post">
                    <?php if (!$advocate['verified']): ?>
                        <button type="submit" name="approve">Approve</button>
                    <?php else: ?>
                        <button type="submit" name="revoke">Revoke</button>
                    <?php endif; ?>
                </form>
            </div>
        <?php else: ?>
            <p>Advocate not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
