<?php
require_once __DIR__ . '/../includes/db.php';

// ========== Handle Actions ==========

// DELETE product
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $del_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $del_stmt->bind_param('i', $delete_id);
    $del_stmt->execute();
    header("Location: inventory.php");
    exit;
}

// ADD product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category = trim($_POST['category']);
    $image = trim($_POST['image']);

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image, category) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('ssdiss', $name, $description, $price, $stock, $image, $category);
    $stmt->execute();
    header("Location: inventory.php");
    exit;
}

// EDIT product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category = trim($_POST['category']);
    $image = trim($_POST['image']);

    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, image=?, category=? WHERE id=?");
    $stmt->bind_param('ssdissi', $name, $description, $price, $stock, $image, $category, $id);
    $stmt->execute();
    header("Location: inventory.php");
    exit;
}

// ========== Fetch Data ==========
$per_page = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $per_page;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = '';
$params = [];
$types = '';

if ($search !== '') {
    $where = "WHERE name LIKE ? OR category LIKE ?";
    $search_param = "%$search%";
    $params = [$search_param, $search_param];
    $types = 'ss';
}

$count_sql = "SELECT COUNT(*) as total FROM products $where";
$count_stmt = $conn->prepare($count_sql);
if ($types) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total_products = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_products / $per_page);

$sql = "SELECT * FROM products $where ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($types) {
    $all_params = array_merge($params, [$per_page, $offset]);
    $stmt->bind_param($types . 'ii', ...$all_params);
} else {
    $stmt->bind_param('ii', $per_page, $offset);
}
$stmt->execute();
$products = $stmt->get_result();
?>
<?php include 'sidebar.php'; ?>

