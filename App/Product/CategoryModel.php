<?php

namespace App\Product;

use Core\ConnectionFactory;
use PDO;

class CategoryModel
{
    private ?int $id;
    private ?string $categoria;

    public function __construct(?int $id = null, ?string $categoria = null)
    {
        $this->id = $id;
        $this->categoria = $categoria;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoria(): ?string
    {
        return $this->categoria;
    }

    public static function findById(int $id): ?CategoryModel
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM categoria WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new CategoryModel($row['id'], $row['categoria']);
        }

        return null;
    }

    public static function fetchAll(): array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM categoria");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $categorias = [];
        foreach ($rows as $row) {
            $categorias[] = new CategoryModel($row['id'], $row['categoria']);
        }

        return $categorias;
    }
}
