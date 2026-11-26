# Nghiệm thu giai đoạn 2 — MVC Foundation

Ngày thực hiện: **2026-11-26**. Phạm vi: foundation PHP MVC và các trang minh họa,
không phải toàn bộ website Viet Cinema. Phần báo cáo giai đoạn 1 được giữ ở cuối làm lịch sử.

## 1. Git thực tế

- Workspace `E:\Projects\viet-cinema`; nhánh **`chore/mvc-foundation`**.
- Origin fetch/push `https://github.com/phuong000/viet-cinema.git`.
- HEAD vẫn `7ccc69a9f939ba10bf8ba868a1cc8cb30cb34a00` (`Initial commit`).
- Đầu giai đoạn 2: README tracked không đổi, các file giai đoạn 1 còn untracked.
  Đã giữ và mở rộng code hiện có; không tạo lại repo/nhánh, không switch nhánh.
- Cuối giai đoạn 2: **1 tracked file sửa (README.md), 85 untracked files**, tổng 86 candidate files.
  Chưa stage bất kỳ file nào. Vì phần lớn source chưa tracked, `git diff --stat` không thể hiện toàn bộ thay đổi.
- Local `main`, `develop` và remote-tracking `origin/PHUONG`, `origin/HUY`, `origin/QUY`
  đều giữ nguyên SHA nêu trên. Không có local branch PHUONG/HUY/QUY được tạo trong phiên này.
- Đã chạy status, branch, remote, diff stat, diff check trước và sau; không fetch/pull/commit/push/merge/deploy.
  Không truy cập hoặc thay đổi database thật. Không reset/clean hoặc xóa source gốc.
- Bằng chứng: [git-state.txt](docs/evidence/git-state.txt), [git-audit.txt](docs/evidence/git-audit.txt).

## 2. Cấu trúc thư mục thực tế

```text
viet-cinema/
  app/
    Controllers/{Auth,Movies,Booking,Admin}/ + HomeController.php
    Core/{Application,Controller,Model,Router,Request,Response,View,Database,
          Environment,HttpException,Session,Csrf,Validator}.php
    Models/{Auth,Movies,Booking}/
    Services/{Auth,Movies,Booking}/
    Middleware/{Middleware,VerifyCsrf,RequireAccess}.php
    Helpers/
    Views/{layouts,partials,auth,movies,booking,admin,errors}/ + home.php
  config/database.example.php
  database/{migrations,seeds}/ + README.md
  public/index.php + .htaccess + assets/{css,js,images}/
  routes/{web,auth,movies,booking,admin}.php
  storage/{logs,cache}/
  tests/{Unit,Integration}/ + run.php + lint.php
  docs/{ARCHITECTURE,CODING_STANDARD,DATABASE_REVIEW,SETUP,TEAM_WORKFLOW,FOUNDATION_GUIDE}.md
  docs/evidence/
  bootstrap.php + composer.json + README.md + FOUNDATION_REPORT.md
  .env.example + .gitignore + .gitattributes + .htaccess
```

Thư mục nghiệp vụ chưa dùng có `.gitkeep`, không sinh class rỗng. `storage/cache/sessions`
được tạo lúc chạy và bị ignore. Không có thư mục upload công khai. Home giữ vị trí giai đoạn 1;
một error template chung xử lý năm status để tránh lặp code. Các khác biệt này được giải thích
trong ARCHITECTURE.md. Danh sách đầy đủ mọi file: [files.txt](docs/evidence/files.txt).

## 3. File mới và file sửa

So với trạng thái đã đọc đầu giai đoạn 2: **67 file mới, 18 file chỉnh sửa, 1 file không đổi (bootstrap.php)**.
Inventory có nhãn NEW_PHASE2/MODIFIED_PHASE2/UNCHANGED_PHASE1 tại `docs/evidence/files.txt`.

File sửa có sẵn:

```text
.env.example, .gitignore, composer.json, README.md, FOUNDATION_REPORT.md
app/Controllers/HomeController.php
app/Core/Database.php, Environment.php, Request.php, Response.php, Router.php, View.php
app/Views/home.php
public/index.php, public/.htaccess
routes/web.php, tests/run.php, docs/FOUNDATION_GUIDE.md
```

Các nhóm file mới: Application/Base Controller/Base Model/Session/CSRF/Validator/HttpException;
middleware và access policy mặc định deny; controller/route/view theo module; main/admin layout,
header/footer/alerts/movie-card; responsive CSS và JavaScript; config mẫu; database docs và placeholders;
tài liệu kiến trúc/setup/nhóm/quy chuẩn; unit/HTTP/Git/lint tests và bằng chứng; root deny `.htaccess`
và `.gitattributes` giữ LF. Không loại bỏ thành phần giai đoạn 1 đang hoạt động tốt.

