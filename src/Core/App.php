<?php

namespace Lima\Core;

require(__DIR__ . '/../../vendor/autoload.php');

class App {
    private $rootPath;
    private $appPath = 'app';
    private $controllerPath = 'Controllers';
    private $modelPath = 'Models';

    public function __construct($rootPath) {
        $this->rootPath = $rootPath;
    }

    public function init() {
        $this->loadEnv();
        $this->loadDefines();
        $this->loadClasses();
    }

    public function loadEnv()
    {
        $dotenv = $dotenv = \Dotenv\Dotenv::createImmutable($this->rootPath);
        $dotenv->load();

        $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);

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
    }

    public function loadClasses()
    {
        spl_autoload_register(function ($class) {
            $controllerPath = $this->rootPath . DIRECTORY_SEPARATOR . $this->appPath . DIRECTORY_SEPARATOR . $this->controllerPath;
            $modelPath = $this->rootPath . DIRECTORY_SEPARATOR . $this->appPath . DIRECTORY_SEPARATOR . $this->modelPath;

            if (file_exists($controllerPath . DIRECTORY_SEPARATOR . $class . '.php')) {
                require_once($controllerPath . DIRECTORY_SEPARATOR . $class . '.php');
            }

            if (file_exists($modelPath . DIRECTORY_SEPARATOR . $class . '.php')) {
                require_once($modelPath . DIRECTORY_SEPARATOR . $class . '.php');
            }
        });
    }
}