<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$user_id = $_SESSION['user_id'];
$tab = $_GET['tab'] ?? 'account';

// Ensure the new columns exist (silent fail if they already do)
try {
    $conn->query("ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER profile_pic");
    $conn->query("ALTER TABLE users ADD COLUMN address_line1 VARCHAR(255) NULL AFTER phone");
    $conn->query("ALTER TABLE users ADD COLUMN city VARCHAR(100) NULL AFTER address_line1");
    $conn->query("ALTER TABLE users ADD COLUMN state VARCHAR(100) NULL AFTER city");
    $conn->query("ALTER TABLE users ADD COLUMN zip_code VARCHAR(20) NULL AFTER state");
    $conn->query("ALTER TABLE users ADD COLUMN country VARCHAR(100) NULL AFTER zip_code");
} catch (mysqli_sql_exception $e) {
    // Columns likely already exist, ignore the error
}

$success_msg = '';
$error_msg = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $name = $first_name . ' ' . $last_name;
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    $address_line1 = trim($_POST['address_line1']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip_code = trim($_POST['zip_code']);
    $country = trim($_POST['country']);

    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir))
            mkdir($upload_dir, 0777, true);

        $file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['profile_pic']['name']));
        $target_file = $upload_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
                $pic_stmt = $conn->prepare("UPDATE users SET profile_pic=? WHERE id=?");
                $pic_stmt->bind_param("si", $target_file, $user_id);
                $pic_stmt->execute();
            }
        } else {
            $error_msg = "Invalid image format. Only JPG, PNG, GIF, and WEBP are allowed.";
        }
    }

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, address_line1=?, city=?, state=?, zip_code=?, country=? WHERE id=?");
    $stmt->bind_param("ssssssssi", $name, $email, $phone, $address_line1, $city, $state, $zip_code, $country, $user_id);

    if ($stmt->execute()) {
        $success_msg = "Account details updated successfully.";
        $_SESSION['user_name'] = $name;
    } else {
        $error_msg = "Failed to update account details.";
    }

    // Password update
    if (!empty($_POST['new_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $pw_stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
        $pw_stmt->bind_param("i", $user_id);
        $pw_stmt->execute();
        $user_data = $pw_stmt->get_result()->fetch_assoc();

        if (password_verify($current_password, $user_data['password'])) {
            if ($new_password === $confirm_password) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $upd_pw = $conn->prepare("UPDATE users SET password=? WHERE id=?");
                $upd_pw->bind_param("si", $hashed, $user_id);
                $upd_pw->execute();
                $success_msg = "Account details and password updated successfully.";
            } else {
                $error_msg = "New passwords do not match.";
            }
        } else {
            $error_msg = "Current password is incorrect.";
        }
    }
}

// Fetch User Data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$name_parts = explode(' ', $user['name'], 2);
$first_name = $name_parts[0];
$last_name = $name_parts[1] ?? '';

