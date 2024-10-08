<?php
require 'database.php';

if (isset($_GET['advocate_id']) && isset($_GET['case_id'])) {
    $advocate_id = $_GET['advocate_id'];
    $case_id = $_GET['case_id'];

    // Retrieve advocate and case details if needed for the chat interface
    // ...

} else {
    echo "Advocate ID or Case ID not specified.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Interface</title>
    <link rel="stylesheet" href="css/chat_interface.css">
</head>
<body>
    <div class="navbar">
        <a class="navbar-brand" href="index.html">Advocate Connect</a>
    </div>
    <div class="chat-interface">
        <h2>Chat with Advocate</h2>
        <!-- Implement chat UI here -->
    </div>
</body>
</html>
