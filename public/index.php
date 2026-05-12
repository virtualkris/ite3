<?php
// Handout 1: The Front Controller & Autoloader
// This file is the single entry point for the entire application.
$sessionPath = __DIR__ . '/../storage/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);
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
if ($uri === $basePath) {
    $uri = '';
} elseif (strpos($uri, $basePath . '/') === 0) {
    $uri = substr($uri, strlen($basePath) + 1);
}
$uri = preg_replace('#^public/?#', '', $uri); // Support direct /ite3/public URLs

if ($uri === '' || $uri === 'index.php') {
    $uri = 'home';
}

$method = $_SERVER['REQUEST_METHOD']; // GET, POST, etc.

// 4. Resolve the route to the appropriate controller action
$router->resolve($uri, $method);
