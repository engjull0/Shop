<?php
session_start();
require 'config.php';


if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $pid = (int)$_POST['product_id'];
    $qty = (int)$_POST['qty'];
    
 
    $stmt = $pdo->prepare("SELECT stock, price FROM products WHERE id = ?");
    $stmt->execute([$pid]);
    $prod = $stmt->fetch();

    if ($prod && $prod['stock'] >= $qty) {
        if (isset($_SESSION['cart'][$pid])) {
            $_SESSION['cart'][$pid]['qty'] += $qty;
        } else {
           
            $_SESSION['cart'][$pid] = ['qty' => $qty, 'price' => $prod['price']];
        }
    } else {
        $error_msg = "Insufficient stock for this item.";
    }
    header('Location: cart.php');
    exit;
}

if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header('Location: cart.php');
    exit;
}


$checkout_error = null;
if (isset($_POST['checkout']) && isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $grand_total = 0;
    
   
    foreach ($_SESSION['cart'] as $pid => $item) {
        $grand_total += $item['qty'] * $item['price'];
    }

    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ? FOR UPDATE"); // Lock row for safety
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if($user['balance'] >= $grand_total) {
        try {
            $pdo->beginTransaction();

       
            $new_balance = $user['balance'] - $grand_total;
            $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $stmt->execute([$new_balance, $user_id]);

          
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status, created_at) VALUES (?, ?, 'pending', NOW())");
            $stmt->execute([$user_id, $grand_total]);
            $order_id = $pdo->lastInsertId();

            $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stock_stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            
            foreach ($_SESSION['cart'] as $pid => $item) {
                $item_stmt->execute([$order_id, $pid, $item['qty'], $item['price']]);
                $stock_stmt->execute([$item['qty'], $pid]);
            }

            $pdo->commit();

      
            $_SESSION['cart'] = [];
            $_SESSION['success_msg'] = "Transaction Complete! Order #$order_id placed. New Balance: " . number_format($new_balance, 2) . " BTC";
            header("Location: cart.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $checkout_error = "System Error: Transaction failed. Please try again.";
        }
    } else {
        $checkout_error = "INSUFFICIENT FUNDS. You need " . number_format($grand_total, 2) . " BTC but only have " . number_format($user['balance'], 2) . " BTC.";
    }
}

$user_balance = 0;
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_balance = $stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/style.css">
    <title>Your Stash</title>
    <style>
        .balance-box { 
            background: rgba(0, 255, 0, 0.1); 
            border: 1px solid #0f0; 
            padding: 15px; 
            margin-bottom: 20px; 
            text-align: right;
            font-size: 1.2em;
        }
        .error-msg { color: #ff4444; border: 1px solid #ff4444; padding: 10px; margin-bottom: 15px; background: rgba(255,0,0,0.1); }
        .success-msg { color: #0f0; border: 1px solid #0f0; padding: 10px; margin-bottom: 15px; background: rgba(0,255,0,0.1); }
    </style>
</head>
<body class="cyber-bg">
    <div class="container">
        <h1 class="neon-text">YOUR STASH</h1>

       
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="success-msg"><?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?></div>
        <?php endif; ?>

        <?php if ($checkout_error): ?>
            <div class="error-msg"><?= $checkout_error ?></div>
        <?php endif; ?>

        <?php if (isset($error_msg)): ?>
            <div class="error-msg"><?= $error_msg ?></div>
        <?php endif; ?>

 
        <?php if (isLoggedIn()): ?>
            <div class="balance-box">
                WALLET BALANCE: <span style="color: #0f0; font-weight: bold;"><?= number_format($user_balance, 2) ?> BTC</span>
            </div>
        <?php endif; ?>

        <table class="cyber-table">
            <thead>
                <tr><th>Item</th><th>Qty</th><th>Price</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php
            $grand_total = 0;
            if (empty($_SESSION['cart'])) {
                echo "<tr><td colspan='4' style='text-align:center'>Your stash is empty.</td></tr>";
            } else {
                foreach ($_SESSION['cart'] as $pid => $item):
                    
                    $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
                    $stmt->execute([$pid]);
                    $name = $stmt->fetchColumn() ?: "Unknown Item";
                    
                    $subtotal = $item['qty'] * $item['price'];
                    $grand_total += $subtotal;
            ?>
                <tr>
                    <td><?= htmlspecialchars($name) ?></td>
                    <td><?= $item['qty'] ?></td>
                    <td><?= number_format($subtotal, 2) ?> BTC</td>
                    <td><a href="?remove=<?= $pid ?>" class="neon-btn-danger">X</a></td>
                </tr>
            <?php 
                endforeach; 
            }
            ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align:right; font-weight:bold;">TOTAL:</td>
                    <td colspan="2" style="color:#0f0; font-weight:bold; font-size:1.2em;"><?= number_format($grand_total, 2) ?> BTC</td>
                </tr>
            </tfoot>
        </table>
        
        <div style="margin-top: 20px; text-align: right;">
            <?php if (isLoggedIn() && !empty($_SESSION['cart'])): ?>
                <form method="POST" style="display: inline-block;">
                    <button name="checkout" class="neon-btn">CONFIRM TRANSACTION</button>
                </form>
            <?php elseif (!isLoggedIn()): ?>
                <p><a href="auth.php" class="neon-btn-small">Login</a> to finalize transaction.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>