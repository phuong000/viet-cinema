# Foundation acceptance — giai đoạn 2.4

Ngày: **2026-11-26**, Asia/Saigon. Workspace: `E:\Projects\viet-cinema`.
Nhánh: **chore/mvc-foundation**; HEAD: `7ccc69a9f939ba10bf8ba868a1cc8cb30cb34a00`.
Origin fetch/push: `https://github.com/phuong000/viet-cinema.git`.

## 1. Kết luận

**GO kỹ thuật cho MVC Foundation local HTTP: regression 203/203 PASS, responsive 45/45 PASS, Apache 48/48 PASS.** Hai blocker kiểm thử browser và Apache trước đây đã được giải quyết bằng công cụ có sẵn.

**NO-GO cho phê duyệt cuối commit/PR tại thời điểm lập báo cáo: còn chờ chủ dự án xác nhận phạm vi MySQL/TLS và nghiệm thu báo cáo/bằng chứng.** Không tự loại bỏ điều kiện nghiệm thu. Đề xuất MySQL runtime thuộc giai đoạn Database, TLS end-to-end thuộc giai đoạn Production; Foundation dùng PDO double và HTTPS cookie fixture hiện có. Câu hỏi xác nhận đã được gửi trong phiên, chưa nhận được chấp thuận khi lập báo cáo.

Không phát hiện lỗi Critical/High; không có lỗi giao diện hoặc ứng dụng Foundation cần sửa qua lần kiểm thử này. ACL Windows và runtime cũ vẫn là giới hạn môi trường local, không được coi đã khắc phục hoặc đủ an toàn cho production.

## 2. Môi trường thực tế và cách biệt lập

| Công cụ | Khảo sát và sử dụng |
| --- | --- |
| PHP | 8.1.10 ZTS VS16 x64, `C:/laragon/bin/php/php-8.1.10-Win32-vs16-x64/php.exe`; không có trong PATH. Chỉ thấy phiên bản này trong Laragon. |
| Apache | 2.4.54 Win64 VS16, `C:/laragon/bin/apache/httpd-2.4.54-win64-VS16/bin/httpd.exe`; dùng mod_php tương thích DLL PHP hiện có. |
| XAMPP | Không thấy C:/xampp hoặc E:/xampp. Laragon hiện có đủ Apache/PHP để kiểm thử. Không bật panel hoặc dịch vụ Laragon. |
| Chrome | 158.0.8127.88, `C:/Program Files/Google/Chrome/Application/chrome.exe`; chạy headless thật. |
| Edge | 158.0.4370.33, `C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe`; tìm thấy nhưng không chạy suite lần hai trên Edge. |
| Node/npm | Node v24.21.0, npm 11.19.0; có trong PATH. |
| Playwright | 1.65.1 đã có ở `E:/Projects/ehs-card-desk/apps/web/node_modules/playwright`; chỉ nạp thư viện, không chạy app/script hoặc sửa workspace đó. Không npm install/npx download. |
| Composer | Có composer.phar tại C:/laragon/bin/composer; validate offline thành công, cảnh báo chưa khai báo license. Không install/update/autoload dump. |
| PHPCS | Không chạy; chưa có công cụ đã xác định để sử dụng. |

Đã đọc FOUNDATION_REPORT.md, PRE_COMMIT_REVIEW.md, source và tests hiện hành. Kiểm lại cấu trúc/namespace/MVC/module boundaries; không xây dựng lại MVC hoặc bổ sung nghiệp vụ.

