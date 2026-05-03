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
    <!-- Custom CSS for Sidebar -->
<style>
    /* Reset Link Styles */
    .custom-nav-link {
        display: flex;
        align-items: center;
        font-size: 0.95rem;
        padding: 0.65rem 1rem 0.65rem 1.5rem; /* Indent text slightly */
        color: #4b5563; /* Gray text */
        border-radius: 0 8px 8px 0; /* Rounded right side */
        transition: all 0.2s ease-in-out;
        font-weight: 500;
        margin-left: -1rem; /* Pull back to edge */
        border-left: 4px solid transparent;
    }
    
    /* Hover Effect */
    .custom-nav-link:hover {
        background-color: #f3f4f6;
        color: #111827;
    }

    /* Active State (Matches Image) */
    .active-link {
        background-color: #eff6ff !important; /* Light blue background */
        color: #2563eb !important; /* Primary blue text */
        border-left: 4px solid #2563eb; /* Blue left border */
    }
    
    /* Icon color when active */
    .active-link i {
        color: #2563eb !important;
    }
</style>
    <title>E-commerce Admin</title>
</head>
<body>
<link rel="stylesheet" href=''>
<!-- Sidebar Container -->
<div class="d-flex flex-column flex-shrink-0 p-3 bg-white border-end shadow-sm" style="width: 260px; min-height: 100vh;">
    
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
            <a href="dashboard.php" class="nav-link custom-nav-link <?= ($current_page == 'dashboard.php' || $current_page == 'index.php' || $current_page == '') ? 'active-link' : '' ?>" aria-current="page">
                <i class="bi bi-grid me-3 fs-5"></i>
                Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a href="orders.php" class="nav-link custom-nav-link <?= ($current_page == 'orders.php') ? 'active-link' : '' ?>">
                <i class="bi bi-journal-text me-3 fs-5"></i>
                Orders
            </a>
        </li>
        <li class="nav-item">
            <a href="customers.php" class="nav-link custom-nav-link <?= ($current_page == 'customers.php') ? 'active-link' : '' ?>">
                <i class="bi bi-people me-3 fs-5"></i>
                Customers
            </a>
        </li>
        <li class="nav-item">
            <a href="settings.php" class="nav-link custom-nav-link <?= ($current_page == 'settings.php') ? 'active-link' : '' ?>">
                <i class="bi bi-gear me-3 fs-5"></i>
                Settings
            </a>
        </li>
    </ul>
</div>

</body>
</html>

<!-- 
    Make sure to include these CDNs in your main layout <head> if not already present:
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
-->
