<?php

namespace App\Endereco;

use Core\ConnectionFactory;
use PDO;

class CidadeModel
{
    private ?int $id;
    private ?string $cidade;
    private ?int $estadoId;

    public function __construct(?int $id = null, ?string $cidade = null, ?int $estadoId = null)
    {
        $this->id = $id;
        $this->cidade = $cidade;
        $this->estadoId = $estadoId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCidade(): ?string
    {
        return $this->cidade;
    }

    public function getEstadoId(): ?int
    {
        return $this->estadoId;
    }

    public static function findById(int $id): ?CidadeModel
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM cidade WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new CidadeModel($row['id'], $row['cidade'], $row['estado']);
        }

        return null;
    }

    public static function fetchAll(): array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM cidade");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cidades = [];
        foreach ($rows as $row) {
            $cidades[] = new CidadeModel($row['id'], $row['cidade'], $row['estado']);
        }

        return $cidades;
    }

    public static function getByEstado(int $estadoId): array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM cidade WHERE estado = ?");
        $stmt->execute([$estadoId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cidades = [];
        foreach ($rows as $row) {
            $cidades[] = new CidadeModel($row['id'], $row['cidade'], $row['estado']);
        }

        return $cidades;
    }
}
