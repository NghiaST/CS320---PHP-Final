<?php
require_once '../includes/auth_check.php';
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Friend Requests</title>
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f4f6fb 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .requests-container {
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
            margin-bottom: 12px;
        }
        h3 {
            color: #333;
            margin-bottom: 8px;
        }
        ul {
            padding-left: 18px;
        }
        li {
            margin-bottom: 8px;
        }
        a, .btn {
            color: #2563eb;
            text-decoration: none;
            margin-right: 10px;
            transition: color 0.2s;
            font-size: 15px;
        }
        a:hover, .btn:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        .btn-accept {
            color: #fff;
            background: #22c55e;
            border: none;
            border-radius: 6px;
            padding: 4px 12px;
            margin-right: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
        }
        .btn-accept:hover {
            background: #16a34a;
        }
        .btn-reject {
            color: #fff;
            background: #ef4444;
            border: none;
            border-radius: 6px;
            padding: 4px 12px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
        }
        .btn-reject:hover {
            background: #b91c1c;
        }
        .empty-msg {
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="requests-container">
    <h2>Friend Requests</h2>

    <h3>Incoming Requests</h3>
    <?php if ($incoming_requests) { ?>
        <ul>
            <?php foreach ($incoming_requests as $request) { ?>
                <li>
                    <?php echo htmlspecialchars($request['username']); ?>
                    <a class="btn-accept" href="?action=accept&request_id=<?php echo $request['id']; ?>">Accept</a>
                    <a class="btn-reject" href="?action=reject&request_id=<?php echo $request['id']; ?>">Reject</a>
                </li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p class="empty-msg">No incoming requests.</p>
    <?php } ?>

    <h3>Outgoing Requests</h3>
    <?php if ($outgoing_requests) { ?>
        <ul>
            <?php foreach ($outgoing_requests as $request) { ?>
                <li><?php echo htmlspecialchars($request['username']); ?></li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p class="empty-msg">No outgoing requests.</p>
    <?php } ?>

    <a href="../dashboard/index.php">Back to Dashboard</a>
</div>
</body>
</html>
