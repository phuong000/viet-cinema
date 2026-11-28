<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Controller;

final class HomeController extends Controller
{
    public function index(): Response
    {
        return $this->view('home', ['title' => getenv('APP_NAME') ?: 'Viet Cinema']);
    }
}
