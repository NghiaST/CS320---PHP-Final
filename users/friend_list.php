<?php
require_once '../includes/auth_check.php';
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../includes/db.php';

$user_id = $_SESSION["user_id"];

// Fetch the friend list
$stmt = $pdo->prepare("SELECT u.id, u.username FROM users u JOIN friend_requests fr ON (u.id = fr.sender_id OR u.id = fr.receiver_id) WHERE fr.status = 'accepted' AND u.id != ? AND (fr.sender_id = ? OR fr.receiver_id = ?)");
$stmt->execute([$user_id, $user_id, $user_id]);
$friends = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Friends</title>
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f4f6fb 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .friends-container {
            max-width: 500px;
            margin: 48px auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(52, 152, 219, 0.10);
            padding: 28px 32px 18px 32px;
        }
        h2 {
            color: #2563eb;
            margin-top: 0;
            margin-bottom: 16px;
        }
        ul {
            padding-left: 18px;
        }
        li {
            margin-bottom: 8px;
        }
        a {
            color: #2563eb;
            text-decoration: none;
            margin-right: 10px;
            transition: color 0.2s;
        }
        a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        .empty-msg {
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="friends-container">
    <h2>Your Friends</h2>
    <a href="../dashboard/index.php">Back to Dashboard</a>
    <?php if ($friends) { ?>
        <ul>
            <?php foreach ($friends as $friend) { ?>
                <li>
                    <?php echo htmlspecialchars($friend['username']); ?>
                    <a href="chat.php?friend_id=<?php echo $friend['id']; ?>">Chat</a>
                </li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p class="empty-msg">You have no friends yet.</p>
    <?php } ?>
</div>
</body>
</html>
