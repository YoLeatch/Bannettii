<?php

namespace App\User;

use Core\ConnectionFactory;
use PDO;

class ClienteModel extends PessoaModel
{
    private ?int $idade;
    private ?string $statusCliente;
    private array $cards = [];

    public function __construct(
        int $id,
        string $nome,
        string $usuario,
        string $senha,
        string $email,
        string $dt_criacao,
        string $CPF,
        string $status,
        string $Uid,
        ?int $idade = null,
        ?string $statusCliente = null
    ) {
        parent::__construct($id, $nome, $usuario, $senha, $email, $dt_criacao, $CPF, $status, $Uid);
        $this->idade = $idade;
        $this->statusCliente = $statusCliente;
        
        if ($this->id) {
            $this->fetchCards();
        }
    }

    public function getIdade(): ?int
    {
        return $this->idade;
    }

    public function getStatusCliente(): ?string
    {
        return $this->statusCliente;
    }

    private function fetchCards(): void
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT id, nome, numero, validade FROM cartao_credito WHERE usuario = ? AND status = '1'");
        $stmt->execute([$this->id]);
        $this->cards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCards(): array
    {
        return $this->cards;
    }

    public function addCard(string $nome, string $numero, string $validade): bool
    {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("INSERT INTO cartao_credito (nome, numero, validade, usuario, status) VALUES (?, ?, ?, ?, '1')");
        
        if ($stmt->execute([$nome, $numero, $validade, $this->id])) {
            $this->fetchCards(); // Refresh cards
            return true;
        }
        return false;
    }

    public function deactivateCard(int $cardId): bool
    {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE cartao_credito SET status = '0' WHERE id = ? AND usuario = ?");
        
        if ($stmt->execute([$cardId, $this->id])) {
            $this->fetchCards(); // Refresh cards
            return true;
        }
        return false;
    }

    public static function findByClienteId(int $id): ?ClienteModel
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("
            SELECT p.*, c.idade, c.status as status_cliente
            FROM pessoa p
            JOIN cliente c ON c.id = p.id
            WHERE p.id = ? AND p.status = '1'
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new ClienteModel(
            $row['id'],
            $row['nome'],
            $row['login'], // DB column
            $row['senha'],
            $row['email'],
            $row['dt_criacao'],
            $row['CPF'],
            $row['status'],
            $row['Uid'],
            $row['idade'],
            $row['status_cliente']
        );
    }

    public static function createCliente(string $nome, string $usuario, string $email, string $senha, string $cpf, int $idade): ?ClienteModel
    {
        // 1. Create Pessoa
        $pessoa = parent::registerPessoa($nome, $usuario, $email, $senha, $cpf);
        
        if (!$pessoa) {
            return null;
        }

        // 2. Insert into Cliente
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("INSERT INTO cliente (id, idade, status) VALUES (?, ?, '1')");
        
        if ($stmt->execute([$pessoa->getId(), $idade])) {
            return self::findByClienteId($pessoa->getId());
        }

        return null;
    }
}
