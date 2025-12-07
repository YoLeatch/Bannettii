<?php

namespace App\Payment;

use Core\ConnectionFactory;
use PDO;

class CartaoCreditoModel
{
    private ?int $id;
    private ?string $nome;
    private ?string $validade;
    private ?int $usuarioId;
    private ?string $status;

    public function __construct(
        ?int $id = null,
        ?string $nome = null,
        ?string $validade = null,
        ?int $usuarioId = null,
        ?string $status = null
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->validade = $validade;
        $this->usuarioId = $usuarioId;
        $this->status = $status;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function getValidade(): ?string
    {
        return $this->validade;
    }

    public function getUsuarioId(): ?int
    {
        return $this->usuarioId;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public static function create(string $nome, string $validade, int $usuarioId): ?CartaoCreditoModel
    {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("INSERT INTO cartao_credito (nome, validade, usuario, status) VALUES (?, ?, ?, '1')");
        
        if ($stmt->execute([$nome, $validade, $usuarioId])) {
            $id = $pdo->lastInsertId();
            return new CartaoCreditoModel($id, $nome, $validade, $usuarioId, '1');
        }

        return null;
    }

    public static function findById(int $id): ?CartaoCreditoModel
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM cartao_credito WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new CartaoCreditoModel(
                $row['id'],
                $row['nome'],
                $row['validade'],
                $row['usuario'],
                $row['status']
            );
        }

        return null;
    }

    public static function getByUser(int $usuarioId): array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM cartao_credito WHERE usuario = ? AND status = '1'");
        $stmt->execute([$usuarioId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $cards = [];
        foreach ($rows as $row) {
            $cards[] = new CartaoCreditoModel(
                $row['id'],
                $row['nome'],
                $row['validade'],
                $row['usuario'],
                $row['status']
            );
        }

        return $cards;
    }

    public function deactivate(): bool
    {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE cartao_credito SET status = '0' WHERE id = ?");
        return $stmt->execute([$this->id]);
    }
}
