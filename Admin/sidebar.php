<?php
// Get the current page name to highlight the active menu item dynamically
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>E-commerce Admin</title>
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }

        .custom-nav-link {
            display: flex;
            align-items: center;
            font-size: 0.95rem;
            padding: 0.65rem 1rem 0.65rem 1.5rem;
            color: #4b5563;
            border-radius: 0 8px 8px 0;
            transition: all 0.2s ease-in-out;
            font-weight: 500;
            margin-left: -1rem;
            border-left: 4px solid transparent;
        }

        .custom-nav-link:hover {
            background-color: #f3f4f6;
            color: #111827;
        }

        .active-link {
            background-color: #eff6ff !important;
            color: #2563eb !important;
            border-left: 4px solid #2563eb;
        }

        .active-link i {
            color: #2563eb !important;
        }
    </style>
</head>
<body>
<div class="d-flex">
<!-- Sidebar Container -->
<div class="d-flex flex-column flex-shrink-0 p-3 bg-white border-end shadow-sm" style="width: 260px; min-height: 100vh; position: sticky; top: 0;">
    
    <!-- Logo & Title -->
    <a href="#" class="d-flex align-items-center mb-4 mt-2 me-md-auto link-dark text-decoration-none px-3">
        <div>
            <span class="fs-6 fw-bold text-primary" style="color: #2563eb !important; letter-spacing: 0.5px;">Admin Panel</span><br>
            <small class="text-muted" style="font-size: 0.75rem; color: #6b7280 !important;">Management Console</small>
        </div>
    </a>
    
    <!-- Navigation Links -->
    <ul class="nav nav-pills flex-column mb-auto gap-1">
        <li class="nav-item">
            <a href="home.php" class="nav-link custom-nav-link <?= ($current_page == 'home.php') ? 'active-link' : '' ?>" aria-current="page">
                <i class="bi bi-grid me-3 fs-5"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="users.php" class="nav-link custom-nav-link <?= ($current_page == 'users.php') ? 'active-link' : '' ?>">
                <i class="bi bi-people me-3 fs-5"></i>
                Users
            </a>
        </li>
        <li class="nav-item">
            <a href="inventory.php" class="nav-link custom-nav-link <?= ($current_page == 'inventory.php') ? 'active-link' : '' ?>">
                <i class="bi bi-box me-3 fs-5"></i>
                Inventory
            </a>
        </li>
        <li class="nav-item">
            <a href="orders.php" class="nav-link custom-nav-link <?= ($current_page == 'orders.php') ? 'active-link' : '' ?>">
                <i class="bi bi-journal-text me-3 fs-5"></i>
                Orders
            </a>
        </li>
        <li class="nav-item">
            <a href="orderitems.php" class="nav-link custom-nav-link <?= ($current_page == 'orderitems.php') ? 'active-link' : '' ?>">
                <i class="bi bi-journal-text me-3 fs-5"></i>
                Orderitems
            </a>
        </li>
    </ul>
</div>
