<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $pid = (int) $_POST['product_id'];
    $qty = (int) $_POST['quantity'];
    if ($qty <= 0) {
        // Remove item
        $_SESSION['cart'] = array_filter($_SESSION['cart'], fn($i) => $i['id'] != $pid);
    } else {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $pid) { $item['quantity'] = $qty; break; }
        }
    }
    header('Location: shopping-cart.php');
    exit;
}

// Handle remove
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $remove_id = (int) $_POST['remove_id'];
    $_SESSION['cart'] = array_filter($_SESSION['cart'], fn($i) => $i['id'] != $remove_id);
    header('Location: shopping-cart.php');
    exit;
}

$cart_count = 0;
$subtotal = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
        $subtotal += $item['price'] * $item['quantity'];
    }
}
$shipping = $subtotal > 0 ? 15.00 : 0;
$tax = $subtotal * 0.07;
$total = $subtotal + $shipping + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Your Cart - AuraCommerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="assets/aura.css" />
</head>
<body>
    <nav class="navbar">
        <div class="navbar-inner">
            <a href="home.php" class="navbar-brand">AuraCommerce</a>
            <div class="nav-menu">
                <a href="home.php" class="nav-item">Home</a>
                <a href="shop.php" class="nav-item">Shop</a>

            </div>
            <div class="nav-right">
                <a href="shopping-cart.php" class="nav-icon-btn" title="Cart">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <?php if ($cart_count > 0): ?><span class="cart-badge"><?= $cart_count ?></span><?php endif; ?>
                </a>
                <?php if (isLoggedIn()): ?>
                    <span class="nav-user-name">Hi, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></span>
                    <?php if (isAdmin()): ?>
                        <a href="Admin/home.php" class="nav-admin-btn">Admin</a>
                    <?php else: ?>
                        <a href="dashboard.php" class="nav-icon-btn" title="Account"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></a>
                    <?php endif; ?>
                    <a href="logout.php" class="nav-logout">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="nav-icon-btn" title="Login"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash"><?= htmlspecialchars($_SESSION['flash']) ?><?php unset($_SESSION['flash']); ?></div>
    <?php endif; ?>

    <main>
        <section class="cart-section">
            <div class="cart-container">
                <h1 class="cart-page-title">Your Cart</h1>
                <p class="cart-page-subtitle">Review your items before proceeding to checkout.</p>

                <?php if (empty($_SESSION['cart'])): ?>
                    <div class="cart-empty">
                        <p>Your cart is empty.</p>
                        <a href="shop.php" class="btn-primary">Continue Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="cart-layout">
                        <div class="cart-items">
                            <div class="cart-table-head">
                                <span>Product</span>
                                <span>Quantity</span>
                                <span>Price</span>
                                <span>Total</span>
                            </div>

                            <?php foreach ($_SESSION['cart'] as $item): ?>
                                <div class="cart-row">
                                    <!-- Product -->
                                    <div class="cart-row-product">
                                        <img class="cart-row-img"
                                             src="<?= htmlspecialchars($item['image'] ?? 'https://via.placeholder.com/80') ?>"
                                             alt="<?= htmlspecialchars($item['name']) ?>" />
                                        <div>
                                            <div class="cart-row-name"><?= htmlspecialchars($item['name']) ?></div>
                                            <?php if (!empty($item['variant'])): ?>
                                                <div class="cart-row-variant"><?= htmlspecialchars($item['variant']) ?></div>
                                            <?php endif; ?>
                                            <form action="shopping-cart.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="remove_id" value="<?= $item['id'] ?>">
                                                <button type="submit" class="cart-row-remove">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                                    Remove
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Quantity -->
                                    <form action="shopping-cart.php" method="POST">
                                        <input type="hidden" name="update_qty" value="1">
                                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                        <div class="cart-qty-wrap">
                                            <button type="submit" name="quantity" value="<?= max(0, $item['quantity'] - 1) ?>" class="cart-qty-btn">−</button>
                                            <span class="cart-qty-val"><?= $item['quantity'] ?></span>
                                            <button type="submit" name="quantity" value="<?= $item['quantity'] + 1 ?>" class="cart-qty-btn">+</button>
                                        </div>
                                    </form>

                                    <!-- Price -->
                                    <div class="cart-row-price">$<?= number_format($item['price'], 2) ?></div>

                                    <!-- Total -->
                                    <div class="cart-row-total">$<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Order Summary -->
                        <div class="cart-sidebar">
                            <div class="order-summary-box">
                                <div class="order-summary-title">Order Summary</div>
                                <div class="summary-row">
                                    <span>Subtotal</span>
                                    <span>$<?= number_format($subtotal, 2) ?></span>
                                </div>
                                <div class="summary-row">
                                    <span>Shipping Estimate</span>
                                    <span>$<?= number_format($shipping, 2) ?></span>
                                </div>
                                <div class="summary-row">
                                    <span>Tax</span>
                                    <span>$<?= number_format($tax, 2) ?></span>
                                </div>
                                <hr class="summary-divider" />
                                <div class="summary-total">
                                    <span>Total</span>
                                    <span>$<?= number_format($total, 2) ?></span>
                                </div>
                                <a href="checkout.php" class="btn-checkout">Proceed to Checkout &rarr;</a>
                                <p class="summary-secure">Secure checkout powered by AuraCommerce.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-inner">
            <div class="footer-brand">AuraCommerce</div>
            <div class="footer-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Returns</a>
                <a href="#">Shipping Info</a>
            </div>
            <div class="footer-copy">© <?= date('Y') ?> AuraCommerce. All rights reserved.</div>
        </div>
    </footer>
</body>
</html>
