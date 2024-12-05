<?php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $config = include(__DIR__ . '/../config/config.php');
        $db_config = $config['db'];

        $this->connection = new mysqli(
            $db_config['host'],
            $db_config['username'],
            $db_config['password'],
            $db_config['database']
        );

        if ($this->connection->connect_error) {
            throw new Exception("Database connection failed: " . $this->connection->connect_error);
        }

        $this->connection->set_charset('utf8mb4');
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }

    public function __clone() {}
    public function __wakeup() {}
}