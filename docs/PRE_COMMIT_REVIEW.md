# Final review & pre-commit audit — giai đoạn 2.3

Ngày: 2026-11-26 (Asia/Saigon). Workspace: `E:\Projects\viet-cinema`.
Nhánh đã xác minh: **chore/mvc-foundation**. HEAD: `7ccc69a9f939ba10bf8ba868a1cc8cb30cb34a00`.

## 1. Quyết định nghiệm thu

**GO kỹ thuật cho MVC Foundation trên PHP Development Server. NO-GO cho nghiệm thu cuối để commit và mở PR lên develop tại thời điểm báo cáo.**

Lý do NO-GO: nghiệm thu giao diện desktop/mobile vẫn BLOCKED, trong khi FOUNDATION_REPORT.md mục 12 yêu cầu người dùng/nhóm xác nhận giao diện và Apache thực trước khi đưa source lên GitHub. Không có lỗi Critical/High được xác nhận trong source hiện tại; 203 kiểm tra hiện có đều PASS. NO-GO ở đây là cổng nghiệm thu còn thiếu bằng chứng, không phải tuyên bố bộ regression thất bại.

Để chuyển GO: hoàn thành kiểm responsive tại năm viewport bên dưới, kiểm Apache thực theo cấu hình dự kiến, ghi kết quả và người nghiệm thu. Nhóm có thể quyết định chấp nhận các giới hạn để mở PR foundation, nhưng báo cáo này không tự coi việc chấp nhận đó đã xảy ra. MySQL/TLS phải được kiểm riêng trước khi sử dụng tính năng DB hoặc triển khai HTTPS; không yêu cầu tạo schema trong giai đoạn này.

Không sửa source/config/test, không stage/commit/push/merge/deploy, không cài phần mềm, không kết nối hoặc thay đổi database thật. File tài liệu này là bổ sung duy nhất của review; test có tạo log/session bị ignore và probe tạm được suite dọn trong finally.

## 2. Phạm vi và phương pháp

Đã đọc toàn bộ PHP ứng dụng, routes, bootstrap/front controller, CSS/JS, config mẫu, composer.json, các cấu hình Git/Apache, tests và tài liệu hướng dẫn. Đã đối chiếu evidence/báo cáo giai đoạn 2; evidence cũ vẫn là lịch sử, không bị ghi đè để giả làm kết quả mới. Kết quả chạy lại giai đoạn 2.3 được ghi trong báo cáo này và output phiên audit.

Inventory gồm cả tracked và untracked không bị ignore, không chỉ tracked diff. Đã kiểm thêm danh sách ignored và ACL thực tế của app/config/database/storage, bao gồm thư mục session. Không quét toàn bộ lịch sử Git hoặc xác minh remote trực tiếp qua fetch; kết luận secrets giới hạn ở nội dung candidate hiện tại, không chứng nhận không có secrets trong lịch sử hay hệ thống bên ngoài.

## 3. Kiến trúc

| Hạng mục | Kết quả |
| --- | --- |
| MVC | Controller trả Response; view/layout không truy vấn DB; Model prepare/execute; Services chỉ có authorization contract thực sự cần. Models/Services nghiệp vụ trống được giữ bằng .gitkeep, không class giả. |
| Namespace/autoload | App tương ứng app/ theo PSR-4; namespace/class/file nhất quán khi đọc source; core được autoload và lint thành công. Autoloader nội bộ không cần vendor. Composer autoload/validate chưa chạy. |
| Routing | Route explicit, danh sách module cố định; không chọn controller/file từ input. Static ưu tiên dynamic, reject duplicate tương đương; 404/405/Allow/HEAD và parameter có regression. |
| Shared | Core, Middleware, layouts, partials, CSS/JS, bootstrap và web.php dùng chung. Header/footer/movie-card/error template được tái sử dụng. Không phát hiện bản sao implementation độc lập hoặc dependency bên ngoài không khai báo. |
| Module PHUONG | Auth/Admin controllers, views, routes; Services/Auth; Core/config/layout điều phối theo TEAM_WORKFLOW. Login/register chỉ kiểm form, không cấp identity. |
| Module HUY | Movies controller/views/routes, Models/Movies và Services/Movies riêng. Không phụ thuộc Booking controller hoặc ghi dữ liệu QUY. |
| Module QUY | Booking controller/view/route, Models/Booking và Services/Booking riêng; chỉ placeholder đọc. Không truy cập trực tiếp catalog. |
| Hợp đồng liên module | Chưa có nghiệp vụ liên module, chưa cần bịa DTO/schema. HUY/QUY cần review contract suất/ghế trước triển khai. |
| Tài liệu clone/run | README + SETUP nêu PHP >=8.1, extension, runtime Laragon nếu không có PATH, document root public, lệnh chạy/test, không cần MySQL/Composer. ARCHITECTURE/CODING_STANDARD/TEAM_WORKFLOW đủ định hướng thành viên mới trong phạm vi foundation. Không thực hiện clone mới độc lập. |