## 4. Chức năng nền tảng hoàn thành

- Bootstrap/autoload giữ nguyên cơ chế nhẹ, không cài framework hoặc dependency bên ngoài.
- Router GET/POST và verb khác, HEAD, tham số một đoạn, route tĩnh ưu tiên, chống đăng ký trùng,
  404/405/Allow; Request đọc query/body và từ chối URL lỗi/traversal.
- Application, Controller, Model, View renderer/layout, JSON và HTML Response.
- PDO lazy, native prepared statements, Model binding; không gọi connection khi boot/render.
- Global và per-route middleware; session, flash message, validator, CSRF và exception handling.
- Trang chủ, phim, chi tiết mẫu, lịch chiếu, login/register, booking, admin demo và health.
- Main/admin layouts, navigation/footer, error/success alerts, forms, buttons, poster/card CSS,
  dark/red palette; media queries 800px/480px; JS bật/tắt hiện password.
- Routes theo owner. Login/register chỉ kiểm tra dữ liệu demo, không tạo identity hoặc ghi DB.
- `/admin/demo` chỉ dữ liệu tĩnh. `/admin` luôn 403 cho đến khi có access policy thực được review.
- Database chỉ cấu hình mẫu, migrations/seeds trống và review questions; không tạo schema SQL.

## 5. Kết quả kiểm thử cuối cùng

Runtime: PHP **8.1.10** tại `C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe`;
Node **v24.21.0**. Không cần Composer để khởi chạy.

| Nhóm kiểm tra | PASS | FAIL | BLOCKED | NOT RUN | Bằng chứng |
| --- | ---: | ---: | ---: | ---: | --- |
| PHP unit/foundation + regression | 63 | 0 | 0 | 0 | unit.txt |
| HTTP thật qua localhost | 56 | 0 | 0 | 0 | http.txt |
| Syntax toàn bộ PHP | 51 | 0 | 0 | 0 | lint.txt |
| Git/ignore/candidate/whitespace/refs | 32 | 0 | 0 | 0 | git-audit.txt |
| JavaScript syntax (`node --check`) | 1 | 0 | 0 | 0 | exit 0, không stdout |
| Responsive/visual trên browser | 0 | 0 | 1 | 0 | browser host unavailable; Cốc Cốc URL policy unsupported |
| Apache runtime | 0 | 0 | 0 | 1 | Chưa khởi động Apache |
| TLS/HTTPS end-to-end | 0 | 0 | 0 | 1 | Chỉ HTTPS detection fixture, không TLS thật |
| MySQL integration | 0 | 0 | 0 | 1 | Không có ERD/DB test; không được chạm DB thật |
| Composer validate | 0 | 0 | 0 | 1 | Composer chưa có trong PATH |
| PHPCS tự động | 0 | 0 | 0 | 1 | Không cài PHPCS |
| **Tổng** | **203** | **0** | **1** | **5** | docs/evidence/ |

203 là số assertion/lint file kiểm chứng, không phải 203 luồng nghiệp vụ độc lập.
Không đếm các kiểm tra NOT RUN như PASS. Các lỗi trung gian trong lúc phát triển được ghi tại
`docs/evidence/README.md`; các suite cuối đều exit 0.

## 6. Regression và HTTP

- **11/11 kiểm tra giai đoạn 1 chạy lại PASS**, không bỏ hoặc thay tiêu chí cũ.
- **6/6 HTTP giai đoạn 1 chạy lại PASS**: home 200, health có query JSON, HEAD body rỗng,
  unknown 404, POST home 405/Allow, `.env` không truy cập được.
- Bộ HTTP bổ sung chứng minh bảy trang module 200, `/admin` 403, movie thiếu 404,
  URL sai/traversal 400, source private 404, CSS/JS đúng content type, static POST 405,
  CSRF thiếu/sai/array/khác session/query bị chặn, CSRF hợp lệ đến được controller,
  validation sai 422, POST demo không cấp admin và không phản chiếu password.
- Test router ngoài public xác minh 500 production không lộ sentinel/stack trace/path,
  session không có fake user/role và bảo vệ CSRF với POST/PUT/PATCH/DELETE.
- Server test tự chọn port localhost và dừng trong finally; server kiểm tra UI thủ công cũng đã dừng.
  Không tạo debug/throw route trong ứng dụng thật.

## 7. Kết quả bảo mật trong phạm vi đã kiểm thử

