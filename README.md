# NCKH Event Shop

Web ban do luu niem va thoi trang theo su kien trong nam, xay dung bang PHP thuan + MariaDB. Du an co giao dien khach hang, trang quan tri, gio hang, dat hang, gui hoa don email va chat ho tro tich hop AI.

## 1. Tong quan du an

- Muc tieu: ban san pham theo mua/su kien nhu Tet, 30/4, Quoc khanh 2/9, Noel va bo suu tap mac dinh.
- Kieu du an: monolithic PHP, khong dung framework.
- Giao dien: Bootstrap 5 + CSS tu viet + theme theo su kien.
- Du lieu: MariaDB/MySQL, ket noi qua PDO.
- Tich hop them:
  - AI chat tu van san pham qua OpenRouter.
  - Gui email hoa don qua PHPMailer.
  - Chuyen doi theme theo thang hoac theo session.

## 2. Chuc nang chinh

### Cho khach hang

- Xem trang chu voi hero va noi dung thay doi theo su kien hien tai.
- Xem danh sach san pham theo danh muc, tim kiem, sap xep, phan trang.
- Xem chi tiet san pham.
- Dang ky, dang nhap, dang xuat tai khoan.
- Them vao gio hang, cap nhat gio hang, mua ngay.
- Thanh toan checkout voi 2 hinh thuc:
  - COD.
  - Online payment mo phong bang QR.
- Tao don hang va xem lich su don trong trang ca nhan.
- Nhan email hoa don sau khi dat hang thanh cong.
- Chat ho tro:
  - Luu hoi thoai user-admin trong bang `messages`.
  - Co che AI goi y san pham, tra ve noi dung + URL dieu huong.

### Cho quan tri vien

- Dang nhap admin bang bien moi truong.
- Dashboard thong ke tong so san pham va don hang.
- Quan ly san pham:
  - Them, sua, xoa.
  - Upload hinh anh.
  - Gan danh muc va su kien cho tung san pham.
- Quan ly don hang:
  - Xem danh sach don.
  - Cap nhat trang thai `pending`, `paid`, `shipped`, `cancelled`.
- Ho tro khach hang qua giao dien chat admin.

## 3. Cong nghe va thu vien

### Backend

- PHP 8.0+.
- MariaDB/MySQL.
- PDO cho truy van CSDL.
- `vlucas/phpdotenv` de doc file `.env`.
- `phpmailer/phpmailer` de gui email hoa don.

### Frontend

- Bootstrap 5.3.2 qua CDN.
- Font Awesome 6 qua CDN.
- Google Fonts: Inter, Playfair Display.
- CSS custom trong `assets/css/`.

## 4. Yeu cau moi truong

- PHP 8.0 tro len.
- Composer.
- MariaDB hoac MySQL.
- Web server local nhu XAMPP, Laragon, Apache, Nginx + PHP-FPM.
- Cac PHP extension nen co:
  - `pdo_mysql`
  - `mbstring`
  - `curl`
  - `openssl`
  - `fileinfo`

## 5. Cau hinh bien moi truong

Du an dang doc bien trong file `.env`.

### Cac key hien co

```env
DB_HOST=
DB_NAME=
DB_USER=
DB_PASS=

APP_NAME=
BASE_URL=

ADMIN_USERNAME=
ADMIN_PASSWORD=
Ad_username=
Ad_password=

OPENROUTER_API_KEY=
OPENROUTER_API_URL=
OPENROUTER_MODEL=

EMAIL_ADMIN=
PHONE_ADMIN=
ADDRESS_ADMIN=
```

### Ghi chu

- He thong admin hien ho tro ca key moi `ADMIN_USERNAME`, `ADMIN_PASSWORD` va key cu `Ad_username`, `Ad_password`.
- `BASE_URL` duoc dung khi goi AI API.
- `EMAIL_ADMIN`, `PHONE_ADMIN`, `ADDRESS_ADMIN` duoc hien thi tren trang lien he va dung cho email.

## 6. Cai dat va chay du an

### Buoc 1: Clone source

```bash
git clone <repo-url>
cd nckh
```

### Buoc 2: Cai dependencies

`vendor/` da co san trong repo, nhung van nen cai lai de dong bo:

```bash
composer install
```

### Buoc 3: Tao file `.env`

Tao file `.env` trong thu muc goc va dien cac bien can thiet.

### Buoc 4: Tao database

1. Tao database moi, vi du `dbnckh`.
2. Import file `dbnckh.sql`.

Neu gap loi `#1064` ngay dong 1 khi import, nguyen nhan thuong la file co UTF-8 BOM. Hay luu file SQL dang `UTF-8 without BOM` roi import lai.

### Buoc 5: Cau hinh web server

Tro document root ve thu muc du an:

```text
d:\Code\php\nckh
```

Vi du voi PHP built-in server:

```bash
php -S localhost:8000
```

Sau do truy cap:

```text
http://localhost:8000
```

## 7. CSDL va cau truc bang

File schema: `dbnckh.sql`

### Cac bang chinh

- `users`: tai khoan nguoi dung.
- `categories`: danh muc san pham.
- `events`: danh sach su kien theo ngay bat dau/ket thuc.
- `products`: san pham, lien ket danh muc va `event_slug`.
- `orders`: thong tin don hang.
- `order_items`: chi tiet tung san pham trong don.
- `messages`: chat giua user va admin.
- `contacts`: bang lien he, hien da co schema nhung form lien he chua submit du lieu xuong bang nay.

### Du lieu mau dang co

