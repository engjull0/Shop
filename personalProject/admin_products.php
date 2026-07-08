<?php
require 'config.php';
if (!isAdmin()) die("Access Denied.");

// --- 1. HANDLE ACTIONS (POST) ---

function redirectSelf() {
    header("Location: admin_products.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A. Add or Update Product
    if (isset($_POST['save_product'])) {
        $id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
        $name = trim($_POST['name']);
        $price = (float)$_POST['price'];
        $desc = trim($_POST['description']);
        $cat_id = (int)$_POST['category_id'];
        
        // Handle Image Upload
        $image_name = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                // Create unique name to prevent overwrites
                $image_name = uniqid() . '.' . $ext;
                $upload_path = 'uploads/' . $image_name;
                
                // Ensure uploads directory exists
                if (!is_dir('uploads')) mkdir('uploads', 0777, true);
                
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_path);
            }
        }

        if ($id) {
            // UPDATE Existing Product
            if ($image_name) {
                // If new image uploaded, update image column too
                $stmt = $pdo->prepare("UPDATE products SET name=?, price=?, description=?, category_id=?, image=? WHERE id=?");
                $stmt->execute([$name, $price, $desc, $cat_id, $image_name, $id]);
            } else {
                // Keep old image
                $stmt = $pdo->prepare("UPDATE products SET name=?, price=?, description=?, category_id=? WHERE id=?");
                $stmt->execute([$name, $price, $desc, $cat_id, $id]);
            }
        } else {
            // INSERT New Product
            if (!$image_name) $image_name = 'placeholder.jpg'; // Default image
            $stmt = $pdo->prepare("INSERT INTO products (name, price, description, category_id, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $price, $desc, $cat_id, $image_name]);
        }
        redirectSelf();
    }

    // B. Delete Product
    if (isset($_POST['delete_product'])) {
        $id = (int)$_POST['product_id'];
        
        // Optional: Delete image file from server
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $prod = $stmt->fetch();
        if ($prod && $prod['image'] !== 'placeholder.jpg' && file_exists('uploads/' . $prod['image'])) {
            unlink('uploads/' . $prod['image']);
        }

        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        redirectSelf();
    }
}

// --- 2. FETCH DATA ---

// Check if we are in "Edit Mode"
$edit_product = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_product = $stmt->fetch();
}

$products = $pdo->query("SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<?php include "includes/header_nav.php"; ?>
    <link rel="stylesheet" href="assets/style1.css">
    <link rel="stylesheet" href="assets/sidebarstyle.css">
    <title>Manage Products</title>
    <style>
        .product-row img { width: 50px; height: 50px; object-fit: cover; border: 1px solid #0f0; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #0f0; }
        .form-control { width: 100%; padding: 8px; background: #111; border: 1px solid #333; color: white; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .admin-table th, .admin-table td { padding: 10px; border-bottom: 1px solid #333; text-align: left; }
        .admin-table th { color: #0f0; }
        .btn-delete { background: #ff4444; color: white; border: none; padding: 5px 10px; cursor: pointer; }
        .btn-edit { background: #ffbb33; color: black; border: none; padding: 5px 10px; cursor: pointer; margin-right: 5px; }
    </style>
</head>
<body class="cyber-bg">
<div class="admin-layout">

<aside class="sidebar">

    <div class="sidebar-logo">
        <i class="fas fa-shield-alt"></i>
        ADMIN CORE
    </div>

    <nav>

        <a href="admin.php">
            <i class="fas fa-chart-line"></i>
            Dashboard
        </a>

        <a href="admin_products.php" class="active">
            <i class="fas fa-box"></i>
            Products
        </a>

        <a href="admin.php">
            <i class="fas fa-layer-group"></i>
            Categories
        </a>

        <a href="admin.php">
            <i class="fas fa-shopping-cart"></i>
            Orders
        </a>

        <a href="admin_bank.php">
            <i class="fas fa-university"></i>
            Bank Settings
        </a>

        <a href="auth.php?action=logout">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>

    </nav>


    <div class="sidebar-status">

        <span></span>

        SYSTEM ONLINE

    </div>


</aside>


<main class="admin-content">
    <div class="container">

        <h1 class="neon-text">PRODUCT DATABASE</h1>
        
        <div style="display: flex; gap: 20px;">
            
            <!-- LEFT: ADD/EDIT FORM -->
            <div style="flex: 1; background: rgba(0,0,0,0.5); padding: 20px; border: 1px solid #333;">
                <h3><?= $edit_product ? 'Edit Item' : 'Add New Item' ?></h3>
                <form method="POST" enctype="multipart/form-data">
                    
                    <?php if ($edit_product): ?>
                        <input type="hidden" name="product_id" value="<?= $edit_product['id'] ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" name="name" class="form-control" required 
                               value="<?= htmlspecialchars($edit_product['name'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Price (BTC)</label>
                        <input type="number" step="0.000001" name="price" class="form-control" required 
                               value="<?= $edit_product['price'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id" class="form-control">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" 
                                    <?= (isset($edit_product) && $edit_product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Image</label>
                        <input type="file" name="image" class="form-control">
                        <?php if ($edit_product && $edit_product['image']): ?>
                            <img src="uploads/<?= $edit_product['image'] ?>" style="width: 50px; margin-top: 5px;">
                        <?php endif; ?>
                    </div>

                    <button type="submit" name="save_product" class="neon-btn-small">
                        <?= $edit_product ? 'Update Product' : 'Add Product' ?>
                    </button>
                    
                    <?php if ($edit_product): ?>
                        <a href="admin_products.php" style="color: #fff; margin-left: 10px;">Cancel</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- RIGHT: PRODUCT LIST -->
            <div style="flex: 2;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Img</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $prod): ?>
                        <tr>
                            <td><img src="uploads/<?= $prod['image'] ?: 'placeholder.jpg' ?>" alt="img"></td>
                            <td><?= htmlspecialchars($prod['name']) ?></td>
                            <td><?= htmlspecialchars($prod['cat_name']) ?></td>
                            <td><?= $prod['price'] ?> BTC</td>
                            <td>
                                <a href="admin_products.php?edit=<?= $prod['id'] ?>" class="btn-edit">Edit</a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this item?');">
                                    <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                    <button type="submit" name="delete_product" class="btn-delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include "includes/footer.php"; ?>
</body>
</html>