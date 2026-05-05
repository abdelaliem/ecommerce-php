<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// Get product ID from URL
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: home.php');
    exit;
}

// Fetch product from DB
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: home.php');
    exit;
}

// Cart count from session
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title><?= htmlspecialchars($product['name']) ?> - AuraCommerce</title>
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
        
        .flash-message { background: #dcfce7; border: 1px solid #86efac; color: #15803d; padding: 0.75rem 1.5rem; text-align: center; font-size: 0.875rem; }
        
        main { flex: 1; display: flex; flex-direction: column; align-items: center; width: 100%; }
        .section { width: 100%; padding: 3rem 0; }
        .container { width: 100%; max-width: 1200px; padding: 0 1.5rem; margin: 0 auto; }
        
        .btn { display: inline-block; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: all 0.2s; }
        .btn-primary { background: #00327d; color: #fff; }
        .btn-primary:hover { background: #0047ab; }
        .btn-secondary { background: #f3f4f5; color: #191c1d; border: 1px solid #e1e3e4; }
        .btn-secondary:hover { background: #e7e8e9; }
        
        .product-detail { display: flex; flex-direction: column; gap: 3rem; }
        @media (min-width: 1024px) { .product-detail { flex-direction: row; } }
        .product-detail-image { flex: 1; }
        .product-detail-image img { width: 100%; object-fit: cover; border-radius: 4px; border: 1px solid #e1e3e4; }
        .product-detail-info { flex: 1; display: flex; flex-direction: column; gap: 1.5rem; }
        .product-detail-title { font-size: 2.5rem; font-weight: 600; color: #191c1d; line-height: 1.2; }
        .product-detail-price { font-size: 1.125rem; color: #575f67; line-height: 1.6; }
        .product-detail-description { font-size: 1rem; color: #191c1d; line-height: 1.6; }
        .product-detail-stock { font-weight: 600; }
        .stock-available { color: #15803d; }
        .stock-low { color: #ea580c; }
        .stock-unavailable { color: #dc2626; }
        .product-detail-actions { display: flex; gap: 1rem; }
        .product-detail-actions form { flex: 1; }
        .product-detail-actions button { width: 100%; padding: 0.75rem; font-size: 0.875rem; font-weight: 600; }
        
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
                <a href="shop.php" class="nav-link active">Shop</a>
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
        <!-- Product Detail Section -->
        <section class="section">
            <div class="container">
                <div class="product-detail">
                    <div class="product-detail-image">
                        <img alt="<?= htmlspecialchars($product['name']) ?>"
                            src="<?= htmlspecialchars($product['image'] ?? 'https://via.placeholder.com/600') ?>" />
                    </div>
                    <div class="product-detail-info">
                        <div>
                            <h1 class="product-detail-title"><?= htmlspecialchars($product['name']) ?></h1>
                            <p class="product-detail-price">$<?= number_format($product['price'], 2) ?></p>
                            <?php if ($product['stock'] <= 0): ?>
                                <p class="product-detail-stock stock-unavailable">Out of Stock</p>
                            <?php elseif ($product['stock'] < 5): ?>
                                <p class="product-detail-stock stock-low">Only <?= $product['stock'] ?> left in stock</p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="product-detail-description"><?= htmlspecialchars($product['description'] ?? 'No description available.') ?></p>
                        </div>
                        <div class="product-detail-actions">
                            <form action="cart-action.php" method="POST">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="action" value="add">
                                <button type="submit" class="btn btn-primary" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                    <?= $product['stock'] <= 0 ? 'Out of Stock' : 'Add to Cart' ?>
                                </button>
                            </form>
                            <a href="shop.php" class="btn btn-secondary">Back to Shop</a>
                        </div>
                    </div>
                </div>
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

