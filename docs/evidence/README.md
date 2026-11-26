# Bằng chứng kiểm thử giai đoạn 2

Các file `.txt` là stdout từ lệnh đã chạy ngày 2026-11-26, không phải kết quả dự đoán.

- `unit.txt`: `php tests/run.php` — gồm 11 kiểm tra regression ban đầu.
- `http.txt`: `php tests/Integration/http.php` — gồm 6 kiểm tra HTTP regression ban đầu.
- `lint.txt`: `php tests/lint.php` — duyệt toàn bộ PHP của app/config/public/routes/tests và bootstrap.
- `git-audit.txt`: `php tests/Integration/git-audit.php` — ignore, tracked files, candidate scan,
  whitespace cả file untracked và refs bảo vệ.
- `git-state.txt`: status, branch, remote, diff stat/check, refs và ls-files tại nghiệm thu.
- `files.txt`: inventory candidate cuối cùng (không bao gồm log/cache runtime bị ignore).

`node --check public/assets/js/app.js` đã chạy exit 0 (không in output).
Các suite PHP dùng runtime Laragon PHP 8.1.10. Không chạy truy vấn database thật.

Responsive visual: **BLOCKED**, browser host không khả dụng; native browser inspection dừng
do URL policy chưa hỗ trợ trên Cốc Cốc. Không có ảnh chụp hoặc tuyên bố desktop/mobile visual PASS.
Apache, MySQL integration, TLS thật, Composer validation và PHPCS: **NOT RUN**.
Secure cookie chỉ được xác nhận với HTTPS detection fixture và unit options, không phải TLS end-to-end.

Lệnh đã thử trong quá trình phát triển có lỗi: session ban đầu trỏ thư mục Laragon ngoài workspace
(đã chuyển về private storage), probe audit PowerShell bị execution policy chặn (đã thay bằng PHP,
không thay execution policy), và no-index diff cần phân biệt exit 1 có thay đổi với lỗi whitespace
(đã sửa audit). Kết quả cuối sau sửa nằm ở các file bằng chứng; không còn FAIL trong suite hiện tại.