Vòng phụ thuộc package được xác nhận: `Core\Router` dùng `Middleware\Middleware`, interface đó dùng Core Request/Response; Core Application dùng VerifyCsrf, VerifyCsrf dùng Core Csrf/Request/Response. Đây là coupling hai chiều ở hạ tầng, không phải vòng gọi controller giữa PHUONG/HUY/QUY và không gây lỗi autoload trong test. Đề xuất sau review: chuyển contract middleware sang Core/Contracts và giữ việc gắn middleware cụ thể ở composition root.

Code có thể tinh giản nhưng không gây lỗi hiện tại: bootstrap trả Router trong khi Application::boot(null) tạo Router khác; config/database.example.php chỉ tham khảo, không được Database nạp; handler /admin trả 403 thêm một lớp bên cạnh DenyAccess. Không xóa các lớp dự phòng này trong audit. CSS dùng chung là điểm dễ conflict; tuân thủ owner PHUONG và tách CSS module khi thật sự cần.

## 4. Bảo mật

| Hạng mục | Kết quả và giới hạn |
| --- | --- |
| CSRF | random_bytes(32), lưu session, hash_equals, body-only; từ chối token thiếu/sai/array/query/khác session. Global middleware áp dụng route đã match với method thay đổi dữ liệu; POST/PUT/PATCH/DELETE có HTTP test. GET/HEAD/OPTIONS không được dùng để ghi dữ liệu. |
| XSS | Dynamic text/attribute dùng View::escape với ENT_QUOTES/ENT_SUBSTITUTE; template name bị giới hạn. Raw content chỉ từ renderer nội bộ; template rẽ nhánh dùng hằng số. CSP self/nosniff/frame-ancestors hỗ trợ phòng vệ. Không có rich HTML từ user. |
| Session | strict mode, only cookies, HttpOnly, SameSite=Lax; Secure theo HTTPS hoặc SESSION_SECURE=true; không tin forwarded header. Session thật trong storage/cache/sessions. Chưa có auth thật nên chưa thực thi regeneration/logout/timeout auth; bắt buộc bổ sung khi làm auth. |
| Validation | Server required/email/min/max; reject array và oversized password; không phản chiếu password. min/max tính byte, đã nêu tài liệu; không tương đương độ dài ký tự Unicode. |
| Authorization | RequireAccess kiểm authenticated + permission; DenyAccess mặc định false. GET /admin 403 kể cả sau POST login mẫu. /admin/demo là trang tĩnh công khai, không phải quyền admin. |
| PDO | Lazy, ERRMODE_EXCEPTION, FETCH_ASSOC, EMULATE_PREPARES=false; DSN mẫu utf8mb4, user/password rỗng, lấy cấu hình từ môi trường. Base Model prepare/execute values; identifier phải allowlist. Test double không chứng minh native MySQL/TLS/transaction. |
| Exception/log | Response lỗi chung, không hiện trace/path/sentinel; log ngoài public. Raw exception được log nên khi thêm DB/service cần rà redaction và retention; không suy ra log luôn an toàn chỉ từ response generic. |
| Private qua HTTP | public là DocumentRoot; app/config/database/storage/tests/.git/.env/bootstrap/composer không public trong HTTP suite. Assets realpath containment + allowlist, PHP probe không chạy/lộ source. Root .htaccess deny; Apache chưa được thực thi. |
| Private trên filesystem | Get-Acl cho thấy Authenticated Users có Modify và BUILTIN Users có ReadAndExecute trên thư mục private/session. Không thể coi mkdir(0700) là ACL riêng tư trên Windows. Đây là rủi ro máy nhiều tài khoản, khác với chặn HTTP; chưa kiểm bằng đăng nhập tài khoản khác. |
| .gitignore | Env thật, config thật, vendor/node_modules, logs/cache/session/uploads, files tạm và IDE bị ignore; .env.example/config example/.gitkeep được giữ. 20 mẫu ignore + 6 exception PASS. |
| Secrets | Scan cả 86 candidate ban đầu: private key/cloud/GitHub token/credential URL không phát hiện. Rà thêm tên api-key/secret/password/token/DB_* và nội dung: chỉ placeholders, biến môi trường, CSRF và dữ liệu test demo. Không có .env/config database thật trong inventory; không phát hiện credential thật. Regex không bao phủ mọi định dạng secret. |

