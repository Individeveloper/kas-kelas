<?php
/**
 * Router
 * Handle routing aplikasi
 */

namespace App;

class Router {
    private static $routes = [];

    public static function get($path, $callback) {
        self::$routes[$path] = ['method' => 'GET', 'callback' => $callback];
    }

    public static function post($path, $callback) {
        self::$routes[$path] = ['method' => 'POST', 'callback' => $callback];
    }

    public static function dispatch($path) {
        $path = parse_url($path, PHP_URL_PATH);
        
        // Remove base path
        $basePath = '/cashflowKas';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        
        if (empty($path)) {
            $path = '/';
        }

        // Check if route exists
        if (isset(self::$routes[$path])) {
            $route = self::$routes[$path];
            
            // Check method
            if ($route['method'] !== $_SERVER['REQUEST_METHOD']) {
                http_response_code(405);
                die('Method Not Allowed');
            }

            // Call callback
            $callback = $route['callback'];
            if (is_callable($callback)) {
                call_user_func($callback);
            } elseif (is_string($callback)) {
                list($controller, $method) = explode('@', $callback);
                $controllerClass = "App\\Controllers\\" . $controller;
                if (method_exists($controllerClass, $method)) {
                    call_user_func([$controllerClass, $method]);
                } else {
                    http_response_code(404);
                    die('Controller method not found');
                }
            }
        } else {
            http_response_code(404);
            die('404 - Page not found');
        }
    }
}
