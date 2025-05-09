<?php
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f4f6fb 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            max-width: 600px;
            margin: 48px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(52, 152, 219, 0.10);
            padding: 32px 36px 24px 36px;
        }
        h2 {
            color: #2563eb;
            margin-top: 0;
            margin-bottom: 10px;
        }
        h3 {
            color: #333;
            margin-bottom: 8px;
        }
        ul {
            padding-left: 18px;
        }
        li {
            margin-bottom: 7px;
        }
        a {
            color: #2563eb;
            text-decoration: none;
            margin-right: 12px;
            transition: color 0.2s;
        }
        a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        .nav-links {
            margin-top: 18px;
            margin-bottom: 10px;
        }
        .empty-msg {
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
    <p>This is your dashboard.</p>

    <h3>Your Friends</h3>
    <?php if ($friends) { ?>
        <ul>
            <?php foreach ($friends as $friend) { ?>
                <li>
                    <?php echo htmlspecialchars($friend['username']); ?>
                    <a href="../users/chat.php?friend_id=<?php echo $friend['id']; ?>">Chat</a>
                </li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p class="empty-msg">You have no friends yet.</p>
    <?php } ?>

    <div class="nav-links">
        <a href="../users/friend_list.php">See Friend List</a>
        <a href="../users/search.php">Search Users</a>
        <a href="../users/friend_request.php">View Friend Requests</a>
        <a href="../auth/logout.php">Logout</a>
    </div>
</div>
</body>
</html>
