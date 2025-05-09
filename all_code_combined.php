// Database connection
<?php
// filepath: c:\xampp\htdocs\includes\db.php
$host = 'localhost';
$dbname = 'cms_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}
?>

// Login functionality
<?php
// filepath: c:\xampp\htdocs\auth\login.php
session_start();
require_once '../includes/db.php';

if (isset($_SESSION["user_id"])) {
    header("Location: ../dashboard/index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Generate a unique session token
        $session_token = bin2hex(random_bytes(32));

        // Update the session token in the database
        $stmt = $pdo->prepare("UPDATE users SET session_token = ? WHERE id = ?");
        $stmt->execute([$session_token, $user['id']]);

        // Store session details
        $_SESSION["user_id"] = $user['id'];
        $_SESSION["username"] = $user['username'];
        $_SESSION["session_token"] = $session_token;

        header("Location: ../dashboard/index.php");
        exit;
    } else {
        $error = "Invalid credentials!";
    }
}
?>

// Logout functionality
<?php
// filepath: c:\xampp\htdocs\auth\logout.php
session_start();
require_once '../includes/db.php';

if (isset($_SESSION["user_id"])) {
    $stmt = $pdo->prepare("UPDATE users SET session_token = NULL WHERE id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
}

session_unset();
session_destroy();
header("Location: login.php");
exit;
?>

// Dashboard
<?php
// filepath: c:\xampp\htdocs\dashboard\index.php
require_once '../includes/auth_check.php';
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once '../includes/db.php';
$user_id = $_SESSION["user_id"];

// Fetch the friend list
$stmt = $pdo->prepare("SELECT u.id, u.username FROM users u JOIN friend_requests fr ON (u.id = fr.sender_id OR u.id = fr.receiver_id) WHERE fr.status = 'accepted' AND u.id != ? AND (fr.sender_id = ? OR fr.receiver_id = ?)");
$stmt->execute([$user_id, $user_id, $user_id]);
$friends = $stmt->fetchAll();
?>

// Friend Requests
<?php
// filepath: c:\xampp\htdocs\users\friend_request.php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once '../includes/db.php';

$user_id = $_SESSION["user_id"];

// Handle accept or reject actions
if (isset($_GET['action'], $_GET['request_id']) && in_array($_GET['action'], ['accept', 'reject'])) {
    $action = $_GET['action'];
    $request_id = $_GET['request_id'];

    if ($action === 'accept') {
        $stmt = $pdo->prepare("UPDATE friend_requests SET status = 'accepted' WHERE id = ? AND receiver_id = ?");
        $stmt->execute([$request_id, $user_id]);
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE friend_requests SET status = 'rejected' WHERE id = ? AND receiver_id = ?");
        $stmt->execute([$request_id, $user_id]);
    }

    header("Location: friend_request.php");
    exit;
}

// Fetch incoming friend requests
$stmt = $pdo->prepare("SELECT fr.id, u.username FROM friend_requests fr JOIN users u ON fr.sender_id = u.id WHERE fr.receiver_id = ? AND fr.status = 'pending'");
$stmt->execute([$user_id]);
$incoming_requests = $stmt->fetchAll();

// Fetch outgoing friend requests
$stmt = $pdo->prepare("SELECT fr.id, u.username FROM friend_requests fr JOIN users u ON fr.receiver_id = u.id WHERE fr.sender_id = ? AND fr.status = 'pending'");
$stmt->execute([$user_id]);
$outgoing_requests = $stmt->fetchAll();
?>

// Chat functionality
<?php
// filepath: c:\xampp\htdocs\users\chat.php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once '../includes/db.php';

if (isset($_GET['friend_id'])) {
    $friend_id = $_GET['friend_id'];
    $user_id = $_SESSION['user_id'];

    // Check if the users are friends
    $stmt = $pdo->prepare("SELECT * FROM friend_requests WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)) AND status = 'accepted'");
    $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
    $friendship = $stmt->fetch();

    if (!$friendship) {
        echo "You are not friends with this user.";
        exit;
    }

    // Get friend's username
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$friend_id]);
    $friend = $stmt->fetch();

    // Get all messages between the user and their friend
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
    $stmt->execute([$user_id, $friend_id, $friend_id, $user_id]);
    $messages = $stmt->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $message = $_POST['message'];

        // Insert the message into the database
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $friend_id, $message]);

        header("Location: chat.php?friend_id=$friend_id");
        exit;
    }
} else {
    echo "No friend selected.";
    exit;
}
?>

// Database schema
```sql
-- filepath: c:\xampp\htdocs\create.sql
CREATE DATABASE cms_db;

USE cms_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    session_token VARCHAR(255) DEFAULT NULL
);

CREATE TABLE friend_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);
```