| Hạng mục | Kết quả và giới hạn |
| --- | --- |
| XSS | Escaping text/attribute đã test, traversal view bị chặn; không hỗ trợ raw user HTML |
| CSRF | Token 32 random bytes trong session, hash_equals, body-only, mọi method thay đổi dữ liệu đã test |
| Session | Start/read/write/flash có test; Set-Cookie thật HttpOnly/SameSite=Lax; local HTTP không Secure; HTTPS fixture có Secure |
| Quyền | DenyAccess/RequireAccess có test; admin thật từ chối ngay cả sau POST login demo |
| Validation | required/email/min/max/array/oversize có test; min/max hiện tính byte |
| Exceptions | HTTP 400/403/404/405/500 và generic 500; production không xuất details; logs private |
| SQL input | Model prepare/bind đã test bằng PDO double; không tuyên bố MySQL transaction/SQL injection audit thực đã hoàn tất |
| Private files | HTTP chặn config/app/database/storage/tests/bootstrap/composer/.git/.env; document root chỉ public |
| PHP trong assets | File PHP probe tạo tạm, request 404, không execute hoặc lộ source; đã xóa probe |
| Upload | Chưa cung cấp upload feature/web directory; chuẩn yêu cầu lưu ngoài public, không tuyên bố đã test Apache upload handler |
| Credentials/Git | 20 ignore mẫu PASS, 6 exception mẫu PASS, tracked files và 86 candidate files được rà; scan token/private-key/credential URL không phát hiện mẫu |

Scan credential theo pattern và rà source không thay thế secret audit toàn diện. File duy nhất tracked
trước phiên là README; `.env` và `config/database.php` không được tạo. Dữ liệu kiểm thử là giả.

## 8. Quyết định kiến trúc

Giữ PHP MVC đơn giản: Router callable + middleware interface, base class nhỏ và service contract có mục đích.
Không container/ORM/framework lớn. CSRF ở Application thay vì phải nhớ thêm cho từng POST module.
Thông báo lỗi luôn chung cả local/production. Session private trong workspace để tránh phụ thuộc thư mục
temp Laragon ngoài workspace. Không tin forwarded headers nếu chưa có trusted proxy.
Không đăng nhập giả; access mặc định deny; demo admin tách endpoint. Không suy đoán 15 bảng từ tên module.
Assets giới hạn loại file và đường dẫn realpath. Apache chỉ rewrite front controller hoặc assets allowlist;
root deny là phòng vệ bổ sung, không thay thế DocumentRoot public đúng.

## 9. Nhiệm vụ nhóm và tài liệu

| Owner / nhánh | Phạm vi |
| --- | --- |
| PHUONG / PHUONG | Auth, Authorization, Users, Administration, MVC Core, config/layout chung |
| HUY / HUY | Movies, Categories, Cinema Rooms, Seats, Showtimes, Reviews |
| QUY / QUY | Booking, Combos, Discounts, Payment, Tickets, Booking History |

Mỗi người sở hữu controller/model/service/view/route module của mình. Shared Core/layout qua review PHUONG;
contract schedule/seat/booking cần HUY+QUY thống nhất, schema cần cả ba. PR vào develop; main chỉ phiên bản ổn định.
Tài liệu README, ARCHITECTURE, TEAM_WORKFLOW, CODING_STANDARD, SETUP, DATABASE_REVIEW và database README
đã bổ sung; workflow chỉ là hướng dẫn, không có lệnh commit/push/merge được tự động thực thi.

## 10. Lệnh khởi chạy và kiểm thử

```powershell
php -S 127.0.0.1:8000 -t public public/index.php
php tests/run.php
php tests/Integration/http.php
php tests/Integration/git-audit.php
php tests/lint.php
node --check public/assets/js/app.js
```

Nếu chưa có PHP trong PATH, thay `php` bằng
`& 'C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe'`.
Mở `http://127.0.0.1:8000`. Không yêu cầu `.env` hoặc MySQL để xem placeholder.

## 11. Diff summary và giới hạn

`git diff --stat`: **README.md | 85 dòng thay đổi; 83 insertions(+), 2 deletions(-)**.
Đây chỉ là tracked diff; 85 file untracked không xuất hiện trong stat. Inventory 86 candidate files
là mô tả đầy đủ hơn; so với giai đoạn 1 gồm 67 mới/18 sửa/1 giữ nguyên.
`git diff --check` PASS, và audit riêng kiểm whitespace từng candidate bao gồm untracked.
Không sửa refs hoặc index. Session/cache/log là runtime artifacts private, bị ignore.

Giới hạn: chưa có auth/CRUD/booking/payment thật; chưa có ERD/SQL; chưa test DB/Apache/TLS thật;
chưa xác minh visual/responsive do công cụ browser bị chặn; chưa chạy Composer/PHPCS.
CSS có breakpoints nhưng không đánh đồng việc có media query với visual PASS.

## 12. GO/NO-GO và công việc tiếp theo

