<?php
require 'database.php';

// Initialize filters with validation
$filter_rating = isset($_GET['rating']) ? filter_var($_GET['rating'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]) : 0;
$filter_field = isset($_GET['field']) ? trim($_GET['field']) : '';
$filter_cases_won = isset($_GET['cases_won']) ? filter_var($_GET['cases_won'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) : 0;

// Build the SQL query with filters
$sql = "SELECT id, name, phone, address, rating, cases_won, fees, profile_picture, specialized_field FROM advocates WHERE verified = 1";
$params = [];
$types = "";

if ($filter_rating > 0) {
    $sql .= " AND rating >= ?";
    $params[] = $filter_rating;
    $types .= "i";
}

if (!empty($filter_field)) {
    $sql .= " AND specialized_field = ?";
    $params[] = $filter_field;
    $types .= "s";
}

if ($filter_cases_won > 0) {
    $sql .= " AND cases_won >= ?";
    $params[] = $filter_cases_won;
    $types .= "i";
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$advocates = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $advocates[] = $row;
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advocate Profiles</title>
    <link rel="stylesheet" href="css/view_advocates.css">
</head>
<body>
<?php include 'templates/navbar.php'; ?>

<!-- Filter Section -->
<div class="filter-container">
    <form method="GET" action="view_advocates.php">
        <label for="rating">Min. Rating:</label>
        <input type="number" id="rating" name="rating" min="0" max="5" value="<?php echo htmlspecialchars($filter_rating); ?>">

        <label for="specialized_field">Specialized Field:</label>
        <select class="dropdown" name="specialized_field" id="specialized_field" required>
            <option value="" disabled selected></option>
            <option value="criminal" <?php if ($filter_field == 'criminal') echo 'selected'; ?>>Criminal</option>
            <option value="business" <?php if ($filter_field == 'business') echo 'selected'; ?>>Business</option>
            <option value="environmental" <?php if ($filter_field == 'environmental') echo 'selected'; ?>>Environmental</option>
            <option value="civil" <?php if ($filter_field == 'civil') echo 'selected'; ?>>Civil</option>
            <option value="constitutional" <?php if ($filter_field == 'constitutional') echo 'selected'; ?>>Constitutional</option>
            <option value="family" <?php if ($filter_field == 'family') echo 'selected'; ?>>Family</option>
            <option value="intellectual_property" <?php if ($filter_field == 'intellectual_property') echo 'selected'; ?>>Intellectual Property</option>
        </select>
        
        <label for="cases_won">Min. Cases Won:</label>
        <input type="number" id="cases_won" name="cases_won" min="0" value="<?php echo htmlspecialchars($filter_cases_won); ?>">

        <button type="submit">Apply Filters</button>
    </form>
</div>

<div class="profile-container">
    <?php if (count($advocates) > 0): ?>
        <?php foreach ($advocates as $advocate): ?>
            <div class="profile-box">
                <?php if (!empty($advocate['profile_picture'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($advocate['profile_picture']); ?>" alt="<?php echo htmlspecialchars($advocate['name']); ?>">
                <?php endif; ?>
                <h2><?php echo htmlspecialchars($advocate['name']); ?></h2>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($advocate['phone']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($advocate['address']); ?></p>
                <p><strong>Rating:</strong> <?php echo htmlspecialchars($advocate['rating']); ?> / 5</p>
                <p><strong>Cases Won:</strong> <?php echo htmlspecialchars($advocate['cases_won']); ?></p>
                <p><strong>Min-Fees:</strong> ₹<?php echo htmlspecialchars($advocate['fees']); ?></p>
                <p><strong>Specialized Field:</strong> <?php echo htmlspecialchars($advocate['specialized_field']); ?></p>

                <a href="advocate_profile.php?id=<?php echo $advocate['id']; ?>">View Profile</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No advocates found.</p>
    <?php endif; ?>
</div>
</body>
</html>
