<?php

class Database
{
    private $host     = 'MySQL-8.0';
    private $port     = 3306;
    private $db_name  = 'book_store';
    private $username = 'root';
    private $password = '';
    private $charset   = 'utf8mb4';
    public  $conn;

    public function getConnection()
    {
        $this->conn = null;
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset={$this->charset}";
        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die(
                "Ошибка подключения к MySQL: " . $e->getMessage() .
                "<br><br>Код ошибки: " . $e->getCode() .
                "<br>Проверьте: запущен ли MySQL-8.0?"
            );
        }
        return $this->conn;
    }
}

$database = new Database();
$pdo = $database->getConnection();