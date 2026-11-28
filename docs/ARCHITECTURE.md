# Kiến trúc MVC

## Request lifecycle

1. Web server chỉ expose `public/`, rewrite request về `public/index.php`.
2. Bootstrap đăng ký autoload `App\` → `app/`, đọc `.env`; environment của tiến trình ưu tiên hơn file.
3. Application tạo Router, nạp `routes/web.php`; file này nạp bốn module route bằng danh sách cố định.
4. Request tách path/query/body, từ chối URL lỗi, dấu phân cách Windows và traversal.
5. Application khởi tạo session cookie an toàn; asset chỉ phục vụ allowlist trong `public/assets`.
6. Router chọn path và method → global middleware → route middleware → controller.
7. Controller gọi service/model khi có nghiệp vụ; placeholder hiện tại không dùng database.
8. View render nội dung vào layout, escape dữ liệu; Response gửi status/header/body. HEAD không có body.
9. HttpException được đổi thành trang lỗi; lỗi bất ngờ trả 500 chung và ghi private log.

## Router và middleware

`add(method, path, callable, middleware = [])`; GET, POST và các verb khác dùng cùng API.
HEAD dùng GET. Tham số `{id}` khớp một đoạn URL và được đọc qua `$request->parameter('id')`.
Route tĩnh ưu tiên route tham số. Route cùng method/path hoặc cùng cấu trúc tham số bị từ chối.
Body POST dùng biểu mẫu PHP; PUT/PATCH/DELETE hỗ trợ application/x-www-form-urlencoded để kiểm tra CSRF.
Chưa có JSON API body parser hoặc file upload API.
Không dùng regex tùy ý hay optional parameter; hai pattern động chồng lấn khác cấu trúc cần tránh
và review thủ công. Không tự chuẩn hóa trailing slash.

Application gắn `VerifyCsrf` cho mọi route đã khớp có method thay đổi dữ liệu. Route không tồn tại
vẫn trả 404; method không được đăng ký trả 405 với Allow. Global middleware bao ngoài middleware
của route theo thứ tự đăng ký. Không gọi Router trực tiếp để phục vụ web và bỏ qua Application.
`OPTIONS` không thay đổi dữ liệu; không có CORS mặc định.

`RequireAccess` nhận `AccessPolicy` và tên permission. `DenyAccess` luôn từ chối; `/admin` được khóa.
`/admin/demo` là giao diện tĩnh công khai tách riêng, không phải endpoint nghiệp vụ hoặc nguồn dữ liệu admin.
PHUONG sẽ cung cấp policy đọc identity đã xác thực và quyền từ nguồn tin cậy; không tin role từ request.

## Các lớp

- **Controller**: xử lý HTTP, gọi Validator, trả Response; kế thừa `Core\Controller` để dùng `view()`.
- **Service**: quy tắc nghiệp vụ và điều phối transaction. Chỉ tạo class khi có hành vi thực tế.
- **Model**: kế thừa `Core\Model` nếu cần; `query()` luôn prepare/execute với parameters.
  Không đưa input vào tên bảng/cột: binding chỉ dành cho giá trị, identifiers cần allowlist.
- **Database**: PDO lazy singleton, exception mode, associative fetch và native prepares.
  Constructor Model có thể nhận PDO cho kiểm thử. Không kết nối trong bootstrap hoặc constructor model.
- **View**: `View::render()` cho partial, `View::page()` cho layout. `$content` là HTML đã render nội bộ;
  dữ liệu động khác dùng `View::escape()`. View name do developer chọn, không lấy trực tiếp từ request.
- **Session/CSRF**: file session trong private `storage/cache/sessions` được tạo khi chạy;
  HttpOnly, SameSite=Lax, Secure khi HTTPS hoặc `SESSION_SECURE=true`. Không tin X-Forwarded-Proto
  mặc định. Sau triển khai reverse proxy phải xác định trusted proxy và cấu hình Secure phù hợp.
- **Validator**: required/email/min/max và kiểm tra kiểu chuỗi. min/max tính byte (`strlen`),
  phù hợp giới hạn kỹ thuật; tên tiếng Việt cần bổ sung quy tắc độ dài ký tự trong module.

## Module boundary

| Module | Owner / branch | Thư mục và route |
| --- | --- | --- |
| Auth, Users, Admin | PHUONG | Controllers/Auth, Controllers/Admin, Models/Auth, Services/Auth, Views/auth, Views/admin, auth.php, admin.php |
| Catalog, cinema, schedule | HUY | Controllers/Movies, Models/Movies, Services/Movies, Views/movies, movies.php |
| Booking và payment | QUY | Controllers/Booking, Models/Booking, Services/Booking, Views/booking, booking.php |
| Core và layout chung | PHUONG, review cùng nhóm | Core, Middleware, Helpers, config, bootstrap, web.php, layouts, partials, assets |

Models/Services chưa có nghiệp vụ dùng `.gitkeep`, không tạo class rỗng. Shared `HomeController`
giữ tại Controllers/ và home view tại Views/ để tương thích giai đoạn 1. Một template `errors/http.php`
phục vụ đủ 400/403/404/405/500, không cần năm template trùng lặp. `storage/cache/sessions` là bổ sung
runtime tự tạo, không commit. Đây là các khác biệt có chủ đích so với cấu trúc mục tiêu.

HUY cung cấp hợp đồng đọc movie/showtime/seat availability; QUY không sửa bảng catalog trực tiếp.
QUY chịu trách nhiệm tính tiền snapshot và transaction booking sau khi ERD được thống nhất.
Không gọi controller module khác; trao đổi qua service contract được nhóm review trước khi triển khai.
Hiện chưa có DTO/interface cho nghiệp vụ chưa chốt, tránh định nghĩa schema giả.

## Giới hạn foundation

Chưa có auth thật, CRUD, payment, upload hoặc schema SQL. Không có debug route trong app.
Fixture test nằm ngoài public, chỉ được dùng khi chạy HTTP suite. Không coi foundation là production-ready
hoặc toàn bộ Viet Cinema đã hoàn thành. Quy trình và chứng cứ nghiệm thu nằm ở FOUNDATION_REPORT.md.