Không có upload/auth/payment thật để nghiệm thu. Không tuyên bố an toàn production hoặc SQL injection audit trên DB thực.

## 5. Regression chạy lại

Runtime: `C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe` — PHP 8.1.10; Node có trong PATH. PHP không có trong PATH, dùng runtime tuyệt đối. Không cài thêm công cụ.

| Lệnh (php dùng runtime trên) | PASS | FAIL | Bằng chứng mới |
| --- | ---: | ---: | --- |
| php tests/run.php | 63 | 0 | TOTAL: 63; gồm 11 regression giai đoạn 1 |
| php tests/Integration/http.php | 56 | 0 | TOTAL HTTP: 56; gồm 6 HTTP regression giai đoạn 1 |
| php tests/lint.php | 51 | 0 | TOTAL LINT: 51 files |
| php tests/Integration/git-audit.php | 32 | 0 | TOTAL GIT AUDIT: 32; 86 candidate trước thêm báo cáo |
| node --check public/assets/js/app.js | 1 | 0 | Không stdout, lệnh hoàn tất không báo lỗi |
| **Tổng** | **203** | **0** | Assertion/lint file, không phải 203 luồng nghiệp vụ |

HTTP suite tự mở server localhost port trống, kiểm private paths/CSRF/cookie/headers/403/500 và dừng child processes trong finally. Không kết nối DB. Kiểm Secure cookie dùng HTTPS detection fixture, không có TLS handshake thực.

### Responsive và kiểm thử chưa chạy

Đã kiểm tra lại công cụ trong phiên 2.3: cua.getState trả `browsers: []` và `The browser access host is unavailable`. Có ứng dụng browser native nhưng không có browser surface với viewport/DOM automation để xác minh các kích thước yêu cầu; không thay đổi cấu hình hoặc cài browser để vượt blocker. Không lấy việc có media queries làm bằng chứng visual PASS.

| Viewport | Overflow | Navigation | Layout | Usability |
| --- | --- | --- | --- | --- |
| 360 × 800 | BLOCKED | BLOCKED | BLOCKED | BLOCKED |
| 390 × 844 | BLOCKED | BLOCKED | BLOCKED | BLOCKED |
| 768 × 1024 | BLOCKED | BLOCKED | BLOCKED | BLOCKED |
| 1366 × 768 | BLOCKED | BLOCKED | BLOCKED | BLOCKED |
| 1920 × 1080 | BLOCKED | BLOCKED | BLOCKED | BLOCKED |

Khi có browser: kiểm /, /movies, /movies/sample, /showtimes, /login, /register, /booking, /admin/demo và error page; so document scrollWidth/clientWidth, kiểm menu/footer, form và password toggle, focus bàn phím, nút bấm và nội dung không bị che/cắt. Ghi browser/version, viewport CSS pixels, ảnh và kết quả thực tế.

Các mục NOT RUN: Apache DocumentRoot/rewrite/handler/private-path thực; MySQL integration; TLS HTTPS end-to-end/proxy/Secure cookie thực; Composer validate/autoload; PHPCS (hai công cụ cuối không có trong PATH). Fresh-clone smoke test và Linux filesystem case sensitivity chưa được thực thi. Không nâng các mục này thành PASS.

## 6. Vấn đề và đề xuất sửa trước khi thay source

