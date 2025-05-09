<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    exit;
}

require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$friend_id = isset($_GET['friend_id']) ? intval($_GET['friend_id']) : 0;
$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

if (!$friend_id) {
    echo json_encode([]);
    exit;
}

// Only fetch messages between the two users, newer than last_id
$stmt = $pdo->prepare("SELECT id, sender_id, receiver_id, message, created_at FROM messages WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND id > ? ORDER BY id ASC");
$stmt->execute([$user_id, $friend_id, $friend_id, $user_id, $last_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($messages);
?>
