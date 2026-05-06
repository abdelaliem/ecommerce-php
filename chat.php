<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$page_title = 'Customer Support';
include 'includes/header.php';
?>

    <div class="dashboard-layout" style="max-width: 900px;">
        
        <main class="main-content">
            <a href="dashboard.php" style="display: inline-flex; align-items: center; gap: 0.5rem; color: #575f67; text-decoration: none; margin-bottom: 1.5rem; font-size: 0.875rem; font-weight: 500;">
                <span class="material-symbols-outlined" style="font-size: 1.2rem;">arrow_back</span> Back to Dashboard
            </a>

            <div class="chat-container">
                <div class="chat-header">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: #eef2ff; display: flex; align-items: center; justify-content: center; color: #1a3a8f; font-size: 1.5rem;">
                        <span class="material-symbols-outlined">headset_mic</span>
                    </div>
                    <div>
                        <h2>Customer Support</h2>
                        <p>We typically reply within a few hours.</p>
                    </div>
                </div>

                <div class="chat-messages" id="chat-messages">
                    <!-- Messages will be loaded here via JS -->
                </div>

                <form class="chat-input" id="chat-form">
                    <input type="text" id="message-input" placeholder="Type your message here..." required autocomplete="off">
                    <button type="submit" class="btn-primary" style="padding: 0 1.5rem; height: 100%; border-radius: 4px;">
                        Send <span class="material-symbols-outlined" style="vertical-align: middle; margin-left: 4px; font-size: 1.2rem;">send</span>
                    </button>
                </form>
            </div>
        </main>
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

<?php include 'includes/footer.php'; ?>