// Fetch Orders if tab is orders
$orders = [];
if ($tab === 'orders') {
    $search = isset($_GET['search']) ? '%' . trim($_GET['search']) . '%' : null;
    $sql = "SELECT * FROM orders WHERE user_id = ?";
    if ($search) {
        $sql .= " AND id LIKE ?";
    }
    $sql .= " ORDER BY created_at DESC";

    $order_stmt = $conn->prepare($sql);
    if ($search) {
        $order_stmt->bind_param("is", $user_id, $search);
    } else {
        $order_stmt->bind_param("i", $user_id);
    }

    $order_stmt->execute();
    $orders_res = $order_stmt->get_result();
    while ($row = $orders_res->fetch_assoc()) {
        // Fetch items for this order
        $items_stmt = $conn->prepare("SELECT oi.*, p.image, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $items_stmt->bind_param("i", $row['id']);
        $items_stmt->execute();
        $row['items'] = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $orders[] = $row;
    }
}

// Default profile pic
$avatar_url = $user['profile_pic'] ? htmlspecialchars($user['profile_pic']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=0047AB&color=fff';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - AuraCommerce</title>
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
                <a href="dashboard.php"
                    style="border-bottom: 2px solid var(--primary); padding-bottom: 20px; margin-bottom: -22px;">Dashboard</a>
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
                <a href="?tab=orders" class="side-link <?= $tab === 'orders' ? 'active' : '' ?>">
                    <i class="bi bi-box"></i> Orders
                </a>
                <a href="?tab=account" class="side-link <?= $tab === 'account' ? 'active' : '' ?>">
                    <i class="bi bi-person"></i> Account Info
                </a>
                <a href="chat.php" class="side-link">
                    <i class="bi bi-chat-dots"></i> Support Chat
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <?php if ($success_msg): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success_msg) ?>
                    </div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                    <div class="alert alert-error">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error_msg) ?>
                    </div>
            <?php endif; ?>

            <?php if ($tab === 'account'): ?>
                    <h1 class="page-title">Account Info & Settings</h1>
                    <p class="page-subtitle">Review your personal details, shipping preferences, and security settings.</p>

                    <form method="POST" enctype="multipart/form-data" class="card">

                        <!-- Personal Information -->
                        <h3 class="section-title">Personal Information</h3>
                        <div class="form-section">
                            <div class="profile-img-col">
                                <img src="<?= $avatar_url ?>" class="profile-img" alt="Profile" id="profilePreview">
                                <label class="btn-link" style="cursor: pointer; display: inline-block;">
                                    Change Photo
                                    <input type="file" name="profile_pic" accept="image/*" style="display: none;"
                                        onchange="if(this.files[0]) document.getElementById('profilePreview').src = window.URL.createObjectURL(this.files[0])">
                                </label>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="first_name" class="form-input"
                                        value="<?= htmlspecialchars($first_name) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-input"
                                        value="<?= htmlspecialchars($last_name) ?>" required>
                                </div>
                                <div class="form-group form-col-full">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-input"
                                        value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                                <div class="form-group form-col-full">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-input"
                                        value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <h3 class="section-title">Shipping Address</h3>
                        <div class="form-section">
                            <div class="form-grid" style="width:100%">
                                <div class="form-group form-col-full">
                                    <label class="form-label">Street Address</label>
                                    <input type="text" name="address_line1" class="form-input"
                                        value="<?= htmlspecialchars($user['address_line1'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">City</label>
                                    <input type="text" name="city" class="form-input"
                                        value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">State / Province</label>
                                    <input type="text" name="state" class="form-input"
                                        value="<?= htmlspecialchars($user['state'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">ZIP / Postal Code</label>
                                    <input type="text" name="zip_code" class="form-input"
                                        value="<?= htmlspecialchars($user['zip_code'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Country</label>
                                    <select name="country" class="form-input">
                                        <option value="United States" <?= ($user['country'] ?? '') === 'United States' ? 'selected' : '' ?>>United States</option>
                                        <option value="Canada" <?= ($user['country'] ?? '') === 'Canada' ? 'selected' : '' ?>>
                                            Canada</option>
                                        <option value="United Kingdom" <?= ($user['country'] ?? '') === 'United Kingdom' ? 'selected' : '' ?>>United Kingdom</option>
                                        <option value="Australia" <?= ($user['country'] ?? '') === 'Australia' ? 'selected' : '' ?>>Australia</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Change Password -->
                        <h3 class="section-title">Change Password</h3>
                        <div class="form-section" style="border:none; margin:0; padding:0;">
                            <div class="form-grid" style="width:100%">
                                <div class="form-group form-col-full">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-input">
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="reset" class="btn btn-outline">Cancel</button>
                            <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
            <?php endif; ?>


            <?php if ($tab === 'orders'): ?>
                    <div class="orders-header">
                        <div>
                            <h1 class="page-title">Order History</h1>
                            <p class="page-subtitle" style="margin-bottom:0;">Review your recent purchases and their fulfillment
                                status.</p>
                        </div>

                    </div>

                    <?php if (empty($orders)): ?>
                            <div class="empty-state">
                                <i class="bi bi-box-seam"></i>
                                <h3>No orders found</h3>
                                <p>Looks like you haven't placed any orders yet.</p>
                                <a href="home.php" class="btn btn-primary">Start Shopping</a>
                            </div>
                    <?php else: ?>
                            <!-- Recent Order (Top Card) -->
                            <?php
                            $recent_order = $orders[0];
                            $order_id_str = '#AC-' . str_pad($recent_order['id'], 4, '0', STR_PAD_LEFT);
                            $status_colors = [
                                'pending' => 'badge-pending',
                                'processing' => 'badge-processing',
                                'delivered' => 'badge-delivered',
                            ];
                            $badge_class = $status_colors[$recent_order['status']] ?? 'badge-delivered';
                            ?>
                            <div class="order-card">
                                <div class="order-card-header">
                                    <div>
                                        <div class="order-id-row">
                                            <span class="order-id">Order <?= $order_id_str ?></span>
                                            <span
                                                class="badge <?= $badge_class ?>"><?= htmlspecialchars($recent_order['status']) ?></span>
                                        </div>
                                        <p class="order-date">Placed on <?= date('F d, Y', strtotime($recent_order['created_at'])) ?>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="order-total-label">Total Amount</p>
                                        <p class="order-total-value">$<?= number_format($recent_order['total'], 2) ?></p>
                                    </div>
                                </div>

                                <div class="order-items">
                                    <?php
                                    $display_limit = 2;
                                    $count = 0;
                                    foreach ($recent_order['items'] as $item):
                                        if ($count >= $display_limit)
                                            break;
                                        ?>
                                            <div class="item-box">
                                                <?php if ($item['image']): ?>
                                                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="">
                                                <?php else: ?>
                                                        <i class="bi bi-image" style="color: #d1d5db; font-size: 1.5rem;"></i>
                                                <?php endif; ?>
                                            </div>
                                            <?php
                                            $count++;
                                    endforeach;

                                    $remaining = count($recent_order['items']) - $display_limit;
                                    if ($remaining > 0):
                                        ?>
                                            <div class="item-box more">
                                                +<?= $remaining ?> items
                                            </div>
                                    <?php endif; ?>
                                </div>

                                <div class="order-footer">
                                    <div class="order-shipping">
                                        <i class="bi bi-truck" style="color:#9ca3af; font-size:1.1rem;"></i>
                                        <?= $recent_order['status'] === 'delivered' ? 'Delivered to' : 'Shipping to' ?>
                                        <?= htmlspecialchars($user['address_line1'] ?: $recent_order['address']) ?>
                                    </div>
                                    <a href="order-details.php?id=<?= $recent_order['id'] ?>" class="btn btn-primary">
                                        View Details <i class="bi bi-arrow-right" style="margin-left:0.4rem;"></i>
                                    </a>
                                </div>
                            </div>

                            <!-- Older Orders -->
                            <?php if (count($orders) > 1): ?>
                                    <h3 class="older-orders-title">Older Orders</h3>
                                    <div class="older-orders-list">
                                        <?php for ($i = 1; $i < count($orders); $i++):
                                            $o = $orders[$i];
                                            $o_id_str = '#AC-' . str_pad($o['id'], 4, '0', STR_PAD_LEFT);
                                            $item_count = count($o['items']);
                                            ?>
                                                <div class="older-order-row">
                                                    <div class="older-info">
                                                        <div class="older-icon">
                                                            <i class="bi bi-box-seam"></i>
                                                        </div>
                                                        <div class="older-details">
                                                            <h4>Order <?= $o_id_str ?></h4>
                                                            <p><?= date('F d, Y', strtotime($o['created_at'])) ?> • <?= $item_count ?>
                                                                Item<?= $item_count !== 1 ? 's' : '' ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="older-actions-group">
                                                        <div class="older-price-col">
                                                            <div class="older-price">$<?= number_format($o['total'], 2) ?></div>
                                                            <div class="older-status"><?= htmlspecialchars($o['status']) ?></div>
                                                        </div>
                                                        <a href="order-details.php?id=<?= $o['id'] ?>" class="btn btn-outline">View Details</a>
                                                    </div>
                                                </div>
                                        <?php endfor; ?>
                                    </div>
                            <?php endif; ?>

                    <?php endif; ?>

            <?php endif; ?>

        </div>
    </div>

</body>

</html>