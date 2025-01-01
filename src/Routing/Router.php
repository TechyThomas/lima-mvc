<?php

namespace Lima\Routing;

class Router
{
    private static $instance = null;

    private function __construct()
    {

    }

    // Prevent our singleton from being cloned or restorable from strings
    protected function __clone(): void
    {
    }
    public function __wakeup(): never
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function Instance()
    {
        if (self::$instance === null) {
            self::$instance = new Router();
        }

        return self::$instance;
    }

    private $routes = [];

    public function registerRoutes($routes)
    {
        $this->routes = $routes;
    }

    public static function registerRoute($request, $url, $controller, $method)
    {
        self::$instance->routes[$request][$url] = ['controller' => $controller, 'method' => $method];
    }

    public function processRequest($url)
    {
        $urlData = explode('/', filter_var(rtrim($url, '/'), FILTER_SANITIZE_URL));
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (empty($this->routes[$requestMethod][$url])) {
            $controller = ucfirst($urlData[0]);
            $method = $urlData[1] ?? 'index';

            $controllerFile = CONTROLLER_PATH . DIRECTORY_SEPARATOR . $controller . '.php';

            $contents = file_get_contents($controllerFile);
            preg_match('/[\r\n]namespace\W(.+);[\r\n]/', $contents, $matches);
            $namespace = $matches[1] ?? null;

            if (!empty($namespace)) {
                $controllerClassFull = $namespace . '\\' . $controller;
                $controllerClass = new $controllerClassFull();
            } else {
                $controllerClass = new $controller();
            }

            $method = str_replace('-', '_', $method);

            if (!method_exists($controllerClass, $method)) {
                die('Method: ' . $method . ' does not exist in controller ' . $controller);
            }

            unset($urlData[0]);
            unset($urlData[1]);

            $params = array_values($urlData);

            call_user_func_array([$controllerClass, $method], $params);
        }
    }
}