# Foundation release report — giai đoạn 2.5

Ngày: **2026-11-26**, Asia/Saigon. Nhánh: **chore/mvc-foundation**.
HEAD: `7ccc69a9f939ba10bf8ba868a1cc8cb30cb34a00`.

## 1. Quyết định release và phạm vi được phê duyệt

**GO cho commit MVC Foundation và mở Pull Request từ chore/mvc-foundation vào develop.** Chủ dự án đã phê duyệt phân kỳ bằng prompt giai đoạn 2.5; lý do chờ xác nhận MySQL/TLS trong báo cáo 2.4 đã được giải quyết. Các báo cáo cũ giữ nguyên giá trị lịch sử; kết luận hiện hành là báo cáo này. Đây không phải GO cho production, database nghiệp vụ hoặc bán vé thật.

| Cổng nghiệm thu | Phạm vi / trạng thái |
| --- | --- |
| Giai đoạn 2 — hiện tại | MVC, PHP, Router, Middleware, Session, CSRF, Validator, PDO configuration, Responsive, Apache. Đã có bằng chứng đạt các tiêu chí Foundation. |
| Giai đoạn 3 — Database | MySQL runtime, kết nối thực, schema, constraints, migrations, seed data: **NOT RUN, chuyển cổng**, không được báo PASS. Chỉ dùng DB test được phép sau chốt ERD. |
| Giai đoạn nghiệp vụ | Transaction, chống đặt trùng ghế, thanh toán, phát hành vé: **chưa triển khai/chưa nghiệm thu**. |
| Giai đoạn triển khai | TLS/HTTPS, trusted proxy, production configuration và deployment security: **NOT RUN, chuyển cổng**. Phải kiểm TLS/cookie thực trước deployment. |

Không bỏ hoặc quên điều kiện: MySQL và TLS là nghĩa vụ của các cổng mới, không còn là blocker commit source Foundation. ACL/runtime local vẫn phải được xử lý trước môi trường nhiều người dùng, credentials/dữ liệu thật hoặc production.

## 2. Đối chiếu kết quả thực tế

| Kiểm tra | Kết quả đã thực hiện | Phạm vi/bằng chứng |
| --- | --- | --- |
| Regression hiện có | **203/203 PASS, 0 FAIL** | 63 unit/foundation + 56 HTTP + 51 lint PHP + 32 Git audit strict + 1 JavaScript syntax; acceptance/unit.txt, http.txt, lint.txt, git-audit.txt, javascript.txt. |
| Responsive | **45/45 PASS** | 9 trang × 360×800, 390×844, 768×1024, 1366×768, 1920×1080; Chrome headless 158.0.8127.88 / Playwright 1.65.1. |
| Apache local biệt lập | **48/48 PASS** | Apache 2.4.54/mod_php 8.1.10, DocumentRoot public, localhost port trống; routing/assets/CSRF/session/private paths/admin deny/errors/PHP asset probe. |
| Composer validate | Exit 0 | Offline, cảnh báo thiếu license; không install/update. |
| Git portable | 32/32 trong evidence 2.4 | Vẫn kiểm ignore/secret/whitespace; mode strict mặc định vẫn giữ đúng branch và refs baseline. |
| Kiểm source portable mới ở 2.5 | **PASS** | Fresh-directory fixture thiếu storage/cache/acceptance ban đầu: portable 32/32, strict vẫn từ chối sai nhánh, node --check exit 0. |

Đã đối chiếu results.json: 93 check đều true, failures rỗng, 45 responsive rows; mỗi JSON từng trang bằng nguyên vẹn object trong mảng responsive. Đọc PNG header xác nhận đủ 45 ảnh, chiều rộng đúng viewport; không tạo lại screenshot hoặc đánh dấu test chưa chạy thành PASS. Kết quả Apache/Chrome đầy đủ tại [results.json](evidence/acceptance/final/results.json); tiến trình test đã dừng.

PHP/HTML/CSS/JS ứng dụng, routing và config server không thay đổi trong 2.5, nên không chạy lại các suite 203/45/48 chỉ để lặp audit. Một lỗi nhỏ của runner tests/Acceptance/portable-git.cjs được xác nhận: clone mới chưa có thư mục cache cha làm mkdtempSync lỗi. Đã thêm mkdirSync recursive trước khi tạo fixture và kiểm lại trong bản sao tối thiểu có cây cache trống. Không sửa assertion để đạt PASS, không hạ số kiểm tra. SETUP được sửa câu Apache chưa kiểm đã lỗi thời; evidence README giải thích việc loại JSON trùng khỏi commit. Chỉ source runner này và tài liệu thay đổi.

