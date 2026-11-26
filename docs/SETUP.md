# Setup Windows, VS Code, XAMPP và PHP Development Server

## 1. Chuẩn bị

Cài Git, VS Code và PHP >= 8.1 (XAMPP hoặc Laragon). Bật PDO/session; pdo_mysql khi làm MySQL,
curl để chạy HTTP tests. Kiểm tra `php --version`, `php -m`. Nếu chưa có PATH, dùng
`C:\xampp\php\php.exe` hoặc đường dẫn Laragon đang có tại máy này:

```powershell
$phpRuntime = 'C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe'
& $phpRuntime --version
```

Không cần Node/npm hay Composer install để chạy Foundation. Composer nếu đã cài:
`composer test` tương đương `php tests/run.php`.

## 2. Clone mới (bỏ qua nếu đang dùng workspace hiện tại)

```powershell
git clone https://github.com/phuong000/viet-cinema.git
cd viet-cinema
git status
git branch -a
# Sau khi Foundation đã được nghiệm thu và đưa lên remote, chọn nhánh được nhóm phân công.
code .
Copy-Item .env.example .env
```

Không tạo lại repo hoặc nhánh hiện có. Workspace đang làm: `E:\Projects\viet-cinema`, nhánh
foundation `chore/mvc-foundation`. Nhánh cá nhân: PHUONG, HUY, QUY; tích hợp develop; ổn định main.
Đặt VS Code encoding UTF-8 và line endings LF. Chưa có cấu hình .vscode được commit.

## 3. PHP Development Server

```powershell
php -S 127.0.0.1:8000 -t public public/index.php
# Hoặc:
& $phpRuntime -S 127.0.0.1:8000 -t public public/index.php
```

Chạy tại repository root. Mở `http://127.0.0.1:8000`, thử `/movies`, `/login`, `/admin/demo`.
Ctrl+C dừng server. Chỉ bind localhost cho phát triển. Không dùng built-in server cho production.
Application phục vụ CSS/JS/image allowlist trong assets; không dùng `return false` để vô tình chạy PHP upload.

## 4. XAMPP Apache

Thêm VirtualHost vào cấu hình Apache riêng của máy (không có thay đổi server tự động trong repo):

```apache
<VirtualHost *:80>
    ServerName viet-cinema.local
    DocumentRoot "E:/Projects/viet-cinema/public"
    <Directory "E:/Projects/viet-cinema">
        Require all denied
    </Directory>
    <Directory "E:/Projects/viet-cinema/public">
        AllowOverride All
        Options -Indexes
        Require all granted
    </Directory>
</VirtualHost>
```

Bật mod_rewrite; khai báo hosts `127.0.0.1 viet-cinema.local` nếu muốn dùng hostname này.
Khởi động lại Apache và mở `http://viet-cinema.local`. Việc chỉnh config/hosts cần quyền quản trị
và do thành viên thực hiện. Nếu đặt dự án trong htdocs, vẫn phải cấu hình document root public;
không truy cập trực tiếp toàn repo qua `http://localhost/viet-cinema`.
Root `.htaccess` deny là lớp dự phòng; không thay thế document root đúng.
PHP có thể cấu hình handler khác nhau giữa XAMPP/Laragon: xác minh private paths và PHP asset bị chặn
trên Apache thực trước triển khai. Apache Laragon biệt lập đã được kiểm ở giai đoạn 2.4;
xem FOUNDATION_ACCEPTANCE_REPORT.md. Kết quả này không thay thế kiểm cấu hình VirtualHost riêng của từng máy.

## 5. Environment và database

`.env` là local và bị Git ignore; chỉnh APP_NAME, DB_DSN/USERNAME/PASSWORD khi cần.
Không cần MySQL để xem placeholder. Không có schema, migration hoặc seed chính thức để import.
Không copy database thật hoặc chạy SQL tự suy đoán. Đọc DATABASE_REVIEW.md trước khi phát triển truy vấn.
`APP_ENV=production` là nhãn môi trường; response luôn giấu exception dù local hay production.
Local HTTP dùng SESSION_SECURE=false; HTTPS/proxy đặt true, kiểm tra Set-Cookie thực tế.

Ứng dụng tự tạo `storage/cache/sessions`; cấp quyền ghi cho user chạy PHP vào storage/cache và storage/logs.
Đừng đổi quyền toàn bộ repo thành public writable. Nếu session lỗi, kiểm tra ACL của storage thay vì
vô hiệu hóa session security. Session file hết hạn do PHP GC; định kỳ dọn cache/log theo quy trình vận hành.

## 6. Kiểm tra trước PR

```powershell
php tests/run.php
php tests/Integration/http.php
php tests/Integration/git-audit.php --portable
php tests/lint.php
Get-ChildItem app,config,public,routes,tests -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
php -l bootstrap.php
git diff --check
git status --short
git check-ignore .env config/database.php storage/logs/application.log
git ls-files
```

HTTP tests cần curl và proc_open, tự khởi động/dừng server port trống. Không đặt fixture router làm
server router cho website dùng thật. Không đưa logs/session vào Git. Nếu port 8000 bận, chọn port khác;
đừng dừng process không thuộc phiên làm việc của bạn.

Git audit không có `--portable` giữ nguyên guard nghiệm thu giai đoạn 2 (nhánh foundation/SHA baseline).
Clone mới hoặc CI dùng `--portable`: vẫn kiểm ignore, secrets, whitespace và xác nhận refs không bị đổi
trong lúc audit, không yêu cầu có đủ các remote-tracking refs lịch sử.

### Quyền thư mục trên Windows

Chặn HTTP không đồng nghĩa file chỉ một tài khoản local được đọc. Trên Windows, `mkdir(0700)` của PHP
không thay thế NTFS ACL. Kiểm `Get-Acl storage/cache/sessions` và config/log trước dùng máy nhiều tài khoản.
Runtime chỉ cần đọc source và ghi các thư mục storage cần thiết; giới hạn đọc session/log/config theo identity
đã chọn. Không tự áp dụng lệnh ACL chung cho mọi máy hoặc đổi quyền toàn repo. Workspace hiện tại còn ACL
kế thừa rộng; xem FOUNDATION_ACCEPTANCE_REPORT.md trước dùng chung hoặc triển khai.
