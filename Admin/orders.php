<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin(1);

// ========== Fetch Data ==========

$per_page = 5;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $per_page;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

$conditions = [];
$params = [];
$types = '';

if ($search !== '') {
    $conditions[] = "(o.id LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if ($filter_status !== '') {
    $conditions[] = "o.status = ?";
    $params[] = $filter_status;
    $types .= 's';
}

$where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

// Count total
$count_sql = "SELECT COUNT(*) as total FROM orders o LEFT JOIN users u ON o.user_id = u.id $where";
$count_stmt = $conn->prepare($count_sql);
if ($types)
    $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total_orders = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $per_page);

// Fetch orders
$sql = "SELECT o.id, u.name AS customer_name, o.created_at, o.total, o.status
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        $where
        ORDER BY o.id DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($types) {
    $all_params = array_merge($params, [$per_page, $offset]);
    $stmt->bind_param($types . 'ii', ...$all_params);
} else {
    $stmt->bind_param('ii', $per_page, $offset);
}
$stmt->execute();
$orders = $stmt->get_result();
?>
<?php include 'sidebar.php'; ?>

<!-- Main Content -->
<div style="flex: 1; padding: 30px 35px; background-color: #f8f9fa; min-height: 100vh;">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 style="font-weight: 700; color: #111827; margin-bottom: 4px;">Orders</h2>
            <p style="color: #6b7280; font-size: 0.95rem; margin: 0;">Manage and track all customer orders.</p>
        </div>
        <!-- Search & Filter -->
        <form method="GET" class="d-flex align-items-center gap-2">
            <div class="input-group"
                style="border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; width: 260px;">
                <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-0 shadow-none"
                    placeholder="Search orders..." value="<?= htmlspecialchars($search) ?>" style="font-size: 0.9rem;">
            </div>
            <div class="dropdown">
                <button class="btn btn-outline-secondary d-flex align-items-center gap-2" type="button"
                    data-bs-toggle="dropdown"
                    style="border-radius: 8px; font-size: 0.9rem; border-color: #d1d5db; padding: 9px 14px;">
                    <i class="bi bi-funnel"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end"
                    style="border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <li><a class="dropdown-item <?= $filter_status === '' ? 'active' : '' ?>"
                            href="?search=<?= urlencode($search) ?>">All</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item <?= $filter_status === 'pending' ? 'active' : '' ?>"
                            href="?status=pending&search=<?= urlencode($search) ?>">Pending</a></li>
                    <li><a class="dropdown-item <?= $filter_status === 'processing' ? 'active' : '' ?>"
                            href="?status=processing&search=<?= urlencode($search) ?>">Processing</a></li>
                    <li><a class="dropdown-item <?= $filter_status === 'delivered' ? 'active' : '' ?>"
                            href="?status=delivered&search=<?= urlencode($search) ?>">Delivered</a></li>
                </ul>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th class="order-th">Order ID</th>
                            <th class="order-th">Customer Name</th>
                            <th class="order-th">Date</th>
                            <th class="order-th">Amount</th>
                            <th class="order-th">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($orders->num_rows > 0): ?>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                                <?php
                                $status = $order['status'];
                                switch ($status) {
                                    case 'processing':
                                        $badge_class = 'badge-processing';
                                        break;
                                    case 'delivered':
                                        $badge_class = 'badge-delivered';
                                        break;
                                    default:
                                        $badge_class = 'badge-pending';
                                }
                                $date = date('M d, Y', strtotime($order['created_at']));
                                ?>
                                <tr style="border-bottom: 1px solid #f3f4f6;">
                                    <td style="padding: 18px 20px; font-weight: 700; color: #111827;">#ORD-
                                        <?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?>
                                    </td>
                                    <td style="padding: 18px 20px; color: #374151;">
                                        <?= htmlspecialchars($order['customer_name'] ?? 'Unknown') ?>
                                    </td>
                                    <td style="padding: 18px 20px; color: #6b7280;">
                                        <?= $date ?>
                                    </td>
                                    <td style="padding: 18px 20px; font-weight: 600; color: #111827;">$
                                        <?= number_format($order['total'], 2) ?>
                                    </td>
                                    <td style="padding: 18px 20px;">
                                        <span class="status-badge <?= $badge_class ?>">
                                            <?= ucfirst(htmlspecialchars($status)) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5" style="color: #6b7280;">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i> No orders found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center px-4 py-3"
                style="border-top: 1px solid #e5e7eb;">
                <span style="color: #6b7280; font-size: 0.85rem;">
                    Showing
                    <?= $total_orders > 0 ? $offset + 1 : 0 ?> to
                    <?= min($offset + $per_page, $total_orders) ?> of
                    <?= $total_orders ?> entries
                </span>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link custom-page-link"
                                href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($filter_status) ?>">Previous</a>
                        </li>
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link custom-page-link"
                                href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($filter_status) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<style>
    .order-th {
        color: #6b7280;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 14px 20px;
        border-top: none;
        background-color: #fff;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 500;
    }

    .badge-processing {
        background: #eff6ff;
        color: #2563eb;
        border: 1px solid #bfdbfe;
    }

    .badge-delivered {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .badge-pending {
        background: #fff;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .custom-page-link {
        border: 1px solid #d1d5db !important;
        color: #374151 !important;
        border-radius: 6px !important;
        padding: 6px 14px !important;
        font-size: 0.85rem !important;
        background: #fff !important;
    }

    .custom-page-link:hover {
        background: #f9fafb !important;
    }

    .page-item.disabled .custom-page-link {
        color: #9ca3af !important;
        background: #f9fafb !important;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>