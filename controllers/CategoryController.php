
<?php

require_once __DIR__ . '/../models/Category.php';

class CategoryController
{
    private $category;

    public function __construct()
    {
        $this->category = new Category();
    }

    // GET: Danh sách thể loại
    public function index()
    {
        return $this->category->getAll();
    }

    // GET: Chi tiết thể loại
    public function show($id)
    {
        return $this->category->getById($id);
    }

    // POST: Thêm thể loại
    public function store($data)
    {
        if (empty(trim($data['name'] ?? ''))) {
            throw new InvalidArgumentException("Tên thể loại không được để trống");
        }

        return $this->category->create($data);
    }

    // PUT: Sửa thể loại
    public function update($id, $data)
    {
        if (!$this->category->getById($id)) {
            throw new RuntimeException("Không tìm thấy thể loại");
        }

        if (empty(trim($data['name'] ?? ''))) {
            throw new InvalidArgumentException("Tên thể loại không được để trống");
        }

        return $this->category->update($id, $data);
    }

    // DELETE: Xóa thể loại
    public function destroy($id)
    {
        if (!$this->category->getById($id)) {
            throw new RuntimeException("Không tìm thấy thể loại");
        }

        if ($this->category->hasMovies($id)) {
            throw new RuntimeException("Không thể xóa thể loại đang có phim");
        }

        return $this->category->delete($id);
    }
}
