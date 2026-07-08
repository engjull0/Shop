<?php
require 'config.php';
if (!isAdmin()) die("Access Denied.");

// --- 1. HANDLE ACTIONS (POST) ---

// Helper to redirect safely
function redirectSelf() {
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A. Add Category
    if (isset($_POST['add_cat'])) {
        $name = trim($_POST['cat_name']); // Trim whitespace
        if (!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
        }
        redirectSelf(); // Prevents reload bug
    }

    // B. Edit Category
    if (isset($_POST['edit_cat'])) {
        $id = (int)$_POST['cat_id'];
        $name = trim($_POST['cat_name']);
        if (!empty($name)) {
            $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $stmt->execute([$name, $id]);
        }
        redirectSelf();
    }

    // C. Delete Category
    if (isset($_POST['delete_cat'])) {
        $id = (int)$_POST['cat_id'];
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        redirectSelf();
    }

  
    if (isset($_POST['update_order'])) {
        $oid = (int)$_POST['order_id'];
        $status = $_POST['status'];
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $oid]);
        redirectSelf();
    }
    
    // E. Delete Order
    if (isset($_POST['delete_order'])) {
        $id = (int)$_POST['order_id'];
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        redirectSelf();
    }
}

// --- 2. FETCH DATA (GET) ---
$orders = $pdo->query("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/style1.css">
    <link rel="stylesheet" href="assets/sidebarstyle.css">
    <title>Admin Panel</title>
    <?php include "includes/header_nav.php"; ?>
</head>
<body class="cyber-bg">
<body class="cyber-bg">

<div class="admin-layout">

<aside class="sidebar">

    <div class="sidebar-logo">
        <i class="fas fa-shield-alt"></i>
        ADMIN CORE
    </div>

    <nav>

        <a href="admin.php" class="active">
            <i class="fas fa-chart-line"></i>
            Dashboard
        </a>

        <a href="admin_products.php">
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
        <h1 class="neon-text">ADMIN CONSOLE</h1>
        <a href="admin_products.php"><i class="fas fa-box"></i> Manage Products</a>
        <a href="admin_bank.php"><i class="fas fa-box"></i> Manage Bank</a>
        <!-- MANAGE CATEGORIES -->
        <h2>Manage Categories</h2>
        
        <!-- Add Form -->
        <form method="POST" class="action-bar">
            <input type="text" name="cat_name" placeholder="New Category Name" required>
            <button name="add_cat" class="neon-btn-small">Add Category</button>
        </form>

        <div class="category-list">
            <?php foreach ($categories as $cat): ?>
                <div class="cat-item">
                    <span><?= htmlspecialchars($cat['name']) ?></span>
                    
                    <div>
                        <!-- Edit Button (Opens JS Modal) -->
                        <button onclick="openEditModal(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>')" class="edit-btn">Edit</button>
                        
                        <!-- Delete Form -->
                        <form method="POST" class="inline-form" onsubmit="return confirm('Delete this category?');">
                            <input type="hidden" name="cat_id" value="<?= $cat['id'] ?>">
                            <button type="submit" name="delete_cat" class="danger-btn">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <hr style="border-color: #333; margin: 30px 0;">

        <!-- RECENT TRANSACTIONS -->
        <h2>Recent Transactions</h2>
        <table class="cyber-table">
            <thead>
                <tr><th>ID</th><th>User</th><th>Total</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['username']) ?></td>
                <td><?= $order['total'] ?></td>
                <td><?= ucfirst($order['status']) ?></td>
                <td>
                    <div style="display:flex; gap:5px;">
                        <!-- Update Status Form -->
                        <form method="POST" class="inline-form">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <select name="status" onchange="this.form.submit()" style="padding: 5px;">
                                <option value="pending" <?= $order['status']=='pending'?'selected':'' ?>>Pending</option>
                                <option value="shipped" <?= $order['status']=='shipped'?'selected':'' ?>>Shipped</option>
                                <option value="delivered" <?= $order['status']=='delivered'?'selected':'' ?>>Delivered</option>
                            </select>
                            <noscript><button name="update_order" class="neon-btn-small">Go</button></noscript>
                        </form>

                        <!-- Delete Order Form -->
                        <form method="POST" class="inline-form" onsubmit="return confirm('Delete this order record?');">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <button type="submit" name="delete_order" class="danger-btn">X</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>

</main>

</div>


<!-- EDIT MODAL

    <!-- EDIT MODAL (Hidden by default) -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-box">
            <h3 style="margin-top:0">Edit Category</h3>
            <form method="POST">
                <input type="hidden" name="cat_id" id="edit_cat_id">
                <input type="text" name="cat_name" id="edit_cat_name" style="width: 90%; margin-bottom: 10px;" required>
                <div style="text-align: right;">
                    <button type="button" onclick="closeEditModal()" style="background:transparent; color:#fff; border:none; cursor:pointer;">Cancel</button>
                    <button type="submit" name="edit_cat" class="neon-btn-small">Save</button>
                </div>
            </form>
        </div>
    </div>
    <?php include "includes/footer.php"; ?>
    <script>
        function openEditModal(id, name) {
            document.getElementById('edit_cat_id').value = id;
            document.getElementById('edit_cat_name').value = name;
            document.getElementById('editModal').style.display = 'flex';
        }
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</body>
</html>