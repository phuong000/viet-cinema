
<?php

require_once __DIR__ . '/../models/Movie.php';
require_once __DIR__ . '/../models/Category.php';

class MovieController
{
    private $movie;
    private $category;

    public function __construct()
    {
        $this->movie = new Movie();
        $this->category = new Category();
    }

    // GET: Danh sách phim
    public function index()
    {
        return $this->movie->getAll();
    }

    // GET: Chi tiết phim
    public function show($id)
    {
        return $this->movie->getById($id);
    }

    // Kiểm tra dữ liệu phim
    private function validate($data)
    {
        if (empty(trim($data['title'] ?? ''))) {
            throw new InvalidArgumentException("Tên phim không được để trống");
        }

        if (!isset($data['duration']) ||
            filter_var($data['duration'], FILTER_VALIDATE_INT) === false ||
            (int)$data['duration'] <= 0) {
            throw new InvalidArgumentException("Thời lượng phim phải là số nguyên dương");
        }

        if (empty($data['category_id']) ||
            !$this->category->getById($data['category_id'])) {
            throw new InvalidArgumentException("Thể loại phim không hợp lệ");
        }

        if (!empty($data['release_date'])) {
            $date = DateTime::createFromFormat('Y-m-d', $data['release_date']);

            if (!$date || $date->format('Y-m-d') !== $data['release_date']) {
                throw new InvalidArgumentException("Ngày phát hành không hợp lệ");
            }
        }
    }

    // POST: Thêm phim
    public function store($data)
    {
        $this->validate($data);
        return $this->movie->create($data);
    }

    // PUT: Sửa phim
    public function update($id, $data)
    {
        if (!$this->movie->getById($id)) {
            throw new RuntimeException("Không tìm thấy phim");
        }

        $this->validate($data);
        return $this->movie->update($id, $data);
    }

    // DELETE: Xóa phim
    public function destroy($id)
    {
        if (!$this->movie->getById($id)) {
            throw new RuntimeException("Không tìm thấy phim");
        }

        return $this->movie->delete($id);
    }
}
