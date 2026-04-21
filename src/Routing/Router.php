<?php

namespace Lima\Routing;

use ReflectionClass;

class Router
{
    private static $instance = null;

    private string $currentController = '';
    private string $currentMethod = '';

    private function __construct()
    {
        $routesFiles = LIMA_ROOT . '/system/routes.php';

        if (file_exists($routesFiles)) {
            require_once($routesFiles);

            if (!empty($routes)) {
                $this->registerRoutes($routes);
            }
        }
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

    private function convertUrlCase($value): string
    {
        $stringData  = explode('-', $value);
        $stringCased = array_map('ucfirst', $stringData);

        return join('', $stringCased);
    }

    public function processRequest($url)
    {
        $urlData       = explode('/', filter_var(rtrim($url, '/'), FILTER_SANITIZE_URL));
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $namespace      = null;
        $controller     = null;
        $method         = null;
        $controllerFile = '';

        $controllerClass = null;

        $urlFirstPart = $urlData[0] ?? '';

        if (!empty($this->routes[$urlFirstPart])) {
            $routeData = $this->routes[$urlFirstPart];

            if (is_array($routeData)) {
                if (!empty($routeData['namespace'])) {
                    $namespace  = $this->convertUrlCase($urlData[0]);
                    $controller = $this->convertUrlCase($urlData[1]);
                    $method     = $urlData[2] ?? 'index';

                    $controllerFile = CONTROLLER_PATH . DIRECTORY_SEPARATOR . $namespace . DIRECTORY_SEPARATOR . $controller . '.php';
                }
            }
        } else if (empty($this->routes[$url])) {
            $controller     = $this->convertUrlCase($urlData[0]);
            $method         = $urlData[1] ?? 'index';
            $controllerFile = CONTROLLER_PATH . DIRECTORY_SEPARATOR . $controller . '.php';
        }

        if (file_exists($controllerFile)) {
            require_once($controllerFile);
        }

        if (!empty($this->routes[$urlFirstPart])) {
            $routeData = $this->routes[$urlFirstPart];

            if (is_array($routeData)) {
                if (!empty($routeData['namespace'])) {
                    if (!empty($this->routes['*']['controller'])) {
                        $controller = $this->routes['*']['controller'];
                    }

                    $fullClassName = $routeData['namespace'] . '\\' .$controller;

                    if (class_exists($fullClassName)) {
                        $controllerClass = new $fullClassName();
                    }
                }
            }
        } else if (empty($this->routes[$url])) {
            if (!empty($this->routes['*']) && !empty($this->routes['*']['namespace'])) {
                if (!empty($this->routes['*']['controller'])) {
                    $controller = $this->routes['*']['controller'];
                }

                $fullClassName = $this->routes['*']['namespace'] . '\\' .$controller;

                if (class_exists($fullClassName)) {
                    $controllerClass = new $fullClassName();
                }
            }
        }

        if (!$controllerClass) {
            die("Controller class not found: {$controller}");
        }

        $method = str_replace('-', '_', $method);

        if (!method_exists($controllerClass, $method)) {
            die('Method: ' . $method . ' does not exist in controller ' . $controller);
        }

        unset($urlData[0]);
        unset($urlData[1]);

        if ($namespace) {
            unset($urlData[2]);
        }

        $params = array_values($urlData);

        $reflection = new ReflectionClass($controllerClass);

        $this->currentController = $reflection->getShortName();
        $this->currentMethod     = $method;

        call_user_func_array([$controllerClass, $method], $params);
    }

    public function getCurrentController(): string
    {
        return $this->currentController;
    }

    public function getCurrentMethod(): string
    {
        return $this->currentMethod;
    }
}