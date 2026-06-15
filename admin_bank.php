<?php
require 'config.php';
if (!isAdmin()) die("Access Denied.");

// Handle Top-Up
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['top_up'])) {
    $user_id = (int)$_POST['user_id'];
    $amount = (float)$_POST['amount'];
    
    if ($amount > 0) {
        $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
        $stmt->execute([$amount, $user_id]);
        $msg = "Successfully added $amount BTC to User ID $user_id";
    }
}

// Fetch all users with their current balance
$users = $pdo->query("SELECT id, username, email, balance FROM users ORDER BY id ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/style.css">
    <title>Admin Bank</title>
    <style>
        .bank-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .bank-table th, .bank-table td { padding: 10px; border-bottom: 1px solid #333; text-align: left; }
        .bank-table th { color: #0f0; }
        .positive { color: #0f0; }
    </style>
</head>
<body class="cyber-bg">
    <div class="container">
        <h1 class="neon-text">CENTRAL BANK CONSOLE</h1>
        
        <?php if(isset($msg)): ?>
            <div style="color: #0f0; margin-bottom: 15px;"><?= $msg ?></div>
        <?php endif; ?>

        <div style="background: rgba(0,0,0,0.5); padding: 20px; border: 1px solid #333; max-width: 500px;">
            <h3>Inject Funds</h3>
            <form method="POST">
                <div style="margin-bottom: 15px;">
                    <label>Select User:</label>
                    <select name="user_id" style="width: 100%; padding: 8px; background: #111; color: white; border: 1px solid #333;">
                        <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>">
                                <?= htmlspecialchars($user['username']) ?> (Current: <?= $user['balance'] ?> BTC)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label>Amount (BTC):</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                </div>

                <button type="submit" name="top_up" class="neon-btn-small">Transfer Funds</button>
            </form>
        </div>

        <h2>User Ledger</h2>
        <table class="bank-table">
            <tr><th>ID</th><th>Username</th><th>Email</th><th>Balance</th></tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td class="positive"><?= number_format($user['balance'], 2) ?> BTC</td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>