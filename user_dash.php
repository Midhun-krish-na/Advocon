<?php 
session_start();
require 'database.php';

// Initialize variables
$search_query = '';
$advocates = [];

// Check if user is logged in and has the correct role
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

// Set user ID and username with default values
$user_id = $_SESSION['id'] ?? null;
$username = $_SESSION['username'] ?? 'User';

// Handle search
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
    $stmt_search = $conn->prepare("SELECT * FROM advocates WHERE name LIKE ? AND verified = 1");
    $search_term = '%' . $search_query . '%';
    $stmt_search->bind_param('s', $search_term);
    $stmt_search->execute();
    $advocates_result = $stmt_search->get_result();
    $advocates = $advocates_result->fetch_all(MYSQLI_ASSOC);
    $stmt_search->close();
} else {
    // Fetch all approved advocates
    $stmt = $conn->prepare("SELECT * FROM advocates WHERE verified = 1");
    $stmt->execute();
    $advocates_result = $stmt->get_result();
    $advocates = $advocates_result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.6/css/unicons.css">
    <link rel="stylesheet" href="css/user_dash.css">
</head>
<body>
    <nav>
        <div class="container">
            <h2 class="log">Advocon</h2>
            <div class="search-bar">
                <form action="user_dash.php" method="get">
                    <i class="uil uil-search"></i>
                    <input type="search" name="search" placeholder="Search advocate" value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="case">
                <label class="btn btn-primary" for="case">Cases</label>
                <div class="profile-photo">
                    <a href="profiles/profile_user.php"><img src="./assets/images/profile-2.jpg" alt="Profile"></a>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">
            <!-- Left Sidebar -->
            <div class="left">
                <div class="profile-photo">
                    <a href="profiles/profile_user.php"><img src="./assets/images/profile-2.jpg" alt="Profile"></a>
                </div>
                <div class="handle">
                    <h4><?php echo htmlspecialchars($username); ?></h4>
                    <p class="text-muted">@user</p>
                </div>

                <!-- Sidebar -->
                <div class="sidebar">
                    <a class="menu-item active" href="user_dash.php">
                        <span><i class="uil uil-home"></i></span><h3>Home</h3>
                    </a>
                    <a class="menu-item" href="view_my_appointments.php">
                        <span><i class="uil uil-people"></i></span><h3>Appointments</h3>
                    </a>
                    <a class="menu-item" href="appointment_updation.php">
                        <span><i class="uil uil-calendar-alt"></i></span><h3>Case</h3>
                    </a>
                    <a class="menu-item" href="view_advocates.php">
                        <span><i class="uil uil-people"></i></span><h3>Advocates</h3>
                    </a>
                </div>
            </div>

            <!-- Right Section for Verified Advocates -->
            <div class="right">
                <div class="profile-container">
                    <?php if (count($advocates) > 0): ?>
                        <?php foreach ($advocates as $advocate): ?>
                            <div class="profile-box">
                                <?php if (!empty($advocate['profile_picture'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($advocate['profile_picture']); ?>" alt="<?php echo htmlspecialchars($advocate['name']); ?>">
                                <?php endif; ?>
                                <p><h2><?php echo htmlspecialchars($advocate['name']); ?></h2></p>
                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($advocate['phone']); ?></p>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($advocate['address']); ?></p>
                                <p><strong>Rating:</strong> <?php echo htmlspecialchars($advocate['rating']); ?> / 5</p>
                                <p><strong>Cases Won:</strong> <?php echo htmlspecialchars($advocate['cases_won']); ?></p>
                                <p><strong>Fees:</strong> $<?php echo htmlspecialchars($advocate['fees']); ?></p>
                                <p><strong>Specialized Field:</strong> <?php echo htmlspecialchars($advocate['specialized_field']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No approved advocates found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="js/dashboard.js"></script>
</body>
</html>
