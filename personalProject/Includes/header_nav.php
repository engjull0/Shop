
<nav style="border-bottom: 2px solid var(--neon-blue); padding: 10px; margin-bottom: 20px;">
    <a href="index.php" class="neon-text" style="text-decoration:none; font-size: 1.5em;">NEON MARKET</a>
    <div style="float: right;">
        <a href="index.php" class="nav-link">Shop</a>
        <?php if (isLoggedIn()): ?>
            <a href="cart.php" class="nav-link">Stash (Cart)</a>
            <?php if (isAdmin()): ?>
                <a href="admin.php" class="nav-link" style="color: var(--neon-pink);">Admin</a>
            <?php endif; ?>
            <a href="auth.php?action=logout" class="nav-link">Disconnect</a>
        <?php else: ?>
            <a href="auth.php" class="nav-link">Access Terminal</a>
        <?php endif; ?>
    </div>
</nav>
<style>
    .nav-link { color: var(--text-color); text-decoration: none; margin-left: 15px; font-weight: bold; }
    .nav-link:hover { color: var(--neon-blue); text-shadow: 0 0 5px var(--neon-blue); }
</style>