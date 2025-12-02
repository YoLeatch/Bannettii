<?php

namespace App\Sales;

use Core\ConnectionFactory;
use PDO;

class CupomModel
{
    private ?int $id;
    private ?string $cod;
    private ?string $criacao;
    private ?string $validade;
    private ?string $status;

    public function __construct(
        ?int $id = null,
        ?string $cod = null,
        ?string $criacao = null,
        ?string $validade = null,
        ?string $status = null
    ) {
        $this->id = $id;
        $this->cod = $cod;
        $this->criacao = $criacao;
        $this->validade = $validade;
        $this->status = $status;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCod(): ?string
    {
        return $this->cod;
    }

    public function getCriacao(): ?string
    {
        return $this->criacao;
    }

    public function getValidade(): ?string
    {
        return $this->validade;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public static function findByCode(string $code): ?CupomModel
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM cupom WHERE cod = ? AND status = '1'");
        $stmt->execute([$code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return new CupomModel(
                $row['id'],
                $row['cod'],
                $row['criacao'],
                $row['validade'],
                $row['status']
            );
        }

        return null;
    }

    public function isValid(): bool
    {
        if ($this->status !== '1') {
            return false;
        }

        $now = new \DateTime();
        $validade = new \DateTime($this->validade);

        return $now <= $validade;
    }
}
