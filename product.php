<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: home.php'); exit; }

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$product) { header('Location: home.php'); exit; }

$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) { $cart_count += $item['quantity']; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title><?= htmlspecialchars($product['name']) ?> - AuraCommerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="assets/aura.css" />
</head>
<body>
    <nav class="navbar">
        <div class="navbar-inner">
            <a href="home.php" class="navbar-brand">AuraCommerce</a>
            <div class="nav-menu">
                <a href="home.php" class="nav-item">Home</a>
                <a href="shop.php" class="nav-item active">Shop</a>

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
                    <a href="login.php" class="nav-logout" style="color: #00327d; font-weight: 600;">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash"><?= htmlspecialchars($_SESSION['flash']) ?><?php unset($_SESSION['flash']); ?></div>
    <?php endif; ?>

    <main>
        <section class="detail-section">
            <div class="detail-container">
                <!-- Breadcrumb -->
                <div class="detail-breadcrumb">
                    <div class="breadcrumb">
                        <a href="shop.php">Shop</a>
                        <span>/</span>
                        <?php if (!empty($product['category'])): ?>
                            <a href="shop.php"><?= htmlspecialchars($product['category']) ?></a>
                            <span>/</span>
                        <?php endif; ?>
                        <span class="breadcrumb-active">Product Detail</span>
                    </div>
                </div>

                <div class="detail-grid">
                    <!-- Gallery -->
                    <div class="detail-gallery">
                        <img class="detail-main-img" id="main-img"
                             src="<?= htmlspecialchars($product['image'] ?? 'https://via.placeholder.com/600') ?>"
                             alt="<?= htmlspecialchars($product['name']) ?>" />
                        <div class="detail-thumbs">
                            <img class="detail-thumb active" onclick="swapImg(this)"
                                 src="<?= htmlspecialchars($product['image'] ?? 'https://via.placeholder.com/100') ?>"
                                 alt="View 1" />
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="detail-info">
                        <?php if (!empty($product['category'])): ?>
                            <span class="detail-badge"><?= htmlspecialchars($product['category']) ?></span>
                        <?php else: ?>
                            <span class="detail-badge">New Arrival</span>
                        <?php endif; ?>

                        <h1 class="detail-title"><?= htmlspecialchars($product['name']) ?></h1>
                        <div class="detail-price">$<?= number_format($product['price'], 2) ?></div>

                        <p class="detail-desc"><?= htmlspecialchars($product['description'] ?? 'No description available.') ?></p>

                        <hr class="detail-divider" />

                        <?php if ($product['stock'] > 0): ?>
                            <form action="cart-action.php" method="POST" class="detail-actions">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="action" value="add">
                                <button type="submit" class="btn-add-cart">Add to Cart &#x1F6D2;</button>
                            </form>
                            <form action="#" method="POST">
                                <button type="button" class="btn-wishlist">Save to Wishlist</button>
                            </form>
                        <?php else: ?>
                            <button class="btn-add-cart" disabled>Out of Stock</button>
                        <?php endif; ?>

                        <hr class="detail-divider" />

                        <!-- Specs -->
                        <div class="specs-title">Specifications</div>
                        <table class="specs-table">
                            <?php if (!empty($product['category'])): ?>
                            <tr>
                                <td>Category</td>
                                <td><?= htmlspecialchars($product['category']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td>Availability</td>
                                <td><?= $product['stock'] > 0 ? $product['stock'] . ' in stock' : 'Out of stock' ?></td>
                            </tr>
                            <tr>
                                <td>SKU</td>
                                <td>#<?= str_pad($product['id'], 5, '0', STR_PAD_LEFT) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
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

    <script>
        function swapImg(thumb) {
            document.getElementById('main-img').src = thumb.src;
            document.querySelectorAll('.detail-thumb').forEach(function(t) { t.classList.remove('active'); });
            thumb.classList.add('active');
        }
    </script>
</body>
</html>
