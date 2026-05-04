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
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
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
    <style>
        :root {
            --primary: #0047AB;
            --primary-dark: #003a8c;
            --secondary: #6C757D;
            --bg: #F8F9FA;
            --text-dark: #111827;
            --border: #e5e7eb;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text-dark);
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Top Nav */
        .top-nav {
            background: #fff;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
            height: 4rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            height: 100%;
            align-items: center;
            display: none;
        }

        @media(min-width: 768px) {
            .nav-links {
                display: flex;
            }
        }

        .nav-link {
            color: var(--secondary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            height: 100%;
            display: flex;
            align-items: center;
            border-bottom: 2px solid transparent;
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .nav-link.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .user-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .user-actions a {
            color: var(--primary);
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
        }

        .user-actions .admin-btn {
            background: var(--primary);
            color: #fff;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.75rem;
        }

        .user-actions .admin-btn:hover {
            background: var(--primary-dark);
        }

        .user-actions .logout-btn {
            color: #dc2626;
            font-weight: 400;
        }

        .user-actions .logout-btn:hover {
            text-decoration: underline;
        }

        /* Layout */
        .dashboard-layout {
            max-width: 1280px;
            margin: 2rem auto;
            padding: 0 1.5rem;
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .sidebar {
            width: 100%;
            max-width: 250px;
            flex-shrink: 0;
        }

        .main-content {
            flex: 1;
            min-width: 0;
        }

        /* Sidebar */
        .sidebar-header {
            margin-bottom: 1.5rem;
            padding: 0 1.5rem;
        }

        .sidebar-header h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .sidebar-header p {
            font-size: 0.875rem;
            color: var(--secondary);
            margin-top: 0.25rem;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--border);
            min-height: 400px;
        }

        .side-link {
            padding: 0.75rem 1.5rem;
            color: #4b5563;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }

        .side-link:hover {
            background: #f3f4f6;
            color: var(--text-dark);
        }

        .side-link.active {
            background: #eef2ff;
            color: var(--primary);
            border-left-color: var(--primary);
            font-weight: 600;
        }

        /* Page Header */
        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: var(--secondary);
            margin-bottom: 2rem;
        }

        /* Forms & Cards */
        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
        }

        .form-section {
            border-bottom: 1px solid var(--border);
            padding-bottom: 2rem;
            margin-bottom: 2rem;
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .profile-img-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            width: 120px;
        }

        .profile-img {
            width: 96px;
            height: 96px;
            border-radius: 12px;
            object-fit: cover;
            border: 1px solid var(--border);
        }

        .btn-link {
            color: var(--primary);
            background: none;
            border: none;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            padding: 0;
        }

        .btn-link:hover {
            text-decoration: underline;
        }

        .form-grid {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-col-full {
            grid-column: span 2;
        }

        @media(max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-col-full {
                grid-column: span 1;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .form-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #4b5563;
        }

        .form-input {
            padding: 0.625rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.875rem;
            color: var(--text-dark);
            outline: none;
            transition: border-color 0.2s;
            font-family: inherit;
            width: 100%;
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 71, 171, 0.1);
        }

        select.form-input {
            background-color: #fff;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border);
        }

        .btn {
            padding: 0.5rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .btn-outline {
            background: #fff;
            border: 1px solid #d1d5db;
            color: #374151;
        }

        .btn-outline:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        /* Orders Tab */
        .orders-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .orders-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .search-wrapper {
            position: relative;
        }

        .search-wrapper i {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        .search-input {
            padding: 0.5rem 1rem 0.5rem 2.25rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.875rem;
            outline: none;
            width: 250px;
        }

        .search-input:focus {
            border-color: var(--primary);
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: #fff;
        }

        .empty-state i {
            font-size: 2.5rem;
            color: #9ca3af;
            margin-bottom: 1rem;
            display: block;
        }

        .empty-state h3 {
            font-size: 1.125rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--secondary);
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .order-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 2.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.2s;
        }

        .order-card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .order-card-header {
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 1rem;
        }

        .order-id-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.25rem;
        }

        .order-id {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .badge {
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-pending {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-processing {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-delivered {
            background: #e5e7eb;
            color: #374151;
        }

        .order-date {
            font-size: 0.875rem;
            color: var(--secondary);
        }

        .order-total-label {
            font-size: 0.875rem;
            color: var(--secondary);
            font-weight: 500;
            margin-bottom: 0.15rem;
            text-align: right;
        }

        .order-total-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-align: right;
        }

        .order-items {
            padding: 1.5rem 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .item-box {
            width: 96px;
            height: 96px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 0.5rem;
        }

        .item-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            mix-blend-mode: multiply;
        }

        .item-box.more {
            color: var(--secondary);
            font-weight: 500;
            font-size: 0.875rem;
        }

        .order-footer {
            padding: 1rem 2rem;
            background: var(--bg);
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .order-shipping {
            font-size: 0.875rem;
            color: #4b5563;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .older-orders-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--border);
        }

        .older-orders-list {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .older-order-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 0;
            border-bottom: 1px solid var(--border);
            transition: background 0.2s;
        }

        .older-order-row:hover {
            background: #f9fafb;
            margin: 0 -0.5rem;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
            border-radius: 8px;
        }

        .older-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .older-icon {
            width: 48px;
            height: 48px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 1.25rem;
        }

        .older-details h4 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.15rem;
        }

        .older-details p {
            font-size: 0.875rem;
            color: var(--secondary);
            font-weight: 500;
        }

        .older-actions-group {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .older-price-col {
            text-align: right;
        }

        .older-price {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1rem;
            margin-bottom: 0.15rem;
        }

        .older-status {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .load-more-wrapper {
            text-align: center;
            margin-top: 2rem;
        }

        @media(max-width: 768px) {

            .order-card-header,
            .older-order-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-total-label,
            .order-total-value,
            .older-price-col {
                text-align: left;
                margin: 0;
            }

            .older-actions-group {
                width: 100%;
                margin-top: 1rem;
                justify-content: space-between;
            }
        }
    </style>
</head>

<body>

    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="nav-container">
            <a class="brand" href="home.php">AuraCommerce</a>
            <div class="nav-links">
                <a class="nav-link" href="home.php">Home</a>
                <a class="nav-link" href="home.php">Shop</a>
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
                <a href="?tab=account" class="side-link">
                    <i class="bi bi-gear"></i> Settings
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
                                <input type="file" name="profile_pic" accept="image/*" style="display: none;" onchange="if(this.files[0]) document.getElementById('profilePreview').src = window.URL.createObjectURL(this.files[0])">
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
                            <a href="#" class="btn btn-primary">
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
                                        <a href="#" class="btn btn-outline">View Details</a>
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