**GO cho MVC Foundation kỹ thuật trong môi trường PHP Development Server đã kiểm thử**:
toàn bộ kiểm thử bắt buộc có thể thực hiện trong môi trường hiện tại đều PASS, đủ regression,
module boundaries, tài liệu, chạy không DB và không phát hiện lỗi nghiêm trọng trong phạm vi test.
Kiểm responsive bằng browser có điều kiện “nếu môi trường hỗ trợ” được ghi BLOCKED, không tự đánh dấu PASS.

**Không phải GO cho toàn website hoặc production deployment.** Nghiệm thu giao diện desktop/mobile
và cấu hình Apache thực vẫn cần người dùng/nhóm xác nhận trước khi đưa source lên GitHub.
Không tự động commit/push/merge/deploy; dừng để người dùng review.

Việc tiếp theo:

1. Người dùng chạy server, nghiệm thu desktop/mobile và các trang mẫu; ghi kết quả visual còn thiếu.
2. Kiểm cấu hình DocumentRoot/rewrite/cookie trên Apache/HTTPS thực khi chọn môi trường chạy.
3. Cả ba chốt ERD, PK/FK, seat locking, transaction, payment state và price snapshot theo DATABASE_REVIEW.
4. PHUONG triển khai auth/authorization/session regeneration thật và test; HUY/QUY phát triển module theo contract.
5. Sau khi nghiệm thu, người dùng quyết định stage/commit/push/PR vào develop; phiên này không làm bước đó.

---

# Phụ lục: Báo cáo PHP MVC Foundation giai đoạn 1 (lịch sử)

Ngày: 2026-11-26.

## Git đã xác minh trước triển khai

- Workspace: `E:\Projects\viet-cinema`.
- Nhánh hiện tại: `chore/mvc-foundation`; working tree sạch trước khi thực hiện.
- Origin fetch/push: `https://github.com/phuong000/viet-cinema.git`.
- HEAD ban đầu: `7ccc69a` (`Initial commit`); repository chỉ có `README.md`.
- Không tạo repository hoặc nhánh, không đổi nhánh; không commit, push hoặc merge.
- `develop` và `main` là nhánh tích hợp/ổn định theo thông tin người dùng; không fetch hay xác nhận trạng thái remote của hai nhánh này.

## Đã triển khai

PHP >= 8.1 MVC tối thiểu: front controller, PSR-4 autoload nội bộ, environment loader,
router tường minh, request/response, controller và view mẫu, HTML escaping, JSON,
404/405 kèm Allow, HEAD, xử lý lỗi 500 không xuất stack trace ra trình duyệt.
PDO kết nối lười, cấu hình qua environment, exceptions và prepared statements native.
Đã thêm `.env.example`, `.gitignore`, composer.json, Apache rewrite và tài liệu tích hợp.
README gốc được giữ nguyên.

## Kiểm thử thực tế

Runtime: `C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe`.
PHP và Composer không nằm trong PATH; Composer chưa được chạy.

- `php tests/run.php`: **11/11 PASS** (home, HTML, JSON, 404, 405/Allow, HEAD,
  escape HTML, chặn view traversal, chặn route trùng, loại query string, tải adapter không kết nối DB).
- `php -l`: **12/12 file PHP PASS**.
- HTTP thực qua PHP built-in server `127.0.0.1:18765`: **6/6 PASS**:
  home 200, health có query trả JSON, HEAD không có body, URL thiếu 404,
  POST home 405, `/.env` 404.
- Server kiểm thử đã dừng sau khi chạy. Không mở kết nối hoặc thay đổi database thật.
- Lần khởi động server bằng Start-Process chưa thành công; chạy foreground qua tool session
  và kiểm thử HTTP sau đó thành công. Một lệnh probe PowerShell bị lỗi tên biến bảo vệ;
  đã sửa probe và chạy lại thành công.

## Giới hạn và yêu cầu còn thiếu

Tin nhắn người dùng kết thúc tại “tạo báo cáo FOUNDATION_”, không có phần yêu cầu chi tiết
được nhắc tới. Tên `FOUNDATION_REPORT.md` được chọn tạm thời. Đã yêu cầu người dùng gửi tiếp.
Không khẳng định hoàn thành toàn bộ yêu cầu chưa nhận được.

Chưa triển khai authentication, administration, nghiệp vụ HUY/QUY, middleware, CSRF,
session, route tham số, schema/migration/seed hay kiểm thử tích hợp DB.
Chưa kiểm chứng Apache, Composer hoặc môi trường production. Đây là nền tối thiểu
đã kiểm thử, cần đối chiếu phần yêu cầu còn thiếu trước khi mở rộng hoặc tích hợp.

Hướng dẫn chạy và phân công: `docs/FOUNDATION_GUIDE.md`.
