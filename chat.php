<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$page_title = 'Customer Support';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - AuraCommerce</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .chat-container {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            height: 600px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .chat-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            background: #fafafa;
        }
        .message {
            max-width: 80%;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            font-size: 0.9375rem;
            line-height: 1.4;
            position: relative;
        }
        .message-sent {
            align-self: flex-end;
            background: var(--primary);
            color: #fff;
            border-bottom-right-radius: 2px;
        }
        .message-received {
            align-self: flex-start;
            background: #fff;
            color: var(--text-dark);
            border: 1px solid var(--border);
            border-bottom-left-radius: 2px;
        }
        .message-time {
            display: block;
            font-size: 0.7rem;
            margin-top: 0.25rem;
            opacity: 0.7;
        }
        .chat-input-area {
            padding: 1.25rem;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 0.75rem;
        }
        .chat-input {
            flex: 1;
            padding: 0.625rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            outline: none;
            font-family: inherit;
        }
        .chat-input:focus {
            border-color: var(--primary);
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
                <a class="nav-link" href="shop.php">Shop</a>
            </div>
            <div class="user-actions">
                <a href="dashboard.php" style="border-bottom: 2px solid var(--primary); padding-bottom: 20px; margin-bottom: -22px;">Dashboard</a>
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
                <a href="dashboard.php?tab=orders" class="side-link">
                    <i class="bi bi-box"></i> Orders
                </a>
                <a href="dashboard.php?tab=account" class="side-link">
                    <i class="bi bi-person"></i> Account Info
                </a>
                <a href="chat.php" class="side-link active">
                    <i class="bi bi-chat-dots"></i> Support Chat
                </a>
            </nav>
        </div>

        <div class="main-content">
            <a href="dashboard.php" class="btn btn-link" style="margin-bottom: 1.5rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>

            <div class="chat-container">
                <div class="chat-header">
                    <div style="width: 40px; height: 40px; border-radius: 8px; background: #eef2ff; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.25rem;">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div>
                        <h2 style="font-size: 1rem; font-weight: 700; color: var(--text-dark);">Customer Support</h2>
                        <p style="font-size: 0.75rem; color: var(--secondary);">We typically reply within a few hours.</p>
                    </div>
                </div>

                <div class="chat-messages" id="chat-messages">
                    <!-- Messages will be loaded here via JS -->
                </div>

                <form class="chat-input-area" id="chat-form">
                    <input type="text" class="chat-input" id="message-input" placeholder="Type your message here..." required autocomplete="off">
                    <button type="submit" class="btn btn-primary">
                        Send <i class="bi bi-send" style="margin-left: 0.5rem;"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const chatMessages = document.getElementById('chat-messages');
        const chatForm = document.getElementById('chat-form');
        const messageInput = document.getElementById('message-input');
        
        let lastMessageCount = 0;

        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function formatTime(dateString) {
            const date = new Date(dateString);
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        async function fetchMessages() {
            try {
                const response = await fetch('chat_api.php?action=fetch');
                const data = await response.json();
                
                if (data.messages) {
                    if (data.messages.length !== lastMessageCount) {
                        chatMessages.innerHTML = '';
                        data.messages.forEach(msg => {
                            const isSent = msg.sender_id === data.current_user_id;
                            const msgDiv = document.createElement('div');
                            msgDiv.className = `message ${isSent ? 'message-sent' : 'message-received'}`;
                            msgDiv.innerHTML = `
                                ${msg.message}
                                <span class="message-time">${formatTime(msg.created_at)}</span>
                            `;
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
                formData.append('message', message);
                
                const response = await fetch('chat_api.php', {
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
    </script>
</body>

</html>

