<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="main-header">
        <div class="nav-container">
            <a href="index.php" class="brand-logo">🏪 ElectroMart</a>
            <nav class="main-nav">
                <a href="index.php" class="nav-link">Home</a>
                <a href="cart.php" class="nav-link">Cart</a>
                <?php if (isLoggedIn()): ?>
                    <span class="user-greeting">Welcome, <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></strong></span>
                    <?php if (isAdmin()): ?>
                        <a href="admin.php" class="nav-link admin-badge">Admin Panel</a>
                    <?php endif; ?>
                    <a href="logout.php" class="nav-link logout-btn">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link login-btn">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <main class="page-content">
