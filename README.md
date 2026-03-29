# web-ban-hang-AI-sendmail

Website ban do luu niem va thoi trang theo su kien trong nam, xay dung bang PHP thuan + MySQL, co tich hop AI chatbot, chat ho tro khach hang, gui email hoa don va khu vuc quan tri.

## Tong quan

Du an hien tai duoc to chuc theo mo hinh PHP truyen thong:

- Frontend cho khach hang: trang chu, danh muc, chi tiet san pham, gio hang, thanh toan, tai khoan, lich su don hang.
- AI chatbot: goi y san pham theo noi dung nguoi dung nhap, uu tien san pham dung su kien dang kich hoat.
- Chat ho tro: khach hang nhan tin voi admin theo thoi gian thuc qua bang `messages`.
- Gui email hoa don: sau khi dat hang, he thong tao noi dung hoa don HTML va gui qua SMTP.
- Admin dashboard: quan ly san pham, danh muc, don hang va chat voi khach.
- Theme theo mua/su kien: `Tet`, `30/4`, `2/9`, `Noel`, `default`.

## Cong nghe dang dung

- PHP
- MySQL / MariaDB
- PDO
- Bootstrap 5
- Font Awesome
- `vlucas/phpdotenv`
- `phpmailer/phpmailer`
- OpenRouter API cho AI chat

## Tinh nang chinh

### 1. Frontend ban hang

- Hien thi san pham theo su kien dang active.
- Loc theo danh muc, tim kiem, sap xep, phan trang.
- Xem chi tiet san pham va mua ngay.
- Gio hang luu trong session.
- Dat hang theo 2 cach:
- Thanh toan khi nhan hang.
- Thanh toan online bang QR tao dong.
- Xem trang xac nhan don hang va chi tiet hoa don.
- Quan ly tai khoan ca nhan, doi mat khau, xem lich su don hang.

### 2. AI chatbot va chat ho tro

- AI chat widget xuat hien o footer.
- AI co the:
- nhan dien y dinh tim san pham,
- loc san pham theo tu khoa,
- uu tien san pham con hang,
- tra ve goi y + link san pham.
- Chat support dung bang `messages`, admin co man hinh doc va phan hoi.

### 3. He thong admin

- Dang nhap admin bang bien moi truong.
- Dashboard thong ke tong san pham va tong don hang.
- CRUD danh muc.
- CRUD san pham, upload anh, gan theo event.
- Quan ly trang thai don hang.
- Cua so chat voi khach hang.

### 4. Theme va su kien

- Theme auto theo thang:
- thang 1-3: Tet
- thang 4-6: 30/4
- thang 7-9: 2/9
- thang 10-12: Noel
- Co the override bang query `?theme=...` va luu vao session.
- Neu co bang `events`, he thong co the lay su kien dang active tu database.

## Cau truc thu muc

