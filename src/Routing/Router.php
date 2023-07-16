<?php

namespace Lima\Routing;

use Lima\Core\Config;

class Router
{
    private $routes = [];

    public function registerRoutes($routes)
    {
        $this->routes = $routes;
    }

    public function processRequest($url)
    {
        $urlData = explode('/', filter_var(rtrim($url, '/'), FILTER_SANITIZE_URL));
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        if (empty($this->routes[$requestMethod][$url])) {
            $controller = ucfirst($urlData[0]);
            $method = $urlData[1] ?? 'index';

            $controllerClass = new $controller();

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