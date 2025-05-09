<?php
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff 0%, #f4f6fb 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .auth-container {
            max-width: 350px;
            margin: 60px auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(52, 152, 219, 0.10);
            padding: 32px 28px 24px 28px;
        }
        h2 {
            color: #2563eb;
            margin-top: 0;
            margin-bottom: 18px;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        label {
            font-size: 15px;
            color: #333;
        }
        input[type="text"], input[type="password"] {
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 10px;
            font-size: 15px;
            background: #f8fafc;
            transition: border 0.2s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border: 1.5px solid #2563eb;
            outline: none;
        }
        input[type="submit"] {
            background: linear-gradient(90deg, #2563eb 0%, #60a5fa 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 0;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 6px;
        }
        input[type="submit"]:hover {
            background: linear-gradient(90deg, #1d4ed8 0%, #3b82f6 100%);
        }
        .error {
            color: #e11d48;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 8px;
            margin-bottom: 8px;
            text-align: center;
        }
        .auth-link {
            text-align: center;
            margin-top: 12px;
        }
        .auth-link a {
            color: #2563eb;
            text-decoration: none;
        }
        .auth-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="auth-container">
    <h2>Login</h2>

    <?php
    if (isset($_SESSION["logged_out_notice"])) {
        echo '<div class="error">' . htmlspecialchars($_SESSION["logged_out_notice"]) . '</div>';
        unset($_SESSION["logged_out_notice"]); // Clear the notice after displaying it
    }

    if (isset($error)) {
        echo '<div class="error">' . htmlspecialchars($error) . '</div>';
    }
    ?>

    <form method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <input type="submit" value="Login">
    </form>

    <div class="auth-link">
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>
</body>
</html>