Snapshot source/config/test/assets hiện tại (72 file; 89,541 bytes) có SHA-256 kết hợp **806c060e3021df0a75d61a4ee773bfae7abc46845b7cb3a8293b2f85723ab5e9**. Cách tính: sort đường dẫn, ghép path + NUL + bytes file + NUL. Dùng để đối chiếu release sau review, không thay thế commit SHA hoặc giả làm hash được lưu từ suite cũ.

## 3. Vì sao 87 tăng thành 192

So inventory trong PRE_COMMIT_REVIEW.md với acceptance/candidates.txt và candidate thực tế đầu 2.5: **87 file cũ vẫn đủ, 0 bị loại; tăng đúng 105**.

| Phần tăng | File |
| --- | ---: |
| 45 PNG cuối + 45 JSON từng trang + final/results.json | 91 |
| Acceptance README, candidates.txt, composer.txt, git-audit.txt, git-portable.txt, git-state.txt, http.txt, initial-results.json, javascript.txt, lint.txt, unit.txt | 11 |
| Hai runner tests/Acceptance/*.cjs | 2 |
| docs/FOUNDATION_ACCEPTANCE_REPORT.md | 1 |
| **Tổng tăng** | **105** |

192 file đầu phiên có **4,133,449 bytes (3.942 MiB)**. Không phải tăng 105 production files: 102 là evidence/hướng dẫn evidence, 2 test và 1 báo cáo. Các source cũ sửa ở giai đoạn 2.4 không làm tăng số file.

Phiên 2.5 bổ sung đúng **3 tài liệu release**: báo cáo này, RELEASE_COMMIT_FILES.txt và RELEASE_EXCLUDED_FILES.txt. Candidate cuối: **195 file**; đề xuất commit **150**, giữ local không stage **45**. Không đổi .gitignore hoặc xóa/di chuyển evidence trong phiên này.

## 4. Phân loại và dung lượng toàn bộ candidate

Đơn vị bytes thực, MiB = bytes/1,048,576; là tổng nội dung file chưa nén, không phải Git pack hoặc dung lượng trên đĩa NTFS. Mỗi file đúng một nhóm. .gitkeep cấu trúc private thuộc P, .gitkeep images thuộc A; evidence có chủ đích thuộc E dù do test sinh, không đếm trùng vào G. Hai manifest release thuộc D.

| Nhóm | Candidate | Bytes | Đề xuất commit | Bytes commit |
| --- | ---: | ---: | ---: | ---: |
| P — Production source code | 54 | 33,284 | 54 | 33,284 |
| C — Configuration templates | 7 | 1,765 | 7 | 1,765 |
| T — Test source code | 8 | 44,945 | 8 | 44,945 |
| D — Documentation | 16 | 124,075 | 16 | 124,075 |
| A — Static assets | 3 | 9,547 | 3 | 9,547 |
| E — Test evidence | 107 | 3,957,334 | 62 | 3,918,976 |
| G — Generated files | 0 | 0 | 0 | 0 |
| X — Temporary files | 0 | 0 | 0 | 0 |
| S — Sensitive files | 0 | 0 | 0 | 0 |
| **Tổng** | **195** | **4,170,950** | **150** | **4,132,592** |

Source/config/test/assets P+C+T+A: **89,541 bytes**. Documentation D: **124,075 bytes**. Test evidence E: **3,957,334 bytes**, gồm **45 PNG (3,766,332 bytes)**; đề xuất giữ E **3,918,976 bytes**. Loại trùng tiết kiệm **38,358 bytes**, không phải tối ưu dung lượng đáng kể; lợi ích là tránh hai bản dữ liệu cùng thông tin bị lệch nhau.

Working tree đề xuất commit khoảng **3.941 MiB**. Git hiện chỉ có 3 packed objects, size-pack 2 KiB; pack sau commit/push phụ thuộc nén/delta và metadata, chưa tạo commit/pack để đo. Toàn repository clone dự kiến cỡ vài MiB, chủ yếu PNG đã nén; đây là ước lượng, không tuyên bố số bytes truyền qua Git chính xác. Cache/log/profile/dependency không cộng vào kích thước release.

## 5. File giữ và file loại khỏi commit

Danh sách chính xác, một path mỗi dòng:

- [RELEASE_COMMIT_FILES.txt](RELEASE_COMMIT_FILES.txt): **150 file** để stage; gồm source/config/assets/test/docs, bằng chứng tóm tắt, 45 screenshot và results.json chứa đầy đủ 45 trường hợp.
- [RELEASE_EXCLUDED_FILES.txt](RELEASE_EXCLUDED_FILES.txt): **45 JSON từng trang** tại docs/evidence/acceptance/final/<viewport>/<page>.json. Tất cả đều trùng object đã kiểm trong final/results.json. **Chỉ đề xuất không stage; file vẫn còn trên máy**, có thể tái sinh bằng runner. Không bỏ final/results.json hoặc PNG.

Giữ cả initial-results.json và các evidence text lịch sử nhỏ để minh bạch 3 FAIL harness ban đầu, không chỉ giữ kết quả PASS. Các file *.txt này là báo cáo kiểm thử đã rà, không phải runtime *.log. Không lược ảnh vì từng trang/kích thước là bằng chứng đã yêu cầu; tổng PNG chỉ khoảng 3.592 MiB nên chưa cần LFS.

Trong candidate không có browser profile, runtime log/cache/session/PID, executable (.exe/.dll/.so/.phar), dependency binary, .env thật, config database thật hoặc private key. Rà đường dẫn cá nhân C:/Users, AppData và tên người dùng: không có trong candidate. Hai tài liệu 2.4 nhắc đường dẫn workspace khác E:/Projects/ehs-card-desk để ghi nguồn Playwright đã dùng; đó là provenance có ích, không hardcode vào runner/production và không phải điều kiện clone chạy app. Thành viên truyền đường dẫn Playwright đã có trên máy mình.

Không bổ sung pattern .gitignore: rules hiện có đã che .env, config/database.php, vendor/node_modules, logs/cache/uploads và file tạm. 328 ignored files đầu phiên gồm artifacts/cache/test fixtures không vào release; không quét/đưa nội dung session vào báo cáo. Giữ 45 JSON untracked hiển thị có chủ đích, dùng manifest stage chính xác thay cho git add .; không dùng ignore để che evidence thất bại. Các pattern hiện tại và ngoại lệ .env.example/.gitkeep được kiểm lại bằng Git audit.

## 6. Final audit, Windows và clone/run

Branch đúng chore/mvc-foundation; origin fetch/push đúng https://github.com/phuong000/viet-cinema.git. HEAD/main/develop và remote-tracking PHUONG/HUY/QUY giữ SHA baseline. Index trống; chỉ README.md tracked modified, còn 194 file mới untracked. Không fetch/pull/switch/stage/commit/push/merge trong phiên. Chưa kiểm trạng thái GitHub live; PR base develop cần chọn đúng khi người dùng mở.

Git diff --check kiểm tracked diff; Git audit cuối kiểm riêng **mọi candidate gồm untracked** cho whitespace/ignore/secret patterns. Scan bổ sung đầu phiên không phát hiện credential URL/private key/cloud/GitHub token. Không chứng nhận mọi định dạng secret hoặc lịch sử remote đều đã sạch. Kiểm không có đường dẫn Windows reserved/invalid/trailing dot-space/collision khác hoa-thường; namespace của 23 class/interface App khớp đường dẫn thực. Tên module/namespace không thay đổi: PHUONG Auth/Admin/Core/shared, HUY Movies, QUY Booking; chưa có gọi controller chéo module hoặc schema nghiệp vụ.

Autoload nội bộ không cần vendor; PHP >=8.1 + PDO/session chạy placeholder không DB/.env. HTTP tests cần curl/proc_open, Git audit cần Git, JS check cần Node; browser/Apache acceptance runner cần tool đã cài và truyền argv. .gitattributes giữ LF, PowerShell examples phù hợp Windows. SETUP đã ghi fallback Laragon/XAMPP và DocumentRoot public, không yêu cầu một đường dẫn máy cá nhân cố định. Chưa thực hiện clone mạng từ GitHub hoặc chạy Linux; không báo hai mục này PASS. Portable fixture tối thiểu đã kiểm cây cache trống, không được đánh đồng với full remote clone.

Portable không làm suy yếu audit gốc: default vẫn đúng nhánh + 5 protected refs/SHA lịch sử và đủ 32 kiểm; --portable thay **hai ràng buộc lịch sử** bằng Git worktree hợp lệ và snapshot refs đầu/cuối để dùng nhánh bất kỳ, giữ nguyên 20 ignore, 6 exceptions, forbidden tracked paths, secret patterns, tracked/untracked whitespace. Portable không tương đương xác minh baseline phase 2; ai cần baseline chạy mode strict. Negative fixture vẫn chứng minh strict fail sai nhánh. Thay đổi 2.5 chỉ tạo thư mục cha, không đổi các điều kiện này.

## 7. Findings còn tồn tại

| Mức | Finding | Điều kiện xử lý / ảnh hưởng release |
| --- | --- | --- |
| Critical / High | Không phát hiện trong phạm vi đã review | Không có blocker mức này được xác nhận. |
| Medium | NTFS ACL private/session kế thừa rộng | OPEN; xử lý theo identity trước máy nhiều tài khoản, credentials/dữ liệu thật hoặc server. Không thay ACL ở phiên này; không chặn commit source Foundation local. |
| Low | PHP 8.1.10/Apache 2.4.54 cũ | OPEN; chọn runtime được hỗ trợ và test lại trước production. |
| Low | Core ↔ Middleware coupling; dynamic routes khác cấu trúc có thể overlap | OPEN; không có xung đột route hoặc lỗi runtime hiện tại, review khi mở rộng. Không refactor ngoài phạm vi. |
| Low | Composer chưa khai báo license | OPEN; chủ dự án chọn license trước phân phối; validate offline đã exit 0. |
| Low | Test runner portable thiếu cache cha ở clone mới | **CLOSED ở 2.5**, fresh-directory fixture PASS, strict guard còn nguyên. |

Browser/Apache blockers đã CLOSED ở 2.4; MySQL/TLS chuyển giai đoạn bằng phê duyệt 2.5, không phải lỗi được giả đánh dấu PASS. PHPCS, Edge/Firefox/Safari/điện thoại thật, Linux và Composer-generated autoload còn NOT RUN, không phải tiêu chí Foundation bắt buộc đã thống nhất. Release giữ các giới hạn đã nêu; không mở rộng schema/CRUD/booking.

## 8. Lệnh Git chính xác để chủ dự án thực hiện

Chỉ dùng cho snapshot và manifest đã review; nếu có source mới sau báo cáo thì kiểm phần bị ảnh hưởng trước commit. Không dùng git add . hoặc -f; 45 file local dư sẽ còn xuất hiện trong status và đó là chủ đích.

```powershell
Set-Location 'E:/Projects/viet-cinema'
if ((git branch --show-current) -ne 'chore/mvc-foundation') { throw 'Sai nhanh' }
git status --short
git diff --check
if ($LASTEXITCODE -ne 0) { throw 'Whitespace error' }
if ((git diff --cached --name-only)) { throw 'Index da co file; can review rieng' }

# Chỉ chủ dự án chạy bước stage này; agent chưa thực thi:
git --literal-pathspecs add --pathspec-from-file=docs/RELEASE_COMMIT_FILES.txt
if ($LASTEXITCODE -ne 0) { throw 'Stage failed' }
$expected = Get-Content docs/RELEASE_COMMIT_FILES.txt | Sort-Object
$actual = git -c core.quotepath=false diff --cached --name-only | Sort-Object
if (Compare-Object $expected $actual) { throw 'Staged files khong khop manifest' }
git diff --cached --check
if ($LASTEXITCODE -ne 0) { throw 'Staged whitespace error' }
git diff --cached --stat
git diff --cached

# Sau khi người dùng đã xem staged diff:
git commit -m "chore: add accepted MVC foundation"
if ($LASTEXITCODE -ne 0) { throw 'Commit failed' }
git push -u origin chore/mvc-foundation
if ($LASTEXITCODE -ne 0) { throw 'Push failed' }
```

Mở PR trên GitHub: **base develop**, **compare chore/mvc-foundation**. Mô tả Foundation, 203/45/48 PASS, phê duyệt phân kỳ và findings; không merge/deploy tự động. Mode Git strict vẫn chỉ dành cho snapshot phase 2; workflow sau commit/clone dùng --portable như README/Composer.

## 9. Đồng bộ PHUONG, HUY, QUY

Chờ Foundation được merge vào develop qua PR trước khi tích hợp vào nhánh thành viên. Mỗi người kiểm working tree sạch, tự bảo toàn thay đổi đang làm; không reset/clean/force push. Người chưa có nhánh local dùng git switch --track origin/PHUONG (hoặc HUY/QUY); người đã có nhánh dùng git switch như dưới.

```powershell
git status --short
git fetch origin
git switch PHUONG  # HUY thay bằng HUY, QUY thay bằng QUY
git pull --ff-only origin PHUONG  # dùng đúng tên nhánh của mình
git merge origin/develop
# Nếu conflict: xử lý cùng owner, review cả hai phía, không chọn ours/theirs toàn bộ.
php tests/run.php
php tests/Integration/http.php
php tests/lint.php
php tests/Integration/git-audit.php --portable
node --check public/assets/js/app.js
# Sau khi tests/review thành công, thành viên tự push nhánh của mình.
```

Clone mới sau merge: git clone --branch develop https://github.com/phuong000/viet-cinema.git; cd viet-cinema; copy .env.example thành .env nếu cần; php -S 127.0.0.1:8000 -t public public/index.php. Không cần migration hoặc MySQL cho trang mẫu. PHP chưa có PATH dùng runtime phù hợp theo SETUP; không sao chép password local của người khác.

PHUONG điều phối Core/security/layout; HUY và QUY chỉ sửa module của mình, review contract chung trước tích hợp. Trước DB phase cả ba chốt ERD; trước deployment kiểm TLS và hardening. Các lệnh sync chỉ là hướng dẫn, không có nhánh nào đã được agent thay đổi.

## 10. Inventory đầy đủ — 195 candidate

Mã nhóm theo bảng mục 4. COMMIT/LOCAL là đề xuất, không phải trạng thái index. Cả 192 file đầu phiên và 3 tài liệu release đều được liệt kê; dung lượng report tự tính đến nội dung inventory này.

| Nhóm | Bytes | Đề xuất | File |
| --- | ---: | --- | --- |
| C | 155 | COMMIT | .env.example |
| C | 99 | COMMIT | .gitattributes |
| C | 345 | COMMIT | .gitignore |
| C | 91 | COMMIT | .htaccess |
| D | 16,949 | COMMIT | FOUNDATION_REPORT.md |
| D | 4,084 | COMMIT | README.md |
| P | 312 | COMMIT | app/Controllers/Admin/DashboardController.php |
| P | 1,335 | COMMIT | app/Controllers/Auth/AuthController.php |
| P | 292 | COMMIT | app/Controllers/Booking/BookingController.php |
| P | 295 | COMMIT | app/Controllers/HomeController.php |
| P | 703 | COMMIT | app/Controllers/Movies/MovieController.php |
| P | 2,286 | COMMIT | app/Core/Application.php |
| P | 282 | COMMIT | app/Core/Controller.php |
| P | 519 | COMMIT | app/Core/Csrf.php |
| P | 703 | COMMIT | app/Core/Database.php |
| P | 725 | COMMIT | app/Core/Environment.php |
| P | 237 | COMMIT | app/Core/HttpException.php |
| P | 411 | COMMIT | app/Core/Model.php |
| P | 1,763 | COMMIT | app/Core/Request.php |
| P | 1,572 | COMMIT | app/Core/Response.php |
| P | 2,964 | COMMIT | app/Core/Router.php |
| P | 1,829 | COMMIT | app/Core/Session.php |
| P | 1,204 | COMMIT | app/Core/Validator.php |
| P | 1,261 | COMMIT | app/Core/View.php |
| P | 0 | COMMIT | app/Helpers/.gitkeep |
| P | 202 | COMMIT | app/Middleware/Middleware.php |
| P | 572 | COMMIT | app/Middleware/RequireAccess.php |
| P | 550 | COMMIT | app/Middleware/VerifyCsrf.php |
| P | 0 | COMMIT | app/Models/Auth/.gitkeep |
| P | 0 | COMMIT | app/Models/Booking/.gitkeep |
| P | 0 | COMMIT | app/Models/Movies/.gitkeep |
| P | 187 | COMMIT | app/Services/Auth/AccessPolicy.php |
| P | 277 | COMMIT | app/Services/Auth/DenyAccess.php |
| P | 0 | COMMIT | app/Services/Booking/.gitkeep |
| P | 0 | COMMIT | app/Services/Movies/.gitkeep |
| P | 646 | COMMIT | app/Views/admin/dashboard.php |
| P | 1,478 | COMMIT | app/Views/auth/form.php |
| P | 615 | COMMIT | app/Views/booking/index.php |
| P | 401 | COMMIT | app/Views/errors/http.php |
| P | 1,658 | COMMIT | app/Views/home.php |
| P | 763 | COMMIT | app/Views/layouts/admin.php |
| P | 766 | COMMIT | app/Views/layouts/main.php |
| P | 278 | COMMIT | app/Views/movies/index.php |
| P | 681 | COMMIT | app/Views/movies/show.php |
| P | 504 | COMMIT | app/Views/movies/showtimes.php |
| P | 682 | COMMIT | app/Views/partials/alerts.php |
| P | 356 | COMMIT | app/Views/partials/footer.php |
| P | 493 | COMMIT | app/Views/partials/header.php |
| P | 576 | COMMIT | app/Views/partials/movie-card.php |
| P | 381 | COMMIT | bootstrap.php |
| C | 504 | COMMIT | composer.json |
| C | 310 | COMMIT | config/database.example.php |
| D | 940 | COMMIT | database/README.md |
| P | 0 | COMMIT | database/migrations/.gitkeep |
| P | 0 | COMMIT | database/seeds/.gitkeep |
| D | 5,838 | COMMIT | docs/ARCHITECTURE.md |
| D | 3,470 | COMMIT | docs/CODING_STANDARD.md |
| D | 3,102 | COMMIT | docs/DATABASE_REVIEW.md |
| D | 18,451 | COMMIT | docs/FOUNDATION_ACCEPTANCE_REPORT.md |
| D | 1,094 | COMMIT | docs/FOUNDATION_GUIDE.md |
| D | 29,597 | COMMIT | docs/FOUNDATION_RELEASE_REPORT.md |
| D | 19,603 | COMMIT | docs/PRE_COMMIT_REVIEW.md |
| D | 5,100 | COMMIT | docs/RELEASE_COMMIT_FILES.txt |
| D | 2,356 | COMMIT | docs/RELEASE_EXCLUDED_FILES.txt |
| D | 5,779 | COMMIT | docs/SETUP.md |
| D | 3,525 | COMMIT | docs/TEAM_WORKFLOW.md |
| D | 1,870 | COMMIT | docs/evidence/README.md |
| D | 2,317 | COMMIT | docs/evidence/acceptance/README.md |
| E | 7,360 | COMMIT | docs/evidence/acceptance/candidates.txt |
| E | 265 | COMMIT | docs/evidence/acceptance/composer.txt |
| E | 678 | LOCAL | docs/evidence/acceptance/final/1366x768/admin-demo.json |
| E | 42,367 | COMMIT | docs/evidence/acceptance/final/1366x768/admin-demo.png |
| E | 808 | LOCAL | docs/evidence/acceptance/final/1366x768/booking.json |
| E | 42,742 | COMMIT | docs/evidence/acceptance/final/1366x768/booking.png |
| E | 806 | LOCAL | docs/evidence/acceptance/final/1366x768/error.json |
| E | 26,310 | COMMIT | docs/evidence/acceptance/final/1366x768/error.png |
| E | 798 | LOCAL | docs/evidence/acceptance/final/1366x768/home.json |
| E | 390,233 | COMMIT | docs/evidence/acceptance/final/1366x768/home.png |
| E | 1,080 | LOCAL | docs/evidence/acceptance/final/1366x768/login.json |
| E | 34,224 | COMMIT | docs/evidence/acceptance/final/1366x768/login.png |
| E | 812 | LOCAL | docs/evidence/acceptance/final/1366x768/movie.json |
| E | 124,352 | COMMIT | docs/evidence/acceptance/final/1366x768/movie.png |
| E | 806 | LOCAL | docs/evidence/acceptance/final/1366x768/movies.json |
| E | 114,689 | COMMIT | docs/evidence/acceptance/final/1366x768/movies.png |
| E | 1,086 | LOCAL | docs/evidence/acceptance/final/1366x768/register.json |
| E | 34,044 | COMMIT | docs/evidence/acceptance/final/1366x768/register.png |
| E | 812 | LOCAL | docs/evidence/acceptance/final/1366x768/showtimes.json |
| E | 35,135 | COMMIT | docs/evidence/acceptance/final/1366x768/showtimes.png |
| E | 680 | LOCAL | docs/evidence/acceptance/final/1920x1080/admin-demo.json |
| E | 45,879 | COMMIT | docs/evidence/acceptance/final/1920x1080/admin-demo.png |
| E | 810 | LOCAL | docs/evidence/acceptance/final/1920x1080/booking.json |
| E | 45,641 | COMMIT | docs/evidence/acceptance/final/1920x1080/booking.png |
| E | 808 | LOCAL | docs/evidence/acceptance/final/1920x1080/error.json |
| E | 30,136 | COMMIT | docs/evidence/acceptance/final/1920x1080/error.png |
| E | 800 | LOCAL | docs/evidence/acceptance/final/1920x1080/home.json |
| E | 399,815 | COMMIT | docs/evidence/acceptance/final/1920x1080/home.png |
| E | 1,082 | LOCAL | docs/evidence/acceptance/final/1920x1080/login.json |
| E | 38,287 | COMMIT | docs/evidence/acceptance/final/1920x1080/login.png |
| E | 814 | LOCAL | docs/evidence/acceptance/final/1920x1080/movie.json |
| E | 128,195 | COMMIT | docs/evidence/acceptance/final/1920x1080/movie.png |
| E | 808 | LOCAL | docs/evidence/acceptance/final/1920x1080/movies.json |
| E | 118,285 | COMMIT | docs/evidence/acceptance/final/1920x1080/movies.png |
| E | 1,088 | LOCAL | docs/evidence/acceptance/final/1920x1080/register.json |
| E | 38,109 | COMMIT | docs/evidence/acceptance/final/1920x1080/register.png |
| E | 814 | LOCAL | docs/evidence/acceptance/final/1920x1080/showtimes.json |
| E | 38,355 | COMMIT | docs/evidence/acceptance/final/1920x1080/showtimes.png |
| E | 674 | LOCAL | docs/evidence/acceptance/final/360x800/admin-demo.json |
| E | 38,242 | COMMIT | docs/evidence/acceptance/final/360x800/admin-demo.png |
| E | 804 | LOCAL | docs/evidence/acceptance/final/360x800/booking.json |
| E | 35,210 | COMMIT | docs/evidence/acceptance/final/360x800/booking.png |
| E | 802 | LOCAL | docs/evidence/acceptance/final/360x800/error.json |
| E | 22,257 | COMMIT | docs/evidence/acceptance/final/360x800/error.png |
| E | 794 | LOCAL | docs/evidence/acceptance/final/360x800/home.json |
| E | 230,256 | COMMIT | docs/evidence/acceptance/final/360x800/home.png |
| E | 1,076 | LOCAL | docs/evidence/acceptance/final/360x800/login.json |
| E | 29,327 | COMMIT | docs/evidence/acceptance/final/360x800/login.png |
| E | 808 | LOCAL | docs/evidence/acceptance/final/360x800/movie.json |
| E | 115,652 | COMMIT | docs/evidence/acceptance/final/360x800/movie.png |
| E | 802 | LOCAL | docs/evidence/acceptance/final/360x800/movies.json |
| E | 101,502 | COMMIT | docs/evidence/acceptance/final/360x800/movies.png |
| E | 1,082 | LOCAL | docs/evidence/acceptance/final/360x800/register.json |
| E | 28,971 | COMMIT | docs/evidence/acceptance/final/360x800/register.png |
| E | 808 | LOCAL | docs/evidence/acceptance/final/360x800/showtimes.json |
| E | 29,567 | COMMIT | docs/evidence/acceptance/final/360x800/showtimes.png |
| E | 674 | LOCAL | docs/evidence/acceptance/final/390x844/admin-demo.json |
| E | 38,741 | COMMIT | docs/evidence/acceptance/final/390x844/admin-demo.png |
| E | 804 | LOCAL | docs/evidence/acceptance/final/390x844/booking.json |
| E | 35,161 | COMMIT | docs/evidence/acceptance/final/390x844/booking.png |
| E | 802 | LOCAL | docs/evidence/acceptance/final/390x844/error.json |
| E | 22,511 | COMMIT | docs/evidence/acceptance/final/390x844/error.png |
| E | 794 | LOCAL | docs/evidence/acceptance/final/390x844/home.json |
| E | 243,160 | COMMIT | docs/evidence/acceptance/final/390x844/home.png |
| E | 1,076 | LOCAL | docs/evidence/acceptance/final/390x844/login.json |
| E | 29,652 | COMMIT | docs/evidence/acceptance/final/390x844/login.png |
| E | 808 | LOCAL | docs/evidence/acceptance/final/390x844/movie.json |
| E | 120,814 | COMMIT | docs/evidence/acceptance/final/390x844/movie.png |
| E | 802 | LOCAL | docs/evidence/acceptance/final/390x844/movies.json |
| E | 106,731 | COMMIT | docs/evidence/acceptance/final/390x844/movies.png |
| E | 1,082 | LOCAL | docs/evidence/acceptance/final/390x844/register.json |
| E | 29,292 | COMMIT | docs/evidence/acceptance/final/390x844/register.png |
| E | 808 | LOCAL | docs/evidence/acceptance/final/390x844/showtimes.json |
| E | 29,794 | COMMIT | docs/evidence/acceptance/final/390x844/showtimes.png |
| E | 676 | LOCAL | docs/evidence/acceptance/final/768x1024/admin-demo.json |
| E | 36,355 | COMMIT | docs/evidence/acceptance/final/768x1024/admin-demo.png |
| E | 806 | LOCAL | docs/evidence/acceptance/final/768x1024/booking.json |
| E | 35,747 | COMMIT | docs/evidence/acceptance/final/768x1024/booking.png |
| E | 804 | LOCAL | docs/evidence/acceptance/final/768x1024/error.json |
| E | 25,641 | COMMIT | docs/evidence/acceptance/final/768x1024/error.png |
| E | 796 | LOCAL | docs/evidence/acceptance/final/768x1024/home.json |
| E | 329,410 | COMMIT | docs/evidence/acceptance/final/768x1024/home.png |
| E | 1,078 | LOCAL | docs/evidence/acceptance/final/768x1024/login.json |
| E | 32,763 | COMMIT | docs/evidence/acceptance/final/768x1024/login.png |
| E | 810 | LOCAL | docs/evidence/acceptance/final/768x1024/movie.json |
| E | 123,317 | COMMIT | docs/evidence/acceptance/final/768x1024/movie.png |
| E | 804 | LOCAL | docs/evidence/acceptance/final/768x1024/movies.json |
| E | 104,869 | COMMIT | docs/evidence/acceptance/final/768x1024/movies.png |
| E | 1,084 | LOCAL | docs/evidence/acceptance/final/768x1024/register.json |
| E | 32,525 | COMMIT | docs/evidence/acceptance/final/768x1024/register.png |
| E | 810 | LOCAL | docs/evidence/acceptance/final/768x1024/showtimes.json |
| E | 32,067 | COMMIT | docs/evidence/acceptance/final/768x1024/showtimes.png |
| E | 55,217 | COMMIT | docs/evidence/acceptance/final/results.json |
| E | 1,798 | COMMIT | docs/evidence/acceptance/git-audit.txt |
| E | 3,781 | COMMIT | docs/evidence/acceptance/git-portable.txt |
| E | 8,837 | COMMIT | docs/evidence/acceptance/git-state.txt |
| E | 2,367 | COMMIT | docs/evidence/acceptance/http.txt |
| E | 55,160 | COMMIT | docs/evidence/acceptance/initial-results.json |
| E | 45 | COMMIT | docs/evidence/acceptance/javascript.txt |
| E | 2,237 | COMMIT | docs/evidence/acceptance/lint.txt |
| E | 2,367 | COMMIT | docs/evidence/acceptance/unit.txt |
| E | 3,146 | COMMIT | docs/evidence/files.txt |
| E | 1,789 | COMMIT | docs/evidence/git-audit.txt |
| E | 1,328 | COMMIT | docs/evidence/git-state.txt |
| E | 2,359 | COMMIT | docs/evidence/http.txt |
| E | 2,229 | COMMIT | docs/evidence/lint.txt |
| E | 2,359 | COMMIT | docs/evidence/unit.txt |
| C | 261 | COMMIT | public/.htaccess |
| A | 9,093 | COMMIT | public/assets/css/app.css |
| A | 0 | COMMIT | public/assets/images/.gitkeep |
| A | 454 | COMMIT | public/assets/js/app.js |
| P | 834 | COMMIT | public/index.php |
| P | 459 | COMMIT | routes/admin.php |
| P | 330 | COMMIT | routes/auth.php |
| P | 214 | COMMIT | routes/booking.php |
| P | 284 | COMMIT | routes/movies.php |
| P | 404 | COMMIT | routes/web.php |
| P | 0 | COMMIT | storage/cache/.gitkeep |
| P | 0 | COMMIT | storage/logs/.gitkeep |
| T | 15,023 | COMMIT | tests/Acceptance/local.cjs |
| T | 1,689 | COMMIT | tests/Acceptance/portable-git.cjs |
| T | 4,795 | COMMIT | tests/Integration/git-audit.php |
| T | 9,957 | COMMIT | tests/Integration/http.php |
| T | 1,075 | COMMIT | tests/Integration/router.php |
| T | 9,295 | COMMIT | tests/Unit/foundation.php |
| T | 1,081 | COMMIT | tests/lint.php |
| T | 2,030 | COMMIT | tests/run.php |
