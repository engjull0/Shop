<?php
require 'config.php';

$message = '';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Handle Login/Register POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = clean($_POST['email']);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php');
            exit;
        } else {
            $message = "Invalid credentials.";
        }
    } elseif ($action === 'register') {
        $username = clean($_POST['username']);
        $email = clean($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $password]);
            $message = "Registration successful! Please login.";
        } catch (Exception $e) {
            $message = "Email already exists.";
        }
    }
}
?>

<!-- Simple Auth UI -->
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/style.css">
    <title>Neon Market - Access</title>
</head>
<body class="cyber-bg">
    <div class="container">
  
        <h1 class="neon-text">SYSTEM ACCESS</h1>
        <?php if ($message): ?><p class="alert"><?= $message ?></p><?php endif; ?>
        
        <div class="auth-box">
            <h3>Login</h3>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="neon-btn">Connect</button>
            </form>
        </div>

        <div class="auth-box">
            <h3>New Identity</h3>
            <form method="POST">
                <input type="hidden" name="action" value="register">
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="neon-btn-secondary">Register</button>
            </form>
        </div>
    </div>
</body>
</html>