| ID | Mức độ | Phát hiện / ảnh hưởng | Đề xuất và điều kiện |
| --- | --- | --- | --- |
| R1 | Medium | Visual năm viewport BLOCKED; thiếu bằng chứng nghiệm thu giao diện. | Chạy checklist browser ở trên, ghi evidence trước final GO. Chưa xác nhận có lỗi layout. |
| R2 | Medium | ACL private/session rộng trên Windows; user local khác có thể đọc hoặc sửa theo quyền kế thừa. | Khi chuẩn bị máy dùng chung/server, owner hệ thống cấp read source cho runtime, chỉ write storage cần thiết; hạn chế đọc session/log/config theo identity. Review ACL cụ thể trước đổi; không tự sửa ACL trong audit. |
| R3 | Medium | tests/Integration/git-audit.php khóa branch chore/mvc-foundation và SHA baseline refs. Thành viên clone thiếu refs hoặc sau develop tiến lên sẽ fail dù source đúng. | Tách guard nghiệm thu giai đoạn 2 khỏi audit Git dùng hằng ngày, hoặc chỉ bật baseline guard bằng tùy chọn rõ ràng; giữ secret/ignore/whitespace checks. Cần sửa trước khi coi đây là gate CI chung cho develop. Không phải FAIL của lần chạy hiện tại. |
| R4 | Low | Core ↔ Middleware có vòng dependency package; không thấy vòng nghiệp vụ liên module. | Tách contract và composition root nếu muốn core độc lập; không cần framework/DI container. |
| R5 | Low | Router chỉ phát hiện duplicate cùng cấu trúc; pattern khác cấu trúc vẫn có thể overlap. Các route hiện tại không có xung đột này. | Khi thêm routes, review ambiguity và thêm test cho pattern thực tế; không mở rộng router chỉ để dự phòng. |
| R6 | Low | PHP 8.1.10 cũ, PHP 8.1 đã ngoài vòng hỗ trợ upstream vào ngày review; regression chưa chạy PHP mới hơn. | Chọn runtime PHP còn được hỗ trợ, chạy lại tests trước dùng server thật; không tự cài/nâng cấp trong phiên này. |
| R7 | Low | Evidence inventory và số file giai đoạn 2 chưa chứa báo cáo 2.3. | Giữ evidence cũ là lịch sử; dùng inventory cuối của báo cáo này cho commit. Không dùng git diff --stat đơn lẻ để review source mới. |

**Critical: 0; High: 0 được xác nhận.** Các mức độ trên gắn với phạm vi foundation/local; R2 và runtime cũ cần xử lý trước môi trường nhiều user hoặc production. Apache/TLS/MySQL chưa test là thiếu bằng chứng, không phải lỗi khai thác đã được xác nhận.

## 7. Git audit

Đã chạy đầy đủ: git status --short, git branch --show-current, git remote -v, git diff --check, git diff --stat, git ls-files, git ls-files --others --exclude-standard.

- Origin fetch/push: `https://github.com/phuong000/viet-cinema.git`; chỉ đọc config, không xác minh trạng thái server GitHub.
- Trước báo cáo: README.md modified tracked; 85 untracked files, tổng 86 candidate. Sau thêm báo cáo: 86 untracked, tổng 87 candidate.
- git ls-files: chỉ README.md. git diff --stat: README.md, 83 insertions(+), 2 deletions(-), không bao gồm source untracked.
- Index không có staged diff. git diff --check không có lỗi; Git audit kiểm thêm whitespace mọi candidate, gồm untracked.
- Protected refs baseline PASS theo audit hiện có; không sửa refs, không fetch/pull/switch/reset/clean.
- Ignored runtime: storage/cache/sessions và bốn logs application/http-fixture/http-server/unit-test. Không đưa chúng vào commit.

Sau khi tạo báo cáo và inventory: chạy lại Git audit **32/32 PASS**, xác nhận **87/87 candidate** không có lỗi whitespace hoặc pattern credential của suite; git diff --check vẫn không có lỗi. Không cộng lần chạy lặp này vào tổng 203 kiểm tra độc lập.

## 8. Lệnh đề xuất cho người dùng — chưa thực thi

Chỉ dùng sau khi đọc findings, hoàn thành hoặc chấp nhận rõ các điều kiện nghiệm thu. Lệnh stage dùng danh sách thư mục đã review; trước stage cần đối chiếu lại inventory vì file mới có thể được thêm sau audit. Không dùng git add -f.

