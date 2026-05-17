<?php
namespace App\Core;

class Router {
    // This class will handle routing logic in the future.
    // For now, it's just a placeholder to demonstrate the autoloader.

    protected $routes = [];
    // 1. Register a GET route
    public function get($uri, $controller) {
        $this->routes['GET'][$uri] = $controller;
    }

    // 2. Register a POST route
    public function post($uri, $controller) {
        $this->routes['POST'][$uri] = $controller;
    }

    // 3. The Dispatcher: Resolve the incoming request to the appropriate controller action
    public function resolve($uri, $method) {
        foreach ($this->routes[$method] ?? [] as $route => $controller) {
            // Handle dynamic parameters in the route (e.g., {id})
            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_-]+)', $route);
            if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
                array_shift($matches); // Remove the full match
                list($controllerClass, $action) = explode('@', $controller);
                $controllerClass = 'App\\Controllers\\' . $controllerClass; // Add namespace

                if (class_exists($controllerClass)) {
                    $controllerInstance = new $controllerClass();
                    if (method_exists($controllerInstance, $action)) {
                        return call_user_func_array([$controllerInstance, $action], $matches);
                    } else {
                        echo "Error: Method $action not found in controller $controllerClass.";
                        return;
                    }
                } else {
                    echo "Error: Controller class $controllerClass not found.";
                    return;
                }
            }
        }

        http_response_code(404);
        echo "404 - Page Not Found";
    }
}
