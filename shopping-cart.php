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
        // Fetch current stock to validate
        $stmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($product && $qty <= $product['stock']) {
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] == $pid) {
                    $item['quantity'] = $qty;
                    break;
                }
            }
        } else {
            $_SESSION['flash'] = "Cannot set quantity to $qty. Only " . ($product['stock'] ?? 0) . " available.";
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

$subtotal = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
}
$shipping = $subtotal > 0 ? 15.00 : 0;
$tax = $subtotal * 0.07;
$total = $subtotal + $shipping + $tax;

$page_title = 'Your Cart';
include 'includes/header.php';
?>

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
                                            src="<?= htmlspecialchars(!empty($item['image']) ? $item['image'] : 'https://via.placeholder.com/80') ?>"
                                            alt="<?= htmlspecialchars($item['name']) ?>" />
                                    <div>
                                        <div class="cart-row-name"><?= htmlspecialchars($item['name']) ?></div>
                                        <?php if (!empty($item['variant'])): ?>
                                            <div class="cart-row-variant"><?= htmlspecialchars($item['variant']) ?></div>
                                        <?php endif; ?>
                                        <form action="shopping-cart.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="remove_id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="cart-row-remove">
                                                <span class="material-symbols-outlined" style="font-size: 1rem;">delete</span>
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
                            <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
                            <p class="summary-secure">Secure checkout powered by AuraCommerce.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