```text
.
|-- admin/                 # Khu vuc quan tri
|-- api/                   # API AJAX cho cart, filter, chat
|-- assets/
|   |-- css/               # CSS chung + CSS admin + CSS theme
|   |-- images/            # Anh san pham, logo, founder
|   `-- img/events/        # Hero image theo su kien
|-- config/
|   `-- database.php       # Ket noi DB + helper core
|-- includes/              # Header, footer, navbar, theme, invoice, chat widget
|-- vendor/                # Composer packages
|-- index.php
|-- category.php
|-- product.php
|-- cart.php
|-- checkout.php
|-- order_success.php
|-- order_detail.php
|-- profile.php
|-- login.php
|-- register.php
|-- contact.php
|-- about.php
|-- sendmail.php
`-- .env
```

## Cac file quan trong

- `config/database.php`
- Load `.env`, tao ket noi PDO, cung cap helper CRUD, auth, cart, event filter, chatbot product suggestion.
- `includes/theme.php`
- Quan ly giao dien theo su kien va logic auto theme.
- `includes/chat_widget.php`
- Widget chat AI + chat support.
- `api/process.php`
- Xu ly gui/lay tin nhan support va goi OpenRouter cho AI.
- `sendmail.php`
- Gui email hoa don bang PHPMailer.
- `includes/invoice_template.php`
- Render hoa don HTML cho web va email.
- `admin/*.php`
- Khu vuc quan tri.

## Danh sach trang frontend

- `index.php`: trang chu theo theme.
- `category.php`: danh sach san pham + bo loc + AJAX pagination.
- `product.php`: chi tiet san pham.
- `cart.php`: gio hang.
- `checkout.php`: dat hang, chon phuong thuc thanh toan, tao QR.
- `order_success.php`: xac nhan dat hang thanh cong.
- `order_detail.php`: xem hoa don chi tiet.
- `profile.php`: thong tin tai khoan, doi mat khau, lich su don hang.
- `login.php`: dang nhap khach hang.
- `register.php`: dang ky khach hang.
- `about.php`: gioi thieu thuong hieu.
- `contact.php`: trang lien he.
- `404.php`: trang loi 404.

## Danh sach trang admin

- `admin/login.php`: dang nhap admin.
- `admin/index.php`: dashboard.
- `admin/products.php`: quan ly san pham.
- `admin/categories.php`: quan ly danh muc.
- `admin/orders.php`: quan ly don hang.
- `admin/chat.php`: chat voi khach hang.

## Danh sach API/AJAX

- `api/cart.php`
- Them, cap nhat, xoa san pham trong gio hang.
- `api/filter_products.php`
- Render lai grid san pham va pagination theo filter.
- `api/process.php`
- Gui tin nhan support.
- Lay lich su chat cua user.
- Admin lay danh sach user va hoi thoai.
- AI chat qua OpenRouter.

## Cac bang du lieu du kien

Khong thay file dump SQL trong workspace hien tai, nhung tu code co the suy ra du an can toi thieu cac bang sau:

### `users`

- `id`
- `name`
- `phone`
- `email`
- `password`
- `password_length`
- `created_at`

### `categories`

- `id`
- `name`

### `products`

- `id`
- `name`
- `price`
- `stock`
- `category_id`
- `description`
- `image`
- `created_at`
- `event_slug` (neu dung he thong su kien)

### `orders`

- `id`
- `user_id`
- `name`
- `phone`
- `address`
- `payment_method`
- `total`
- `status`
- `created_at`

### `order_items`

- `id`
- `order_id`
- `product_id`
- `product_name`
- `price`
- `quantity`

### `messages`

- `id`
- `user_id`
- `message`
- `is_admin`
- `created_at`

### `events` (tuy chon nhung duoc ho tro trong code)

- `id`
- `slug`
- `name`
- `start_date`
- `end_date`
- `priority`
- `is_enabled`

## Cai dat va chay local

### 1. Yeu cau

- PHP 8.x khuyen nghi
- MySQL / MariaDB
- Composer
- Web server local nhu XAMPP, Laragon, Apache, Nginx

### 2. Cai package

```bash
composer install
```

### 3. Tao CSDL

- Tao database, mac dinh theo `.env` hien tai la `bandosukien`.
- Tao cac bang theo cau truc phan tren.
- Nap du lieu mau cho `categories`, `products`, `users`, `events` neu can.

### 4. Cau hinh `.env`

Tao file `.env` o root project.

README nay co dua lai noi dung `.env` hien tai cua du an, nhung da che cac gia tri nhay cam de tranh lo secret khi public repo.

```dotenv
# Database Configuration Host
# DB_HOST="sql204.infinityfree.com"
# DB_NAME="if0_41419290_crowne"
# DB_USER="if0_41419290"
# DB_PASS="***REDACTED***"

# Database Configuration Local
DB_HOST="localhost"
DB_NAME="bandosukien"
DB_USER="root"
DB_PASS="***REDACTED***"

# Application Settings
APP_NAME="Crowne"
BASE_URL="https://crowne.kesug.com/"

# Admin login
# Luu y: code admin dang ho tro ca cap key moi va key cu:
# ADMIN_USERNAME / ADMIN_PASSWORD
# hoac Ad_username / Ad_password
Ad_username="admin"
Ad_password="***REDACTED***"

# AI config
OPENROUTER_API_KEY="***REDACTED***"
OPENROUTER_API_URL="https://openrouter.ai/api/v1/chat/completions"
OPENROUTER_MODEL="nvidia/nemotron-3-super-120b-a12b:free"

# Email config
EMAIL_ADMIN="tine.dao19@gmail.com"
PHONE_ADMIN="0979499802"
ADDRESS_ADMIN="Dai hoc Hung Vuong, Phu Tho"
```

### 5. Chay du an

Dat project vao web root, vi du:

- XAMPP: `htdocs/web-ban-hang-AI-sendmail`
- Laragon: `www/web-ban-hang-AI-sendmail`

Sau do truy cap:

```text
http://localhost/web-ban-hang-AI-sendmail/
```

Neu dung virtual host thi truy cap theo domain local cua ban.

## Tai khoan dang nhap

### Khach hang

- Dang ky truc tiep tai `register.php`
- Dang nhap bang so dien thoai + mat khau

### Admin

- Dang nhap tai `admin/login.php`
- Tai khoan lay tu `.env`
- Code hien dang ho tro:
- `ADMIN_USERNAME`, `ADMIN_PASSWORD`
- hoac `Ad_username`, `Ad_password`

## Luong dat hang

1. Khach dang ky / dang nhap.
2. Chon san pham trong su kien dang active.
3. Them vao gio hang hoac mua ngay.
4. Sang `checkout.php`.
5. Dien thong tin giao hang.
6. Chon `cod` hoac `online`.
7. He thong tao don hang + `order_items`.
8. Gui email hoa don neu co email.
9. Chuyen huong sang `order_success.php`.
10. Xem lai trong `profile.php?tab=orders` hoac `order_detail.php`.

## Ghi chu ky thuat quan trong

- `config/database.php` co request-level cache cho kiem tra ton tai bang.
- San pham co the bi loc theo `event_slug` neu schema co cot nay.
- `getCatalogProducts()` la helper trung tam cho listing va pagination.
- `getChatbotProductSuggestions()` la helper lay san pham de AI tra loi.
- Gio hang luu trong `$_SESSION['cart']`.
- `includes/header.php` va `includes/footer.php` duoc dung chung cho frontend.

## Cac van de / rui ro quan sat duoc khi quet code

### 1. Lo secret trong cau hinh

- File `.env` hien dang chua gia tri thuc cho:
- DB password
- OpenRouter API key
- thong tin email admin
- Tuyet doi khong nen public nguyen van file `.env`.

### 2. Mat khau SMTP dang hard-code

- `sendmail.php` dang chua mat khau SMTP hard-code trong code.
- Nen dua bien nay vao `.env`, vi du `SMTP_PASSWORD`.

### 3. Ten bien moi truong admin chua dong nhat

- `.env` hien dang dung `Ad_username`, `Ad_password`.
- `admin/login.php` da ho tro them `ADMIN_USERNAME`, `ADMIN_PASSWORD`.
- Nen chuan hoa ve 1 chuan duy nhat.

### 4. `BASE_URL` trong `.env` goc dang bi sai format

- File `.env` dang de `BASE_URL=http:https://crowne.kesug.com/`
- Trong README nay da viet lai theo y dinh hop ly la `https://crowne.kesug.com/`
- Nen sua file `.env` that de tranh loi header / referer khi goi AI API.

### 5. Van de encoding

- Nhieu file dang co dau hieu encoding loi khi doc bang terminal.
- Nen chuan hoa toan bo file sang UTF-8 khong BOM.

### 6. Chua thay file SQL dump trong workspace

- Hien khong thay file `.sql` nao trong root project.
- Neu muon repo de setup nhanh, nen bo sung:
- `database.sql` hoac `schema.sql`
- va co them du lieu mau.

## Goi y cai thien tiep

- Tao file `.env.example`.
- Chuyen SMTP password vao `.env`.
- Bo sung file dump CSDL.
- Viet migration / seeder thay cho import tay.
- Chuan hoa toan bo giao dien sang tieng Viet co dau.
- Them validation server-side chat che hon cho checkout va upload.
- Them CSRF protection cho form admin / user.

## Composer packages

```json
{
  "require": {
    "vlucas/phpdotenv": "^5.6",
    "phpmailer/phpmailer": "^7.0"
  }
}
```

## Trang thai hien tai cua README

README nay duoc viet dua tren viec quet thuc te cac file sau:

- `config/database.php`
- `includes/theme.php`
- `includes/chat_widget.php`
- `includes/invoice_template.php`
- `sendmail.php`
- `api/cart.php`
- `api/filter_products.php`
- `api/process.php`
- cac trang frontend
- cac trang admin
- file `.env`

No phan anh trang thai codebase hien tai, khong phai tai lieu ly tuong hoa.
