# Viet Cinema

Nền tảng website rạp chiếu phim theo PHP MVC cho nhóm PHUONG, HUY và QUY.
Bản hiện tại là **MVC Foundation**: các trang mẫu, layout dùng chung và core có kiểm thử;
chưa có đăng nhập, CRUD nghiệp vụ, đặt ghế hoặc thanh toán thật.

## Công nghệ

PHP >= 8.1, PDO/MySQL, HTML5, CSS3, JavaScript thuần. Không phụ thuộc framework lớn
hoặc thư viện bên ngoài. Composer khai báo PSR-4 và script; autoloader nội bộ cho phép
chạy ngay khi chưa cài Composer. `ext-session` cần để chạy; `ext-curl` và `proc_open`
cần cho bộ HTTP test. `pdo_mysql` chỉ cần khi triển khai truy vấn MySQL sau này.

## Cài đặt và chạy

```powershell
git clone https://github.com/phuong000/viet-cinema.git
cd viet-cinema
# Chọn nhánh phù hợp đã có trên remote; không tạo lại nhánh hiện hành.
Copy-Item .env.example .env
php -S 127.0.0.1:8000 -t public public/index.php
```

Mở <http://127.0.0.1:8000>. Website hoạt động ngay cả khi không có `.env` và MySQL.
DocumentRoot **bắt buộc là `public/`**, không phải repository root. Hướng dẫn Windows,
VS Code, Laragon và XAMPP: [docs/SETUP.md](docs/SETUP.md).

## Cấu hình database

Chỉnh `DB_DSN`, `DB_USERNAME`, `DB_PASSWORD` trong `.env` bằng cấu hình local của bạn.
Không ghi credentials vào source. `.env.example` và `config/database.example.php`
chỉ là mẫu không có mật khẩu. Chưa có schema chính thức, không tự động chạy migration/seed.
Không gọi `Database::connection()` khi khởi động; chỉ model có truy vấn mới kết nối.
Xem [database/README.md](database/README.md) và [docs/DATABASE_REVIEW.md](docs/DATABASE_REVIEW.md).

## Kiểm thử

```powershell
php tests/run.php
php tests/Integration/http.php
php tests/Integration/git-audit.php --portable
php tests/lint.php
```

Bộ kiểm thử không kết nối database thật. HTTP test tự mở server localhost với port trống,
chạy request bằng curl rồi dừng server. PDO binding được kiểm tra bằng test double, không
phải tích hợp MySQL. Kết quả và hạn chế thực tế: [FOUNDATION_REPORT.md](FOUNDATION_REPORT.md).

Git audit `--portable` dùng cho clone mới, nhánh thành viên và CI. Chạy không có cờ này là
nghiệm thu lịch sử giai đoạn 2, yêu cầu đúng nhánh foundation và refs baseline; không dùng làm gate của develop.

## Trang mẫu

`/`, `/movies`, `/movies/sample`, `/showtimes`, `/login`, `/register`, `/booking`,
`/admin/demo`; `/health` trả JSON. `POST /login` và `POST /register` chỉ kiểm tra biểu mẫu
với CSRF, không tạo user hoặc phiên đăng nhập. `/admin` mặc định 403, không có Admin mặc định.

## Cấu trúc

```text
app/Controllers/{Auth,Movies,Booking,Admin}/
app/Models/{Auth,Movies,Booking}/
app/Services/{Auth,Movies,Booking}/
app/{Core,Middleware,Helpers}/
app/Views/{layouts,partials,auth,movies,booking,admin,errors}/
config/                  Cấu hình tham khảo, cấu hình thật bị ignore
database/{migrations,seeds}/  Chưa có SQL hoặc dữ liệu thật
public/{index.php,assets/}    DocumentRoot duy nhất
routes/{web,auth,movies,booking,admin}.php
storage/{logs,cache}/    File nội bộ, không public
tests/{Unit,Integration}/
docs/                   Kiến trúc, setup, quy chuẩn và workflow
bootstrap.php           Autoload và environment
```

## Làm việc nhóm

- **PHUONG**: Auth, Authorization, Users, Administration, Core, config và layout chung.
- **HUY**: Movies, Categories, Cinema Rooms, Seats, Showtimes, Reviews.
- **QUY**: Booking, Combos, Discounts, Payment, Tickets, Booking History.

Các nhánh cá nhân `PHUONG`, `HUY`, `QUY` mở Pull Request vào `develop`; `main` là nhánh ổn định.
Không đưa Foundation lên GitHub trước khi người dùng nghiệm thu.

Tài liệu: [kiến trúc](docs/ARCHITECTURE.md), [quy trình nhóm](docs/TEAM_WORKFLOW.md),
[coding standard](docs/CODING_STANDARD.md).
