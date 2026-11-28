
<?php

require_once __DIR__ . '/../config/database.php';

class Category
{
    private $conn;
    private $table = "categories";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // READ: Lấy tất cả thể loại
    public function getAll()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // READ: Lấy thể loại theo ID
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    // CREATE: Thêm thể loại
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table}
                (name, description)
                VALUES (:name, :description)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? null
        ]);
    }

    // UPDATE: Cập nhật thể loại
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table}
                SET name = :name,
                    description = :description
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null
        ]);
    }

    // DELETE: Xóa thể loại
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    // Kiểm tra thể loại đã có phim chưa
    public function hasMovies($id)
    {
        $sql = "SELECT COUNT(*) FROM movies WHERE category_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetchColumn() > 0;
    }
}
