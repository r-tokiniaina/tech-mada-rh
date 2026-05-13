<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::postLogin');

$routes->group('employe', ['filter' => 'auth:employe'], function($routes) {
});

$routes->group('rh', ['filter' => 'auth:rh'], function($routes) {
});

$routes->group('admin', ['filter' => 'auth:admin'], function($routes) {
});
