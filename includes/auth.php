<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me']);
    if (count($parts) === 2) {
        list($id, $token) = $parts;
        if (is_numeric($id)) {
            $stmt = $conn->prepare("SELECT id, name, role, remember_token FROM users WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if ($user['remember_token'] !== null && hash_equals($user['remember_token'], hash('sha256', $token))) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];
                }
            }
            $stmt->close();
        }
    }
}

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
    function requireUser(int $depth = 0) {
        requireLogin($depth);
    }
}

// Admin  → admin@admin.com      / admin123
// Admin2 → superadmin@admin.com / password123
// User   → sara@example.com     / password123