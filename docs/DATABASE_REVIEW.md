# Database review — chưa chốt ERD

## Bằng chứng hiện có

Nhóm dự kiến dùng MySQL với **15 bảng** theo ERD đã thống nhất sơ bộ. Trong workspace chưa có
ERD, SQL, danh sách bảng/cột hoặc ràng buộc. Không suy đoán tên cột, kiểu dữ liệu hay constraint
và không tạo schema chính thức từ tên module. Chưa chạy kết nối, migration, seed hoặc truy vấn DB thật.

## Các vấn đề cần thống nhất

| Chủ đề | Câu hỏi / quyết định còn mở | Người phối hợp |
| --- | --- | --- |
| PK/FK | Bảng nào, khóa nào, kiểu dữ liệu tương thích, nullable, cascade/restrict, index FK? | Cả ba |
| Phim–thể loại | Một hay nhiều thể loại/phim? Có cần bảng liên kết? Quy tắc duy nhất và xóa? | HUY |
| Phòng–ghế–suất | Quan hệ phòng/cinema, định danh ghế trong phòng, suất thuộc phòng nào, lịch trùng xử lý thế nào? | HUY + QUY |
| Chống trùng ghế | Định nghĩa ghế theo suất, thời gian giữ, hết hạn; chiến lược khóa và constraint nào được ERD cho phép? | HUY + QUY |
| Transaction đặt vé | Ranh giới giữ ghế/đặt vé/thanh toán, rollback, retry, deadlock và idempotency? | QUY + HUY |
| Thanh toán | Tập trạng thái, chuyển trạng thái hợp lệ, webhook trùng/đảo thứ tự, hoàn tiền, đối soát? | QUY |
| Giá vé/combo | Giá nguồn và snapshot tại booking, đơn vị tiền/làm tròn, thay đổi giá sau đặt? | QUY + HUY |
| Mã giảm giá | Hiệu lực/thời gian, hạn mức tổng/user, cộng dồn, đồng thời, hoàn quota khi hủy? | QUY + PHUONG |
| Vé/QR | Mã vé/QR duy nhất ở phạm vi nào, phát sinh ngẫu nhiên, một lần sử dụng, chống quét lại và truy cập trái phép? | QUY + PHUONG |
| User/quyền | Mapping identity vào booking, reviews và admin; lưu mật khẩu hash, chính sách lưu/xóa? | PHUONG + cả nhóm |

Các câu hỏi trên là tiêu chí review, **không phải ràng buộc đã được quyết định**. Chưa khẳng định
cần thêm bảng nào hoặc đã có unique key chống double booking. Tính đúng phải được chứng minh bằng
ERD chính thức và kiểm thử cạnh tranh trên DB test sau này.

## Biên bản quyết định hiện tại

| Quyết định | Trạng thái |
| --- | --- |
| Dùng MySQL/PDO lazy | Chọn cho foundation; chưa chạy DB integration |
| Tổng số 15 bảng | Thông tin sơ bộ từ yêu cầu; chưa được kiểm chứng bằng ERD |
| Schema, tên bảng/cột và PK/FK chính thức | OPEN — cần ERD |
| Constraint, transaction và state machine | OPEN — cần nhóm review |
| Migration/seed | Chỉ có thư mục; không có SQL và không được chạy |

Khi chốt: ghi phiên bản/link ERD, ngày, người đồng ý, tác động từng module và test cần thêm.
Mọi thay đổi database phải qua PR và thống nhất cả ba; chỉ thử trên database local/test đã được phép.
