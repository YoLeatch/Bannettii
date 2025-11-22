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
    protected string $dt_criacao;
    protected string $status;
    protected string $Uid;
    protected array $emails = [];
    protected array $addresses = [];

    public function __construct(
        int $id,
        string $nome,
        string $usuario,
        string $senha,
        string $dt_criacao,
        string $status,
        string $Uid
    ) 
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->usuario = $usuario;
        $this->senha = $senha;
        $this->dt_criacao = $dt_criacao;
        $this->status = $status;
        $this->Uid = $Uid;
        
        if ($this->id) {
            $this->emails = $this->fetchEmails();
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

    public function getEmails(): array 
    {
        return $this->emails;
    }

    public function getAddresses(): array 
    {
        return $this->addresses;
    }

    protected function fetchEmails(): array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM Email WHERE pessoa = ? AND status = '1'");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        if ($search === 'email') {
            $stmt = $pdo->prepare("
                SELECT p.* 
                FROM Pessoa p
                JOIN Email e ON e.pessoa = p.id
                WHERE e.email = ? AND e.status = '1' AND p.status = '1'
            ");
        } else {
            $columns = ['id', 'login', 'Uid'];
            if (!in_array($search, $columns)) {
                throw new \Exception("Coluna de busca inválida.");
            }
            $stmt = $pdo->prepare("SELECT * FROM Pessoa WHERE $search = ? AND status = '1'");
        }

        $stmt->execute([$value]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new PessoaModel(
            $row['id'], 
            $row['nome'], 
            $row['login'], // DB column is still 'login'
            $row['senha'], 
            $row['dt_criacao'], 
            $row['status'], 
            $row['Uid']
        );
    }

    public function updatePessoa(string $nome = null, string $usuario = null): bool
    {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE Pessoa SET nome = :nome, login = :login WHERE id = :id");
        
        return $stmt->execute([
            'id' => $this->id,
            'nome' => $nome ?? $this->nome,
            'login' => $usuario ?? $this->usuario
        ]);
    }

    public static function registerPessoa(string $nome, string $usuario, string $email, string $senha): ?PessoaModel
    {
        $pdo = ConnectionFactory::getConnection('default');
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                INSERT INTO Pessoa (nome, login, senha, dt_criacao, status, Uid) 
                VALUES (:nome, :login, :senha, NOW(), '1', :Uid)
            ");

            $uid = self::generateUID();

            $stmt->execute([
                'nome' => $nome,
                'login' => $usuario,
                'senha' => $senha,
                'Uid' => $uid
            ]);

            $newId = $pdo->lastInsertId();

            $stmtEmail = $pdo->prepare("INSERT INTO Email (email, pessoa, status) VALUES (:email, :pessoa, '1')");
            $stmtEmail->execute([
                'email' => $email,
                'pessoa' => $newId
            ]);

            $pdo->commit();

            return self::findByData('id', $newId);

        } catch (\Exception $e) {
            $pdo->rollBack();
            return null;
        }
    }

    private static function generateUID(): int
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $uid = 0;
        do {
            $uid = random_int(10000000, 2147483647);
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Pessoa WHERE Uid = ?");
            $stmt->execute([$uid]);
            $count = $stmt->fetchColumn();
        } while ($count > 0);
        return $uid;
    }
    
    public static function updatePassword(int $id, string $newPasswordHash): bool 
    {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE Pessoa SET senha = :senha WHERE id = :id");
        
        return $stmt->execute([
            'senha' => $newPasswordHash,
            'id' => $id
        ]);
    }

    public function deactivatePessoa(): bool 
    {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE Pessoa SET status = '0' WHERE id = :id");
        
        return $stmt->execute([
            'id' => $this->id
        ]);
    }
    
    public function getPasswordHash(): string {
        return $this->senha;
    }
}