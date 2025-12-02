<?php

namespace App\Endereco;

use Core\ConnectionFactory;
use PDO;

class EstadoModel
{
    private ?int $id;
    private ?string $estado;

    public function __construct(?int $id = null, ?string $estado = null)
    {
        $this->id = $id;
        $this->estado = $estado;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public static function findById(int $id): ?EstadoModel
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM estado WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new EstadoModel($row['id'], $row['estado']);
        }

        return null;
    }

    public static function fetchAll(): array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM estado");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $estados = [];
        foreach ($rows as $row) {
            $estados[] = new EstadoModel($row['id'], $row['estado']);
        }

        return $estados;
    }
}
