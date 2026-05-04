<?php

class Database
{
    private $host     = 'dpg-d7sejev7f7vs7385jqig-a.singapore-postgres.render.com';
    private $port     = 5432;
    private $db_name  = 'book_db_of2k';
    private $username = 'book_db_of2k_user';
    private $password = 'X41hqwzEXohzTcBhbdnqVyMBhj6zNjVh';

    public $conn;

    public function getConnection()
    {
        $this->conn = null;

        $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}";

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die("Ошибка подключения к PostgreSQL: " . $e->getMessage());
        }

        return $this->conn;
    }
}

$database = new Database();
$pdo = $database->getConnection();
