<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Carga Composer

use Dotenv\Dotenv;

class Database {
    private $conn;

    public function __construct() {
        // Cargar variables de entorno desde config/.env
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $host = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_NAME'];
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASS'];
        $port = $_ENV['DB_PORT'];

        try {
            $this->conn = new PDO(
                "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8",
                $username,
                $password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die(json_encode(["error" => "Error de conexión: " . $e->getMessage()]));
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}