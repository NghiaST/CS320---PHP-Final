<?php
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat with <?php echo htmlspecialchars($friend['username']); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #e0e7ff 0%, #f4f6fb 100%);
            margin: 0;
            padding: 0;
        }
        .chat-container {
            max-width: 520px;
            margin: 48px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(52, 152, 219, 0.08);
            padding: 28px 32px 18px 32px;
        }
        h2 {
            margin-top: 0;
            color: #2563eb;
            font-size: 1.6em;
            letter-spacing: 1px;
        }
        .chat-nav {
            margin-bottom: 18px;
        }
        .chat-nav a {
            color: #2563eb;
            text-decoration: none;
            margin-right: 18px;
            font-size: 15px;
            transition: color 0.2s;
        }
        .chat-nav a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        .messages {
            border: 1px solid #e0e0e0;
            background: #f3f6fa;
            padding: 18px 12px;
            height: 340px;
            overflow-y: auto;
            border-radius: 10px;
            margin-bottom: 14px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .msg-row {
            display: flex;
            flex-direction: row;
        }
        .msg-bubble {
            padding: 12px 18px;
            border-radius: 20px;
            max-width: 75%;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 2px;
            word-break: break-word;
            box-shadow: 0 2px 8px rgba(52,152,219,0.06);
            position: relative;
        }
        .msg-me {
            background: linear-gradient(135deg, #60a5fa 0%, #2563eb 100%);
            color: #fff;
            align-self: flex-end;
            margin-left: auto;
            border-bottom-right-radius: 6px;
        }
        .msg-friend {
            background: #e5e7eb;
            color: #222;
            align-self: flex-start;
            margin-right: auto;
            border-bottom-left-radius: 6px;
        }
        .msg-meta {
            font-size: 11px;
            color: #7b8794;
            margin-top: 6px;
            margin-bottom: 2px;
            text-align: right;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        textarea {
            resize: none;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 10px;
            font-size: 15px;
            font-family: inherit;
            background: #f8fafc;
            transition: border 0.2s;
        }
        textarea:focus {
            border: 1.5px solid #2563eb;
            outline: none;
        }
        button {
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 0;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            box-shadow: 0 2px 8px rgba(52,152,219,0.08);
        }
        button:hover {
            background: linear-gradient(90deg, #1d4ed8 0%, #3b82f6 100%);
        }
    </style>
</head>
<body>
<div class="chat-container">
    <h2>Chat with <?php echo htmlspecialchars($friend['username']); ?></h2>
    <div class="chat-nav">
        <a href="friend_list.php">Back to Friend List</a>
        <a href="friend_request.php">Back to Friend Requests</a>
    </div>

    <div id="messages" class="messages">
        <?php foreach ($messages as $msg) { ?>
            <div class="msg-row">
                <div class="msg-bubble <?php echo $msg['sender_id'] == $user_id ? 'msg-me' : 'msg-friend'; ?>">
                    <span><?php echo htmlspecialchars($msg['message']); ?></span>
                    <div class="msg-meta">
                        <?php echo $msg['sender_id'] == $user_id ? 'You' : htmlspecialchars($friend['username']); ?>
                        &middot;
                        <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <form method="POST" autocomplete="off">
        <textarea name="message" required placeholder="Type your message..." rows="2"></textarea>
        <button type="submit">Send</button>
    </form>
</div>

<script>
let lastMessageId = <?php echo !empty($messages) ? end($messages)['id'] : 0; ?>;
const friendId = <?php echo (int)$friend_id; ?>;
const messagesDiv = document.getElementById('messages');

function scrollMessagesToBottom() {
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function fetchNewMessages() {
    fetch('fetch_messages.php?friend_id=' + friendId + '&last_id=' + lastMessageId)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                data.forEach(msg => {
                    const row = document.createElement('div');
                    row.className = 'msg-row';
                    const bubble = document.createElement('div');
                    bubble.className = 'msg-bubble ' + (msg.sender_id == <?php echo $user_id; ?> ? 'msg-me' : 'msg-friend');
                    bubble.innerHTML = '<span>' + escapeHtml(msg.message) + '</span>' +
                        '<div class="msg-meta">' +
                        (msg.sender_id == <?php echo $user_id; ?> ? 'You' : <?php echo json_encode($friend['username']); ?>) +
                        ' &middot; ' +
                        new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) +
                        '</div>';
                    row.appendChild(bubble);
                    messagesDiv.appendChild(row);
                    lastMessageId = msg.id;
                });
                scrollMessagesToBottom();
            }
        });
}

function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Scroll to bottom on initial page load
window.onload = scrollMessagesToBottom;

setInterval(fetchNewMessages, 2000);
</script>
</body>
</html>
