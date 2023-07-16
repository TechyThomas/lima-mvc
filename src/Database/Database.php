<?php

namespace Lima\Database;

class Database
{
    private static $instance = null;
    private $database;

    private function __construct($host, $name, $user, $pass)
    {
        $this->database = new \PDO("mysql:host=" . $host . ";dbname=" . $name, $user, $pass);
        $this->database->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
    }

    // Prevent our singleton from being cloned or restorable from strings
    protected function __clone()
    {
    }
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function getInstance($host, $name, $user, $pass)
    {
        if (empty(self::$instance)) {
            self::$instance = new Database($host, $name, $user, $pass);
        }

        return self::$instance;
    }

    public function getPDO(): \PDO
    {
        return $this->database;
    }
}