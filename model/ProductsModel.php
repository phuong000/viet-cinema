<?php
require_once __DIR__ . '/BaseModel.php';

class ProductsModel extends BaseModel {
    public function __construct() {
        parent::__construct();
    }

    public function getAllProducts() {
        $sql = "SELECT * FROM products";
        return $this->getAll($sql);
    }

    public function getProductById($id) {
        $sql = "SELECT * FROM products WHERE id = :id";
        return $this->getOne($sql, [':id' => $id]);
    }
}