Runner `tests/Acceptance/local.cjs` tạo config, PID và logs dưới **storage/cache/acceptance** (ignored). Apache dùng `-X -f <config-riêng>`, bind `127.0.0.1:<port-trống>` và DocumentRoot **public/**; không sửa httpd.conf toàn hệ thống, hosts, firewall, ACL hoặc dịch vụ. Environment riêng DB_DSN rỗng, SESSION_SECURE=false. Config không có alias tới DB hoặc thư mục private; riêng `/throw` là alias fixture lỗi test-only tới tests/Integration/router.php trong tiến trình biệt lập để xác minh 500.

Chrome dùng profile/context tạm của Playwright, không thao tác tab/profile đang dùng. Các child process tạo bởi runner đều đã dừng trong finally (`childProcessesStopped: true`); kiểm lại không còn process httpd. PHP probe assets được xóa sau suite. Không dừng process thuộc dự án khác.

## 3. Blocker trước xử lý và thay đổi

| Trước xử lý | Cách xử lý / kết quả |
| --- | --- |
| Browser access host unavailable | Khảo sát tìm được Chrome + Playwright đã cài, chạy trực tiếp headless. Không cần browser connector, tải browser hay cài dependency. |
| Apache chưa kiểm | Dùng Apache Laragon qua config local biệt lập; kiểm rewrite/front controller/static/session/security/error status thực. |
| Git audit khóa nhánh/SHA lịch sử | Bổ sung **--portable** cho clone mới/CI; giữ nguyên chế độ nghiệm thu giai đoạn 2 khi không truyền cờ. Không giảm/bỏ 32 kiểm tra cũ. |
| ACL Windows chưa rõ cách vận hành | Bổ sung cảnh báo NTFS và hướng dẫn kiểm quyền trong SETUP; không đổi ACL máy. Finding vẫn OPEN. |
| Inventory cũ | Tạo inventory mới gồm tracked/untracked/evidence, giữ nguyên báo cáo/evidence 2 và 2.3 làm lịch sử. |

Các file thay đổi so với đầu 2.4: `tests/Integration/git-audit.php`, README.md, composer.json, docs/SETUP.md, docs/FOUNDATION_GUIDE.md. File mới: hai runner trong tests/Acceptance, báo cáo này và docs/evidence/acceptance. **Không sửa app/, routes/, public/ hoặc bootstrap.php.**

Composer `test:git` và hướng dẫn hằng ngày dùng --portable; `test:git:phase2` giữ audit lịch sử. Chế độ portable kiểm môi trường worktree và refs không đổi trong quá trình audit, đồng thời giữ ignore/secret/whitespace checks. Đã kiểm thêm bằng repo fixture độc lập với nhánh acceptance-fixture, không có refs lịch sử; portable 32/32 PASS và chế độ strict vẫn từ chối sai nhánh. Fixture chỉ nằm trong ignored storage/cache, không đổi refs/index repository dự án.

Các sai lệch harness đã sửa, không che giấu:

- Lần đầu: 88/91 PASS, 3 FAIL do harness yêu cầu text/javascript trong khi Apache trả application/javascript hợp lệ, và yêu cầu Set-Cookie ở response đã có session. Đổi phép kiểm sang MIME hợp lệ và cookie thực trong cookie jar. Responsive lần đầu vẫn 45/45 PASS; giữ `initial-results.json`.
- Portable fixture ban đầu thiếu .gitattributes nên cảnh báo LF/CRLF làm whitespace audit từ chối. Bổ sung đúng .gitattributes của dự án vào fixture; không tắt whitespace check hay đổi Git config toàn hệ thống.
- Công cụ ghép contact sheet có cảnh báo cache font không ghi được; ảnh trang gốc vẫn được lưu. Contact sheets thử nghiệm ở ignored cache, không dùng làm điều kiện PASS thay cho screenshots/JSON cuối.

## 4. Responsive thực tế

Chrome headless, viewport chính xác theo CSS pixels, deviceScaleFactor=1; bật hasTouch ở chiều rộng dưới 800. Đây là viewport emulation, không phải chứng nhận thiết bị iOS/Android thật.

| Viewport | 9 trang | Overflow | Header/nav/footer/layout | Form/thao tác | JS/resources |
| --- | --- | --- | --- | --- | --- |
| 360 × 800 | 9/9 PASS | PASS | PASS | PASS | PASS |
| 390 × 844 | 9/9 PASS | PASS | PASS | PASS | PASS |
| 768 × 1024 | 9/9 PASS | PASS | PASS | PASS | PASS |
| 1366 × 768 | 9/9 PASS | PASS | PASS | PASS | PASS |
| 1920 × 1080 | 9/9 PASS | PASS | PASS | PASS | PASS |

Mỗi kích thước đã kiểm `/`, `/movies`, `/movies/sample`, `/showtimes`, `/login`, `/register`, `/booking`, `/admin/demo`, `/missing` (404).

Đo document scrollWidth/clientWidth và bounding box nội dung hiển thị: không có tràn ngang. Loại khỏi phép đo các phần trang trí aria-hidden và skip-link đang ẩn; không che giấu overflow nội dung sử dụng. Kiểm header/main/footer hiển thị và CSS thực được áp dụng. Trial click mọi link/button/input để kiểm scroll/hit target/overlay; thực click nav Phim và kiểm URL, fill và submit hai form demo, bật/tắt password, scroll đến và kiểm link footer. Không có pageerror JavaScript hoặc request failed; mọi response CSS/JS/image được quan sát đều 200.

Đã xem contact sheets của đủ 45 trường hợp lần đầu (source UI không thay đổi), đối chiếu thêm ảnh cuối đại diện mỗi viewport: home, form, movie detail, admin demo và error. Không thấy nội dung bị che/cắt, header/footer lệch hoặc form không sử dụng được. Không khẳng định đã thử bàn phím ảo, screen reader hoặc mọi browser.

Bằng chứng cuối: **45 PNG full-page + 45 JSON từng trang**, cùng [final/results.json](evidence/acceptance/final/results.json). Mỗi thư mục 360x800, 390x844, 768x1024, 1366x768, 1920x1080 có home/movies/movie/showtimes/login/register/booking/admin-demo/error. Không đánh đồng screenshot full-page có chiều cao dài hơn viewport với thay đổi viewport; JSON ghi width/height thực.

## 5. Apache thực tế

**48/48 PASS**, nằm trong cùng results.json và chạy trước responsive:

- Config `httpd -t` hợp lệ; front controller và toàn bộ 9 route có HTML/status đúng.
- Assets CSS/JS trả 200 đúng MIME; file PHP probe trong assets trả 404, không execute/lộ source.
- `/admin` 403 trước/sau form demo; `/missing` 404; sai method 405 + Allow; URL malformed 400; HEAD body rỗng.
- Login/register: session cookie thật HttpOnly/SameSite=Lax, không Secure trên local HTTP; missing/invalid CSRF 403, validation 422, token/session đúng tới placeholder 200; không phản chiếu password.
- `/app/`, `/config/`, `/database/`, `/storage/`, `/.env`, private files trong các thư mục đó và `/.git/config` không truy cập được (404).
- Fixture `/throw` trả 500 generic, không lộ sentinel/trace/workspace; các response ứng dụng không lộ PHP source hoặc stack trace.

400 malformed URL do Apache có thể trả trước PHP; không tuyên bố mọi error page đều do MVC render. Đây là Apache biệt lập với cấu hình nêu trên, không phải xác nhận mọi VirtualHost/XAMPP thực tế của thành viên. Session storage và log vẫn chịu NTFS ACL hiện tại.

## 6. Regression và bảo mật

| Kiểm tra | PASS | FAIL | Evidence |
| --- | ---: | ---: | --- |
| Unit/foundation + regression giai đoạn 1 | 63 | 0 | acceptance/unit.txt |
| PHP Development Server HTTP | 56 | 0 | acceptance/http.txt |
| Lint toàn bộ PHP hiện có | 51 | 0 | acceptance/lint.txt |
| Git audit giai đoạn 2, không cờ portable | 32 | 0 | acceptance/git-audit.txt |
| JavaScript syntax ứng dụng | 1 | 0 | acceptance/javascript.txt |
| **Bộ 203 hiện có** | **203** | **0** | Không giảm assertion hoặc file PHP |
| Apache local biệt lập | 48 | 0 | acceptance/final/results.json |
| Responsive 9 × 5 | 45 | 0 | acceptance/final/ |

Các kiểm bổ sung không cộng trùng vào 203: portable Git audit trên workspace và fixture riêng, hai kiểm syntax runner JS; Composer validate offline exit 0 với warning license. Không tự chọn license thay chủ dự án.

Bảo mật: CSRF, validation, session, middleware, XSS escaping, generic exceptions và admin default deny đều giữ nguyên và regression PASS. PDO lazy/native prepares/binding được kiểm bằng double, không kết nối DB. Git audit quét cả tracked/untracked candidate cho private-key/cloud/GitHub token/credential URL, kiểm .gitignore, forbidden tracked paths và whitespace từng file; không phát hiện credential thật trong candidate đã rà. Scan theo pattern không chứng minh lịch sử Git hoặc mọi dạng secret đều sạch.

Không có .env/config database thật trong candidate; DSN trong example chỉ localhost/utf8mb4, user/password trống. Evidence không lưu cookie value, CSRF token hoặc password đã điền; ảnh được chụp lại trên form trống sau submit. Ignored runtime logs/session/PID/config Apache và repo fixture không đưa vào commit. Không sửa .gitignore để bỏ qua source hoặc làm audit PASS.

## 7. Rà toàn bộ findings trước đó

| Finding | Mức | Trạng thái / ảnh hưởng nghiệm thu |
| --- | --- | --- |
| R1: responsive BLOCKED | Medium | **CLOSED**: 45/45 và ảnh/JSON; môi trường browser đã chạy được. |
| Apache chưa kiểm | Medium, thiếu bằng chứng | **CLOSED trong local config này**: 48/48; không còn blocker kỹ thuật này. |
| R2: ACL private rộng | Medium | **OPEN**: Authenticated Users Modify, Users ReadAndExecute trên private/session. Không đổi system ACL. Phải xử lý trước dùng máy nhiều tài khoản hoặc dữ liệu/credentials thật; không phải lỗi route public trong fixture HTTP đã kiểm. Chủ dự án cần biết đây là giới hạn local. |
| R3: Git audit khóa lịch sử | Medium | **RESOLVED cho workflow clone/CI** bằng --portable và fixture. Strict mode nguyên vẹn cho 203 kiểm tra. |
| R4: Core ↔ Middleware coupling | Low | **OPEN, không chặn Foundation**: không có vòng gọi nghiệp vụ/module hoặc lỗi runtime; không refactor kiến trúc khi không cần. |
| R5: route động có thể overlap | Low | **OPEN cho việc thêm route**: route hiện tại không xung đột; tiếp tục review ambiguity khi phát triển. |
| R6: PHP 8.1.10 cũ | Low | **OPEN**: chỉ có runtime cũ sẵn; Apache 2.4.54 cũng cũ. Không tự nâng cấp. Chọn runtime được hỗ trợ và chạy lại suite trước production; không chứng nhận runtime này an toàn production. |
| R7: inventory cũ | Low | **CLOSED cho snapshot mới**: acceptance/candidates.txt là danh sách hiện hành; evidence cũ giữ lịch sử. |
| Composer thiếu license | Low | **OPEN**: schema validate thành công, chủ dự án quyết định license trước công bố/phân phối. |

**Critical: 0, High: 0 được xác nhận.** Medium/Low có ảnh hưởng bắt buộc (responsive, Apache, workflow Git) đã được xử lý; không bỏ qua chúng chỉ vì mức độ không cao. ACL/runtime không được nâng thành PASS; giới hạn dùng local được ghi rõ để chủ dự án nghiệm thu.

## 8. MySQL, TLS và điều kiện cần xác nhận

Đối chiếu FOUNDATION_REPORT.md mục 5/7/11/12 và PRE_COMMIT_REVIEW.md mục 1/5: các báo cáo trước đã ghi MySQL/TLS chưa chạy, giới hạn GO kỹ thuật local và yêu cầu kiểm trước tính năng DB/deployment; chúng **không có biên bản chấp thuận rõ của chủ dự án cho việc phân kỳ**. Vì vậy báo cáo này ghi đề xuất, không tự xóa điều kiện.

| Hạng mục | Hiện trạng | Đề xuất cần chủ dự án xác nhận |
| --- | --- | --- |
| MySQL runtime | NOT RUN; không schema/ERD chính thức, không connection/migration/SQL | Nghiệm thu ở giai đoạn Database bằng DB test riêng sau chốt ERD. Foundation hiện chỉ nghiệm thu lazy PDO/binding qua double. |
| TLS end-to-end | NOT RUN; local Apache HTTP; chỉ cookie HTTPS detection fixture | Nghiệm thu ở giai đoạn Production/HTTPS trên môi trường được phép, gồm trusted proxy và Secure cookie thật. |
| ACL/runtime local | Chưa harden/nâng cấp | Nghiệm thu commit source với giới hạn môi trường local này, không cho phép dữ liệu thật/production. |
| Kết quả UI/Apache | Đã có evidence PASS | Chủ dự án xem báo cáo/ảnh và chấp nhận Foundation để chuyển final GO. |

Nếu MySQL hoặc TLS vẫn là điều kiện bắt buộc của Foundation, giữ **NO-GO** và chuẩn bị môi trường test được cấp phép ở bước tiếp theo; không được chạy DB thật hoặc tự cài/cấu hình hệ thống theo báo cáo này.

## 9. BLOCKED / NOT RUN còn lại

Không còn BLOCKED browser/Apache trong môi trường khảo sát. Chưa chạy: MySQL runtime, TLS thực, Edge/Firefox/Safari, điện thoại vật lý/bàn phím ảo, PHPCS, Linux runtime/case sensitivity, clone từ GitHub và Composer-generated autoload. Portable fixture là repo test tối thiểu, không được báo là đã clone GitHub thành công. Các mục ngoài Foundation local không tính là PASS.

## 10. Git và candidate files

Đã kiểm status/branch/remote/diff check/stat, tracked và untracked, cùng ignored runtime. Không có staged diff; HEAD và refs bảo vệ giữ baseline giai đoạn 2. README.md vẫn là file duy nhất đã tracked; phần lớn source và toàn bộ evidence vẫn untracked, nên tracked diff stat không mô tả đủ thay đổi.

Danh sách đầy đủ dự kiến commit: [evidence/acceptance/candidates.txt](evidence/acceptance/candidates.txt). File này bao gồm chính nó, báo cáo và toàn bộ evidence cuối. Chỉ gồm candidate không bị ignore; không có runtime log/cache/config Apache/session, dependency hoặc repo fixture.

Snapshot cuối có **192 candidate files: 1 tracked modified (README.md), 191 untracked**. Git audit strict kiểm đủ 192/192 file không lỗi whitespace/secret patterns và 32/32 assertion PASS; git diff --check sạch. Index không có staged changes. Chi tiết commands/refs tại acceptance/git-state.txt; không fetch hoặc xác nhận remote server ngoài URL đã cấu hình.

## 11. Lệnh đề xuất — chỉ chủ dự án thực hiện sau final GO

```powershell
git status --short
git branch --show-current
git diff --check
git ls-files --others --exclude-standard
$phpRuntime = 'C:/laragon/bin/php/php-8.1.10-Win32-vs16-x64/php.exe'
& $phpRuntime tests/run.php
& $phpRuntime tests/Integration/http.php
& $phpRuntime tests/lint.php
& $phpRuntime tests/Integration/git-audit.php --portable
node --check public/assets/js/app.js

# Chỉ sau xác nhận điều kiện phạm vi và quyết định GO của chủ dự án:
git add -- README.md .env.example .gitattributes .gitignore .htaccess FOUNDATION_REPORT.md bootstrap.php composer.json app config database docs public routes storage tests
git diff --cached --check
git diff --cached --stat
git diff --cached --name-only
git diff --cached
git commit -m "chore: add accepted MVC foundation"
git push -u origin chore/mvc-foundation
```

Mở PR thủ công: compare chore/mvc-foundation, base develop; đính kèm báo cáo và evidence. Không tự stage/commit/push/merge/deploy, không đổi main/develop/PHUONG/HUY/QUY, không thay đổi database hoặc cài phần mềm. Dừng sau báo cáo để chủ dự án nghiệm thu.
