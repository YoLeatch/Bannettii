<?php

namespace App\Endereco;

use Core\ConnectionFactory;
use PDO;

class EnderecoModel
{
    private ?int $id;
    private ?string $logradouro;
    private ?int $pessoaId;
    private ?int $cidadeId;
    private ?string $data;
    private ?string $status;
    private ?string $cep;

    private ?string $Cidade;
    private ?string $Estado;
    private ?string $tipo;

    public function __construct(
        ?int $id = null, 
        ?string $logradouro = null, 
        ?int $pessoaId = null, 
        ?int $cidadeId = null, 
        ?string $data = null,
        ?string $status = null,
        ?string $cep = null,
        ?string $Cidade = null,
        ?string $Estado = null,
        ?string $tipo = null
    )
    {
        $this->id = $id;
        $this->logradouro = $logradouro;
        $this->pessoaId = $pessoaId;
        $this->cidadeId = $cidadeId;
        $this->data = $data;
        $this->status = $status;
        $this->cep = $cep;
        $this->Cidade = $Cidade;
        $this->Estado = $Estado;
        $this->tipo = $tipo;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogradouro(): ?string
    {
        return $this->logradouro;
    }

    public function getPessoaId(): ?int
    {
        return $this->pessoaId;
    }

    public function getCidadeId(): ?int
    {
        return $this->cidadeId;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getCep(): ?string
    {
        return $this->cep;
    }

    public function getCidade(): ?string
    {
        return $this->Cidade;
    }

    public function getEstado(): ?string
    {
        return $this->Estado;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public static function getByPessoa(int $pessoaId): array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("
            SELECT e.*, c.cidade as nome_cidade, est.estado as nome_estado, t.tipo as nome_tipo
            FROM endereco e
            JOIN cidade c ON e.cidade = c.id
            JOIN estado est ON c.estado = est.id
            LEFT JOIN tipo t ON t.endereco = e.id
            WHERE e.pessoa = ? AND e.status = '1'
        ");
        $stmt->execute([$pessoaId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $addresses = [];
        foreach ($rows as $row) {
            $addresses[] = new EnderecoModel(
                $row['id'],
                $row['logradouro'],
                $row['pessoa'],
                $row['cidade'],
                $row['data'],
                $row['status'],
                $row['cep'],
                $row['nome_cidade'],
                $row['nome_estado'],
                $row['nome_tipo']
            );
        }
        return $addresses;
    }

    private function fetchAllEndereco(int $pessoaId, int $status = 1): array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM endereco WHERE pessoa = ? AND status = ?");
        $stmt->execute([$pessoaId, $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createEndereco(): bool
    {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("INSERT INTO endereco (logradouro, pessoa, cidade, data, status, cep) VALUES (?, ?, ?, NOW(), '1', ?)");
        
        return $stmt->execute([$this->logradouro, $this->pessoaId, $this->cidadeId, $this->cep]);
    }

    public function updateEndereco(): bool
    {
        $pdo = ConnectionFactory::getConnection('default');
        
        $sql = "UPDATE endereco 
                SET logradouro = ?, cidade = ?, cep = ?
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$this->logradouro, $this->cidadeId, $this->cep, $this->id]);
    }

    public function desactivateEndereco(): bool
    {
        $pdo = ConnectionFactory::getConnection('default');
        
        $sql = "UPDATE endereco 
                SET status = '0' 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$this->id]);
    }
}