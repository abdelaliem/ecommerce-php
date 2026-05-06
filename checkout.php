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
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal = trim($_POST['postal'] ?? '');
    $country = trim($_POST['country'] ?? '');
    
    $full_address = "$address, $city, $postal, $country";

    // Start transaction
    $conn->begin_transaction();

    try {
        // 1. Verify Stock for all items
        foreach ($_SESSION['cart'] as $item) {
            $stmt = $conn->prepare("SELECT stock, name FROM products WHERE id = ? FOR UPDATE");
            $stmt->bind_param("i", $item['id']);
            $stmt->execute();
            $prod = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$prod) {
                throw new Exception("Product " . $item['name'] . " not found.");
            }
            if ($prod['stock'] < $item['quantity']) {
                throw new Exception("Sorry, " . $prod['name'] . " is out of stock or has insufficient quantity.");
            }
        }

        // 2. Calculate totals
        $subtotal = 0;
        foreach ($_SESSION['cart'] as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $shipping = 15.00;
        $tax = $subtotal * 0.07;
        $total = $subtotal + $shipping + $tax;

        // 3. Insert order (including address)
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total, status, address, created_at) VALUES (?, ?, 'pending', ?, NOW())");
        $stmt->bind_param("ids", $user_id, $total, $full_address);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        // 4. Insert order items and update stock
        foreach ($_SESSION['cart'] as $item) {
            // Insert item
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
            $stmt->execute();
            $stmt->close();

            // Update stock
            $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->bind_param("ii", $item['quantity'], $item['id']);
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();

        // Clear cart
        $_SESSION['cart'] = [];
        $_SESSION['flash'] = 'Order #' . $order_id . ' placed successfully! Thank you.';
        header('Location: home.php');
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['flash'] = "Checkout Error: " . $e->getMessage();
        header('Location: checkout.php');
        exit;
    }
}

$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 15.00;
$tax = $subtotal * 0.07;
$total = $subtotal + $shipping + $tax;

$page_title = 'Checkout';
include 'includes/header.php';
?>

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
                            <button type="submit" name="place_order" class="btn-place-order">Place Order</button>
                            <p class="summary-secure">Secure checkout powered by AuraCommerce.</p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>
