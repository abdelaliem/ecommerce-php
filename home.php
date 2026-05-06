<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Fetch all products from DB
$products = [];
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$page_title = 'Home';
include 'includes/header.php';
?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-inner">
            <div class="hero-text">
                <h1 class="hero-title">Elevate Your Everyday Essentials</h1>
                <p class="hero-subtitle">
                    Discover a curated collection of minimalist products designed for modern living. Precision
                    engineering meets uncompromising aesthetics.
                </p>
                <div class="hero-actions">
                    <a class="btn-primary" href="#products">Shop Collection</a>
                </div>
            </div>
            <div class="hero-image-wrap">
                <div class="hero-image-box">
                    <img alt="Hero" src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800" />
                </div>
            </div>
        </div>
    </section>

    <!-- Product Grid Section -->
    <section id="products" class="products-section">
        <div class="products-inner">
            <div class="products-header">
                <div>
                    <h2 class="products-title">All Products</h2>
                    <p class="products-subtitle">Browse our full catalog.</p>
                </div>
            </div>

            <?php if (empty($products)): ?>
                <div class="empty-state">No products available at the moment.</div>
            <?php else: ?>
                <div class="product-grid">
                    <?php foreach ($products as $p): ?>
                        <div class="product-card">
                            <a href="product.php?id=<?= $p['id'] ?>" class="product-thumb">
                                <img alt="<?= htmlspecialchars($p['name']) ?>"
                                    src="<?= htmlspecialchars($p['image'] ?? 'https://via.placeholder.com/400') ?>" />
                                <?php if ($p['stock'] <= 0): ?>
                                    <div class="product-badge badge-out">OUT OF STOCK</div>
                                <?php elseif ($p['stock'] < 5): ?>
                                    <div class="product-badge badge-low">LOW STOCK</div>
                                <?php else: ?>
                                    <div class="product-badge badge-new">
                                        <?= htmlspecialchars($p['category'] ?? 'NEW') ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="product-info">
                                <a href="product.php?id=<?= $p['id'] ?>" class="product-name"><?= htmlspecialchars($p['name']) ?></a>
                                <p class="product-price">$<?= number_format($p['price'], 2) ?></p>
                                <form action="cart-action.php" method="POST" class="product-atc-form">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="action" value="add">
                                    <button type="submit" <?= $p['stock'] <= 0 ? 'disabled' : '' ?> class="btn-atc">
                                        <?= $p['stock'] <= 0 ? 'Out of Stock' : 'Add to Cart' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

<?php include 'includes/footer.php'; ?>