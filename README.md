# web-ban-hang-AI-sendmail

Website bán đồ lưu niệm và thời trang theo sự kiện trong năm, xây dựng bằng PHP thuần + MySQL, có tích hợp AI chatbot, chat hỗ trợ khách hàng, gửi email hóa đơn và khu vực quản trị.

## Tổng quan

Dự án hiện tại được tổ chức theo mô hình PHP truyền thống:

- **Frontend cho khách hàng:** Trang chủ, danh mục, chi tiết sản phẩm, giỏ hàng, thanh toán, tài khoản cá nhân, lịch sử đơn hàng.
- **AI chatbot:** Gợi ý sản phẩm dựa theo yêu cầu người dùng nhập, ưu tiên tìm kiếm sản phẩm thuộc các sự kiện đang kích hoạt.
- **Chat hỗ trợ:** Khách hàng có thể nhắn tin trực tiếp với admin theo thời gian thực (lưu qua bảng `messages`).
- **Gửi email hóa đơn:** Tự động render hóa đơn HTML và gửi email xác nhận qua SMTP sau khi đặt hàng thành công.
- **Admin dashboard:** Quản lý sản phẩm, danh mục, theo dõi đơn hàng và cửa sổ chat với khách hàng.
- **Theme theo mùa/sự kiện:** Giao diện thay đổi linh hoạt theo `Tet`, `30/4`, `2/9`, `Noel`, `default`.

## Công nghệ sử dụng

- PHP 8.x
- MySQL / MariaDB
- PDO
- Bootstrap 5
- Font Awesome
- `vlucas/phpdotenv` (Quản lý biến môi trường)
- `phpmailer/phpmailer` (Gửi Email)
- OpenRouter API (Tích hợp AI)

## Tính năng chính

### 1. Frontend bán hàng
- Hiển thị sản phẩm động theo sự kiện đang active.
- Lọc sản phẩm theo danh mục, tìm kiếm, sắp xếp và phân trang bằng AJAX.
- Chức năng xem chi tiết sản phẩm và thao tác "Mua ngay".
- Giỏ hàng (Cart) lưu trữ an toàn trong Session.
- Đặt hàng theo 2 phương thức:
  - Thanh toán khi nhận hàng (COD).
  - Thanh toán online thông qua mã QR tạo động.
- Giao diện lịch sử đơn hàng chi tiết và hóa đơn trực quan.

### 2. AI Chatbot và Chat hỗ trợ
- Tích hợp AI chat widget ở footer trang web.
- AI có khả năng:
  - Nhận diện ý định mua sắm của khách.
  - Lọc sản phẩm theo từ khóa tự nhiên.
  - Ưu tiên hiển thị các sản phẩm còn hàng.
  - Trả lời gợi ý kèm theo link trực tiếp đến sản phẩm.
- Tính năng Chat Support sử dụng bảng `messages`, cho phép Admin có không gian riêng để theo dõi và phản hồi khách hàng.

### 3. Hệ thống quản trị (Admin)
- Xác thực bảo mật qua biến môi trường (`.env`).
- Dashboard tổng quan, thống kê lượng sản phẩm và đơn hàng.
- CRUD Danh mục & Sản phẩm, hỗ trợ upload ảnh và gắn thẻ sự kiện (event).
- Quản lý và thay đổi trạng thái đơn hàng.
- Màn hình chat tập trung để tư vấn người dùng.

### 4. Theme và Sự kiện
- Cơ chế Theme tự động đổi theo quý/tháng:
  - Tháng 1-3: Tết
  - Tháng 4-6: 30/4
  - Tháng 7-9: 2/9
  - Tháng 10-12: Noel
- Hỗ trợ ép kiểu theme tạm thời qua Query String: `?theme=...` (Lưu Session).

## Cấu trúc thư mục

```text
.
|-- admin/                 # Giao diện và logic khu vực quản trị
|-- api/                   # Các file API AJAX xử lý cart, filter, chat
|-- assets/
|   |-- css/               # CSS tổng thể, CSS admin và CSS cho theme
|   |-- images/            # Ảnh upload của sản phẩm, logo
|   `-- img/events/        # Ảnh Hero Banner phân theo từng sự kiện
|-- config/
|   `-- database.php       # Khởi tạo kết nối DB và khai báo các helpers
|-- includes/              # Các components: Header, footer, navbar, theme, chat widget...
|-- vendor/                # Thư mục sinh ra khi cài Composer
|-- index.php              # Trang chủ hệ thống
|-- ...                    # Các file giao diện Frontend khác
|-- sendmail.php           # Logic thiết lập và gửi Email bằng PHPMailer
`-- .env                   # Cấu hình biến môi trường (Database, API Keys...)
```

## Hướng dẫn cài đặt (Setup)

Để chạy dự án local thành công, vui lòng thực hiện tuần tự các bước sau:

### 1. Yêu cầu hệ thống
- PHP 8.x (Khuyến nghị)
- MySQL / MariaDB
- Composer (Dùng để tải thư viện)
- Web Server ảo như XAMPP, Laragon, WAMP...

### 2. Cài đặt thư viện (Composer)
Mở Terminal (hoặc Command Prompt), di chuyển vào thư mục gốc của dự án và chạy lệnh sau để tải về `phpdotenv` và `phpmailer`:
```bash
composer install
```
*(Nếu chạy thành công, thư mục `vendor` sẽ xuất hiện).*

### 3. Khởi tạo Cơ sở dữ liệu (Import DB)
1. Mở phpMyAdmin (thường là `http://localhost/phpmyadmin`) hoặc công cụ quản lý MySQL của bạn.
2. Tạo một Database mới, ví dụ: `bandosukien` (nên chọn Collation là `utf8mb4_unicode_ci`).
3. Tìm file CSDL mẫu (`.sql`) của dự án (ví dụ `database.sql` nếu có) và **Import** vào database vừa tạo.
   *Hệ thống cần tối thiểu các bảng: `users`, `categories`, `products`, `orders`, `order_items`, `messages` và `events`.*

