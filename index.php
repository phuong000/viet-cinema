
<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/controllers/MovieController.php';
require_once __DIR__ . '/controllers/CategoryController.php';

$movieController = new MovieController();
$categoryController = new CategoryController();

function response($status, $message, $data = null)
{
    http_response_code($status);

    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

function getInput()
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (stripos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        return is_array($input) ? $input : [];
    }

    return $_POST;
}

function getId()
{
    $id = $_GET['id'] ?? null;

    if ($id === null || filter_var($id, FILTER_VALIDATE_INT) === false || (int)$id <= 0) {
        throw new InvalidArgumentException("ID không hợp lệ");
    }

    return (int)$id;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $resource = $_GET['resource'] ?? '';
    $input = getInput();

    // Hỗ trợ PUT và DELETE thông qua POST
    if ($method === 'POST' && isset($input['_method'])) {
        $method = strtoupper($input['_method']);
    }

    // ================= PHIM =================

    if ($resource === 'movies') {

        switch ($method) {

            case 'GET':
                if (isset($_GET['id'])) {
                    $data = $movieController->show(getId());

                    if (!$data) {
                        response(404, "Không tìm thấy phim");
                    }

                    response(200, "Lấy thông tin phim thành công", $data);
                }

                response(200, "Lấy danh sách phim thành công",
                    $movieController->index());

            case 'POST':
                $movieController->store($input);
                response(201, "Thêm phim thành công");

            case 'PUT':
                $movieController->update(getId(), $input);
                response(200, "Cập nhật phim thành công");

            case 'DELETE':
                $movieController->destroy(getId());
                response(200, "Xóa phim thành công");

            default:
                response(405, "Phương thức không được hỗ trợ");
        }
    }

    // ================= THỂ LOẠI =================

    if ($resource === 'categories') {

        switch ($method) {

            case 'GET':
                if (isset($_GET['id'])) {
                    $data = $categoryController->show(getId());

                    if (!$data) {
                        response(404, "Không tìm thấy thể loại");
                    }

                    response(200, "Lấy thông tin thể loại thành công", $data);
                }

                response(200, "Lấy danh sách thể loại thành công",
                    $categoryController->index());

            case 'POST':
                $categoryController->store($input);
                response(201, "Thêm thể loại thành công");

            case 'PUT':
                $categoryController->update(getId(), $input);
                response(200, "Cập nhật thể loại thành công");

            case 'DELETE':
                $categoryController->destroy(getId());
                response(200, "Xóa thể loại thành công");

            default:
                response(405, "Phương thức không được hỗ trợ");
        }
    }

    response(404, "API không tồn tại");

} catch (InvalidArgumentException $e) {
    response(422, $e->getMessage());

} catch (RuntimeException $e) {
    response(404, $e->getMessage());

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        response(409, "Dữ liệu bị trùng hoặc đang được dữ liệu khác sử dụng");
    }

    response(500, "Lỗi cơ sở dữ liệu");

} catch (Throwable $e) {
    response(500, "Lỗi hệ thống: " . $e->getMessage());
}
