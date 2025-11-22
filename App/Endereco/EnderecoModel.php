<?php

namespace App\Endereco;

use Core\ConnectionFactory;
use PDO;

class AddressModel
{
    private ?int $id;
    private ?string $logradouro;
    private ?int $pessoaId;
    private ?int $cidadeId;
    private ?string $data;
    private ?string $status;
    private ?string $cep;
    
    // Extra fields from joins
    private ?string $nomeCidade;
    private ?string $nomeEstado;
    private ?string $tipo;

    public function __construct(
        ?int $id = null, 
        ?string $logradouro = null, 
        ?int $pessoaId = null, 
        ?int $cidadeId = null, 
        ?string $data = null,
        ?string $status = null,
        ?string $cep = null,
        ?string $nomeCidade = null,
        ?string $nomeEstado = null,
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
        $this->nomeCidade = $nomeCidade;
        $this->nomeEstado = $nomeEstado;
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

    public function getNomeCidade(): ?string
    {
        return $this->nomeCidade;
    }

    public function getNomeEstado(): ?string
    {
        return $this->nomeEstado;
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
            FROM Endereco e
            JOIN Cidade c ON e.Cidade = c.id
            JOIN Estado est ON c.estado = est.id
            LEFT JOIN Tipo t ON t.Endereco = e.id
            WHERE e.Pessoa = ? AND e.status = '1'
        ");
        $stmt->execute([$pessoaId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $addresses = [];
        foreach ($rows as $row) {
            $addresses[] = new AddressModel(
                $row['id'],
                $row['logradouro'],
                $row['Pessoa'],
                $row['Cidade'],
                $row['data'],
                $row['status'],
                $row['CEP'],
                $row['nome_cidade'],
                $row['nome_estado'],
                $row['nome_tipo']
            );
        }
        return $addresses;
    }

    public function create(): bool
    {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("INSERT INTO Endereco (logradouro, Pessoa, Cidade, data, status, CEP) VALUES (?, ?, ?, NOW(), '1', ?)");
        
        return $stmt->execute([$this->logradouro, $this->pessoaId, $this->cidadeId, $this->cep]);
    }

    public function update(): bool
    {
        $pdo = ConnectionFactory::getConnection('default');
        
        $sql = "UPDATE Endereco 
                SET logradouro = ?, Cidade = ?, CEP = ?
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$this->logradouro, $this->cidadeId, $this->cep, $this->id]);
    }

    public function delete(): bool
    {
        $pdo = ConnectionFactory::getConnection('default');
        
        $sql = "UPDATE Endereco 
                SET status = '0' 
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$this->id]);
    }
}