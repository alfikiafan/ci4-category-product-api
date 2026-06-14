<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->group('api', ['namespace' => 'App\Controllers\Api'], static function (RouteCollection $routes) {
    $routes->resource('categories', [
        'controller' => 'CategoryController',
        'except'     => 'new,edit',
    ]);

    $routes->resource('products', [
        'controller' => 'ProductController',
        'except'     => 'new,edit',
    ]);
});

$routes->set404Override(static function () {
    $response = service('response');
    $response->setStatusCode(404)
        ->setJSON([
            'status'  => 'error',
            'message' => 'Endpoint not found',
        ])
        ->send();
});
