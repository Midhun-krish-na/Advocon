<?php
require 'database.php'; // Database connection

$advocate_id = isset($_GET['id']) ? intval($_GET['id']) : 0; // Get advocate_id from URL

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_name = trim($_POST['client_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $description = trim($_POST['description']);
    $errors = array();

    // Validate form data
    if (empty($client_name) || empty($email) || empty($phone) || empty($subject) || empty($description)) {
        $errors[] = "All fields are required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email is not valid.";
    }
    if (!validate_phone($phone)) {
        $errors[] = "Phone number is not valid.";
    }

    // If there are no errors, insert the case into the database
    if (count($errors) === 0) {
        $sql = "INSERT INTO cases (advocate_id, client_name, email, phone, subject, description, submission_date) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("isssss", $advocate_id, $client_name, $email, $phone, $subject, $description);
            if ($stmt->execute()) {
                echo "<script>alert('Your case has been submitted successfully. The advocate will review it shortly.');</script>";
            } else {
                echo "Error: " . $conn->error;
            }
            $stmt->close();
        }
    } else {
        $error_message = implode(" | ", $errors);
        echo "<script>alert('$error_message');</script>";
    }
}

$conn->close();

function validate_phone($phone) {
    return preg_match("/^\d{10,15}$/", $phone);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Case</title>
    <link rel="stylesheet" href="css/submit_case.css">
</head>
<body>
<?php include 'templates/navbar.php'; ?>

    <div class="form-container">
        <h2>Submit Your Case</h2>
        <form action="submit_case.php?id=<?php echo $advocate_id; ?>" method="post">
            <div class="input-group">
                <label for="client_name">Name</label>
                <input type="text" name="client_name" id="client_name" required>
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="input-group">
                <label for="phone">Phone</label>
                <input type="tel" name="phone" id="phone" required>
            </div>
            <!-- Advocate ID is now hidden and pre-filled -->
            <input type="hidden" name="advocate_id" value="<?php echo $advocate_id; ?>">
            <div class="input-group">
                <label for="subject">Case Subject</label>
                <input type="text" name="subject" id="subject" required>
            </div>
            <div class="input-group">
                <label for="description">Case Description</label>
                <textarea name="description" id="description" required></textarea>
            </div>
            <button type="submit">Submit Case</button>
        </form>
    </div>
</body>
</html>
