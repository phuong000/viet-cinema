<?php
declare(strict_types=1);

namespace App\Controllers\Movies;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

final class MovieController extends Controller
{
    public function index(): Response
    {
        return $this->view('movies/index', ['title' => 'Khám phá phim']);
    }

    public function show(Request $request): Response
    {
        if ($request->parameter('id') !== 'sample') {
            return Response::error(404);
        }
        return $this->view('movies/show', ['title' => 'Hành trình ánh sáng']);
    }

    public function showtimes(): Response
    {
        return $this->view('movies/showtimes', ['title' => 'Lịch chiếu']);
    }
}
