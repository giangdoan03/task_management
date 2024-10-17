<?php

namespace App;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        // Lấy cấu hình từ file config.php
        $config = require __DIR__ . '/../config/config.php';

        try {
            // Kết nối với database
            $this->connection = new PDO(
                'mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['dbname'],
                $config['db']['username'],
                $config['db']['password']
            );
            // Cấu hình PDO
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    // Singleton method để lấy kết nối
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance->connection;
    }
}
