<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin(1);

$admin_id = $_SESSION['user_id'];

// Fetch all users who have chatted with admin
$stmt = $conn->prepare("SELECT DISTINCT u.id, u.name, u.email FROM users u JOIN messages m ON u.id = m.sender_id OR u.id = m.receiver_id WHERE u.id != ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$chat_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$selected_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : (count($chat_users) > 0 ? $chat_users[0]['id'] : 0);
?>
<!-- Header -->
<?php include 'sidebar.php'; ?>

<!-- Main Content -->
<div class="container-fluid bg-light flex-grow-1 p-0 overflow-auto">
    <!-- Topbar -->
    <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow-sm px-4">
        <h4 class="mb-0 text-gray-800">Support Chat</h4>
    </nav>

    <div class="container-fluid px-4">
        <div class="row">
            <!-- Sidebar with users list -->
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Conversations</h6>
                    </div>
                    <div class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;">
                        <?php if (empty($chat_users)): ?>
                            <div class="p-3 text-muted">No conversations yet.</div>
                        <?php else: ?>
                            <?php foreach ($chat_users as $cu): ?>
                                <a href="chat.php?user_id=<?= $cu['id'] ?>" class="list-group-item list-group-item-action <?= $cu['id'] == $selected_user_id ? 'active' : '' ?>">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1 fw-bold"><?= htmlspecialchars($cu['name']) ?></h6>
                                    </div>
                                    <small class="<?= $cu['id'] == $selected_user_id ? 'text-white-50' : 'text-muted' ?>"><?= htmlspecialchars($cu['email']) ?></small>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="col-md-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Chat with User</h6>
                    </div>
                    <div class="card-body p-0 d-flex flex-column" style="height: 600px;">
                        <?php if (!$selected_user_id): ?>
                            <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted">
                                <i class="bi bi-chat-dots" style="font-size: 3rem;"></i>
                                <p class="mt-2">Select a conversation to start chatting.</p>
                            </div>
                        <?php else: ?>
                            <div class="flex-grow-1 p-3" id="chat-messages" style="overflow-y: auto; background-color: #f8f9fa;">
                                <!-- Messages will load here -->
                            </div>
                            <div class="p-3 border-top bg-white">
                                <form id="chat-form" class="d-flex gap-2">
                                    <input type="hidden" id="selected_user_id" value="<?= $selected_user_id ?>">
                                    <input type="text" id="message-input" class="form-control" placeholder="Type a message..." required autocomplete="off">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-send-fill"></i></button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div> <!-- Close outer d-flex from sidebar.php -->

<script>
    const chatMessages = document.getElementById('chat-messages');
    const chatForm = document.getElementById('chat-form');
    const messageInput = document.getElementById('message-input');
    const selectedUserId = document.getElementById('selected_user_id')?.value;
    
    let lastMessageCount = 0;

    if (selectedUserId) {
        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        async function fetchMessages() {
            try {
                const response = await fetch('../chat_api.php?action=fetch&user_id=' + selectedUserId);
                const data = await response.json();
                
                if (data.messages) {
                    if (data.messages.length !== lastMessageCount) {
                        chatMessages.innerHTML = '';
                        data.messages.forEach(msg => {
                            const isSent = msg.sender_id === data.current_user_id;
                            const msgDiv = document.createElement('div');
                            msgDiv.className = `d-flex mb-3 ${isSent ? 'justify-content-end' : 'justify-content-start'}`;
                            
                            const innerDiv = document.createElement('div');
                            innerDiv.style.maxWidth = '75%';
                            innerDiv.className = `p-3 rounded ${isSent ? 'bg-primary text-white' : 'bg-white border'}`;
                            innerDiv.innerHTML = `
                                <div class="mb-1">${msg.message}</div>
                                <div style="font-size: 0.7rem; opacity: 0.8; text-align: right;">${formatTime(msg.created_at)}</div>
                            `;
                            
                            msgDiv.appendChild(innerDiv);
                            chatMessages.appendChild(msgDiv);
                        });
                        lastMessageCount = data.messages.length;
                        scrollToBottom();
                    }
                }
            } catch (error) {
                console.error('Error fetching messages:', error);
            }
        }

        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = messageInput.value.trim();
            if (!message) return;

            messageInput.value = '';

            try {
                const formData = new FormData();
                formData.append('action', 'send');
                formData.append('user_id', selectedUserId);
                formData.append('message', message);
                
                const response = await fetch('../chat_api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                if (data.success) {
                    fetchMessages();
                }
            } catch (error) {
                console.error('Error sending message:', error);
            }
        });

        // Fetch messages initially and poll every 3 seconds
        fetchMessages();
        setInterval(fetchMessages, 3000);
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
