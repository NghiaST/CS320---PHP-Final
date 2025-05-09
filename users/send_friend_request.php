<?php
require_once '../includes/auth_check.php';
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once '../includes/db.php';

if (isset($_GET['receiver_id'])) {
    $sender_id = $_SESSION["user_id"];
    $receiver_id = $_GET['receiver_id'];

    // Check if a friend request already exists
    $stmt = $pdo->prepare("SELECT * FROM friend_requests WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
    $stmt->execute([$sender_id, $receiver_id, $receiver_id, $sender_id]);
    $existing_request = $stmt->fetch();

    if ($existing_request) {
        header("Location: search.php?error=Friend request already sent or you are already friends.");
        exit;
    }

    // Insert a new friend request
    $stmt = $pdo->prepare("INSERT INTO friend_requests (sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$sender_id, $receiver_id]);

    header("Location: search.php?success=Friend request sent.");
    exit;
}
?>

<a href="../dashboard/index.php">Back to Dashboard</a>
<a href="friend_request.php">View Friend Requests</a>
<a href="chat.php">Chat with Friends</a>
