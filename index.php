<?php
require 'config.php';

// Fetch Products with Search/Filter
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

$sql = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.name LIKE ?";
$params = ["%$search%"];

if ($category) {
    $sql .= " AND c.name = ?";
    $params[] = $category;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch Categories for Filter
$cats = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/style.css">
    <title>Neon Black Market</title>
</head>
<body class="cyber-bg">
    <?php include 'includes/header_nav.php'; // Create a simple nav file later ?>
    
    <div class="container">
        <h1 class="neon-text">ILLEGAL TECH BAZAAR</h1>
        
        <!-- Search & Filter -->
        <form method="GET" class="filter-bar">
            <input type="text" name="search" placeholder="Search Items..." value="<?= htmlspecialchars($search) ?>">
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($cats as $cat): ?>
                    <option value="<?= $cat['name'] ?>" <?= $category == $cat['name'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="neon-btn">Scan</button>
        </form>

        <!-- Product Grid -->
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="card">
                    <img src="uploads/<?= $product['image'] ?: 'placeholder.jpg' ?>" alt="Product">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p class="price"><?= number_format($product['price'], 2) ?> BTC</p>
                    <a href="product.php?id=<?= $product['id'] ?>" class="neon-btn-small">Inspect</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>


<h3>SCAN for Free stuff</h3>
<img id="image" src="uploads/hidden.PNG">


