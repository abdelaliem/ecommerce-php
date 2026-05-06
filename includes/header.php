<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// Cart count from session
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}

// Active page detection
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title><?php echo isset($page_title) ? $page_title . ' - AuraCommerce' : 'AuraCommerce - Minimalist E-Commerce'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="assets/aura.css" />
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        <?php echo isset($additional_css) ? $additional_css : ''; ?>
    </style>
</head>
<body>

    <!-- Top Navigation -->
    <nav class="navbar">
        <div class="navbar-inner">
            <a href="home.php" class="navbar-brand">AuraCommerce</a>
            <div class="nav-menu">
                <a class="nav-item <?php echo ($current_page == 'home.php') ? 'active' : ''; ?>" href="home.php">Home</a>
                <a class="nav-item <?php echo ($current_page == 'shop.php') ? 'active' : ''; ?>" href="shop.php">Shop</a>
            </div>
            <div class="nav-right">
                <a href="shopping-cart.php" class="nav-icon-btn" title="Cart">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
                <?php if (isLoggedIn()): ?>
                    <a href="<?= isAdmin() ? 'Admin/home.php' : 'dashboard.php' ?>" class="nav-icon-btn" title="Account">
                        <span class="material-symbols-outlined">account_circle</span>
                        <span class="nav-user-name hidden-sm"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    </a>
                    <a href="logout.php" class="nav-logout">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn-primary" style="padding: 0.5rem 1.25rem; font-size: 0.8rem;">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash">
            <?= htmlspecialchars($_SESSION['flash']) ?>
            <?php unset($_SESSION['flash']); ?>
        </div>
    <?php endif; ?>

    <main>
