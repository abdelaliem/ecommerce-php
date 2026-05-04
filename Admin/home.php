<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin(1);
// --- Dashboard Queries ---

// Total Sales
$salesResult = $conn->query("SELECT COALESCE(SUM(total),0) as total_sales FROM orders");
$totalSales = $salesResult->fetch_assoc()['total_sales'];

// Active Orders (pending + processing)
$activeResult = $conn->query("SELECT COUNT(*) as cnt FROM orders WHERE status IN ('pending','processing')");
$activeOrders = $activeResult->fetch_assoc()['cnt'];

// New Customers (role = 'user', not admin)
$custResult = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE role = 'user'");
$newCustomers = $custResult->fetch_assoc()['cnt'];

// Total Products
$prodResult = $conn->query("SELECT COUNT(*) as cnt FROM products");
$totalProducts = $prodResult->fetch_assoc()['cnt'];

// Total Orders
$totalOrdersResult = $conn->query("SELECT COUNT(*) as cnt FROM orders");
$totalOrdersCount = $totalOrdersResult->fetch_assoc()['cnt'];

// Recent Orders (last 5)
$recentOrders = $conn->query("
    SELECT o.id, o.total, o.status, o.created_at, u.name as customer_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
");

// Top Products (by order_items quantity)
$topProducts = $conn->query("
    SELECT p.name, p.category, p.image, p.price, COALESCE(SUM(oi.quantity),0) as sold
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    GROUP BY p.id
    ORDER BY sold DESC
    LIMIT 5
");

// Recent Customers (role = 'user')
$recentCustomers = $conn->query("
    SELECT name, email, created_at, profile_pic
    FROM users
    WHERE role = 'user'
    ORDER BY created_at DESC
    LIMIT 5
");

function avatarColor($name)
{
    $c = ['#4F46E5', '#7C3AED', '#DB2777', '#DC2626', '#EA580C', '#D97706', '#16A34A', '#0891B2', '#2563EB', '#9333EA'];
    return $c[abs(crc32($name)) % count($c)];
}
function initials($name)
{
    $parts = explode(' ', trim($name));
    return strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}
?>
<?php include 'sidebar.php'; ?>
<style>
    .main-content {
        flex: 1;
        padding: 2rem 2.5rem;
        min-height: 100vh;
        overflow-y: auto;
    }

    .page-header {
        margin-bottom: 2rem;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 0.95rem;
        margin-top: 0.35rem;
    }

    .btn-report {
        background: #2563eb;
        color: #fff;
        border: none;
        padding: 0.55rem 1.25rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        transition: background 0.2s;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-report:hover {
        background: #1d4ed8;
        color: #fff;
    }

    /* Stat Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        transition: box-shadow 0.2s;
    }

    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .stat-icon.blue {
        background: #dbeafe;
        color: #2563eb;
    }

    .stat-icon.green {
        background: #d1fae5;
        color: #059669;
    }

    .stat-icon.purple {
        background: #ede9fe;
        color: #7c3aed;
    }

    .stat-icon.orange {
        background: #ffedd5;
        color: #ea580c;
    }

    .stat-label {
        font-size: 0.8rem;
        font-weight: 500;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.35rem;
    }

    .stat-value {
        font-size: 1.65rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.2;
    }

    .stat-trend {
        font-size: 0.75rem;
        margin-top: 0.35rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .stat-trend.up {
        color: #059669;
    }

    .stat-trend.down {
        color: #dc2626;
    }

    /* Cards */
    .card-section {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.25rem;
    }

    .card-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #111827;
    }

    .card-link {
        font-size: 0.8rem;
        color: #2563eb;
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .card-link:hover {
        text-decoration: underline;
    }

    /* Orders Table */
    .orders-table {
        width: 100%;
        border-collapse: collapse;
    }

    .orders-table thead th {
        padding: 0.6rem 0.75rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
    }

    .orders-table tbody td {
        padding: 0.75rem;
        font-size: 0.875rem;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .orders-table tbody tr:last-child td {
        border-bottom: none;
    }

    .orders-table tbody tr:hover {
        background: #f9fafb;
    }

    .status-badge {
        padding: 0.2rem 0.65rem;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 600;
        display: inline-block;
    }

    .status-completed,
    .status-delivered {
        background: #d1fae5;
        color: #065f46;
    }

    .status-processing {
        background: #fef3c7;
        color: #92400e;
    }

    .status-pending {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-cancelled {
        background: #fef2f2;
        color: #991b1b;
    }

    /* Bottom Grid */
    .bottom-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 992px) {
        .bottom-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Product List */
    .product-item {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.65rem 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .product-item:last-child {
        border-bottom: none;
    }

    .product-img {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        object-fit: cover;
        background: #f3f4f6;
        flex-shrink: 0;
    }

    .product-info {
        flex: 1;
    }

    .product-name {
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
    }

    .product-cat {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .product-sales {
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
        text-align: right;
    }

    /* Customer List */
    .customer-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.65rem 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .customer-item:last-child {
        border-bottom: none;
    }

    .customer-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 0.75rem;
        flex-shrink: 0;
    }

    .customer-info {
        flex: 1;
    }

    .customer-name {
        font-size: 0.875rem;
        font-weight: 600;
        color: #111827;
    }

    .customer-email {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .customer-date {
        font-size: 0.75rem;
        color: #9ca3af;
        text-align: right;
        white-space: nowrap;
    }

    .empty-note {
        text-align: center;
        color: #9ca3af;
        padding: 2rem;
        font-size: 0.875rem;
    }
</style>

<!-- Main Content -->
<div class="main-content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-start page-header">
        <div>
            <h1 class="page-title">Dashboard Overview</h1>
            <p class="page-subtitle">Welcome back, Admin. Here's what's happening today.</p>
        </div>
        <a href="#" class="btn-report" onclick="window.print(); return false;">
            <i class="bi bi-file-earmark-bar-graph"></i> Generate Report
        </a>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-wallet2"></i></div>
            <div>
                <div class="stat-label">Total Sales</div>
                <div class="stat-value">$
                    <?= number_format($totalSales, 2) ?>
                </div>
                <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> +12.5% from last month</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-box-seam"></i></div>
            <div>
                <div class="stat-label">Active Orders</div>
                <div class="stat-value">
                    <?= number_format($activeOrders) ?>
                </div>
                <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> +5.2% from last week</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-label">New Customers</div>
                <div class="stat-value">
                    <?= number_format($newCustomers) ?>
                </div>
                <div class="stat-trend down"><i class="bi bi-arrow-down-short"></i> -1.4% from yesterday</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="bi bi-tag"></i></div>
            <div>
                <div class="stat-label">Total Products</div>
                <div class="stat-value">
                    <?= number_format($totalProducts) ?>
                </div>
                <div class="stat-trend up"><i class="bi bi-arrow-up-short"></i> +3 new this week</div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card-section">
        <div class="card-header">
            <h2 class="card-title">Recent Orders</h2>
            <a href="orders.php" class="card-link">View All <i class="bi bi-arrow-right"></i></a>
        </div>
        <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $recentOrders->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight:600; color:#2563eb;">#ORD-
                                <?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($order['customer_name'] ?? 'Unknown') ?>
                            </td>
                            <td>
                                <?= date('M d, Y', strtotime($order['created_at'])) ?>
                            </td>
                            <td style="font-weight:600;">$
                                <?= number_format($order['total'], 2) ?>
                            </td>
                            <td><span class="status-badge status-<?= $order['status'] ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-note"><i class="bi bi-inbox"
                    style="font-size:2rem; display:block; margin-bottom:0.5rem;"></i>No orders yet.</div>
        <?php endif; ?>
    </div>

    <!-- Bottom Grid: Top Products + Recent Customers -->
    <div class="bottom-grid">
        <!-- Top Products -->
        <div class="card-section">
            <div class="card-header">
                <h2 class="card-title">Top Products</h2>
                <a href="inventory.php" class="card-link">View All <i class="bi bi-arrow-right"></i></a>
            </div>
            <?php if ($topProducts && $topProducts->num_rows > 0): ?>
                <?php while ($prod = $topProducts->fetch_assoc()): ?>
                    <div class="product-item">
                        <?php if ($prod['image']): ?>
                            <img src="<?= htmlspecialchars($prod['image']) ?>" alt="" class="product-img">
                        <?php else: ?>
                            <div class="product-img" style="display:flex;align-items:center;justify-content:center;color:#9ca3af;">
                                <i class="bi bi-box"></i>
                            </div>
                        <?php endif; ?>
                        <div class="product-info">
                            <div class="product-name">
                                <?= htmlspecialchars($prod['name']) ?>
                            </div>
                            <div class="product-cat">
                                <?= htmlspecialchars($prod['category'] ?? 'Uncategorized') ?>
                            </div>
                        </div>
                        <div class="product-sales">
                            <?= $prod['sold'] ?> sold
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-note">No products yet.</div>
            <?php endif; ?>
        </div>

        <!-- Recent Customers -->
        <div class="card-section">
            <div class="card-header">
                <h2 class="card-title">Recent Customers</h2>
                <a href="users.php" class="card-link">View All <i class="bi bi-arrow-right"></i></a>
            </div>
            <?php if ($recentCustomers && $recentCustomers->num_rows > 0): ?>
                <?php while ($cust = $recentCustomers->fetch_assoc()): ?>
                    <div class="customer-item">
                        <div class="customer-avatar" style="background:<?= avatarColor($cust['name']) ?>">
                            <?= initials($cust['name']) ?>
                        </div>
                        <div class="customer-info">
                            <div class="customer-name">
                                <?= htmlspecialchars($cust['name']) ?>
                            </div>
                            <div class="customer-email">
                                <?= htmlspecialchars($cust['email']) ?>
                            </div>
                        </div>
                        <div class="customer-date">
                            <?= date('M d, Y', strtotime($cust['created_at'])) ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-note">No customers yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div><!-- /main-content -->
</div><!-- /d-flex wrapper from sidebar -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>