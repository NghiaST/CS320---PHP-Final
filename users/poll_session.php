<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION["user_id"], $_SESSION["session_token"])) {
    echo json_encode(["status" => "logged_out"]);
    exit;
}

$user_id = $_SESSION["user_id"];
$session_token = $_SESSION["session_token"];

// Check if the session token matches the one in the database
$stmt = $pdo->prepare("SELECT session_token FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || $user['session_token'] !== $session_token) {
    echo json_encode(["status" => "logged_out"]);
} else {
    echo json_encode(["status" => "active"]);
}
?>
