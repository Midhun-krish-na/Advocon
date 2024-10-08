<?php
session_start();
require 'database.php';

// Check if admin is logged in and has the correct role
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Check if user_name is set, if not set a default value
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : "Admin";

$sql = "SELECT id, name, email, phone, address, rating, cases_won, fees, verified FROM advocates";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!--iconscout-->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v2.1.6/css/unicons.css">
    <!--css-->
    <link rel="stylesheet" href="css/ad_das.css">
</head>
<body>
    <nav>
        <div class="container">
            <div class="search-bar">
                <i class="uil uil-search"></i>
                <input type="search" placeholder="Search">
            </div>
            <h2 class="log">Advocon.</h2>
            <div class="profile-photo">
                <a href="profiles/profile_ad.php"><img src="./assets/images/admin_profile.jpg" alt="Profile"></a>
            </div>
        </div>
    </nav>

    <main>
        <div class="container">
            <!-- Left Sidebar -->
            <div class="left">
                <div class="profile">
                    <div class="profile-photo">
                        <a href="profiles/profile_ad.php"><img src="./assets/images/admin_profile.jpg" alt="Profile"></a>
                    </div>
                    <div class="handle">
                        <h4><?php echo htmlspecialchars($user_name); ?></h4>
                        <p class="text-muted">@admin</p>
                    </div>
                </div>
                <div class="sidebar">
                    <a class="menu-item active" href="admin_dashboard.php">
                        <span><i class="uil uil-home"></i></span><h3>Home</h3>
                    </a>
                    <a class="menu-item" href="admin_manage_advocates.php">
                        <span><i class="uil uil-user-check"></i></span><h3>Verify Advocates</h3>
                    </a>
                </div>
            </div>
            <!-- Right Section -->
            <div class="right">
                <h2>Verify Advocates</h2>
                <?php if ($result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Rating</th>
                                <th>Cases Won</th>
                                <th>Fees</th>
                                <th>Verified</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                                    <td><?php echo htmlspecialchars($row['rating']); ?></td>
                                    <td><?php echo htmlspecialchars($row['cases_won']); ?></td>
                                    <td><?php echo htmlspecialchars($row['fees']); ?></td>
                                    <td><?php echo $row['verified'] ? 'Yes' : 'No'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No advocates found.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="js/dashboard.js"></script>
</body>
</html>
