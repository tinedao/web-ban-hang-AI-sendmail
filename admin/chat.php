<?php include 'header.php'; ?>

<style>
    .chat-container {
        height: calc(100vh - 110px);
        background: #fff;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #e9edf3;
        box-shadow: 0 10px 30px rgba(17, 24, 39, 0.08);
        display: flex;
    }

    .chat-sidebar {
        width: 320px;
        border-right: 1px solid #eee;
        display: flex;
        flex-direction: column;
        background: #fff;
    }

    .chat-search {
        padding: 15px;
        border-bottom: 1px solid #eee;
    }

    .user-list {
        flex: 1;
        overflow-y: auto;
    }

    .user-item {
        padding: 15px;
        border-bottom: 1px solid #f8f9fa;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-item:hover {
        background: #f8f9fa;
    }

    .user-item.active {
        background: #eef3ff;
        border-left: 4px solid var(--bs-primary);
    }

    .user-avatar {
        width: 45px;
        height: 45px;
        background: #ddd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #555;
        flex-shrink: 0;
    }

    .user-info {
        flex: 1;
        overflow: hidden;
    }

    .user-name {
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 2px;
        color: #333;
    }

    .user-last-msg {
        font-size: 0.85rem;
        color: #888;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-meta {
        text-align: right;
        font-size: 0.75rem;
        color: #aaa;
    }

    .badge-unread {
        background: var(--bs-danger);
        color: #fff;
        font-size: 0.7rem;
        padding: 2px 6px;
        border-radius: 10px;
    }

    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #f5f7fb;
    }

    .chat-header {
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        background: #fff;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chat-messages {
        flex: 1;
        padding: 18px 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .message-row {
        display: flex;
        width: 100%;
    }

    .message-row.admin {
        justify-content: flex-end;
    }

    .message-row.user {
        justify-content: flex-start;
    }

    .message {
        max-width: min(72%, 640px);
        padding: 10px 14px;
        border-radius: 16px;
        font-size: 0.94rem;
        line-height: 1.5;
        word-break: break-word;
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.06);
    }

    .message.admin {
        background: var(--bs-primary);
        color: #fff;
        border-bottom-right-radius: 5px;
    }

    .message.user {
        background: #fff;
        border: 1px solid #e5e7eb;
        color: #333;
        border-bottom-left-radius: 5px;
    }

    .message-text {
        white-space: pre-wrap;
    }

    .message-time {
        font-size: 0.72rem;
        margin-top: 6px;
        opacity: 0.8;
        text-align: right;
        line-height: 1.2;
    }

    .chat-input-area {
        padding: 12px;
        background: #fff;
        border-top: 1px solid #eee;
    }

    #adminChatForm {
        align-items: center;
    }

    #msgInput {
        min-height: 42px;
        border-radius: 10px;
        border: 1px solid #d9e2f0;
    }

    #msgInput:focus {
        border-color: #6ea8fe;
        box-shadow: 0 0 0 0.18rem rgba(13, 110, 253, 0.15);
    }

    .chat-send-btn {
        width: 44px;
        height: 42px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        color: #fff;
        background: linear-gradient(180deg, #1f6feb 0%, #0d6efd 100%);
        border: 1px solid #0d6efd;
    }

    .chat-send-btn:hover {
        color: #fff;
        background: linear-gradient(180deg, #125fd0 0%, #0b5ed7 100%);
        border-color: #0b5ed7;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #aaa;
    }

    @media (max-width: 992px) {
        .chat-container {
            flex-direction: column;
            height: auto;
            min-height: calc(100vh - 120px);
        }

        .chat-sidebar {
            width: 100%;
            max-height: 260px;
            border-right: none;
            border-bottom: 1px solid #eee;
        }

        .message {
            max-width: 86%;
        }
    }
</style>

<div class="container-fluid p-0">
    <h3 class="mb-3 px-3">Hỗ trợ khách hàng</h3>

    <div class="chat-container mx-3 mb-3">
        <div class="chat-sidebar">
            <div class="chat-search">
                <input type="text" id="searchUserInput" class="form-control" placeholder="Tìm kiếm khách hàng...">
            </div>
            <div class="user-list" id="userList">
                <div class="text-center p-3 text-muted"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải...</div>
            </div>
        </div>

        <div class="chat-main">
            <div class="chat-header" id="chatHeader" style="display:none;">
                <div class="user-avatar bg-primary text-white" id="headerAvatar">U</div>
                <div>
                    <div class="user-name" id="headerName">User Name</div>
                    <small class="text-muted" id="headerPhone"></small>
                </div>
            </div>

            <div class="chat-messages" id="chatMessages">
                <div class="empty-state">
                    <i class="fa-regular fa-comments fa-3x mb-3"></i>
                    <p>Chọn một khách hàng để bắt đầu trò chuyện</p>
                </div>
            </div>

            <div class="chat-input-area" id="chatInputArea" style="display:none;">
                <form id="adminChatForm" class="d-flex gap-2">
                    <input type="hidden" id="currentUserId">
                    <input type="text" id="msgInput" class="form-control" placeholder="Nhập tin nhắn..." autocomplete="off">
                    <button type="submit" class="btn chat-send-btn"><i class="fa-solid fa-paper-plane"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const AdminChat = {
    currentUserId: null,
    interval: null,
    users: [],

    escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    },

    formatTime(value) {
        if (!value) return '--:--';
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return '--:--';
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    },

    formatDateTime(value) {
        if (!value) return '--';
        const d = new Date(value);
        if (Number.isNaN(d.getTime())) return '--';
        return d.toLocaleString('vi-VN');
    },

    init() {
        document.getElementById('adminChatForm').addEventListener('submit', AdminChat.send);
        document.getElementById('searchUserInput').addEventListener('input', AdminChat.renderUsers);
        AdminChat.loadUsers();
        setInterval(AdminChat.loadUsers, 5000);
    },

    loadUsers() {
        fetch('../api/process.php?action=admin_get_users')
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success') return;
                AdminChat.users = Array.isArray(data.users) ? data.users : [];
                AdminChat.renderUsers();
            });
    },

    renderUsers() {
        const list = document.getElementById('userList');
        const keyword = (document.getElementById('searchUserInput').value || '').toLowerCase().trim();
        const users = AdminChat.users.filter(u => {
            if (!keyword) return true;
            const text = `${u.name || ''} ${u.phone || ''} ${u.last_message || ''}`.toLowerCase();
            return text.includes(keyword);
        });

        if (users.length === 0) {
            list.innerHTML = '<div class="text-center p-3 text-muted">Không có hội thoại</div>';
            return;
        }

        list.innerHTML = users.map((u) => {
            const id = Number(u.id || 0);
            const name = AdminChat.escapeHtml(u.name || 'Khách');
            const phone = AdminChat.escapeHtml(u.phone || '');
            const lastMessage = AdminChat.escapeHtml(u.last_message || 'Tin nhắn');
            const lastTime = AdminChat.formatTime(u.last_time);
            const avatar = AdminChat.escapeHtml((u.name || 'K').charAt(0).toUpperCase());
            const unread = Number(u.unread_count || 0);
            const activeClass = id === AdminChat.currentUserId ? 'active' : '';

            return `
                <div class="user-item ${activeClass}" data-id="${id}" data-name="${name}" data-phone="${phone}">
                    <div class="user-avatar">${avatar}</div>
                    <div class="user-info">
                        <div class="user-name">${name}</div>
                        <div class="user-last-msg ${unread > 0 ? 'fw-bold text-dark' : ''}">${lastMessage}</div>
                    </div>
                    <div class="user-meta">
                        <div>${lastTime}</div>
                        ${unread > 0 ? `<span class="badge-unread">${unread}</span>` : ''}
                    </div>
                </div>
            `;
        }).join('');

        list.querySelectorAll('.user-item').forEach((item) => {
            item.addEventListener('click', () => {
                AdminChat.selectUser(
                    Number(item.dataset.id || 0),
                    item.dataset.name || '',
                    item.dataset.phone || ''
                );
            });
        });
    },

    selectUser(id, name, phone) {
        if (!id) return;
        AdminChat.currentUserId = id;
        document.getElementById('currentUserId').value = String(id);
        document.getElementById('chatHeader').style.display = 'flex';
        document.getElementById('chatInputArea').style.display = 'block';
        document.getElementById('headerName').innerText = name || 'Khách';
        document.getElementById('headerPhone').innerText = phone || '';
        document.getElementById('headerAvatar').innerText = (name || 'K').charAt(0).toUpperCase();
        AdminChat.renderUsers();
        AdminChat.loadMessages();

        if (AdminChat.interval) clearInterval(AdminChat.interval);
        AdminChat.interval = setInterval(AdminChat.loadMessages, 3000);
    },

    loadMessages() {
        if (!AdminChat.currentUserId) return;
        fetch(`../api/process.php?action=admin_get_conversation&user_id=${AdminChat.currentUserId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status !== 'success') return;
                const box = document.getElementById('chatMessages');
                const messages = Array.isArray(data.messages) ? data.messages : [];
                box.innerHTML = messages.map((m) => {
                    const role = Number(m.is_admin) === 1 ? 'admin' : 'user';
                    return `<div class="message-row ${role}">
                        <div class="message ${role}">
                            <div class="message-text">${AdminChat.escapeHtml(m.message || '')}</div>
                            <div class="message-time">${AdminChat.formatDateTime(m.created_at)}</div>
                        </div>
                    </div>`;
                }).join('');
                box.scrollTop = box.scrollHeight;
                AdminChat.loadUsers();
            });
    },

    send(e) {
        e.preventDefault();
        const input = document.getElementById('msgInput');
        const msg = (input.value || '').trim();
        if (!msg || !AdminChat.currentUserId) return;

        const formData = new FormData();
        formData.append('action', 'admin_send');
        formData.append('user_id', String(AdminChat.currentUserId));
        formData.append('message', msg);

        fetch('../api/process.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    input.value = '';
                    AdminChat.loadMessages();
                }
            });
    }
};

document.addEventListener('DOMContentLoaded', AdminChat.init);
</script>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