### 4. Thiết lập biến môi trường (.env)
Tạo một file mới tinh tên là `.env` (lưu ý có dấu chấm ở đầu) đặt ngang hàng với thư mục `config`. Sao chép nội dung sau vào file `.env` và tùy chỉnh lại cho đúng thông tin máy bạn:

```dotenv
# Database Configuration Local
DB_HOST="localhost"
DB_NAME="bandosukien"
DB_USER="root"
DB_PASS="" # Nhập mật khẩu MySQL của bạn, nếu dùng XAMPP thường để trống

# Application Settings
APP_NAME="Crowné"
BASE_URL="http://localhost/web-ban-hang-AI-sendmail/"

# Admin login
# Sử dụng tài khoản này để đăng nhập vào thư mục /admin/login.php
Ad_username="admin"
Ad_password="mat_khau_admin_cua_ban"

# AI config (OpenRouter)
OPENROUTER_API_KEY="sk-or-v1-YOUR-KEY-HERE"
OPENROUTER_API_URL="https://openrouter.ai/api/v1/chat/completions"
OPENROUTER_MODEL="nvidia/nemotron-3-super-120b-a12b:free"

# Email config (Dùng cho SMTP)
EMAIL_ADMIN="email_cua_ban@gmail.com"
SMTP_PASSWORD="mat_khau_ung_dung_gmail_cua_ban"
PHONE_ADMIN="0979499802"
ADDRESS_ADMIN="Dai hoc Hung Vuong, Phu Tho"
```

### 5. Chạy ứng dụng
Đưa thư mục mã nguồn vào thư mục chứa web của server:
- **XAMPP:** `C:\xampp\htdocs\web-ban-hang-AI-sendmail`
- **Laragon:** `C:\laragon\www\web-ban-hang-AI-sendmail`

Cuối cùng, mở trình duyệt web và truy cập vào đường dẫn:
`http://localhost/web-ban-hang-AI-sendmail/`

---

## Tài khoản đăng nhập mẫu

**Tài khoản Khách hàng:**
- Khách có thể trực tiếp đăng ký tài khoản tại màn hình `register.php`.
- Đăng nhập bằng số điện thoại + mật khẩu.

**Tài khoản Admin:**
- Truy cập trang: `http://localhost/web-ban-hang-AI-sendmail/admin/login.php`
- Điền thông tin đã cài đặt trong biến `Ad_username` và `Ad_password` ở file `.env`.

## Luồng đặt hàng cơ bản
1. Khách hàng đăng ký / đăng nhập.
2. Duyệt sản phẩm hiển thị theo sự kiện đang active, thêm vào giỏ hàng hoặc bấm "Mua ngay".
3. Chuyển sang giao diện thanh toán `checkout.php`.
4. Khai báo thông tin nhận hàng, lựa chọn phương thức (COD hoặc Thanh toán Online QR).
5. Hệ thống khởi tạo đơn hàng và các `order_items`.
6. Tự động gửi Email hóa đơn nếu cấu hình SMTP chính xác.
7. Chuyển hướng người dùng sang trang `order_success.php`.
8. Khách hàng có thể theo dõi lại đơn trong `profile.php?tab=orders` hoặc in hóa đơn tại `order_detail.php`.

## Ghi chú Kỹ thuật quan trọng
- File `config/database.php` đóng vai trò trung tâm, quản lý khởi tạo PDO, helper CRUD, và auth.
- Hàm `getCatalogProducts()` là core helper để render danh sách sản phẩm và phân trang.
- Giỏ hàng được lưu trữ dưới dạng mảng trong `$_SESSION['cart']`.

## Các vấn đề cần lưu ý và Cải thiện
1. **Lộ Secret Config:** Nếu đẩy dự án lên Github công khai, tuyệt đối **KHÔNG** đưa file `.env` lên mạng. Cần đưa vào file `.gitignore`.
2. **Hard-code SMTP Mật khẩu:** File `sendmail.php` hiện đang ghi trực tiếp mật khẩu email (`klnupvgegggzwmdr`) vào code. Hãy sử dụng lệnh gọi qua `$_ENV['SMTP_PASSWORD']` như gợi ý phía trên.
3. **Đồng nhất biến môi trường Admin:** File `.env` đang dùng `Ad_username` và code lại support `ADMIN_USERNAME`, nên đồng nhất lại thành 1 định dạng chuẩn.
4. **Thiếu file SQL Backup:** Dự án nên có 1 file dump `.sql` chính thức đi kèm để người dùng sau có thể clone và import dễ dàng hơn.
