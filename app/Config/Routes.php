<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('health', 'Health::index');

// API group
$routes->group('api', function ($routes) {
    // products
    $routes->get('products', 'ProductController::index');
    $routes->get('products/(:num)', 'ProductController::show/$1');
    $routes->post('products', 'ProductController::create');
    $routes->patch('products/(:num)', 'ProductController::update/$1');
    $routes->delete('products/(:num)', 'ProductController::delete/$1');

    // transactions
    $routes->get('transactions', 'TransactionController::index');
    $routes->post('transactions', 'TransactionController::create');

    // auth (optional)
    $routes->post('auth/login', 'AuthController::login');
});

