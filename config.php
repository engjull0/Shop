<?php
session_start();
$host = 'localhost';
$db   = 'neon_market';
$user = 'root';
$pass = ''; // Change as needed
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Helper: Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: Check if admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Helper: Sanitize input
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>