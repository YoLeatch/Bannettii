<?php

namespace App\Product;

use Core\ConnectionFactory;

class ProductModel {
    private $id;
    private $name;
    private $image;
    private $price;

    public function __construct($id, $name, $image, $price) {
        $this->id = $id;
        $this->name = $name;
        $this->image = $image;
        $this->price = $price;
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getPrice() {
        return $this->price;
    }

    public static function findById($id) {
        $conn = ConnectionFactory::getConnection();
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            return new ProductModel($result['id'], $result['name'], $result['image'], $result['price']);
        }
        return null;
    }
}