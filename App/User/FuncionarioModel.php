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
        string $email,
        string $dt_criacao,
        string $CPF,
        string $status,
        string $Uid,
        ?string $carteirinha = null,
        ?string $statusFuncionario = null
    ) {
        parent::__construct($id, $nome, $usuario, $senha, $email, $dt_criacao, $CPF, $status, $Uid);
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
            FROM pessoa p
            JOIN funcionario f ON f.id = p.id
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
            $row['email'],
            $row['dt_criacao'],
            $row['CPF'],
            $row['status'],
            $row['Uid'],
            $row['carteirinha'],
            $row['status_funcionario']
        );
    }

    public static function createFuncionario(string $nome, string $usuario, string $email, string $senha, string $cpf, string $carteirinha): ?FuncionarioModel
    {
        // 1. Create Pessoa
        $pessoa = parent::registerPessoa($nome, $usuario, $email, $senha, $cpf);
        
        if (!$pessoa) {
            return null;
        }

        // 2. Insert into Funcionario
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("INSERT INTO funcionario (id, carteirinha, status) VALUES (?, ?, '1')");
        
        if ($stmt->execute([$pessoa->getId(), $carteirinha])) {
            return self::findByFuncionarioId($pessoa->getId());
        }

        return null;
    }
    public static function fetchAll(): array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("
            SELECT p.*, f.carteirinha, f.status as status_funcionario
            FROM pessoa p
            JOIN funcionario f ON f.id = p.id
            WHERE p.status = '1'
        ");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $funcionarios = [];
        foreach ($rows as $row) {
            $funcionarios[] = new FuncionarioModel(
                $row['id'],
                $row['nome'],
                $row['login'],
                $row['senha'],
                $row['email'],
                $row['dt_criacao'],
                $row['CPF'],
                $row['status'],
                $row['Uid'],
                $row['carteirinha'],
                $row['status_funcionario']
            );
        }

        return $funcionarios;
    }
}
