<?php
declare(strict_types=1);

use App\Controllers\Booking\BookingController;

// QUY: read-only placeholder; no seat hold, booking or payment.
$router->add('GET', '/booking', [new BookingController(), 'index']);
