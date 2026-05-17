<?php

session_start(); // Start the session to manage user authentication and flash messages

// Composer Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Environment Variable
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

error_reporting(E_ALL);
$debug = $_ENV['APP_DEBUG'] ?? 'true';
if ($debug === 'true') {
    ini_set('display_errors', 1);
}

use App\Core\Router;

$router = new Router(); // 1. Initialize the Router

// 2. Define our Routes
require __DIR__ . '/../app/routes.php'; // This file will define the routes using $router->get() and $router->post()
// 3. Capture the current request
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$basePath = trim($_ENV['APP_BASE_PATH'] ?? 'ite3', '/');
if (!empty($basePath)) {
    $uri = preg_replace("#^" . preg_quote($basePath) . "/?#", '', $uri);
}

if ($uri === '' || $uri === 'index.php') {
    $uri = 'home';
}

$method = $_SERVER['REQUEST_METHOD']; // GET, POST, etc.

// 4. Resolve the route to the appropriate controller action
$router->resolve($uri, $method);
