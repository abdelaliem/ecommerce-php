<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// Cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}

// Handle remove action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $remove_id = (int) $_POST['remove_id'];
    if (isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($remove_id) {
            return $item['id'] != $remove_id;
        });
    }
    $_SESSION['flash'] = 'Item removed from cart.';
    header('Location: shopping-cart.php');
    exit;
}

// Calculate total
$total = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Shopping Cart - AuraCommerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f8f9fa; color: #191c1d; font-family: 'Inter', sans-serif; line-height: 1.5; }
        a { color: inherit; text-decoration: none; }
        
        .navbar { background: #fff; border-bottom: 1px solid #e1e3e4; position: sticky; top: 0; z-index: 50; }
        .navbar-container { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; height: 4rem; display: flex; justify-content: space-between; align-items: center; }
        .navbar-brand { font-size: 1.25rem; font-weight: 700; color: #00327d; }
        .nav-menu { display: none; gap: 2rem; }
        @media (min-width: 768px) { .nav-menu { display: flex; } }
        .nav-link { font-size: 0.875rem; font-weight: 500; color: #434653; transition: color 0.2s; }
        .nav-link:hover { color: #00327d; }
        .nav-right { display: flex; align-items: center; gap: 1rem; }
        .cart-badge { background: #dc2626; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold; }
        .user-greeting { font-size: 0.875rem; color: #434653; }
        
        .flash-message { background: #dcfce7; border: 1px solid #86efac; color: #15803d; padding: 0.75rem 1.5rem; text-align: center; font-size: 0.875rem; }
        
        main { flex: 1; display: flex; flex-direction: column; align-items: center; width: 100%; }
        .section { width: 100%; padding: 3rem 0; }
        .container { width: 100%; max-width: 1200px; padding: 0 1.5rem; margin: 0 auto; }
        .section-title { font-size: 2.5rem; font-weight: 600; color: #191c1d; margin-bottom: 0.5rem; }
        
        .btn { display: inline-block; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: all 0.2s; }
        .btn-primary { background: #00327d; color: #fff; }
        .btn-primary:hover { background: #0047ab; }
        .btn-secondary { background: #f3f4f5; color: #191c1d; border: 1px solid #e1e3e4; }
        .btn-secondary:hover { background: #e7e8e9; }
        
        .cart-item { display: flex; gap: 1.5rem; background: #f3f4f5; padding: 1.5rem; border-radius: 4px; border: 1px solid #e1e3e4; margin-bottom: 1.5rem; align-items: center; }
        .cart-item-image { width: 80px; height: 80px; object-fit: cover; border-radius: 4px; }
        .cart-item-info { flex: 1; }
        .cart-item-name { font-size: 1rem; font-weight: 600; color: #191c1d; margin-bottom: 0.25rem; }
        .cart-item-price { font-size: 0.875rem; color: #575f67; }
        .cart-item-qty { font-size: 0.875rem; font-weight: 500; margin-top: 0.5rem; }
        .cart-item-total { text-align: right; min-width: 100px; }
        .cart-item-total-label { font-size: 0.875rem; color: #575f67; margin-bottom: 0.5rem; }
        .cart-item-total-value { font-size: 1.125rem; font-weight: 600; color: #191c1d; }
        .cart-remove { cursor: pointer; color: #dc2626; font-size: 1.25rem; transition: color 0.2s; }
        .cart-remove:hover { color: #b91c1c; }
        
        .cart-summary { display: flex; justify-content: space-between; align-items: center; padding: 2rem 0; border-top: 1px solid #e1e3e4; margin-top: 2rem; }
        .cart-total { font-size: 1.5rem; font-weight: 600; color: #191c1d; }
        .cart-actions { display: flex; gap: 1rem; }
        .cart-actions .btn { padding: 0.75rem 1.5rem; }
        
        .empty-state { text-align: center; padding: 3rem 1.5rem; color: #575f67; }
        
        footer { background: #f3f4f5; border-top: 1px solid #e1e3e4; margin-top: auto; width: 100%; }
        .footer-container { max-width: 1200px; margin: 0 auto; padding: 3rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
        @media (min-width: 768px) { .footer-container { flex-direction: row; justify-content: space-between; align-items: center; } }
        .footer-brand { font-weight: 600; color: #191c1d; }
        .footer-links { display: flex; flex-wrap: wrap; justify-content: center; gap: 1.5rem; font-size: 0.75rem; color: #575f67; }
        .footer-links a { text-decoration: underline; transition: color 0.2s; }
        .footer-links a:hover { color: #00327d; }
        .footer-copyright { font-size: 0.75rem; color: #575f67; text-align: center; }
        @media (min-width: 768px) { .footer-copyright { text-align: right; } }
        
        html, body { width: 100%; height: 100%; }
        body { display: flex; flex-direction: column; }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="home.php" class="navbar-brand">AuraCommerce</a>
            <div class="nav-menu">
                <a href="home.php" class="nav-link">Home</a>
                <a href="shop.php" class="nav-link">Shop</a>
            </div>
            <div class="nav-right">
                <a href="shopping-cart.php" class="cart-icon">
                    🛒
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
                <?php if (isLoggedIn()): ?>
                    <span class="user-greeting">Hi, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></span>
                    <?php if (isAdmin()): ?>
                        <a href="Admin/home.php" class="btn btn-primary">Admin</a>
                    <?php else: ?>
                        <a href="dashboard.php" class="nav-link">👤</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-secondary" style="color: #dc2626; border-color: #dc2626;">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">👤</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Flash message -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash-message">
            <?= htmlspecialchars($_SESSION['flash']) ?>
            <?php unset($_SESSION['flash']); ?>
        </div>
    <?php endif; ?>

    <main>
        <section class="section">
            <div class="container">
                <h1 class="section-title">Shopping Cart</h1>

                <?php if (empty($_SESSION['cart'])): ?>
                    <div class="empty-state">
                        <p class="empty-state-message">Your cart is empty.</p>
                        <a href="shop.php" class="btn btn-primary" style="display: inline-block;">Continue Shopping</a>
                    </div>
                <?php else: ?>
                    <div style="margin-top: 2rem;">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="cart-item">
                                <img alt="<?= htmlspecialchars($item['name']) ?>"
                                    class="cart-item-image"
                                    src="https://via.placeholder.com/80" />
                                <div class="cart-item-info">
                                    <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                    <div class="cart-item-price">$<?= number_format($item['price'], 2) ?> each</div>
                                    <div class="cart-item-qty">Qty: <?= $item['quantity'] ?></div>
                                </div>
                                <div class="cart-item-total">
                                    <div class="cart-item-total-label">Subtotal</div>
                                    <div class="cart-item-total-value">$<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                                </div>
                                <form action="shopping-cart.php" method="POST" style="margin: 0; margin-left: 1rem;">
                                    <input type="hidden" name="remove_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="cart-remove" title="Remove item">✕</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-summary">
                        <div class="cart-total">Total: $<?= number_format($total, 2) ?></div>
                        <div class="cart-actions">
                            <a href="shop.php" class="btn btn-secondary">Continue Shopping</a>
                            <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-container">
            <div class="footer-brand">AuraCommerce</div>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
            <div class="footer-copyright">© <?= date('Y') ?> AuraCommerce. All rights reserved.</div>
        </div>
    </footer>
</body>

</html>
