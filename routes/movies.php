<?php
declare(strict_types=1);

use App\Controllers\Movies\MovieController;

// HUY
$movies = new MovieController();
$router->add('GET', '/movies', [$movies, 'index']);
$router->add('GET', '/movies/{id}', [$movies, 'show']);
$router->add('GET', '/showtimes', [$movies, 'showtimes']);
