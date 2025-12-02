<?php

namespace App\Product;

use Core\ConnectionFactory;
use PDO;

class SubCategoryModel
{
    private ?int $id;
    private ?int $categoriaId;
    private ?string $subCategoria;

    public function __construct(?int $id = null, ?int $categoriaId = null, ?string $subCategoria = null)
    {
        $this->id = $id;
        $this->categoriaId = $categoriaId;
        $this->subCategoria = $subCategoria;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoriaId(): ?int
    {
        return $this->categoriaId;
    }

    public function getSubCategoria(): ?string
    {
        return $this->subCategoria;
    }

    public static function findById(int $id): ?SubCategoryModel
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM sub_categoria WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new SubCategoryModel($row['id'], $row['categoria'], $row['sub_categoria']);
        }

        return null;
    }

    public static function fetchAll(): array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM sub_categoria");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subCategorias = [];
        foreach ($rows as $row) {
            $subCategorias[] = new SubCategoryModel($row['id'], $row['categoria'], $row['sub_categoria']);
        }

        return $subCategorias;
    }

    public static function getByCategory(int $categoriaId): array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM sub_categoria WHERE categoria = ?");
        $stmt->execute([$categoriaId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $subCategorias = [];
        foreach ($rows as $row) {
            $subCategorias[] = new SubCategoryModel($row['id'], $row['categoria'], $row['sub_categoria']);
        }

        return $subCategorias;
    }
}
