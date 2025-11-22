<?php

namespace App\User;

use Core\ConnectionFactory;
use PDO;

class FuncionarioModel extends PessoaModel
{
    private ?string $carteirinha;
    private ?string $statusFuncionario;

    public function __construct(
        int $id,
        string $nome,
        string $usuario,
        string $senha,
        string $dt_criacao,
        string $status,
        string $Uid,
        ?string $carteirinha = null,
        ?string $statusFuncionario = null
    ) {
        parent::__construct($id, $nome, $usuario, $senha, $dt_criacao, $status, $Uid);
        $this->carteirinha = $carteirinha;
        $this->statusFuncionario = $statusFuncionario;
    }

    public function getCarteirinha(): ?string
    {
        return $this->carteirinha;
    }

    public function getStatusFuncionario(): ?string
    {
        return $this->statusFuncionario;
    }

    public static function findByFuncionarioId(int $id): ?FuncionarioModel
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("
            SELECT p.*, f.carteirinha, f.status as status_funcionario
            FROM Pessoa p
            JOIN Funcionario f ON f.id = p.id
            WHERE p.id = ? AND p.status = '1'
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new FuncionarioModel(
            $row['id'],
            $row['nome'],
            $row['login'], // DB column
            $row['senha'],
            $row['dt_criacao'],
            $row['status'],
            $row['Uid'],
            $row['carteirinha'],
            $row['status_funcionario']
        );
    }

    public static function createFuncionario(string $nome, string $usuario, string $email, string $senha, string $carteirinha): ?FuncionarioModel
    {
        // 1. Create Pessoa
        $pessoa = parent::registerPessoa($nome, $usuario, $email, $senha);
        
        if (!$pessoa) {
            return null;
        }

        // 2. Insert into Funcionario
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("INSERT INTO Funcionario (id, carteirinha, status) VALUES (?, ?, '1')");
        
        if ($stmt->execute([$pessoa->getId(), $carteirinha])) {
            return self::findByFuncionarioId($pessoa->getId());
        }

        return null;
    }
}
