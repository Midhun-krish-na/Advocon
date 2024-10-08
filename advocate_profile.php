<?php
session_start();
require_once "database.php";

if (!isset($_GET['id'])) {
    echo "Advocate ID not specified.";
    exit;
}

$advocate_id = intval($_GET['id']);
$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null; // Assuming user is logged in and their ID is stored in session

// Fetch advocate details
$sql = "SELECT id, name, phone, address, rating, cases_won, fees, profile_picture, specialized_field, certifications FROM advocates WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $advocate_id);
$stmt->execute();
$result = $stmt->get_result();
$advocate = $result->fetch_assoc();
$stmt->close();

// Fetch user's existing rating if logged in
$user_rating = null;
if ($user_id) {
    $sql_rating = "SELECT rating FROM reviews WHERE advocate_id = ? AND id = ?";
    $stmt_rating = $conn->prepare($sql_rating);
    $stmt_rating->bind_param("ii", $advocate_id, $user_id);
    $stmt_rating->execute();
    $result_rating = $stmt_rating->get_result();
    $user_rating = $result_rating->fetch_assoc();
    $stmt_rating->close();
}

// Fetch advocate reviews
$sql_reviews = "SELECT user_name, rating, review FROM reviews WHERE advocate_id = ?";
$stmt_reviews = $conn->prepare($sql_reviews);
$stmt_reviews->bind_param("i", $advocate_id);
$stmt_reviews->execute();
$result_reviews = $stmt_reviews->get_result();

$reviews = [];
while ($row = $result_reviews->fetch_assoc()) {
    $reviews[] = $row;
}
$stmt_reviews->close();

// Handle rating submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rating']) && $user_id) {
    $new_rating = intval($_POST['rating']);

    if ($user_rating) {
        // Update existing rating
        $sql_update_rating = "UPDATE reviews SET rating = ? WHERE advocate_id = ? AND id = ?";
        $stmt_update = $conn->prepare($sql_update_rating);
        $stmt_update->bind_param("iii", $new_rating, $advocate_id, $user_id);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        // Insert new rating
        $sql_insert_rating = "INSERT INTO reviews (advocate_id, id, rating) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert_rating);
        $stmt_insert->bind_param("iii", $advocate_id, $user_id, $new_rating);
        $stmt_insert->execute();
        $stmt_insert->close();
    }

    // Recalculate and update advocate's overall rating
    $sql_avg_rating = "SELECT AVG(rating) as avg_rating FROM reviews WHERE advocate_id = ?";
    $stmt_avg = $conn->prepare($sql_avg_rating);
    $stmt_avg->bind_param("i", $advocate_id);
    $stmt_avg->execute();
    $result_avg = $stmt_avg->get_result();
    $avg_rating = $result_avg->fetch_assoc()['avg_rating'];
    $stmt_avg->close();

    $sql_update_advocate = "UPDATE advocates SET rating = ? WHERE id = ?";
    $stmt_update_advocate = $conn->prepare($sql_update_advocate);
    $stmt_update_advocate->bind_param("di", $avg_rating, $advocate_id);
    $stmt_update_advocate->execute();
    $stmt_update_advocate->close();

    $_SESSION['success_message'] = "Your rating has been submitted.";
    header("Location: advocate_profile.php?id=" . $advocate_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advocate Profile</title>
    <link rel="stylesheet" href="css/advocate_profile.css">
</head>
<body>
<?php include 'templates/navbar.php'; ?>

    <div class="profile-container">
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
            <div class="profile-box">
                <?php if (!empty($advocate['profile_picture'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($advocate['profile_picture']); ?>" alt="<?php echo htmlspecialchars($advocate['name']); ?>">
                <?php endif; ?>
                <h2><?php echo htmlspecialchars($advocate['name']); ?></h2>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($advocate['phone']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($advocate['address']); ?></p>
                <p><strong>Rating:</strong> <?php echo htmlspecialchars($advocate['rating']); ?> / 5</p>
                <p><strong>Cases Won:</strong> <?php echo htmlspecialchars($advocate['cases_won']); ?></p>
                <p><strong>Min. Fees:</strong> <?php echo htmlspecialchars($advocate['fees']); ?></p>
                <p><strong>Specialized Field:</strong> <?php echo htmlspecialchars($advocate['specialized_field']); ?></p>

                <!-- Star Rating System -->
                <div class="rating">
                    <form action="" method="post">
                        <label for="rating">Your Rating:</label>
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php if ($user_rating && $user_rating['rating'] == $i) echo 'checked'; ?>>
                                <label for="star<?php echo $i; ?>">&#9733;</label>
                            <?php endfor; ?>
                        </div>
                        <button type="submit">Submit Rating</button>
                    </form>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button onclick="window.location.href='submit_case.php?id=<?php echo $advocate['id']; ?>'">Case Details</button>
                    <button onclick="window.location.href='request_appointment.php?id=<?php echo $advocate['id']; ?>'">Appointment</button>
                </div>
            </div>
        <?php else: ?>
            <p>Advocate not found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
