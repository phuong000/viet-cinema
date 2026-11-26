# Quy trình nhóm

## Sở hữu và phạm vi

| Thành viên / nhánh | Nghiệp vụ | File sở hữu |
| --- | --- | --- |
| PHUONG / PHUONG | Authentication, Authorization, User Management, Administration | Auth và Admin controllers/views; Models/Auth; Services/Auth; auth.php và admin.php |
| HUY / HUY | Movies, Categories, Cinema Rooms, Seats, Showtimes, Reviews | Module Movies trong Controllers/Models/Services/Views; movies.php |
| QUY / QUY | Booking, Combos, Discounts, Payment, Tickets, Booking History | Module Booking trong Controllers/Models/Services/Views; booking.php |

PHUONG điều phối Core, Middleware, Helpers, config, bootstrap, layout/partials, public assets và web.php.
Thành viên được phát triển cả frontend lẫn backend trong module mình. Chỉ thêm CSS selector có phạm vi
module vào file riêng khi cần; layout và component dùng chung trao đổi qua PR để tránh chỉnh trùng.
Migrations/seeds và database docs cần cả ba thống nhất; không sửa schema thật một cách độc lập.

## Git hằng ngày (hướng dẫn, không phải lệnh đã thực hiện)

```powershell
git status
git branch --show-current
git fetch origin
git switch HUY  # hoặc PHUONG / QUY, chỉ khi working tree đã an toàn
git pull --ff-only origin HUY
# Lập trình và kiểm thử module của mình
git diff --check
git diff
git add <cac-file-da-review>
git diff --cached
git commit -m "feat(movies): describe concrete change"
git push origin HUY
```

Nếu working tree có thay đổi, không switch/reset/clean để làm mất code. Lưu thay đổi bằng commit
đã review hoặc cách nhóm thống nhất trước khi đổi nhánh. Không stage toàn bộ mà chưa kiểm tra credentials.

## Pull Request và review

Mở PR từ nhánh cá nhân vào `develop`, không đẩy trực tiếp vào `main`. PR nêu vấn đề, phạm vi module,
ảnh giao diện nếu có, commands/kết quả test và ảnh hưởng database. Người phụ trách module review nghiệp vụ;
PHUONG review mọi thay đổi core/security/layout; thay đổi contract giữa HUY và QUY cần cả hai review.
Tất cả kiểm thử foundation phải chạy lại khi thay đổi shared code. Chỉ merge sau review theo quy định nhóm.
`main` nhận phiên bản ổn định sau nghiệm thu chung; Foundation GO không tương đương release toàn website.

## Conflict

Đồng bộ `develop` vào nhánh cá nhân theo quy ước nhóm (merge hoặc rebase do nhóm quyết định).
Đọc cả hai phía conflict, trao đổi với tác giả, giữ các hành vi hợp lệ, chạy lại unit/HTTP tests.
Không chọn toàn bộ ours/theirs một cách mù quáng, không force push nhánh dùng chung.
Nhờ owner xử lý conflict ở Core/layout/database nếu chưa hiểu tác động.

## Database

Đề xuất thay đổi trong DATABASE_REVIEW.md: bằng chứng ERD, mục đích, owner, tác động module,
transaction/locking và kế hoạch kiểm thử. Cả ba chốt PK/FK, constraint và trạng thái trước khi viết migration.
Không tạo schema chính thức từ suy đoán; chỉ chạy migration trên DB local/test sau khi được phép.
Đừng đưa dump, dữ liệu khách hàng hoặc mật khẩu thật vào PR.

Phiên triển khai foundation hiện tại chỉ làm việc trên `chore/mvc-foundation`, không thực hiện
fetch/pull/commit/push/merge hoặc đổi refs của các nhánh thành viên.