<!-- Main Content -->
<div style="flex: 1; padding: 30px 35px; background-color: #f8f9fa; min-height: 100vh;">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 style="font-weight: 700; color: #111827; margin-bottom: 4px;">Inventory</h2>
            <p style="color: #6b7280; font-size: 0.95rem; margin: 0;">Manage your product catalog, pricing, and current stock levels.</p>
        </div>
        <button class="btn d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addModal" style="padding: 10px 20px; border-radius: 8px; font-weight: 500; background-color: #1e3a5f; color: #fff;">
            <i class="bi bi-plus-lg"></i> Add Product
        </button>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body p-4">

            <!-- Search & Actions -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <form method="GET" class="d-flex" style="max-width: 400px; flex: 1;">
                    <div class="input-group" style="border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden;">
                        <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-0 shadow-none" placeholder="Search by name, SKU..." value="<?= htmlspecialchars($search) ?>" style="font-size: 0.9rem;">
                    </div>
                </form>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary d-flex align-items-center gap-2" style="border-radius: 8px; font-size: 0.9rem; border-color: #d1d5db;">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <button class="btn btn-outline-secondary d-flex align-items-center gap-2" style="border-radius: 8px; font-size: 0.9rem; border-color: #d1d5db;">
                        <i class="bi bi-download"></i> Export
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table align-middle" style="margin-bottom: 0;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th class="table-header">Product Name</th>
                            <th class="table-header">Category</th>
                            <th class="table-header">Price</th>
                            <th class="table-header">Stock Status</th>
                            <th class="table-header">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products->num_rows > 0): ?>
                            <?php while ($product = $products->fetch_assoc()): ?>
                                <?php
                                $stock = (int)$product['stock'];
                                if ($stock > 10) {
                                    $badge_class = 'badge-in-stock';
                                    $badge_text = "In Stock ($stock)";
                                    $dot_color = '#22c55e';
                                } elseif ($stock > 0) {
                                    $badge_class = 'badge-low-stock';
                                    $badge_text = "Low Stock ($stock)";
                                    $dot_color = '#f59e0b';
                                } else {
                                    $badge_class = 'badge-out-stock';
                                    $badge_text = "Out of Stock";
                                    $dot_color = '#ef4444';
                                }
                                ?>
                                <tr style="border-bottom: 1px solid #f3f4f6;">
                                    <td style="padding: 16px;">
                                        <div class="d-flex align-items-center gap-3">
                                            <div style="width: 48px; height: 48px; border-radius: 8px; overflow: hidden; background: #f3f4f6; flex-shrink: 0;">
                                                <?php if ($product['image']): ?>
                                                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center justify-content-center h-100"><i class="bi bi-image text-muted"></i></div>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; color: #111827;"><?= htmlspecialchars($product['name']) ?></div>
                                                <div style="color: #6b7280; font-size: 0.8rem;"><?= htmlspecialchars($product['category'] ?? 'Uncategorized') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 16px; color: #374151; font-size: 0.9rem;"><?= htmlspecialchars($product['category'] ?? '-') ?></td>
                                    <td style="padding: 16px; color: #111827; font-weight: 600;">$<?= number_format($product['price'], 2) ?></td>
                                    <td style="padding: 16px;">
                                        <span class="stock-badge <?= $badge_class ?>">
                                            <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: <?= $dot_color ?>; margin-right: 6px;"></span>
                                            <?= $badge_text ?>
                                        </span>
                                    </td>
                                    <td style="padding: 16px;">
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-light edit-btn" style="border-radius: 6px;" title="Edit"
                                                data-bs-toggle="modal" data-bs-target="#editModal"
                                                data-id="<?= $product['id'] ?>"
                                                data-name="<?= htmlspecialchars($product['name']) ?>"
                                                data-description="<?= htmlspecialchars($product['description'] ?? '') ?>"
                                                data-price="<?= $product['price'] ?>"
                                                data-stock="<?= $product['stock'] ?>"
                                                data-category="<?= htmlspecialchars($product['category'] ?? '') ?>"
                                                data-image="<?= htmlspecialchars($product['image'] ?? '') ?>">
                                                <i class="bi bi-pencil" style="color: #6b7280;"></i>
                                            </button>
                                            <a href="inventory.php?delete=<?= $product['id'] ?>" class="btn btn-sm btn-light" style="border-radius: 6px;" title="Delete" onclick="return confirm('Are you sure you want to delete this product?')">
                                                <i class="bi bi-trash" style="color: #ef4444;"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5" style="color: #6b7280;">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i> No products found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3" style="border-top: 1px solid #e5e7eb;">
                <span style="color: #6b7280; font-size: 0.85rem;">Showing <?= $offset + 1 ?> to <?= min($offset + $per_page, $total_products) ?> of <?= $total_products ?> products</span>
                <nav>
                    <ul class="pagination pagination-sm mb-0 gap-1">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link custom-page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item">
                            <a class="page-link custom-page-link <?= ($i == $page) ? 'active-page' : '' ?>" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                            <a class="page-link custom-page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ========== ADD PRODUCT MODAL ========== -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 12px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: #111827;">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: #374151; font-size: 0.9rem;">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control custom-input" placeholder="e.g. Elite Wireless Headphones" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: #374151; font-size: 0.9rem;">Description</label>
                        <textarea name="description" class="form-control custom-input" rows="2" placeholder="Short description..."></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color: #374151; font-size: 0.9rem;">Price ($) <span class="text-danger">*</span></label>
                            <input type="number" name="price" class="form-control custom-input" step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color: #374151; font-size: 0.9rem;">Stock <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control custom-input" min="0" placeholder="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: #374151; font-size: 0.9rem;">Category</label>
                        <input type="text" name="category" class="form-control custom-input" placeholder="e.g. Electronics">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: #374151; font-size: 0.9rem;">Image URL</label>
                        <input type="url" name="image" class="form-control custom-input" placeholder="https://example.com/image.jpg">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 8px; border-color: #d1d5db;">Cancel</button>
                    <button type="submit" class="btn" style="background-color: #1e3a5f; color: #fff; border-radius: 8px;">
                        <i class="bi bi-plus-lg me-1"></i> Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========== EDIT PRODUCT MODAL ========== -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 12px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" style="color: #111827;">Edit Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: #374151; font-size: 0.9rem;">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit-name" class="form-control custom-input" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: #374151; font-size: 0.9rem;">Description</label>
                        <textarea name="description" id="edit-description" class="form-control custom-input" rows="2"></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color: #374151; font-size: 0.9rem;">Price ($) <span class="text-danger">*</span></label>
                            <input type="number" name="price" id="edit-price" class="form-control custom-input" step="0.01" min="0" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold" style="color: #374151; font-size: 0.9rem;">Stock <span class="text-danger">*</span></label>
                            <input type="number" name="stock" id="edit-stock" class="form-control custom-input" min="0" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: #374151; font-size: 0.9rem;">Category</label>
                        <input type="text" name="category" id="edit-category" class="form-control custom-input">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="color: #374151; font-size: 0.9rem;">Image URL</label>
                        <input type="url" name="image" id="edit-image" class="form-control custom-input">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" style="border-radius: 8px; border-color: #d1d5db;">Cancel</button>
                    <button type="submit" class="btn" style="background-color: #1e3a5f; color: #fff; border-radius: 8px;">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .table-header { color: #6b7280; font-weight: 600; font-size: 0.85rem; padding: 12px 16px; text-transform: uppercase; letter-spacing: 0.5px; border-top: none; }
    .stock-badge { display: inline-flex; align-items: center; padding: 5px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 500; }
    .badge-in-stock { background: #f0f9ff; color: #1e40af; border: 1px solid #bfdbfe; }
    .badge-low-stock { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; }
    .badge-out-stock { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .custom-page-link { border: 1px solid #d1d5db !important; color: #374151 !important; border-radius: 6px !important; padding: 6px 12px !important; font-size: 0.85rem !important; background: #fff !important; }
    .active-page { background-color: #1e3a5f !important; color: #fff !important; border-color: #1e3a5f !important; }
    .custom-input { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 14px; font-size: 0.9rem; transition: border-color 0.2s; }
    .custom-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
</style>

<!-- JavaScript: Fill Edit Modal with product data -->
<script>
document.querySelectorAll('.edit-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('edit-id').value = this.dataset.id;
        document.getElementById('edit-name').value = this.dataset.name;
        document.getElementById('edit-description').value = this.dataset.description;
        document.getElementById('edit-price').value = this.dataset.price;
        document.getElementById('edit-stock').value = this.dataset.stock;
        document.getElementById('edit-category').value = this.dataset.category;
        document.getElementById('edit-image').value = this.dataset.image;
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
