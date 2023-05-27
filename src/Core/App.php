<?php

namespace Lima\Core;

require(__DIR__ . '/../../vendor/autoload.php');

class App {
    private $rootPath;

    public function __construct($rootPath) {
        $this->rootPath = $rootPath;
    }

    public function init() {
        $dotenv = $dotenv = \Dotenv\Dotenv::createImmutable($this->rootPath);
        $dotenv->load();

        $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);
    }
}