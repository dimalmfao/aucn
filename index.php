<?php
session_start();
require 'config/config.yml';

// Database connection
$mysqli = new mysqli($db['host'], $db['username'], $db['password'], $db['database']);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Function to sanitize user input
function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Handle new message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $message = sanitize($_POST['message']);
    $user_id = $_SESSION['user_id'];

    if (!empty($message)) {
        $stmt = $mysqli->prepare("INSERT INTO messages (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $message);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch chat messages
$result = $mysqli->query("SELECT messages.message, users.username FROM messages JOIN users ON messages.user_id = users.id ORDER BY created_at DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Application</title>
</head>
<body>
    <h1>Chat Application</h1>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form method="POST">
            <textarea name="message" required></textarea>
            <button type="submit">Send</button>
        </form>
    <?php else: ?>
        <p>Please <a href="login.php">login</a> to chat.</p>
    <?php endif; ?>

    <div id="chat-messages">
        <?php while ($row = $result->fetch_assoc()): ?>
            <p><strong><?php echo sanitize($row['username']); ?>:</strong> <?php echo sanitize($row['message']); ?></p>
        <?php endwhile; ?>
    </div>

    <?php
    $mysqli->close();
    ?>
</body>
</html>