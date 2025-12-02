<?php

namespace App\User;

use Core\ConnectionFactory;
use PDO;

class CargoModel
{
    private ?int $id;
    private ?string $cargo;
    private ?int $poder;

    public function __construct(?int $id = null, ?string $cargo = null, ?int $poder = null)
    {
        $this->id = $id;
        $this->cargo = $cargo;
        $this->poder = $poder;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCargo(): ?string
    {
        return $this->cargo;
    }

    public function getPoder(): ?int
    {
        return $this->poder;
    }

    public static function findById(int $id): ?CargoModel
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM cargo WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new CargoModel($row['id'], $row['cargo'], $row['poder']);
        }

        return null;
    }

    public static function fetchAll(): array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM cargo");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cargos = [];
        foreach ($rows as $row) {
            $cargos[] = new CargoModel($row['id'], $row['cargo'], $row['poder']);
        }

        return $cargos;
    }
}
