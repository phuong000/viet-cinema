<?php
declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;

final class AuthController extends Controller
{
    public function login(Request $request): Response
    {
        return $this->form($request, 'login');
    }

    public function register(Request $request): Response
    {
        return $this->form($request, 'register');
    }

    private function form(Request $request, string $mode): Response
    {
        $errors = [];
        $message = null;
        if ($request->method === 'POST') {
            $errors = Validator::errors($request->all(), [
                'email' => ['required', 'email', 'max:254'],
                'password' => ['required', 'min:8', 'max:128'],
            ]);
            if ($errors === []) {
                // Foundation never creates an identity or grants permissions.
                $message = 'Biểu mẫu mẫu hợp lệ. Chức năng tài khoản chưa được kích hoạt; không có dữ liệu được lưu.';
            }
        }
        return $this->view('auth/form', [
            'title' => $mode === 'login' ? 'Đăng nhập' : 'Đăng ký',
            'mode' => $mode, 'errors' => $errors, 'message' => $message,
        ], $errors === [] ? 200 : 422);
    }
}
