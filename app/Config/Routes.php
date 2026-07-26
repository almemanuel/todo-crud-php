<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'TaskController::index');

$routes->resource('tasks', [
    'controller' => 'TaskController',
    'only' => ['index', 'new', 'create', 'edit', 'update', 'delete']
]);
