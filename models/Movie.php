
<?php

require_once __DIR__ . '/../config/database.php';

class Movie
{
    private $conn;
    private $table = "movies";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // READ: Lấy tất cả phim kèm tên thể loại
    public function getAll()
    {
        $sql = "SELECT m.*, c.name AS category_name
                FROM movies m
                INNER JOIN categories c
                ON m.category_id = c.id
                ORDER BY m.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // READ: Lấy phim theo ID
    public function getById($id)
    {
        $sql = "SELECT m.*, c.name AS category_name
                FROM movies m
                INNER JOIN categories c
                ON m.category_id = c.id
                WHERE m.id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    // CREATE: Thêm phim
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table}
                (title, description, director, duration,
                 release_date, poster, category_id)
                VALUES
                (:title, :description, :director, :duration,
                 :release_date, :poster, :category_id)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'director' => $data['director'] ?? null,
            'duration' => $data['duration'],
            'release_date' => $data['release_date'] ?: null,
            'poster' => $data['poster'] ?? null,
            'category_id' => $data['category_id']
        ]);
    }

    // UPDATE: Cập nhật phim
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table}
                SET title = :title,
                    description = :description,
                    director = :director,
                    duration = :duration,
                    release_date = :release_date,
                    poster = :poster,
                    category_id = :category_id
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            'id' => $id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'director' => $data['director'] ?? null,
            'duration' => $data['duration'],
            'release_date' => $data['release_date'] ?: null,
            'poster' => $data['poster'] ?? null,
            'category_id' => $data['category_id']
        ]);
    }

    // DELETE: Xóa phim
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }
}
