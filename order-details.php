<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$order_id) {
    header('Location: dashboard.php?tab=orders');
    exit;
}

// Fetch order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header('Location: dashboard.php?tab=orders');
    exit;
}

// Fetch items
$items_stmt = $conn->prepare("SELECT oi.*, p.image, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$o_id_str = '#AC-' . str_pad($order['id'], 4, '0', STR_PAD_LEFT);
$page_title = "Order $o_id_str";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - AuraCommerce</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>

    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="nav-container">
            <a class="brand" href="home.php">AuraCommerce</a>
            <div class="nav-links">
                <a class="nav-link" href="home.php">Home</a>
                <a class="nav-link" href="shop.php">Shop</a>
            </div>
            <div class="user-actions">
                <a href="dashboard.php" style="border-bottom: 2px solid var(--primary); padding-bottom: 20px; margin-bottom: -22px;">Dashboard</a>
                <?php if (isAdmin()): ?>
                        <a href="Admin/home.php" class="admin-btn">Admin</a>
                <?php endif; ?>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>


    <div class="dashboard-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Dashboard</h2>
                <p>Manage your account</p>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php?tab=orders" class="side-link active">
                    <i class="bi bi-box"></i> Orders
                </a>
                <a href="dashboard.php?tab=account" class="side-link">
                    <i class="bi bi-person"></i> Account Info
                </a>
                <a href="chat.php" class="side-link">
                    <i class="bi bi-chat-dots"></i> Support Chat
                </a>
            </nav>
        </div>

        <div class="main-content">
            <a href="dashboard.php?tab=orders" class="btn btn-link" style="margin-bottom: 1.5rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>

            <div class="order-card" style="padding: 2rem;">
                <div class="order-card-header" style="border-bottom: 1px solid var(--border); padding-bottom: 1.5rem; margin-bottom: 1.5rem; border: none; padding-left: 0; padding-right: 0;">
                    <div>
                        <div class="order-id-row">
                            <span class="order-id">Order <?= $o_id_str ?></span>
                            <?php
                            $status_colors = [
                                'pending' => 'badge-pending',
                                'processing' => 'badge-processing',
                                'delivered' => 'badge-delivered',
                            ];
                            $badge_class = $status_colors[$order['status']] ?? 'badge-delivered';
                            ?>
                            <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($order['status']) ?></span>
                        </div>
                        <p class="order-date">Placed on <?= date('F d, Y h:i A', strtotime($order['created_at'])) ?></p>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1.5rem; margin-bottom: 2rem;">
                    <?php foreach ($items as $item): ?>
                        <div style="display: flex; gap: 1rem; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
                            <div class="item-box">
                                <?php if ($item['image']): ?>
                                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="">
                                <?php else: ?>
                                        <i class="bi bi-image" style="color: #d1d5db; font-size: 1.5rem;"></i>
                                <?php endif; ?>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: var(--text-dark);"><?= htmlspecialchars($item['name']) ?></div>
                                <div style="font-size: 0.875rem; color: var(--secondary);">Qty: <?= $item['quantity'] ?> &times; $<?= number_format($item['price'], 2) ?></div>
                            </div>
                            <div style="font-weight: 700; color: var(--text-dark);">
                                $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="background: var(--bg); border-radius: 8px; padding: 1.5rem; display: flex; flex-direction: column; gap: 0.75rem;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.875rem; color: var(--secondary);">
                        <span>Subtotal</span>
                        <span>$<?= number_format($order['total'], 2) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 1.125rem; font-weight: 700; color: var(--text-dark); border-top: 1px solid var(--border); padding-top: 0.75rem;">
                        <span>Total</span>
                        <span>$<?= number_format($order['total'], 2) ?></span>
                    </div>
                </div>

                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.5rem;">Shipping Address</h3>
                    <p style="font-size: 0.875rem; color: var(--secondary); line-height: 1.5;"><?= nl2br(htmlspecialchars($order['address'])) ?></p>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
