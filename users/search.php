<?php
require_once '../includes/auth_check.php';
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $searchQuery = $_POST['search'];
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE username LIKE ?");
    $stmt->execute(['%' . $searchQuery . '%']);
    $users = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Users</title>
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f4f6fb 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .search-container {
            max-width: 420px;
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
        form {
            display: flex;
            gap: 8px;
            margin-bottom: 18px;
        }
        input[type="text"] {
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 10px;
            font-size: 15px;
            background: #f8fafc;
            flex: 1;
            transition: border 0.2s;
        }
        input[type="text"]:focus {
            border: 1.5px solid #2563eb;
            outline: none;
        }
        button {
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 18px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: linear-gradient(90deg, #1d4ed8 0%, #3b82f6 100%);
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
        .search-results {
            margin-top: 10px;
        }
        .search-results p {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
<div class="search-container">
    <h2>Search Users</h2>
    <form method="POST">
        <input type="text" name="search" placeholder="Search users" required>
        <button type="submit">Search</button>
    </form>

    <a href="friend_request.php">View Friend Requests</a>
    <a href="../dashboard/index.php">Back to Dashboard</a>

    <?php if (isset($users)) { ?>
        <div class="search-results">
            <h3>Search Results:</h3>
            <?php foreach ($users as $user) { ?>
                <p>
                    <?php echo htmlspecialchars($user['username']); ?>
                    <a href="send_friend_request.php?receiver_id=<?php echo $user['id']; ?>">Send Friend Request</a>
                </p>
            <?php } ?>
        </div>
    <?php } ?>
</div>
</body>
</html>
