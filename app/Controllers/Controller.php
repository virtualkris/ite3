<?php
namespace App\Controllers;

abstract class Controller {
    protected function render($viewName, $data = []) {
        // Extract the data array into variables for use in the view
        extract($data);
        // Start output buffering to capture the view's output
        ob_start();
        // Include the view file (e.g., app/Views/home.php)
        include __DIR__ . "/../Views/{$viewName}.php";
        // Get the content of the view and clean the buffer
        $content = ob_get_clean();
        // Include the main layout and pass the content to it
        include __DIR__ . "/../Views/layouts/main.php";
    }
}