- 7 danh muc san pham.
- 5 su kien:
  - `tet`
  - `gpmnam`
  - `quockhanh`
  - `noel`
  - `default`
- 1 user mau trong bang `users`.

## 8. Co che theme va su kien

Du an co 2 lop xu ly su kien:

### Theme giao dien

- Dinh nghia trong `includes/theme.php`.
- Tu dong doi theo thang:
  - Thang 1-3: `tet`
  - Thang 4-6: `gpmnam`
  - Thang 7-9: `quockhanh`
  - Thang 10-12: `noel`
- Co the override bang query string `?theme=tet`, `?theme=noel`, ... va luu vao session.

### Su kien ban hang

- Lay theo bang `events` neu co.
- Ham `getActiveSaleEvent()` tim su kien dang hieu luc theo ngay hien tai.
- San pham trong catalog se duoc loc theo `products.event_slug`.

## 9. Luong nghiep vu chinh

### Dat hang

1. User dang nhap.
2. Them san pham vao gio hang hoac mua ngay.
3. Vao `checkout.php`.
4. Tao don trong bang `orders`.
5. Tao cac dong chi tiet trong `order_items`.
6. Gui email hoa don neu co email nhan.
7. Chuyen sang `order_success.php`.

### Chat ho tro

1. User gui tin nhan.
2. Tin nhan luu vao bang `messages`.
3. Admin xem danh sach user dang chat trong `admin/chat.php`.
4. Neu dung AI, endpoint `api/process.php?action=chat_ai` goi OpenRouter de sinh JSON:
   - `reply`
   - `url`

## 10. API/endpoint noi bo

### `api/cart.php`

- `POST action=add`: them vao gio hang.
- `POST action=update`: cap nhat so luong.
- `POST action=remove`: xoa khoi gio hang.
- Tra JSON.

### `api/filter_products.php`

- Loc san pham bang AJAX theo:
  - `category`
  - `sort`
  - `search`
  - `page`
- Tra JSON gom HTML grid va HTML pagination.

### `api/process.php`

- `action=send`: user gui tin nhan.
- `action=fetch`: lay lich su chat cua user.
- `action=chat_ai`: goi AI tu van.
- `action=admin_get_users`: admin lay danh sach user chat.
- `action=admin_get_conversation`: admin lay hoi thoai cua 1 user.
- `action=admin_send`: admin gui tin nhan.

## 11. Cau truc thu muc

```text
admin/           Trang quan tri
api/             Endpoint AJAX/JSON
assets/
  css/           CSS chung + CSS theme
  images/        Anh san pham, logo, founder
  img/events/    Anh hero theo su kien
config/          Cau hinh ket noi DB + helper dung chung
includes/        Header, footer, navbar, theme, widget chat, invoice template
vendor/          Thu vien Composer
about.php        Trang gioi thieu
cart.php         Gio hang
category.php     Danh sach san pham
checkout.php     Thanh toan
contact.php      Trang lien he
dbnckh.sql       Schema database
index.php        Trang chu
login.php        Dang nhap user
product.php      Chi tiet san pham
profile.php      Ho so va lich su don
register.php     Dang ky
sendmail.php     Gui email hoa don
```

## 12. Tai khoan va dang nhap

### User

- Dang ky tai `register.php`.
- Dang nhap tai `login.php`.
- Trong du lieu mau da co 1 user seed, nhung README nay khong liet ke mat khau plaintext.

### Admin

- Dang nhap tai `admin/login.php`.
- Tai khoan admin doc tu `.env`.

## 13. Diem ky thuat dang co san

- Helper truy van tong hop trong `config/database.php`:
  - `getData()`
  - `getCount()`
  - `insertData()`
  - `updateData()`
  - `deleteData()`
- Helper gio hang:
  - `addToCart()`
  - `updateCart()`
  - `removeFromCart()`
  - `getCartSnapshot()`
- Helper event/theme:
  - `getActiveSaleEvent()`
  - `getActiveSaleEventSlug()`
  - `hasEventsTable()`
  - `hasProductEventColumn()`

## 14. Luu y hien trang du an

- Nhieu file dang co dau hieu loi encoding tieng Viet do da mo/luu sai bang ma truoc do. Chuc nang van chay, nhung chu hien thi co the bi vo dau o mot so noi.
- `contact.php` moi la giao dien form, chua co xu ly submit vao bang `contacts`.
- `sendmail.php` hien dang dung `EMAIL_ADMIN` tu `.env`, nhung mat khau SMTP trong code dang hard-code. Nen dua bien nay vao `.env` de an toan hon.
- Repo dang commit ca `vendor/`, co the gay nang source va kho review diff.
- Thanh toan online hien la mo phong bang QR, chua tich hop cong thanh toan thuc te.

## 15. Huong phat trien de xuat

- Dua toan bo secret SMTP vao `.env`.
- Hoan thien form lien he de luu `contacts` va gui thong bao.
- Chuan hoa UTF-8 khong BOM cho toan bo source.
- Tach logic helper DB, auth, cart, event thanh cac module rieng.
- Bo sung validation server-side va CSRF protection cho form admin/user.
- Them migration hoac seeder thay cho import SQL thu cong.

## 16. Tac gia va muc dich

Du an phu hop de hoc tap, demo mon hoc, nghien cuu khoa hoc hoac lam do an nho ve:

- PHP thuần
- PDO va MariaDB
- Gio hang va dat hang
- Theme theo su kien
- Tich hop AI chat
- Gui email bang PHPMailer

Neu muon nang cap du an, nen uu tien bao mat secret, sua encoding va bo sung test/co che deploy.
