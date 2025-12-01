<?php

namespace App\User;

use Core\ConnectionFactory;
use App\Endereco\AddressModel;
use PDO;

class PessoaModel {
    protected int $id;
    protected string $nome;
    protected string $usuario; // Renamed from login
    protected string $senha;
    protected string $email;
    protected string $dt_criacao;
    protected string $CPF;
    protected string $status;
    protected string $Uid;
    protected array $addresses = [];

    public function __construct(
        int $id,
        string $nome,
        string $usuario,
        string $senha,
        string $email,
        string $dt_criacao,
        string $CPF,
        string $status,
        string $Uid
    ) 
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->usuario = $usuario;
        $this->senha = $senha;
        $this->email = $email;
        $this->dt_criacao = $dt_criacao;
        $this->CPF = $CPF;
        $this->status = $status;
        $this->Uid = $Uid;
        
        if ($this->id) {
            $this->addresses = \App\Endereco\EnderecoModel::getByPessoa($this->id);
        }
    }

    public function getId(): int 
    {
        return $this->id;
    }

    public function getNome(): string 
    {
        return $this->nome;
    }

    public function getUsuario(): string 
    {
        return $this->usuario;
    }

    public function getSenha(): string 
    {
        return $this->senha;
    }

    public function getEmail(): string 
    {
        return $this->email;
    }

    public function getDtCriacao(): string 
    {
        return $this->dt_criacao;
    }

    public function getStatus(): string 
    {
        return $this->status;
    }

    public function getUid(): int 
    {
        return (int)$this->Uid;
    }

    public function getAddresses(): array 
    {
        return $this->addresses;
    }

    public static function findByData(string $column, $value): ?PessoaModel 
    {
        $dataMap = [
            'id' => 'id',
            'email' => 'email',
            'uid' => 'Uid',
            'usuario' => 'login' // Map 'usuario' search to 'login' column
        ];

        $search = $dataMap[strtolower($column)] ?? $column;

        $pdo = ConnectionFactory::getConnection('read_only');

        $columns = ['id', 'login', 'Uid', 'email'];
        if (!in_array($search, $columns)) {
            throw new \Exception("Coluna de busca inválida.");
        }
        
        $stmt = $pdo->prepare("SELECT * FROM pessoa WHERE $search = ? AND status = '1'");
        $stmt->execute([$value]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new PessoaModel(
            $row['id'], 
            $row['nome'], 
            $row['login'], 
            $row['senha'], 
            $row['email'],
            $row['dt_criacao'], 
            $row['CPF'],
            $row['status'], 
            $row['Uid']
        );
    }

    public function updatePessoa(string $nome = null, string $usuario = null, string $email = null): bool
    {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE pessoa SET nome = :nome, login = :login, email = :email WHERE id = :id");
        
        return $stmt->execute([
            'id' => $this->id,
            'nome' => $nome ?? $this->nome,
            'login' => $usuario ?? $this->usuario,
            'email' => $email ?? $this->email
        ]);
    }

    public static function registerPessoa(string $nome, string $usuario, string $email, string $senha, string $cpf): ?PessoaModel
    {
        $pdo = ConnectionFactory::getConnection('default');
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                INSERT INTO pessoa (nome, login, senha, email, CPF, dt_criacao, status, Uid) 
                VALUES (:nome, :login, :senha, :email, :cpf, NOW(), '1', :Uid)
            ");

            $uid = self::generateUID();

            $stmt->execute([
                'nome' => $nome,
                'login' => $usuario,
                'senha' => $senha,
                'email' => $email,
                'cpf' => $cpf,
                'Uid' => $uid
            ]);

            $newId = $pdo->lastInsertId();

            $pdo->commit();

            return self::findByData('id', $newId);

        } catch (\Exception $e) {
            $pdo->rollBack();
            echo "Register Error: " . $e->getMessage() . "\n";
            return null;
        }
    }

    private static function generateUID(): int
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $uid = 0;
        do {
            $uid = random_int(10000000, 2147483647);
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM pessoa WHERE Uid = ?");
            $stmt->execute([$uid]);
            $count = $stmt->fetchColumn();
        } while ($count > 0);
        return $uid;
    }
    
    public static function updatePassword(int $id, string $newPasswordHash): bool 
    {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE pessoa SET senha = :senha WHERE id = :id");
        
        return $stmt->execute([
            'senha' => $newPasswordHash,
            'id' => $id
        ]);
    }

    public function deactivatePessoa(): bool 
    {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE pessoa SET status = '0' WHERE id = :id");
        
        return $stmt->execute([
            'id' => $this->id
        ]);
    }
    
    public function getPasswordHash(): string {
        return $this->senha;
    }
}