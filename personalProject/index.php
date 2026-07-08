<?php
require 'config.php';


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
    <link rel="stylesheet" href="assets/style1.css">
    <title>Neon Black Market</title>
</head>
<body class="cyber-bg">
    <?php include 'includes/header_nav.php'; // Create a simple nav file later ?>
    
    <div class="container">
        <h1 class="neon-text">ILLEGAL TECH BAZAAR</h1>
        
   
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


<?php
require 'config.php';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

// Validate and sanitize inputs
$search = trim($search);
$category = trim($category);

$sql = "SELECT p.*, c.name as cat_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.name LIKE ?";
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

<footer class="site-footer">

<div class="scanline"></div>

<div class="footer-container">

    <div class="footer-section">
        <h4>About Us</h4>
        <p>Your trusted source for quality tech products. We provide genuine electronics with warranty and excellent customer service.</p>
    </div>

    <div class="footer-section">
        <h4>Quick Links</h4>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="faq.php">FAQ</a></li>
        </ul>
    </div>

    <div class="footer-section">
        <h4>Customer Service</h4>
        <ul>
            <li><a href="shipping.php">Shipping Info</a></li>
            <li><a href="returns.php">Returns & Exchanges</a></li>
            <li><a href="warranty.php">Warranty Policy</a></li>
            <li><a href="privacy.php">Privacy Policy</a></li>
            <li><a href="terms.php">Terms of Service</a></li>
        </ul>
    </div>

    <div class="footer-section">
        <h4>Contact Info</h4>
        <p>Email: support@example.com</p>
        <p>Phone: +1 (123) 456-7890</p>
        <p>Address: Unknown</p>

        <div class="social-links">
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        </div>
    </div>

</div>

<div class="footer-bottom">
    <p>&copy; <?= date('Y') ?> Tech Marketplace. All rights reserved.</p>
    <p>Secure Shopping • SSL Encrypted • Verified Seller</p>
</div>

</footer>
</footer>
</body>
</html>





