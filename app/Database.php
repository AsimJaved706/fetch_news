<?php
class Database 
{
    private static $instance = null;
    private $pdo;
    
    private function __construct() 
    {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', 
                      Config::DB_HOST, Config::DB_NAME);
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];
        
        $this->pdo = new PDO($dsn, Config::DB_USER, Config::DB_PASS, $options);
    }
    
    public static function getInstance() 
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() 
    {
        return $this->pdo;
    }
    
    public function prepare($sql) 
    {
        return $this->pdo->prepare($sql);
    }
    
    public function lastInsertId() 
    {
        return $this->pdo->lastInsertId();
    }
}