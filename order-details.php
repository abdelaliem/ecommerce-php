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
include 'includes/header.php';
?>

    <div class="dashboard-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Dashboard</h2>
                <p>Manage your account</p>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php?tab=orders" class="side-link active">
                    <span class="material-symbols-outlined">package_2</span> Orders
                </a>
                <a href="dashboard.php?tab=account" class="side-link">
                    <span class="material-symbols-outlined">person</span> Account Info
                </a>
                <a href="chat.php" class="side-link">
                    <span class="material-symbols-outlined">chat</span> Support Chat
                </a>
            </nav>
        </div>

        <div class="main-content">
            <a href="dashboard.php?tab=orders" style="display: inline-flex; align-items: center; gap: 0.5rem; color: #575f67; text-decoration: none; margin-bottom: 1.5rem; font-size: 0.875rem; font-weight: 500;">
                <span class="material-symbols-outlined" style="font-size: 1.2rem;">arrow_back</span> Back to Orders
            </a>

            <div class="order-details-card">
                <div class="od-header">
                    <div>
                        <h2>Order <?= $o_id_str ?></h2>
                        <p>Placed on <?= date('F d, Y h:i A', strtotime($order['created_at'])) ?></p>
                    </div>
                    <div class="od-status <?= $order['status'] === 'delivered' ? 'delivered' : '' ?>">
                        <?= htmlspecialchars($order['status']) ?>
                    </div>
                </div>

                <div class="od-items">
                    <?php foreach ($items as $item): ?>
                        <div class="od-item">
                            <img src="<?= htmlspecialchars($item['image'] ?: 'https://via.placeholder.com/150') ?>" alt="Product" class="od-item-img">
                            <div class="od-item-info">
                                <div class="od-item-title"><?= htmlspecialchars($item['name']) ?></div>
                                <div class="od-item-meta">Qty: <?= $item['quantity'] ?> &times; $<?= number_format($item['price'], 2) ?></div>
                            </div>
                            <div class="od-item-price">
                                $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="od-summary">
                    <div class="od-summary-row">
                        <span>Total Amount</span>
                        <span>$<?= number_format($order['total'], 2) ?></span>
                    </div>
                    <div class="od-summary-row total">
                        <span>Total</span>
                        <span>$<?= number_format($order['total'], 2) ?></span>
                    </div>
                </div>

                <div class="od-address">
                    <h3>Shipping Address</h3>
                    <p><?= nl2br(htmlspecialchars($order['address'])) ?></p>
                </div>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
