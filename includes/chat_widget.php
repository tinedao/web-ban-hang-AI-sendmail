<?php
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!-- Chat Dock Container -->
<div id="chat-dock">
    <!-- AI Button -->
    <!-- Nếu chưa đăng nhập (ẩn Support), nút AI sẽ dời sang phải (right: 30px) cho đẹp -->
    <button class="chat-widget-btn chat-ai-btn" onclick="ChatApp.toggle('ai')" title="Trợ lý AI" style="<?= !$isLoggedIn ? 'right: 30px;' : '' ?>">
        <i class="fa-solid fa-robot"></i>
    </button>
    <?php if ($isLoggedIn): ?>
    <!-- Support Button -->
    <button class="chat-widget-btn" onclick="ChatApp.toggle('support')" title="Chat nhân viên">
        <i class="fa-solid fa-headset"></i>
    </button>
    <?php endif; ?>
</div>

<!-- AI Chat Box -->
<div class="chat-box" id="box-ai" style="<?= !$isLoggedIn ? 'right: 30px;' : 'right: 100px;' ?>">
    <div class="chat-box-header">
        <h5 class="m-0"><i class="fa-solid fa-robot me-2 text-warning"></i>Trợ lý AI</h5>
        <div>
            <button type="button" class="btn btn-sm text-white p-0 me-2" onclick="ChatApp.resetAi()" title="Làm mới cuộc trò chuyện"><i class="fa-solid fa-rotate-right"></i></button>
            <button type="button" class="btn-close btn-close-white" onclick="ChatApp.toggle('ai')"></button>
        </div>
    </div>
    <div class="chat-box-body" id="msg-ai">
        <div class="chat-message bot">Xin chào! Tôi là AI tư vấn đồ lưu niệm sự kiện <?= $_ENV['APP_NAME'] ?>. Bạn cần tìm sản phẩm nào?</div>
    </div>
    <div class="chat-box-footer">
        <form onsubmit="ChatApp.send(event, 'ai')" class="chat-input-group">
            <input type="text" name="msg" class="chat-input-modern" placeholder="Hỏi AI về sản phẩm..." autocomplete="off">
            <button type="submit" class="chat-btn-send"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
    </div>
</div>

<!-- Support Chat Box -->
<div class="chat-box" id="box-support">
    <div class="chat-box-header">
        <h5 class="m-0"><i class="fa-solid fa-headset me-2 text-warning"></i>Hỗ trợ trực tuyến</h5>
        <button type="button" class="btn-close btn-close-white" onclick="ChatApp.toggle('support')"></button>
    </div>
    <div class="chat-box-body" id="msg-support">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="text-center mt-5 text-muted small">
                <i class="fa-solid fa-lock fa-2x mb-3 opacity-50"></i><br>
                Vui lòng <a href="login.php" class="fw-bold">đăng nhập</a> để chat.
            </div>
        <?php endif; ?>
    </div>
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="chat-box-footer">
        <form onsubmit="ChatApp.send(event, 'support')" class="chat-input-group">
            <input type="text" name="msg" class="chat-input-modern" placeholder="Nhập tin nhắn..." autocomplete="off">
            <button type="submit" class="chat-btn-send"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
