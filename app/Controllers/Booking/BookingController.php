<?php
declare(strict_types=1);

namespace App\Controllers\Booking;

use App\Core\Controller;
use App\Core\Response;

final class BookingController extends Controller
{
    public function index(): Response
    {
        return $this->view('booking/index', ['title' => 'Đặt vé']);
    }
}
