<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', function() {
    return redirect()->to('login');
});

$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::postLogin');
$routes->get('logout', 'AuthController::logout');

$routes->group('employe', ['filter' => 'auth:employe'], function($routes) {
    $routes->get('/', 'EmployeController::index');
    $routes->get('dashboard', 'EmployeController::dashboard');
    $routes->get('conges', 'EmployeController::mesConges');
    $routes->get('conges/nouveau', 'EmployeController::create');
    $routes->post('conges', 'EmployeController::store');
    $routes->post('conges/(:num)/annuler', 'EmployeController::cancel/$1');
    $routes->get('profil', 'EmployeController::profile');
    $routes->post('profil', 'EmployeController::updateProfile');
});

$routes->group('rh', ['filter' => 'auth:rh'], function($routes) {
    $routes->get('/', 'RhController::index');
    $routes->get('demandes', 'RhController::demandes');
    $routes->post('conges/(:num)/decision', 'RhController::decision/$1');
    $routes->get('soldes', 'RhController::soldes');
});

$routes->group('admin', ['filter' => 'auth:admin'], function($routes) {
    $routes->get('/', 'AdminController::index');
    $routes->get('dashboard', 'AdminController::dashboard');
    $routes->get('demandes', 'AdminController::demandes');

    $routes->get('employes', 'AdminController::employes');
    $routes->post('employes', 'AdminController::storeEmploye');
    $routes->get('employes/(:num)/edit', 'AdminController::editEmploye/$1');
    $routes->post('employes/(:num)', 'AdminController::updateEmploye/$1');
    $routes->post('employes/(:num)/toggle', 'AdminController::toggleEmploye/$1');

    $routes->get('departements', 'AdminController::departements');
    $routes->post('departements', 'AdminController::storeDepartement');
    $routes->post('departements/(:num)', 'AdminController::updateDepartement/$1');
    $routes->post('departements/(:num)/delete', 'AdminController::deleteDepartement/$1');

    $routes->get('types', 'AdminController::types');
    $routes->post('types', 'AdminController::storeType');
    $routes->post('types/(:num)', 'AdminController::updateType/$1');
    $routes->post('types/(:num)/delete', 'AdminController::deleteType/$1');

    $routes->get('soldes', 'AdminController::soldes');
    $routes->post('soldes', 'AdminController::saveSolde');
});
