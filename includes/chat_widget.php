<?php
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!-- Chat Dock Container -->
<div id="chat-dock">
    <button class="chat-widget-btn chat-ai-btn" onclick="ChatApp.toggle('ai')" title="Trợ lý AI" style="<?= !$isLoggedIn ? 'right: 30px;' : '' ?>">
        <i class="fa-solid fa-robot"></i>
    </button>
    <?php if ($isLoggedIn): ?>
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
        <?php if (!$isLoggedIn): ?>
            <div class="text-center mt-5 text-muted small">
                <i class="fa-solid fa-lock fa-2x mb-3 opacity-50"></i><br>
                Vui lòng <a href="login.php" class="fw-bold">đăng nhập</a> để chat.
            </div>
        <?php endif; ?>
    </div>
    <?php if ($isLoggedIn): ?>
    <div class="chat-box-footer">
        <form onsubmit="ChatApp.send(event, 'support')" class="chat-input-group">
            <input type="text" name="msg" class="chat-input-modern" placeholder="Nhập tin nhắn..." autocomplete="off">
            <button type="submit" class="chat-btn-send"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
    </div>
    <?php endif; ?>
</div>

<style>
    #box-ai .chat-product-list {
        width: min(100%, 280px);
        display: flex;
        flex-direction: column;
        gap: 12px;
        align-self: flex-start;
        margin-top: -4px;
    }

    #box-ai .chat-product-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 248, 248, 0.98));
        border: 1px solid rgba(197, 160, 89, 0.22);
        border-radius: 16px;
        box-shadow: 0 10px 24px rgba(17, 17, 17, 0.08);
        overflow: hidden;
    }

    #box-ai .chat-product-thumb {
        width: 70px;
        height: 70px;
        flex: 0 0 70px;
        border-radius: 14px;
        overflow: hidden;
        background: #f4f4f4;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    #box-ai .chat-product-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    #box-ai .chat-product-body {
        min-width: 0;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    #box-ai .chat-product-name {
        margin: 0;
        font-size: 0.9rem;
        line-height: 1.35;
        font-weight: 600;
        color: #1f1f1f;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    #box-ai .chat-product-price {
        margin: 0;
        font-size: 0.84rem;
        font-weight: 700;
        color: var(--accent-color);
    }

    #box-ai .chat-product-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 2px;
        width: fit-content;
        color: #111 !important;
        text-decoration: none !important;
        font-size: 0.8rem;
        font-weight: 600;
        padding-bottom: 1px;
        border-bottom: 1px solid rgba(197, 160, 89, 0.45);
        transition: transform 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    }

    #box-ai .chat-product-link:hover {
        color: var(--accent-color) !important;
        border-color: var(--accent-color);
        transform: translateX(2px);
    }

    #box-ai .chat-invoice-card {
        width: min(100%, 296px);
        align-self: flex-start;
        padding: 14px;
        margin-top: -4px;
        background: linear-gradient(180deg, rgba(255, 252, 245, 0.98), rgba(255, 255, 255, 0.98));
        border: 1px solid rgba(197, 160, 89, 0.3);
        border-radius: 18px;
        box-shadow: 0 12px 28px rgba(17, 17, 17, 0.08);
        color: #1f1f1f;
    }

    #box-ai .chat-invoice-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }

    #box-ai .chat-invoice-title {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
    }

    #box-ai .chat-invoice-code {
        margin: 3px 0 0;
        font-size: 0.78rem;
        color: #7b7b7b;
    }

    #box-ai .chat-invoice-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 9px;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }

    #box-ai .chat-invoice-status.status-success {
        background: rgba(25, 135, 84, 0.12);
        color: #198754;
    }

    #box-ai .chat-invoice-status.status-warning {
        background: rgba(255, 193, 7, 0.16);
        color: #8a5a00;
    }

    #box-ai .chat-invoice-status.status-info {
        background: rgba(13, 202, 240, 0.16);
        color: #0a6c86;
    }

    #box-ai .chat-invoice-status.status-danger {
        background: rgba(220, 53, 69, 0.12);
        color: #b42318;
    }

    #box-ai .chat-invoice-status.status-secondary {
        background: rgba(108, 117, 125, 0.14);
        color: #5c6670;
    }

    #box-ai .chat-invoice-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 12px;
    }

    #box-ai .chat-invoice-meta-item {
        padding: 8px 10px;
        background: rgba(17, 17, 17, 0.04);
        border-radius: 12px;
    }

    #box-ai .chat-invoice-meta-label {
        display: block;
        margin-bottom: 4px;
        font-size: 0.7rem;
        color: #7b7b7b;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    #box-ai .chat-invoice-meta-value {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        line-height: 1.35;
    }

    #box-ai .chat-invoice-customer {
        padding: 10px 12px;
        background: rgba(255, 255, 255, 0.82);
        border: 1px dashed rgba(197, 160, 89, 0.32);
        border-radius: 14px;
        margin-bottom: 12px;
    }

    #box-ai .chat-invoice-customer strong {
        display: block;
        margin-bottom: 4px;
        font-size: 0.84rem;
    }

    #box-ai .chat-invoice-customer span {
        display: block;
        font-size: 0.76rem;
        line-height: 1.45;
        color: #5f6368;
    }

    #box-ai .chat-invoice-items {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-bottom: 12px;
    }

    #box-ai .chat-invoice-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        padding: 9px 10px;
        background: rgba(17, 17, 17, 0.03);
        border-radius: 12px;
    }

    #box-ai .chat-invoice-item-name {
        margin: 0;
        font-size: 0.8rem;
        font-weight: 600;
        line-height: 1.35;
    }

    #box-ai .chat-invoice-item-qty {
        margin-top: 3px;
        font-size: 0.73rem;
        color: #777;
    }

    #box-ai .chat-invoice-item-price {
        flex: 0 0 auto;
        font-size: 0.78rem;
        font-weight: 700;
        color: var(--accent-color);
        text-align: right;
    }

    #box-ai .chat-invoice-more {
        font-size: 0.74rem;
        color: #777;
        margin: -2px 0 12px;
    }

    #box-ai .chat-invoice-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding-top: 10px;
        border-top: 1px solid rgba(17, 17, 17, 0.08);
    }

    #box-ai .chat-invoice-total-label {
        display: block;
        font-size: 0.72rem;
        color: #777;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    #box-ai .chat-invoice-total-value {
        display: block;
        font-size: 1rem;
        font-weight: 700;
        color: var(--accent-color);
    }

    #box-ai .chat-invoice-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 12px;
        border-radius: 12px;
        background: #111;
        color: #fff !important;
        text-decoration: none !important;
        font-size: 0.78rem;
        font-weight: 600;
        white-space: nowrap;
    }

    #box-ai .chat-invoice-link:hover {
        background: var(--accent-color);
        color: #111 !important;
    }
