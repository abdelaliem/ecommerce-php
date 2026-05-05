<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

if (empty($_SESSION['cart'])) {
    header('Location: shopping-cart.php');
    exit;
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Calculate totals
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $shipping = 15.00;
    $tax = $subtotal * 0.07;
    $total = $subtotal + $shipping + $tax;

    // Insert order
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status, created_at) VALUES (?, ?, 'pending', NOW())");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert order items
    foreach ($_SESSION['cart'] as $item) {
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
        $stmt->execute();
        $stmt->close();
    }

    // Clear cart
    $_SESSION['cart'] = [];
    $_SESSION['flash'] = 'Order #' . $order_id . ' placed successfully! Thank you.';
    header('Location: home.php');
    exit;
}

$cart_count = 0;
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 15.00;
$tax = $subtotal * 0.07;
$total = $subtotal + $shipping + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Checkout - AuraCommerce</title>
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
                <a href="#" class="nav-item">Categories</a>
                <a href="#" class="nav-item">Contact</a>
            </div>
            <div class="nav-right">
                <a href="shopping-cart.php" class="nav-icon-btn" title="Cart">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <?php if ($cart_count > 0): ?><span class="cart-badge"><?= $cart_count ?></span><?php endif; ?>
                </a>
                <?php if (isLoggedIn()): ?>
                    <span class="nav-user-name">Hi, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></span>
                    <a href="logout.php" class="nav-logout">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main>
        <section class="checkout-section">
            <div class="checkout-container">
                <h1 class="checkout-title">Checkout</h1>

                <form action="checkout.php" method="POST">
                    <div class="checkout-layout">
                        <!-- Shipping & Payment -->
                        <div class="checkout-form-wrap">
                            <div class="checkout-section-title">Shipping Information</div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" name="first_name" required placeholder="John" />
                                </div>
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" name="last_name" required placeholder="Doe" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="email" required placeholder="you@example.com" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>" />
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" name="address" required placeholder="123 Main St" />
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>City</label>
                                    <input type="text" name="city" required placeholder="New York" />
                                </div>
                                <div class="form-group">
                                    <label>Postal Code</label>
                                    <input type="text" name="postal" required placeholder="10001" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Country</label>
                                <select name="country">
                                    <option>United States</option>
                                    <option>Egypt</option>
                                    <option>United Kingdom</option>
                                    <option>Germany</option>
                                    <option>France</option>
                                    <option>Canada</option>
                                    <option>Australia</option>
                                </select>
                            </div>

                            <div class="checkout-section-title" style="margin-top:2rem;">Payment</div>
                            <div class="form-group">
                                <label>Card Number</label>
                                <input type="text" name="card_number" placeholder="4242 4242 4242 4242" maxlength="19" />
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Expiry</label>
                                    <input type="text" name="expiry" placeholder="MM / YY" maxlength="7" />
                                </div>
                                <div class="form-group">
                                    <label>CVC</label>
                                    <input type="text" name="cvc" placeholder="123" maxlength="4" />
                                </div>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="checkout-summary">
                            <div class="order-summary-box">
                                <div class="order-summary-title">Order Summary</div>
                                <?php foreach ($_SESSION['cart'] as $item): ?>
                                    <div class="summary-row">
                                        <span><?= htmlspecialchars($item['name']) ?> &times;<?= $item['quantity'] ?></span>
                                        <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                    </div>
                                <?php endforeach; ?>
                                <hr class="summary-divider" />
                                <div class="summary-row"><span>Subtotal</span><span>$<?= number_format($subtotal, 2) ?></span></div>
                                <div class="summary-row"><span>Shipping</span><span>$<?= number_format($shipping, 2) ?></span></div>
                                <div class="summary-row"><span>Tax (7%)</span><span>$<?= number_format($tax, 2) ?></span></div>
                                <hr class="summary-divider" />
                                <div class="summary-total"><span>Total</span><span>$<?= number_format($total, 2) ?></span></div>
                                <button type="submit" name="place_order" class="btn-place-order">Place Order &rarr;</button>
                                <p class="summary-secure">Secure checkout powered by AuraCommerce.</p>
                            </div>
                        </div>
                    </div>
                </form>
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
