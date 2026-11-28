# Coding standard

## PHP

Theo PSR-12: UTF-8 không BOM, LF, bốn spaces, không tab; một class/interface mỗi file,
`declare(strict_types=1);`, namespace sau dòng trống, imports trước class. Không closing PHP tag
trong file chỉ PHP. Dùng type declarations và return types; ưu tiên câu lệnh dễ đọc, hàm ngắn.
Templates PHP/HTML và các test double ngắn không là chuẩn cho style class nghiệp vụ.
Chưa cài PHPCS; syntax test không đồng nghĩa kiểm chứng đầy đủ PSR-12 tự động.

Namespace `App\Controllers\Movies` tương ứng `app/Controllers/Movies`. Class PascalCase,
method/variable camelCase, constant UPPER_SNAKE_CASE. Route lowercase, danh từ dễ hiểu,
không chứa action thực thi tự do từ request. Tên bảng/cột cần theo quyết định ERD chung, chưa chốt tại đây.

## MVC conventions

- Controller suffix `Controller`; kế thừa Core Controller; trả Response, không echo hoặc exit.
- Model chịu trách nhiệm truy vấn, không render HTML. Luôn dùng `query($sql, $parameters)` hoặc prepare/execute;
  tuyệt đối không nối chuỗi input vào SQL. Không tự kết nối DB trong constructor.
- Service chứa nghiệp vụ phối hợp nhiều model và transaction; không đọc trực tiếp globals HTTP.
- View không truy vấn database hoặc xử lý quyền. Dùng `View::escape()` cho text/attribute động,
  quote mọi attribute. Không escape toàn bộ `$content` do renderer tạo; không trộn raw user HTML vào content.
- Route nằm trong file module, controller không đăng ký routes. Route thay đổi dữ liệu dùng POST/PUT/PATCH/DELETE,
  không dùng GET để ghi dữ liệu và không bỏ qua CSRF.
- Tài liệu và thông báo người dùng bằng tiếng Việt có dấu; identifier/code bằng tiếng Anh nhất quán.

## Security

Validate phía server cả kiểu và miền giá trị; browser validation chỉ hỗ trợ UX. Không lưu/echo password
hoặc token trong log. Không đưa raw exception vào response. Mật khẩu thật sau này dùng password_hash/verify,
regenerate session ID khi login/đổi quyền, invalidation khi logout; chưa triển khai trong foundation.
Không tin user/role/price từ client; admin mặc định deny. Thay đổi access policy phải có security review.

CSRF hidden field dùng `Csrf::token()`. Session cookie HttpOnly, Lax, Secure theo HTTPS.
Sau proxy HTTPS đặt `SESSION_SECURE=true` và xác minh môi trường; không tự tin mọi forwarded header.
Đừng commit .env/config thật, logs/cache hoặc uploads. Không hardcode credentials.

Chỉ public là DocumentRoot. Upload tương lai lưu ở `storage/uploads` ngoài public; nếu cần tải xuống,
dùng controller với kiểm tra quyền và MIME, không include/require file upload. Không mở một thư mục public
uploads có thể chạy PHP; muốn phục vụ trực tiếp phải cấu hình handler cấm thực thi và kiểm thử trên web server thật.
Foundation hiện không cung cấp upload feature/directory công khai.

## Kiểm thử và PR

Chạy `php tests/run.php`, `php tests/Integration/http.php`, PHP lint cho file thay đổi và `git diff --check`.
Thêm test chứng minh hành vi/regression với input bất thường. Không test bằng DB production.
Không tuyên bố PASS cho công cụ chưa chạy; ghi BLOCKED/NOT RUN cùng lý do.
