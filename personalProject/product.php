<?php
require 'config.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) die("Product not found.");


if (isAdmin() && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
        header('Location: index.php');
        exit;
    } elseif (isset($_POST['update'])) {
        $name = clean($_POST['name']);
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $pdo->prepare("UPDATE products SET name=?, price=?, stock=? WHERE id=?")
            ->execute([$name, $price, $stock, $id]);
        header("Location: product.php?id=$id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/style1.css">
    <title><?= htmlspecialchars($product['name']) ?></title>
</head>
<body class="cyber-bg">
    <div class="container">
        <h1 class="neon-text">ILLEGAL TECH BAZAAR</h1>
        <a href="index.php" class="back-link">&lt; Back to Market</a>
        <?php include "includes/header_nav.php"; ?>
        <div class="product-detail">
            <img src="uploads/<?= $product['image'] ?: 'placeholder.jpg' ?>" class="detail-img">
            <div class="info">
                <h1 class="neon-text"><?= htmlspecialchars($product['name']) ?></h1>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                <h2 class="price"><?= number_format($product['price'], 2) ?> BTC</h2>
                <p>Stock: <?= $product['stock'] ?></p>
                
                <?php if (isLoggedIn()): ?>
                    <form action="cart.php" method="POST">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="number" name="qty" value="1" min="1" max="<?= $product['stock'] ?>">
                        <button type="submit" class="neon-btn">Add to Cart</button>
                    </form>
                <?php else: ?>
                    <p><a href="auth.php">Login</a> to purchase.</p>
                <?php endif; ?>

                <?php if (isAdmin()): ?>
                    <hr class="neon-hr">
                    <h3>Admin Controls</h3>
                    <form method="POST">
                        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>">
                        <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>">
                        <input type="number" name="stock" value="<?= $product['stock'] ?>">
                        <button name="update" class="neon-btn-small">Update</button>
                        <button name="delete" class="neon-btn-danger" onclick="return confirm('Delete this item?')">Delete</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php include "includes/footer.php"; ?>