const ChatApp = {
    getAbsoluteUrl: (url) => {
        try {
            return new URL(url, window.location.href);
        } catch (e) {
            return null;
        }
    },

    isCategoryUrl: (url) => {
        const parsed = url instanceof URL ? url : ChatApp.getAbsoluteUrl(url);
        if (!parsed) return false;
        const path = parsed.pathname.toLowerCase();
        return path.endsWith('/category.php') || path.endsWith('category.php');
    },

    initCategoryInteractions: () => {
        const form = document.getElementById('filterForm');
        const grid = document.getElementById('product-grid');
        const pagination = document.getElementById('pagination-container');
        if (!form || !grid || !pagination) return;

        const fetchProducts = (page = 1) => {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            params.set('page', page);

            const newUrl = window.location.pathname + '?' + params.toString();
            window.history.pushState({ path: newUrl }, '', newUrl);

            grid.style.opacity = '0.5';

            fetch('api/filter_products.php?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    grid.innerHTML = data.grid;
                    pagination.innerHTML = data.pagination;
                    grid.style.opacity = '1';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                })
                .catch(() => {
                    grid.style.opacity = '1';
                });
        };

        const resetFilters = () => {
            const catAll = document.getElementById('catAll');
            const sortNew = document.getElementById('sortNew');
            if (catAll) catAll.checked = true;
            if (sortNew) sortNew.checked = true;
            fetchProducts(1);
        };

        window.fetchProducts = fetchProducts;
        window.resetFilters = resetFilters;

        form.querySelectorAll('.filter-input').forEach(input => {
            input.addEventListener('change', () => fetchProducts(1));
        });

        pagination.addEventListener('click', function(e) {
            if (e.target.classList.contains('page-link')) {
                e.preventDefault();
                const page = e.target.getAttribute('data-page');
                if (page) fetchProducts(page);
            }
        });
    },

    navigateContent: (url, pushState = true) => {
        const targetUrl = ChatApp.getAbsoluteUrl(url);
        const contentRoot = document.getElementById('app-content');

        if (!targetUrl || !contentRoot || !ChatApp.isCategoryUrl(targetUrl)) {
            window.location.href = targetUrl ? targetUrl.href : url;
            return;
        }

        contentRoot.style.opacity = '0.5';

        fetch(targetUrl.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const nextContent = doc.getElementById('app-content');

                if (!nextContent) {
                    window.location.href = targetUrl.href;
                    return;
                }

                contentRoot.innerHTML = nextContent.innerHTML;
                contentRoot.style.opacity = '1';

                if (doc.title) document.title = doc.title;
                if (pushState) history.pushState({ partial: true, url: targetUrl.href }, '', targetUrl.href);

                ChatApp.initCategoryInteractions();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            })
            .catch(() => {
                contentRoot.style.opacity = '1';
                window.location.href = targetUrl.href;
            });
    },

    interval: null,
    chatHistory: JSON.parse(sessionStorage.getItem('ai_chat_history')) || [], // Load lịch sử từ sessionStorage
    
    toggle: (type) => {
        const target = document.getElementById(`box-${type}`);
        const isActive = target.classList.contains('active');
        
        // Đóng tất cả box trước
        document.querySelectorAll('.chat-box').forEach(b => b.classList.remove('active'));
        
        if (!isActive) {
            target.classList.add('active');
            if (type === 'support') {
                ChatApp.fetchSupport();
                if (!ChatApp.interval) ChatApp.interval = setInterval(ChatApp.fetchSupport, 3000);
            } else {
                clearInterval(ChatApp.interval);
                ChatApp.interval = null;
            }
        } else {
            clearInterval(ChatApp.interval);
            ChatApp.interval = null;
        }
    },

    addMsg: (type, text, sender) => {
        const box = document.getElementById(`msg-${type}`);
        const div = document.createElement('div');
        div.className = `chat-message ${sender}`;
        div.innerText = text;
        
        box.appendChild(div);
        box.scrollTop = box.scrollHeight;
    },

    send: (e, type) => {
        e.preventDefault();
        const input = e.target.querySelector('input[name="msg"]');
        const msg = input.value.trim();
        if (!msg) return;

        // Lưu tin nhắn user vào lịch sử
        if (type === 'ai') ChatApp.chatHistory.push({ role: 'user', content: msg });
        if (type === 'ai') sessionStorage.setItem('ai_chat_history', JSON.stringify(ChatApp.chatHistory));

        ChatApp.addMsg(type, msg, 'user');
        input.value = '';

        const formData = new FormData();
        formData.append('message', msg);
        // Gửi kèm lịch sử chat để AI nhớ ngữ cảnh
        if (type === 'ai') formData.append('history', JSON.stringify(ChatApp.chatHistory));
        formData.append('action', type === 'ai' ? 'chat_ai' : 'send');

        fetch('api/process.php', { method: 'POST', body: formData })
            .then(res => res.text())
            .then(text => {
                // Xử lý trường hợp API trả về JSON lồng nhau hoặc text thuần
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("JSON Parse Error:", text);
                    return { error: true, raw: text };
                }
            })
            .then(data => {
                if (type === 'ai') {
                    // Parse nội dung JSON từ AI (vì AI trả về string JSON bên trong field content)
                    const aiContent = data.choices?.[0]?.message?.content || "{}";
                    let aiObj = {};
                    try { aiObj = JSON.parse(aiContent); } catch { aiObj = { reply: aiContent, url: "" }; }

                    // Lưu câu trả lời của AI vào lịch sử
                    ChatApp.chatHistory.push({ role: 'assistant', content: aiObj.reply });
                    sessionStorage.setItem('ai_chat_history', JSON.stringify(ChatApp.chatHistory));

                    ChatApp.addMsg('ai', aiObj.reply || "Xin lỗi, tôi chưa hiểu ý bạn.", 'bot');

                    // Tự động chuyển trang nếu AI trả về URL (Khách đã đồng ý)
                    if (aiObj.url && aiObj.url.trim() !== "") {
                        // Đợi 1.5s để khách kịp đọc tin nhắn xác nhận rồi mới chuyển
                        setTimeout(() => { ChatApp.navigateContent(aiObj.url); }, 1500);
                    }
                } else if (data.status === 'success') {
                    ChatApp.fetchSupport();
                }
            })
            .catch(err => console.error(err));
    },

    fetchSupport: () => {
        const container = document.getElementById('msg-support');
        if (!container) return;
        fetch('api/process.php?action=fetch')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    container.innerHTML = '';
                    if (data.messages.length === 0) container.innerHTML = '<div class="text-center text-muted small mt-4">Chưa có tin nhắn.</div>';
                    data.messages.forEach(m => {
                        const sender = m.is_admin == 1 ? 'admin' : 'user';
                        const div = document.createElement('div');
                        div.className = `chat-message ${sender}`;
                        div.innerText = m.message;
                        container.appendChild(div);
                    });
                    container.scrollTop = container.scrollHeight;
                }
            });
    },

    resetAi: () => {
        sessionStorage.removeItem('ai_chat_history');
        ChatApp.chatHistory = [];
        document.getElementById('msg-ai').innerHTML = '<div class="chat-message bot">Xin chào! Tôi là AI tư vấn đồ lưu niệm sự kiện <?= $_ENV['APP_NAME'] ?>. Bạn cần tìm sản phẩm nào?</div>';
    }
};

// Tự động khôi phục tin nhắn cũ khi tải lại trang
if (ChatApp.chatHistory.length > 0) {
    const aiBox = document.getElementById('msg-ai');
    if (aiBox) {
        aiBox.innerHTML = ''; // Xóa tin nhắn chào mặc định
        ChatApp.chatHistory.forEach(msg => {
            const sender = msg.role === 'user' ? 'user' : 'bot';
            ChatApp.addMsg('ai', msg.content, sender);
        });
    }
}
// Ho tro back/forward cho luong chuyen trang category khong reload layout.
window.addEventListener('popstate', function() {
    if (ChatApp.isCategoryUrl(window.location.href)) {
        ChatApp.navigateContent(window.location.href, false);
    } else {
        window.location.reload();
    }
});
</script>
