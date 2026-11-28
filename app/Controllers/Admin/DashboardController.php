<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;

final class DashboardController extends Controller
{
    public function demo(): Response
    {
        return $this->view('admin/dashboard', ['title' => 'Dashboard mẫu'], 200, 'admin');
    }
}
