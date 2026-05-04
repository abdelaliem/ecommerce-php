<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php');
    exit;
}

$action = $_POST['action'] ?? '';
$product_id = (int) ($_POST['product_id'] ?? 0);

if ($action === 'add' && $product_id > 0) {
    // Fetch product to check stock
    $stmt = $conn->prepare("SELECT id, name, price, stock FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if ($product && $product['stock'] > 0) {
        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check if product already in cart
        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $product_id) {
                $item['quantity']++;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1
            ];
        }

        $_SESSION['flash'] = 'Product added to cart!';
    } else {
        $_SESSION['flash'] = 'Product out of stock or not found.';
    }
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'home.php'));
exit;
?>