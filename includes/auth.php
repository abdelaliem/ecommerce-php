<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

if (!function_exists('requireLogin')) {
    /**
     * Redirect to login.php if the user is not authenticated.
     * Pass the depth from webroot so the path resolves correctly.
     * depth=0  → root files (login.php)
     * depth=1  → subdirectory files (Admin/login.php)
     */
    function requireLogin(int $depth = 0) {
        if (!isLoggedIn()) {
            $prefix = str_repeat('../', $depth);
            header('Location: ' . $prefix . 'login.php');
            exit();
        }
    }
}

if (!function_exists('requireAdmin')) {
    function requireAdmin(int $depth = 0) {
        if (!isLoggedIn()) {
            $prefix = str_repeat('../', $depth);
            header('Location: ' . $prefix . 'login.php');
            exit();
        }
        if (!isAdmin()) {
            $prefix = str_repeat('../', $depth);
            header('Location: ' . $prefix . 'home.php');
            exit();
        }
    }
}

if (!function_exists('requireUser')) {
    /** Blocks admin users from accessing user-only pages. */
    function requireUser(int $depth = 0) {
        requireLogin($depth);
    }
}

// Admin  → admin@admin.com      / admin123
// Admin2 → superadmin@admin.com / password123
// User   → sara@example.com     / password123