```powershell
git status --short
git branch --show-current
git diff --check
git ls-files --others --exclude-standard

# Chạy lại tests nếu source/config thay đổi sau review.
$phpRuntime = 'C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe'
& $phpRuntime tests/run.php
& $phpRuntime tests/Integration/http.php
& $phpRuntime tests/lint.php
& $phpRuntime tests/Integration/git-audit.php
node --check public/assets/js/app.js

# Sau khi điều kiện GO đã được đáp ứng và người dùng quyết định commit:
git add -- README.md .env.example .gitattributes .gitignore .htaccess FOUNDATION_REPORT.md bootstrap.php composer.json app config database docs public routes storage tests
git diff --cached --check
git diff --cached --stat
git diff --cached --name-only
git diff --cached
git commit -m "chore: add reviewed MVC foundation"
git push -u origin chore/mvc-foundation
```

Sau push, mở Pull Request thủ công: base `develop`, compare `chore/mvc-foundation`; đính kèm báo cáo, kết quả regression, findings và bằng chứng visual/Apache mới. Không merge/deploy theo kết luận này. Không chạy git-audit baseline trên nhánh khác rồi coi lỗi branch là lỗi ứng dụng; xử lý R3 trước khi dùng cho CI chung.

## 9. Danh sách file dự kiến commit

87 file gồm README.md đã tracked và 86 file mới (bao gồm báo cáo này). .gitkeep được giữ; runtime logs/session/cache contents bị loại. Inventory bên dưới là snapshot, không phải lệnh stage.

```text
.env.example
.gitattributes
.gitignore
.htaccess
app/Controllers/Admin/DashboardController.php
app/Controllers/Auth/AuthController.php
app/Controllers/Booking/BookingController.php
app/Controllers/HomeController.php
app/Controllers/Movies/MovieController.php
app/Core/Application.php
app/Core/Controller.php
app/Core/Csrf.php
app/Core/Database.php
app/Core/Environment.php
app/Core/HttpException.php
app/Core/Model.php
app/Core/Request.php
app/Core/Response.php
app/Core/Router.php
app/Core/Session.php
app/Core/Validator.php
app/Core/View.php
app/Helpers/.gitkeep
app/Middleware/Middleware.php
app/Middleware/RequireAccess.php
app/Middleware/VerifyCsrf.php
app/Models/Auth/.gitkeep
app/Models/Booking/.gitkeep
app/Models/Movies/.gitkeep
app/Services/Auth/AccessPolicy.php
app/Services/Auth/DenyAccess.php
app/Services/Booking/.gitkeep
app/Services/Movies/.gitkeep
app/Views/admin/dashboard.php
app/Views/auth/form.php
app/Views/booking/index.php
app/Views/errors/http.php
app/Views/home.php
app/Views/layouts/admin.php
app/Views/layouts/main.php
app/Views/movies/index.php
app/Views/movies/show.php
app/Views/movies/showtimes.php
app/Views/partials/alerts.php
app/Views/partials/footer.php
app/Views/partials/header.php
app/Views/partials/movie-card.php
bootstrap.php
composer.json
config/database.example.php
database/migrations/.gitkeep
database/README.md
database/seeds/.gitkeep
docs/ARCHITECTURE.md
docs/CODING_STANDARD.md
docs/DATABASE_REVIEW.md
docs/evidence/files.txt
docs/evidence/git-audit.txt
docs/evidence/git-state.txt
docs/evidence/http.txt
docs/evidence/lint.txt
docs/evidence/README.md
docs/evidence/unit.txt
docs/FOUNDATION_GUIDE.md
docs/PRE_COMMIT_REVIEW.md
docs/SETUP.md
docs/TEAM_WORKFLOW.md
FOUNDATION_REPORT.md
public/.htaccess
public/assets/css/app.css
public/assets/images/.gitkeep
public/assets/js/app.js
public/index.php
README.md
routes/admin.php
routes/auth.php
routes/booking.php
routes/movies.php
routes/web.php
storage/cache/.gitkeep
storage/logs/.gitkeep
tests/Integration/git-audit.php
tests/Integration/http.php
tests/Integration/router.php
tests/lint.php
tests/run.php
tests/Unit/foundation.php
```
