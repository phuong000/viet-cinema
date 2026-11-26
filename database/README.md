# Database preparation

Dự kiến MySQL, 15 bảng theo ERD sơ bộ do nhóm đề cập. Chưa nhận được ERD chính thức hoặc
danh sách bảng/cột nên **không có schema SQL chính thức**, không migration/seed để chạy.

- `migrations/` và `seeds/`: `.gitkeep` giữ cấu trúc, không tự động thực thi.
- `../.env.example`, `../config/database.example.php`: cấu hình mẫu không có credentials thật.
- `App\Core\Database::connection()`: lazy PDO; chỉ gọi khi có truy vấn cần thiết.
- `App\Core\Model::query()`: prepare/execute, bind giá trị đầu vào.

Xác nhận các câu hỏi trong `docs/DATABASE_REVIEW.md` cùng cả ba thành viên trước khi tạo migration.
Foundation không truy cập database thật và không thay đổi database hiện có. Kiểm thử binding hiện
dùng PDO double, chưa xác nhận tương thích schema/constraint/transaction trên MySQL.
