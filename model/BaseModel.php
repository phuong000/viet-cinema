<?php
require_once __DIR__ . '/../config/database.php';
class BaseModel {
    //private
    //protected
    //public
    protected static $conn;
    public function __construct() {
        try{
            self::$conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
            // set the PDO error mode to exception
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Connected successfully";
        } catch(PDOException $e) {
            echo ("Connection failed: " . $e->getMessage());
        }

    }
    protected function getAll($sql, $params = []) {
        $stmt = self::$conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    protected function getOne($sql, $params = []) {
        $stmt = self::$conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
}