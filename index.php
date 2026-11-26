<?php
require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/ProductsModel.php';
$productModel = new ProductsModel();
$products = $productModel->getAllProducts();
print_r($products);
?>