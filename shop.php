<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

// Fetch all products from DB
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Get unique categories
$categories = [];
foreach ($products as $p) {
    if ($p['category'] && !in_array($p['category'], $categories)) {
        $categories[] = $p['category'];
    }
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
    <title>Shop - AuraCommerce</title>
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
        .nav-link.active { color: #00327d; }
        .nav-right { display: flex; align-items: center; gap: 1rem; }
        .cart-badge { background: #dc2626; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold; }
        .user-greeting { font-size: 0.875rem; color: #434653; }
        
        .flash-message { background: #dcfce7; border: 1px solid #86efac; color: #15803d; padding: 0.75rem 1.5rem; text-align: center; font-size: 0.875rem; }
        
        main { flex: 1; display: flex; flex-direction: column; align-items: center; width: 100%; }
        .section { width: 100%; padding: 3rem 0; }
        .section-header { border-bottom: 1px solid #e1e3e4; }
        .container { width: 100%; max-width: 1200px; padding: 0 1.5rem; margin: 0 auto; }
        .section-title { font-size: 2.5rem; font-weight: 600; color: #191c1d; margin-bottom: 0.5rem; }
        .section-subtitle { font-size: 1.125rem; color: #575f67; line-height: 1.6; }
        
        .btn { display: inline-block; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: all 0.2s; }
        .btn-primary { background: #00327d; color: #fff; }
        .btn-primary:hover { background: #0047ab; }
        .btn-secondary { background: #f3f4f5; color: #191c1d; border: 1px solid #e1e3e4; }
        .btn-secondary:hover { background: #e7e8e9; }
        
        .filters { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .filter-btn { padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; border: 1px solid #e1e3e4; border-radius: 4px; background: #fff; color: #191c1d; cursor: pointer; transition: all 0.2s; }
        .filter-btn:hover { border-color: #00327d; color: #00327d; }
        .filter-btn.active { background: #00327d; color: #fff; border-color: #00327d; }
        
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 2rem; }
        @media (min-width: 640px) { .product-grid { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); } }
        @media (min-width: 1024px) { .product-grid { grid-template-columns: repeat(4, 1fr); } }
        
        .product-card { display: flex; flex-direction: column; background: #fff; border: 1px solid #e1e3e4; border-radius: 4px; overflow: hidden; transition: all 0.3s; }
        .product-card:hover { border-color: #00327d; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .product-image-wrap { position: relative; width: 100%; aspect-ratio: 1; overflow: hidden; background: #f3f4f5; }
        .product-image { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
        .product-card:hover .product-image { transform: scale(1.05); }
        
        .product-badge { position: absolute; top: 0.5rem; left: 0.5rem; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 600; border-radius: 2px; color: white; }
        .badge-stock-out { background: #dc2626; }
        .badge-stock-low { background: #ea580c; }
        .badge-category { background: #00327d; }
        
        .product-info { padding: 1.25rem; flex: 1; display: flex; flex-direction: column; }
        .product-name { font-size: 0.875rem; font-weight: 500; color: #191c1d; }
        .product-name:hover { color: #00327d; }
        .product-price { font-size: 1rem; font-weight: 600; color: #575f67; margin-top: 0.5rem; margin-bottom: 1rem; }
        .product-actions { margin-top: auto; }
        .product-actions button { width: 100%; padding: 0.5rem; font-size: 0.75rem; font-weight: 500; }
        
        .no-results { text-align: center; padding: 3rem 1.5rem; color: #575f67; display: none; }
        .no-results.show { display: block; }
        
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
                <a href="shop.php" class="nav-link active">Shop</a>
            </div>
            <div class="nav-right">
                <a href="shopping-cart.php" class="cart-icon" title="Cart">
                    <?php if ($cart_count > 0): ?><span class="cart-badge"><?= $cart_count ?></span><?php endif; ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>

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
                    <a href="login.php" class="btn btn-primary">Login</a>
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
        <!-- Shop Header -->
        <section class="section section-header">
            <div class="container">
                <h1 class="section-title">Shop</h1>
                <p class="section-subtitle">Our complete minimalist catalog — <?= count($products) ?> products.</p>
            </div>
        </section>

        <!-- Category Filters -->
        <section class="section" style="padding-top: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color);">
            <div class="container">
                <div class="filters" id="category-filters">
                    <button class="filter-btn active" data-filter="all">
                        All (<?= count($products) ?>)
                    </button>
                    <?php foreach ($categories as $cat): ?>
                        <?php $count = count(array_filter($products, fn($p) => $p['category'] === $cat)); ?>
                        <button class="filter-btn" data-filter="<?= htmlspecialchars($cat) ?>">
                            <?= htmlspecialchars($cat) ?> (<?= $count ?>)
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Product Grid Section -->
        <section class="section">
            <div class="container">
                <div id="no-results" class="no-results">
                    <p>No products found in this category.</p>
                </div>

                <?php if (empty($products)): ?>
                    <div class="empty-state">
                        <p class="empty-state-message">No products available at the moment.</p>
                    </div>
                <?php else: ?>
                    <div class="product-grid" id="product-grid">
                        <?php foreach ($products as $p): ?>
                            <div class="product-card" data-category="<?= htmlspecialchars($p['category'] ?? '') ?>">
                                <a href="product.php?id=<?= $p['id'] ?>" class="product-image-wrap">
                                    <img alt="<?= htmlspecialchars($p['name']) ?>" class="product-image"
                                        src="<?= htmlspecialchars($p['image'] ?? 'https://via.placeholder.com/400') ?>" />
                                    <?php if ($p['stock'] <= 0): ?>
                                        <span class="product-badge badge-stock-out">OUT OF STOCK</span>
                                    <?php elseif ($p['stock'] < 5): ?>
                                        <span class="product-badge badge-stock-low">LOW STOCK</span>
                                    <?php else: ?>
                                        <span class="product-badge badge-category"><?= htmlspecialchars($p['category'] ?? 'NEW') ?></span>
                                    <?php endif; ?>
                                </a>
                                <div class="product-info">
                                    <a href="product.php?id=<?= $p['id'] ?>" class="product-name"><?= htmlspecialchars($p['name']) ?></a>
                                    <p class="product-price">$<?= number_format($p['price'], 2) ?></p>
                                    <div class="product-actions">
                                        <form action="cart-action.php" method="POST">
                                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                            <input type="hidden" name="action" value="add">
                                            <button type="submit" class="btn btn-primary" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>
                                                <?= $p['stock'] <= 0 ? 'Out of Stock' : 'Add to Cart' ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
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

    <script>
        (function () {
            const filterBtns = document.querySelectorAll('#category-filters .filter-btn');
            const cards = document.querySelectorAll('#product-grid .product-card');
            const noResults = document.getElementById('no-results');

            filterBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    // Update active button styling
                    filterBtns.forEach(function (b) {
                        b.classList.remove('active');
                    });
                    this.classList.add('active');

                    var filter = this.dataset.filter;
                    var visible = 0;

                    cards.forEach(function (card) {
                        var match = filter === 'all' || card.dataset.category === filter;
                        if (match) {
                            card.style.display = '';
                            card.style.opacity = '0';
                            card.style.transform = 'translateY(16px)';
                            (function (idx) {
                                setTimeout(function () {
                                    card.style.transition = 'opacity .3s ease, transform .3s ease';
                                    card.style.opacity = '1';
                                    card.style.transform = 'translateY(0)';
                                }, idx * 60);
                            })(visible);
                            visible++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    noResults.classList.toggle('show', visible === 0);
                });
            });
        })();
    </script>
</body>

</html>

