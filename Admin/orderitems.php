<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin(1);

// Pagination
$per_page = 5;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

$offset = ($page - 1) * $per_page;

// Count total
$count_sql = "SELECT COUNT(*) as total FROM order_items";
$count_result = $conn->query($count_sql);
$total_items = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_items / $per_page);

// Fetch data
$sql = "SELECT 
            oi.id,
            oi.order_id,
            p.name AS product_name,
            u.name AS customer_name,
            oi.quantity,
            oi.price,
            o.created_at
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        LEFT JOIN orders o ON oi.order_id = o.id
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY oi.id DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $per_page, $offset);
$stmt->execute();
$items = $stmt->get_result();
?>

<?php include 'sidebar.php'; ?>

<!-- Main Content -->
<div style="flex: 1; padding: 30px 35px; background-color: #f8f9fa; min-height: 100vh;">

    <!-- Header -->
    <div class="mb-4">
        <h2 style="font-weight: 700; color: #111827;">Order Items</h2>
        <p style="color: #6b7280;">View all items inside orders.</p>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th class="order-th">Item ID</th>
                            <th class="order-th">Order</th>
                            <th class="order-th">Customer</th>
                            <th class="order-th">Product</th>
                            <th class="order-th">Qty</th>
                            <th class="order-th">Price</th>
                            <th class="order-th">Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($items->num_rows > 0): ?>
                            <?php while ($item = $items->fetch_assoc()): ?>
                                <tr style="border-bottom: 1px solid #f3f4f6;">

                                    <td style="padding: 18px 20px; font-weight: 700;">
                                        #ITM-<?= str_pad($item['id'], 4, '0', STR_PAD_LEFT) ?>
                                    </td>

                                    <td style="padding: 18px 20px;">
                                        #ORD-<?= str_pad($item['order_id'], 4, '0', STR_PAD_LEFT) ?>
                                    </td>

                                    <td style="padding: 18px 20px;">
                                        <?= htmlspecialchars($item['customer_name']) ?>
                                    </td>

                                    <td style="padding: 18px 20px;">
                                        <?= htmlspecialchars($item['product_name']) ?>
                                    </td>

                                    <td style="padding: 18px 20px;">
                                        <?= $item['quantity'] ?>
                                    </td>

                                    <td style="padding: 18px 20px; font-weight: 600;">
                                        $<?= number_format($item['price'], 2) ?>
                                    </td>

                                    <td style="padding: 18px 20px; color: #6b7280;">
                                        <?= date('M d, Y', strtotime($item['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5" style="color: #6b7280;">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i> No items found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
             <div class="d-flex justify-content-between align-items-center px-4 py-3"
                style="border-top: 1px solid #e5e7eb;">
                <div class="d-flex justify-content-between align-items-center px-4 py-3"
                    style="border-top: 1px solid #e5e7eb;">
                    <span style="color: #6b7280; font-size: 0.85rem;">
                        Showing <?= $total_items > 0 ? $offset + 1 : 0 ?>
                        to <?= min($offset + $per_page, $total_items) ?>
                        of <?= $total_items ?> entries
                    </span>

                    <nav>
                        <ul class="pagination pagination-sm mb-0 gap-1">

                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link custom-page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                            </li>

                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link custom-page-link" href="?page=<?= $page + 1 ?>">Next</a>
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

    =======.order-th {
        color: #6b7280;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 14px 20px;
        border-top: none;
        background-color: #fff;
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