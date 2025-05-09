<?php
require_once '../includes/auth_check.php';
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once '../includes/db.php';

if (isset($_GET['request_id'])) {
    $request_id = $_GET['request_id'];
    $user_id = $_SESSION["user_id"];

    // Accept the friend request
    $stmt = $pdo->prepare("UPDATE friend_requests SET status = 'accepted' WHERE id = ? AND receiver_id = ?");
    $stmt->execute([$request_id, $user_id]);

    header("Location: dashboard/index.php");
    exit;
}
?>
