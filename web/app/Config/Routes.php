<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Halaman detail tempat nongkrong
$routes->get('/tempat/(:num)', 'Detail::index/$1');

// Simpan ulasan
$routes->post('/ulasan/simpan', 'Ulasan::simpan');