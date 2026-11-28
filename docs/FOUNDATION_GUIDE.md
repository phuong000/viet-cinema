# PHP MVC Foundation

Hướng dẫn giai đoạn 2 hiện hành:

- [SETUP.md](SETUP.md): cài PHP, chạy trên Windows/VS Code/XAMPP.
- [ARCHITECTURE.md](ARCHITECTURE.md): lifecycle, Core, middleware và ranh giới module.
- [TEAM_WORKFLOW.md](TEAM_WORKFLOW.md): quyền sở hữu và PR vào develop.
- [CODING_STANDARD.md](CODING_STANDARD.md): conventions, security và test.
- [DATABASE_REVIEW.md](DATABASE_REVIEW.md): các vấn đề ERD còn mở.
- [../FOUNDATION_REPORT.md](../FOUNDATION_REPORT.md): kết quả và giới hạn nghiệm thu.

Chạy từ repository root:

```powershell
php -S 127.0.0.1:8000 -t public public/index.php
php tests/run.php
php tests/Integration/http.php
php tests/Integration/git-audit.php --portable
php tests/lint.php
```

Nếu PHP chưa có PATH, dùng đường dẫn runtime trong SETUP.md. Không cần kết nối database
để chạy trang mẫu. Không có login, booking hoặc payment thật. Admin mặc định bị từ chối.
Chỉ public/ được expose. Không commit/push/merge/deploy trước khi người dùng nghiệm thu.
