<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION["user_id"], $_SESSION["session_token"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$session_token = $_SESSION["session_token"];

// Check if the session token matches the one in the database
$stmt = $pdo->prepare("SELECT session_token FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || $user['session_token'] !== $session_token) {
    // Set a logout notice for the user
    $_SESSION["logged_out_notice"] = "You have been logged out because your account was accessed from another device.";

    // Destroy the session and redirect to login
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php");
    exit;
}
?>
