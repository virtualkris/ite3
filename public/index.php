<?php
// Handout 1: The Front Controller & Autoloader
// This file is the single entry point for the entire application.
session_start(); // Start the session to manage user authentication and flash messages
// 1. Setup Error Reporting (Crucial for beginners to see what's wrong)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Define the Manual Autoloader
// This function runs automatically whenever you try to use a class that isn't loaded yet.
spl_autoload_register(function ($class) {

    $prefix = 'App\\'; // Namespace prefix
    $base_dir = __DIR__ . '/../app/'; // The physical directory where your classes are stored
    $len = strlen($prefix); // Check if the class being called starts with the namespace prefix
    
    if (strncmp($prefix, $class, $len) !== 0) {
        return; // It's not our class, ignore it.
    }

    // Get the relative class name
    $relative_class = substr($class, $len);
    // Replace the namespace backslash (\) with a directory separator (/) and append .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php'; // Convert namespace to file path
    
    // If the file exists, require it. Otherwise, show an error message.
    if (file_exists($file)) {
        require $file;
    } else {
        echo "Autoloader Error: Could not find file at $file for class $class." . "<br><br>";
    }
});

// 3. Determin the Request Path (The Router Warmer)
// We clean the URL to see where the user is trying to go.
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
// Since we are in a subfolder (/ite3/), we remove it from the path
$uri = str_replace('ite3/', '', $uri);

// Default to 'home' if no path is provided
if ($uri === '' || $uri === 'index.php') {
    $uri = 'home';
}

//echo "<h1>ite3 CMS Engine</h1>";
//echo "<strong>Requested Route:</strong> " . $uri . "<br><br>";

// 4. TEST: Trying to use a class that doesn't exist to see the autoloader in action
// This will trigger the spl_autoload_register function and show us if it's working correctly.

use App\Controllers\PostController;

if (class_exists('App\Controllers\PostController')) {
        $test = new PostController();
} else {
    echo "Please create app/Controllers/PostController.php to test the autoloader.";
}

use App\Core\Router;

$router = new Router(); // 1. Initialize the Router

// 2. Define our Routes
require __DIR__ . '/../app/routes.php'; // This file will define the routes using $router->get() and $router->post()
// 3. Capture the current request
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uri = str_replace('ite3/', '', $uri); // Remove subfolder from URI

if ($uri === '' || $uri === 'index.php') {
    $uri = 'home';
}

$method = $_SERVER['REQUEST_METHOD']; // GET, POST, etc.

// 4. Resolve the route to the appropriate controller action
$router->resolve($uri, $method);