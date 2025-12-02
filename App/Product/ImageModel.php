<?php

namespace App\Product;

use Core\ConnectionFactory;
use PDO;

class ImageModel
{
    private ?int $id;
    private ?string $imagem;
    private ?int $produtoId;

    public function __construct(?int $id = null, ?string $imagem = null, ?int $produtoId = null)
    {
        $this->id = $id;
        $this->imagem = $imagem;
        $this->produtoId = $produtoId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImagem(): ?string
    {
        return $this->imagem;
    }

    public function getProdutoId(): ?int
    {
        return $this->produtoId;
    }

    public static function findById(int $id): ?ImageModel
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM imagem WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new ImageModel($row['id'], $row['imagem'], $row['produto']);
        }

        return null;
    }

    public static function getByProduct(int $produtoId): array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM imagem WHERE produto = ?");
        $stmt->execute([$produtoId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $imagens = [];
        foreach ($rows as $row) {
            $imagens[] = new ImageModel($row['id'], $row['imagem'], $row['produto']);
        }

        return $imagens;
    }
}
