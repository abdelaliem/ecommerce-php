<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$current_user_id = $_SESSION['user_id'];
$is_admin = isAdmin();

$admin_id = 0;
if (!$is_admin) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $admin_id = $res->fetch_assoc()['id'];
    }
    $stmt->close();
}

$action = $_REQUEST['action'] ?? '';

if ($action === 'fetch') {
    $other_user_id = $is_admin ? (int)($_GET['user_id'] ?? 0) : $admin_id;
    
    if (!$other_user_id) {
        echo json_encode(['messages' => []]);
        exit;
    }

    $stmt = $conn->prepare("SELECT m.*, u.name as sender_name FROM messages m JOIN users u ON m.sender_id = u.id WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?) ORDER BY m.created_at ASC");
    $stmt->bind_param('iiii', $current_user_id, $other_user_id, $other_user_id, $current_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'id' => $row['id'],
            'sender_id' => $row['sender_id'],
            'sender_name' => $row['sender_name'],
            'message' => $row['message'],
            'created_at' => $row['created_at']
        ];
    }
    $stmt->close();

    echo json_encode(['messages' => $messages, 'current_user_id' => $current_user_id]);
    exit;

} elseif ($action === 'send') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['error' => 'Invalid method']);
        exit;
    }

    $message = trim($_POST['message'] ?? '');
    $other_user_id = $is_admin ? (int)($_POST['user_id'] ?? 0) : $admin_id;

    if (empty($message) || !$other_user_id) {
        echo json_encode(['error' => 'Invalid input']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param('iis', $current_user_id, $other_user_id, $message);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Database error']);
    }
    exit;
}

echo json_encode(['error' => 'Invalid action']);
