<?php

namespace App\User;

use Core\ConnectionFactory;
use PDO;

class TelefoneModel
{
    private ?int $id;
    private ?int $telefone;
    private ?int $pessoaId;
    private ?string $status;

    public function __construct(
        ?int $id = null,
        ?int $telefone = null,
        ?int $pessoaId = null,
        ?string $status = null
    ) {
        $this->id = $id;
        $this->telefone = $telefone;
        $this->pessoaId = $pessoaId;
        $this->status = $status;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTelefone(): ?int
    {
        return $this->telefone;
    }

    public function getPessoaId(): ?int
    {
        return $this->pessoaId;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public static function create(int $telefone, int $pessoaId): ?TelefoneModel
    {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("INSERT INTO telefone (telefone, pessoa, status) VALUES (?, ?, '1')");
        
        if ($stmt->execute([$telefone, $pessoaId])) {
            $id = $pdo->lastInsertId();
            return new TelefoneModel($id, $telefone, $pessoaId, '1');
        }

        return null;
    }

    public static function getByPessoa(int $pessoaId): array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM telefone WHERE pessoa = ? AND status = '1'");
        $stmt->execute([$pessoaId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $telefones = [];
        foreach ($rows as $row) {
            $telefones[] = new TelefoneModel(
                $row['id'],
                $row['telefone'],
                $row['pessoa'],
                $row['status']
            );
        }

        return $telefones;
    }

    public function deactivate(): bool
    {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE telefone SET status = '0' WHERE id = ?");
        return $stmt->execute([$this->id]);
    }
}
