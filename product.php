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

$page_title = htmlspecialchars($product['name']);
include 'includes/header.php';
?>

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
                            <button type="submit" class="btn-add-cart">Add to Cart</button>
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

    <script>
        function swapImg(thumb) {
            document.getElementById('main-img').src = thumb.src;
            document.querySelectorAll('.detail-thumb').forEach(function(t) { t.classList.remove('active'); });
            thumb.classList.add('active');
        }
    </script>

<?php include 'includes/footer.php'; ?>
