<?php
require 'config.php';

// Initialize Cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $pid = $_POST['product_id'];
    $qty = $_POST['qty'];
    
    // Check stock
    $stmt = $pdo->prepare("SELECT stock, price FROM products WHERE id = ?");
    $stmt->execute([$pid]);
    $prod = $stmt->fetch();

    if ($prod && $prod['stock'] >= $qty) {
        if (isset($_SESSION['cart'][$pid])) {
            $_SESSION['cart'][$pid]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$pid] = ['qty' => $qty, 'price' => $prod['price']];
        }
    }
    header('Location: cart.php');
    exit;
}

// Remove from Cart
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header('Location: cart.php');
    exit;
}

// Checkout
if (isset($_POST['checkout']) && isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $total = 0;
    
    // Calculate Total
    foreach ($_SESSION['cart'] as $pid => $item) {
        $total += $item['qty'] * $item['price'];
    }

    // Create Order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
    $stmt->execute([$user_id, $total]);
    $order_id = $pdo->lastInsertId();

    // Create Order Items & Update Stock
    foreach ($_SESSION['cart'] as $pid => $item) {
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $pid, $item['qty'], $item['price']]);
        
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['qty'], $pid]);
    }

    $_SESSION['cart'] = [];
    echo "<script>alert('Order Placed! Trace ID: $order_id'); window.location='index.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/style1.css">
    <title>Your Stash</title>
    <?php include "includes/header_nav.php"; ?>
</head>
<body class="cyber-bg">
<div class="container">
        <h1 class="neon-text">ILLEGAL TECH BAZAAR</h1>
        <h1 class="neon-text">YOUR STASH</h1>
        <table class="cyber-table">
            <tr><th>Item ID</th><th>Qty</th><th>Price</th><th>Action</th></tr>
            <?php
            $grand_total = 0;
            foreach ($_SESSION['cart'] as $pid => $item):
                $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
                $stmt->execute([$pid]);
                $name = $stmt->fetchColumn();
                $subtotal = $item['qty'] * $item['price'];
                $grand_total += $subtotal;
            ?>
            <tr>
                <td><?= htmlspecialchars($name) ?></td>
                <td><?= $item['qty'] ?></td>
                <td><?= number_format($subtotal, 2) ?> BTC</td>
                <td><a href="?remove=<?= $pid ?>" class="neon-btn-danger">X</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <h2>Total: <?= number_format($grand_total, 2) ?> BTC</h2>
        
        <?php if (isLoggedIn() && !empty($_SESSION['cart'])): ?>
            <form method="POST">
                <button name="checkout" class="neon-btn">CONFIRM TRANSACTION</button>
            </form>
        <?php elseif (!isLoggedIn()): ?>
            <p><a href="auth.php">Login</a> to finalize transaction.</p>
        <?php endif; ?>
    </div>
    <?php include "includes/footer.php"; ?>
</body>
</html>