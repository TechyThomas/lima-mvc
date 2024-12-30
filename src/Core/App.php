<?php

namespace Lima\Core;

class App
{
    private static $instance = null;

    private $rootPath;
    private $appPath = 'app';
    private $controllerPath = 'Controllers';
    private $modelPath = 'Models';

    public function __construct($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    // Prevent our singleton from being cloned or restorable from strings
    protected function __clone(): void
    {
    }
    public function __wakeup(): never
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function Instance($rootPath)
    {
        if (self::$instance === null) {
            self::$instance = new App($rootPath);
        }

        return self::$instance;
    }

    public function init()
    {
        $this->loadEnvironment();
        $this->loadDefines();
        $this->loadClasses();
        $this->loadRoutes();
    }

    public function loadEnvironment()
    {
        $dotenv = $dotenv = \Dotenv\Dotenv::createImmutable($this->rootPath);
        $dotenv->load();

        $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);

        $dotenv->ifPresent('DB_DEFAULT_LIMIT')->isInteger();

        foreach ($_ENV as $name => $value) {
            define($name, $value);
        }

        if (!empty($_ENV['LIMA_CONTROLLER_PATH'])) {
            $this->controllerPath = $_ENV['LIMA_CONTROLLER_PATH'];
        }

        if (!empty($_ENV['LIMA_MODEL_PATH'])) {
            $this->modelPath = $_ENV['LIMA_MODEL_PATH'];
        }
    }

    public function loadDefines()
    {
        define('LIMA_ROOT', $this->rootPath);

        $controllerPath = $this->rootPath . DIRECTORY_SEPARATOR . $this->appPath . DIRECTORY_SEPARATOR . $this->controllerPath;
        $modelPath = $this->rootPath . DIRECTORY_SEPARATOR . $this->appPath . DIRECTORY_SEPARATOR . $this->modelPath;

        define('CONTROLLER_PATH', $controllerPath);
        define('MODEL_PATH', $modelPath);
    }

    public function loadClasses()
    {
        spl_autoload_register(function ($class) {
            $controllerPath = CONTROLLER_PATH;
            $modelPath = MODEL_PATH;

            if (file_exists($controllerPath . DIRECTORY_SEPARATOR . $class . '.php')) {
                require_once($controllerPath . DIRECTORY_SEPARATOR . $class . '.php');
            }

            if (file_exists($modelPath . DIRECTORY_SEPARATOR . $class . '.php')) {
                require_once($modelPath . DIRECTORY_SEPARATOR . $class . '.php');
            }
        });
    }

    public function loadRoutes()
    {
        $url = $_GET['url'] ?? 'home';

        $router = \Lima\Routing\Router::Instance();
        $router->processRequest($url);
    }

    public static function Run($rootPath): void
    {
        $app = self::Instance($rootPath);
        $app->init();
    }
}