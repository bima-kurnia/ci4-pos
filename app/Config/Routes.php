<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// -------------------------------------------------------
// Public routes
// -------------------------------------------------------
$routes->get('/',      'AuthController::login');
$routes->get('/login', 'AuthController::login');
$routes->post('/login', 'AuthController::loginPost');
$routes->get('/logout', 'AuthController::logout');

// -------------------------------------------------------
// Protected routes (require login)
// -------------------------------------------------------
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');

    // POS
    $routes->get('pos', 'PosController::index');
    $routes->post('pos/checkout', 'PosController::checkout');
    $routes->post('pos/calculate', 'PosController::calculate');

    // Products
    $routes->get('products',               'ProductController::index');
    $routes->get('products/search',        'ProductController::search');
    $routes->get('products/(:num)',        'ProductController::getProduct/$1');
    $routes->get('products/stock/(:num)',  'ProductController::stockLog/$1');

    // Customers
    $routes->get('customers',        'CustomerController::index');
    $routes->get('customers/search', 'CustomerController::search');

    // Transactions
    $routes->get('transactions',        'TransactionController::index');
    $routes->get('transactions/(:num)', 'TransactionController::show/$1');

    // -------------------------------------------------------
    // Admin-only routes
    // -------------------------------------------------------
    $routes->group('', ['filter' => 'admin'], function ($routes) {

        // Products CRUD
        $routes->get('products/create',        'ProductController::create');
        $routes->post('products/store',        'ProductController::store');
        $routes->get('products/edit/(:num)',   'ProductController::edit/$1');
        $routes->post('products/update/(:num)','ProductController::update/$1');
        $routes->post('products/delete/(:num)','ProductController::delete/$1');

        // Categories
        $routes->get('categories',                'CategoryController::index');
        $routes->post('categories/store',         'CategoryController::store');
        $routes->post('categories/update/(:num)', 'CategoryController::update/$1');
        $routes->post('categories/delete/(:num)', 'CategoryController::delete/$1');

        // Customers CRUD
        $routes->post('customers/store',         'CustomerController::store');
        $routes->post('customers/update/(:num)', 'CustomerController::update/$1');
        $routes->post('customers/delete/(:num)', 'CustomerController::delete/$1');

        // Users
        $routes->get('users',                'UserController::index');
        $routes->post('users/store',         'UserController::store');
        $routes->post('users/update/(:num)', 'UserController::update/$1');
        $routes->post('users/delete/(:num)', 'UserController::delete/$1');

        // Transactions
        $routes->post('transactions/cancel/(:num)', 'TransactionController::cancel/$1');

        // ── Analytics (Admin only) ──────────────────────────────────────
        $routes->get('analytics',                    'AnalyticsController::index');
        $routes->get('analytics/sales-forecast',     'AnalyticsController::salesForecast');
        $routes->get('analytics/product-forecast',   'AnalyticsController::productForecast');
        $routes->get('analytics/customer-insights',  'AnalyticsController::customerInsights');
        $routes->get('analytics/clear-cache',        'AnalyticsController::clearCache');
    });
});