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

// Cart count from session
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>AuraCommerce - Minimalist E-Commerce</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-primary-fixed-variant": "#00419e",
                        "background": "#f8f9fa",
                        "surface": "#f8f9fa",
                        "surface-variant": "#e1e3e4",
                        "outline": "#737784",
                        "on-background": "#191c1d",
                        "outline-variant": "#c3c6d5",
                        "on-primary": "#ffffff",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f3f4f5",
                        "surface-container-high": "#e7e8e9",
                        "on-primary-container": "#a5bdff",
                        "on-secondary": "#ffffff",
                        "primary-container": "#0047ab",
                        "on-surface": "#191c1d",
                        "primary": "#00327d",
                        "secondary": "#575f67",
                        "error": "#ba1a1a"
                    },
                    borderRadius: { "DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem" },
                    spacing: { "base": "8px", "stack-lg": "3rem", "container-max": "1200px", "gutter": "1.5rem", "section-padding": "5rem", "stack-md": "1.5rem", "stack-sm": "0.5rem" },
                    fontFamily: { "h3": ["Inter"], "h1": ["Inter"], "body-md": ["Inter"], "body-lg": ["Inter"], "h2": ["Inter"], "label-sm": ["Inter"] },
                    fontSize: {
                        "h3": ["1.5rem", { "lineHeight": "1.3", "fontWeight": "600" }],
                        "h1": ["2.5rem", { "lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "600" }],
                        "body-md": ["1rem", { "lineHeight": "1.5", "fontWeight": "400" }],
                        "body-lg": ["1.125rem", { "lineHeight": "1.6", "fontWeight": "400" }],
                        "h2": ["2rem", { "lineHeight": "1.2", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "label-sm": ["0.875rem", { "lineHeight": "1.4", "letterSpacing": "0.05em", "fontWeight": "500" }]
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>

<body class="bg-surface-container-lowest text-on-surface font-body-md min-h-screen flex flex-col antialiased">

    <!-- TopNavBar -->
    <nav class="bg-white sticky top-0 w-full z-50 border-b border-gray-200">
        <div
            class="max-w-7xl mx-auto px-6 h-16 flex justify-between items-center font-sans text-sm font-medium tracking-tight">
            <a class="text-xl font-bold text-blue-700 hover:text-blue-700 transition-colors"
                href="home.php">AuraCommerce</a>
            <div class="hidden md:flex items-center gap-8 h-full pt-5">
                <a class="text-blue-700 border-b-2 border-blue-700 pb-5" href="home.php">Home</a>
                <a class="text-gray-600 pb-5 hover:text-blue-700 transition-colors" href="home.php">Shop</a>
            </div>
            <div class="flex items-center gap-4 text-blue-700">
                <a href="shopping-cart.php" class="relative hover:text-blue-900 transition-colors active:scale-95">
                    <span class="material-symbols-outlined">shopping_cart</span>
                    <?php if ($cart_count > 0): ?>
                        <span
                            class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
                <?php if (isLoggedIn()): ?>
                    <span class="text-sm text-gray-600">Hi, <strong>
                            <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </strong></span>
                    <?php if (isAdmin()): ?>
                        <a href="Admin/home.php"
                            class="text-xs bg-blue-700 text-white px-3 py-1 rounded hover:bg-blue-800">Admin</a>
                    <?php else: ?>
                        <a href="dashboard.php"
                            class="text-sm font-semibold text-blue-700 hover:underline flex items-center gap-1">
                            <span class="material-symbols-outlined text-[22px]">account_circle</span>
                        </a>
                    <?php endif; ?>
                    <a href="logout.php" class="text-sm text-red-500 hover:underline">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="hover:text-blue-900 transition-colors">
                        <span class="material-symbols-outlined">account_circle</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Flash message -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-800 px-6 py-3 text-center text-sm">
            <?= htmlspecialchars($_SESSION['flash']) ?>
            <?php unset($_SESSION['flash']); ?>
        </div>
    <?php endif; ?>

    <main class="flex-grow flex flex-col items-center w-full">
        <!-- Hero Section -->
        <section
            class="w-full max-w-[1200px] mx-auto px-6 py-section-padding flex flex-col md:flex-row items-center gap-stack-lg border-b border-surface-variant">
            <div class="flex-1 flex flex-col items-start gap-stack-md">
                <h1 class="font-h1 text-h1 text-on-surface">Elevate Your Everyday Essentials</h1>
                <p class="font-body-lg text-body-lg text-secondary max-w-lg">
                    Discover a curated collection of minimalist products designed for modern living. Precision
                    engineering meets uncompromising aesthetics.
                </p>
                <div class="flex gap-4 pt-4">
                    <a class="bg-primary text-on-primary font-label-sm text-label-sm px-8 py-4 rounded-DEFAULT hover:bg-on-primary-fixed-variant transition-colors"
                        href="#products">
                        Shop Collection
                    </a>
                </div>
            </div>
            <div class="flex-1 w-full relative">
                <div
                    class="aspect-[4/3] bg-surface-container-low rounded-lg overflow-hidden border border-outline-variant">
                    <img alt="Hero" class="w-full h-full object-cover mix-blend-multiply opacity-90"
                        src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800" />
                </div>
            </div>
        </section>

        <!-- Product Grid Section -->
        <section id="products" class="w-full max-w-[1200px] mx-auto px-6 py-section-padding flex flex-col gap-stack-lg">
            <div class="flex justify-between items-end border-b border-surface-variant pb-4">
                <div>
                    <h2 class="font-h2 text-h2 text-on-surface">All Products</h2>
                    <p class="font-body-md text-body-md text-secondary mt-2">Browse our full catalog.</p>
                </div>
            </div>

            <?php if (empty($products)): ?>
                <p class="text-secondary text-center py-12">No products available at the moment.</p>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($products as $p): ?>
                        <div class="group flex flex-col gap-3">
                            <a href="product.php?id=<?= $p['id'] ?>"
                                class="block aspect-square bg-surface-container-low rounded-DEFAULT border border-surface-variant overflow-hidden relative">
                                <img alt="<?= htmlspecialchars($p['name']) ?>"
                                    class="w-full h-full object-cover mix-blend-multiply group-hover:scale-105 transition-transform duration-500"
                                    src="<?= htmlspecialchars($p['image'] ?? 'https://via.placeholder.com/400') ?>" />
                                <?php if ($p['stock'] <= 0): ?>
                                    <div
                                        class="absolute top-2 left-2 bg-red-600 text-white px-2 py-1 font-label-sm text-xs rounded-sm">
                                        OUT OF STOCK</div>
                                <?php elseif ($p['stock'] < 5): ?>
                                    <div
                                        class="absolute top-2 left-2 bg-orange-500 text-white px-2 py-1 font-label-sm text-xs rounded-sm">
                                        LOW STOCK</div>
                                <?php else: ?>
                                    <div
                                        class="absolute top-2 left-2 bg-primary text-on-primary px-2 py-1 font-label-sm text-xs rounded-sm">
                                        <?= htmlspecialchars($p['category'] ?? 'NEW') ?>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="flex flex-col">
                                <a href="product.php?id=<?= $p['id'] ?>"
                                    class="font-label-sm text-label-sm text-on-surface hover:text-primary truncate"><?= htmlspecialchars($p['name']) ?></a>
                                <p class="font-body-md text-body-md text-secondary mt-1">$<?= number_format($p['price'], 2) ?>
                                </p>
                                <form action="cart-action.php" method="POST" class="mt-2">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <input type="hidden" name="action" value="add">
                                    <button type="submit" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>
                                        class="w-full py-2 text-xs font-medium bg-primary text-on-primary rounded-DEFAULT hover:bg-on-primary-fixed-variant transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                        <?= $p['stock'] <= 0 ? 'Out of Stock' : 'Add to Cart' ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer class="bg-gray-50 w-full mt-auto border-t border-gray-200">
        <div class="max-w-7xl mx-auto py-12 px-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="text-base font-semibold text-gray-900">AuraCommerce</div>
            <div class="flex flex-wrap justify-center gap-6 text-xs text-gray-500">
                <a class="hover:text-blue-700 underline underline-offset-4" href="#">Privacy Policy</a>
                <a class="hover:text-blue-700 underline underline-offset-4" href="#">Terms of Service</a>
            </div>
            <div class="text-xs text-gray-500">© <?= date('Y') ?> AuraCommerce. All rights reserved.</div>
        </div>
    </footer>
</body>

</html>