</style>

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
    aiThinkingEl: null,

    toggle: (type) => {
        const target = document.getElementById(`box-${type}`);
        const isActive = target.classList.contains('active');

        document.querySelectorAll('.chat-box').forEach(box => box.classList.remove('active'));

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
        return div;
    },

    getProductImageUrl: (product) => {
        const imageName = (product?.image || '').trim();
        return `assets/images/${imageName !== '' ? imageName : 'placeholder.jpg'}`;
    },

    addInvoicePreview: (invoice) => {
        const box = document.getElementById('msg-ai');
        if (!box || !invoice || typeof invoice !== 'object') return;

        const card = document.createElement('div');
        card.className = 'chat-invoice-card';

        const header = document.createElement('div');
        header.className = 'chat-invoice-head';

        const headerLeft = document.createElement('div');
        const title = document.createElement('p');
        title.className = 'chat-invoice-title';
        title.textContent = 'Tom tat hoa don';

        const code = document.createElement('p');
        code.className = 'chat-invoice-code';
        code.textContent = `${invoice?.order_code || ''} • ${invoice?.created_at_label || ''}`;

        headerLeft.appendChild(title);
        headerLeft.appendChild(code);

        const status = document.createElement('span');
        status.className = `chat-invoice-status status-${invoice?.status_tone || 'secondary'}`;
        status.textContent = invoice?.status_label || 'Khong ro';

        header.appendChild(headerLeft);
        header.appendChild(status);
        card.appendChild(header);

        const meta = document.createElement('div');
        meta.className = 'chat-invoice-meta';

        const paymentMeta = document.createElement('div');
        paymentMeta.className = 'chat-invoice-meta-item';
        paymentMeta.innerHTML = `<span class="chat-invoice-meta-label">Thanh toan</span><span class="chat-invoice-meta-value"></span>`;
        paymentMeta.querySelector('.chat-invoice-meta-value').textContent = invoice?.payment_method_label || '';

        const quantityMeta = document.createElement('div');
        quantityMeta.className = 'chat-invoice-meta-item';
        quantityMeta.innerHTML = `<span class="chat-invoice-meta-label">So luong</span><span class="chat-invoice-meta-value"></span>`;
        quantityMeta.querySelector('.chat-invoice-meta-value').textContent = `${invoice?.total_quantity || 0} san pham`;

        meta.appendChild(paymentMeta);
        meta.appendChild(quantityMeta);
        card.appendChild(meta);

        if (invoice?.customer_name || invoice?.customer_phone || invoice?.customer_address) {
            const customer = document.createElement('div');
            customer.className = 'chat-invoice-customer';

            const name = document.createElement('strong');
            name.textContent = invoice?.customer_name || 'Khach hang';
            customer.appendChild(name);

            if (invoice?.customer_phone) {
                const phone = document.createElement('span');
                phone.textContent = invoice.customer_phone;
                customer.appendChild(phone);
            }

            if (invoice?.customer_address) {
                const address = document.createElement('span');
                address.textContent = invoice.customer_address;
                customer.appendChild(address);
            }

            card.appendChild(customer);
        }

        if (Array.isArray(invoice?.items) && invoice.items.length > 0) {
            const list = document.createElement('div');
            list.className = 'chat-invoice-items';

            invoice.items.forEach(item => {
                const row = document.createElement('div');
                row.className = 'chat-invoice-item';

                const left = document.createElement('div');
                const itemName = document.createElement('p');
                itemName.className = 'chat-invoice-item-name';
                itemName.textContent = item?.name || 'San pham';

                const qty = document.createElement('div');
                qty.className = 'chat-invoice-item-qty';
                qty.textContent = `x${item?.qty || 1}`;

                left.appendChild(itemName);
                left.appendChild(qty);

                const price = document.createElement('div');
                price.className = 'chat-invoice-item-price';
                price.textContent = item?.subtotal_formatted || item?.price_formatted || '';

                row.appendChild(left);
                row.appendChild(price);
                list.appendChild(row);
            });

            card.appendChild(list);
        }

        if ((invoice?.item_count || 0) > (invoice?.items || []).length) {
            const more = document.createElement('div');
            more.className = 'chat-invoice-more';
            more.textContent = `Con ${invoice.item_count - invoice.items.length} san pham nua trong don hang.`;
            card.appendChild(more);
        }

        const footer = document.createElement('div');
        footer.className = 'chat-invoice-foot';

        const totalWrap = document.createElement('div');
        totalWrap.innerHTML = `<span class="chat-invoice-total-label">Tong cong</span><span class="chat-invoice-total-value"></span>`;
        totalWrap.querySelector('.chat-invoice-total-value').textContent = invoice?.total_formatted || '';

        footer.appendChild(totalWrap);

        if (invoice?.detail_url) {
            const link = document.createElement('a');
            link.className = 'chat-invoice-link';
            link.href = invoice.detail_url;
            link.innerHTML = 'Xem hoa don <i class="fa-solid fa-arrow-right"></i>';
            footer.appendChild(link);
        }

        card.appendChild(footer);
        box.appendChild(card);
        box.scrollTop = box.scrollHeight;
    },

    addAiResponse: (text, products = [], invoice = null) => {
        const box = document.getElementById('msg-ai');
        if (!box) return;

        ChatApp.addMsg('ai', text || 'Xin lỗi, tôi chưa hiểu ý bạn.', 'bot');

        if (invoice && typeof invoice === 'object') {
            ChatApp.addInvoicePreview(invoice);
        }

        if (!Array.isArray(products) || products.length === 0) {
            return;
        }

        const list = document.createElement('div');
        list.className = 'chat-product-list';

        products.slice(0, 3).forEach(product => {
            const card = document.createElement('div');
            card.className = 'chat-product-card';

            const thumb = document.createElement('div');
            thumb.className = 'chat-product-thumb';

            const image = document.createElement('img');
            image.src = ChatApp.getProductImageUrl(product);
            image.alt = product?.name || 'Sản phẩm';
            thumb.appendChild(image);

            const body = document.createElement('div');
            body.className = 'chat-product-body';

            const name = document.createElement('div');
            name.className = 'chat-product-name';
            name.textContent = product?.name || 'Sản phẩm';

            const price = document.createElement('div');
            price.className = 'chat-product-price';
            price.textContent = product?.price_formatted || '';

            const link = document.createElement('a');
            link.className = 'chat-product-link';
            link.href = product?.url || '#';
            link.innerHTML = 'Xem thêm <i class="fa-solid fa-arrow-right"></i>';

            body.appendChild(name);
            body.appendChild(price);
            body.appendChild(link);
            card.appendChild(thumb);
            card.appendChild(body);
            list.appendChild(card);
        });

        box.appendChild(list);
        box.scrollTop = box.scrollHeight;
    },

    setAiPending: (form, isPending) => {
        if (!form) return;
        const input = form.querySelector('input[name="msg"]');
        const button = form.querySelector('button[type="submit"]');
        if (input) input.disabled = isPending;
        if (button) button.disabled = isPending;
    },

    showAiThinking: () => {
        const box = document.getElementById('msg-ai');
        if (!box) return null;

        ChatApp.removeAiThinking();

        const indicator = document.createElement('div');
        indicator.className = 'typing-indicator';
        indicator.setAttribute('data-thinking', 'true');
        indicator.innerHTML = `
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
        `;

        box.appendChild(indicator);
        box.scrollTop = box.scrollHeight;
        ChatApp.aiThinkingEl = indicator;
        return indicator;
    },

    removeAiThinking: () => {
        if (ChatApp.aiThinkingEl && ChatApp.aiThinkingEl.parentNode) {
            ChatApp.aiThinkingEl.parentNode.removeChild(ChatApp.aiThinkingEl);
        }
        ChatApp.aiThinkingEl = null;
    },

    send: (e, type) => {
        e.preventDefault();
        const form = e.target;
        const input = form.querySelector('input[name="msg"]');
        const msg = input.value.trim();
        if (!msg) return;
        if (type === 'ai' && ChatApp.aiThinkingEl) return;

        ChatApp.addMsg(type, msg, 'user');
        input.value = '';

        const formData = new FormData();
        formData.append('message', msg);
        formData.append('action', type === 'ai' ? 'chat_ai' : 'send');

        if (type === 'ai') {
            ChatApp.setAiPending(form, true);
            ChatApp.showAiThinking();
        }

        fetch('api/process.php', { method: 'POST', body: formData })
            .then(res => res.text())
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (error) {
                    console.error('JSON Parse Error:', text);
                    return { error: true, raw: text };
                }
            })
            .then(data => {
                if (type === 'ai') {
                    ChatApp.removeAiThinking();
                    ChatApp.setAiPending(form, false);

                    const aiContent = data.choices?.[0]?.message?.content || '{}';
                    let aiObj = {};
                    try {
                        aiObj = JSON.parse(aiContent);
                    } catch {
                        aiObj = { reply: aiContent, url: '', products: [], invoice: null };
                    }

                    ChatApp.addAiResponse(
                        aiObj.reply,
                        Array.isArray(aiObj.products) ? aiObj.products : [],
                        aiObj.invoice && typeof aiObj.invoice === 'object' ? aiObj.invoice : null
                    );

                    if (aiObj.url && aiObj.url.trim() !== '') {
                        setTimeout(() => { ChatApp.navigateContent(aiObj.url); }, 1500);
                    }
                } else if (data.status === 'success') {
                    ChatApp.fetchSupport();
                }
            })
            .catch(err => {
                if (type === 'ai') {
                    ChatApp.removeAiThinking();
                    ChatApp.setAiPending(form, false);
                    ChatApp.addMsg('ai', 'Xin lỗi, em đang gặp trục trặc một chút. Anh/chị vui lòng thử lại giúp em nhé.', 'bot');
                }
                console.error(err);
            })
            .finally(() => {
                if (type === 'ai') {
                    ChatApp.removeAiThinking();
                    ChatApp.setAiPending(form, false);
                }
            });
    },

    fetchSupport: () => {
        const container = document.getElementById('msg-support');
        if (!container) return;

        fetch('api/process.php?action=fetch')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    container.innerHTML = '';
                    if (data.messages.length === 0) {
                        container.innerHTML = '<div class="text-center text-muted small mt-4">Chưa có tin nhắn.</div>';
                    }

                    data.messages.forEach(message => {
                        const sender = message.is_admin == 1 ? 'admin' : 'user';
                        const div = document.createElement('div');
                        div.className = `chat-message ${sender}`;
                        div.innerText = message.message;
                        container.appendChild(div);
                    });

                    container.scrollTop = container.scrollHeight;
                }
            });
    },

    resetAi: () => {
        ChatApp.removeAiThinking();
        document.getElementById('msg-ai').innerHTML = '<div class="chat-message bot">Xin chào! Tôi là AI tư vấn đồ lưu niệm sự kiện <?= $_ENV['APP_NAME'] ?>. Bạn cần tìm sản phẩm nào?</div>';
    }
};

window.addEventListener('popstate', function() {
    if (ChatApp.isCategoryUrl(window.location.href)) {
        ChatApp.navigateContent(window.location.href, false);
    } else {
        window.location.reload();
    }
});
</script>
