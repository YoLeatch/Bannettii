<?php

namespace App\User;

use Core\ConnectionFactory;
use App\Endereco\AddressModel;
use PDO;

class PessoaModel {
    protected int $id;
    protected string $nome;
    protected ?string $image;
    protected string $senha;
    protected string $email;
    protected ?string $dt_nascimento;
    protected string $dt_criacao;
    protected string $CPF;
    protected string $status;
    protected int $Uid;
    protected array $addresses = [];

    public function __construct(
        int $id,
        string $nome,
        ?string $image,
        string $senha,
        string $email,
        ?string $dt_nascimento,
        string $dt_criacao,
        string $CPF,
        string $status,
        int $Uid
    ) 
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->image = $image;
        $this->senha = $senha;
        $this->email = $email;
        $this->dt_nascimento = $dt_nascimento;
        $this->dt_criacao = $dt_criacao;
        $this->CPF = $CPF;
        $this->status = $status;
        $this->Uid = $Uid;
        
        if ($this->id) {
            try {
                $this->addresses = \App\Endereco\EnderecoModel::getByPessoa($this->id);
            } catch (\Exception $e) {
                $this->addresses = [];
            }
        }
    }

    public function getDtNascimento(): ?string 
    {
        return $this->dt_nascimento;
    }

    public function getId(): int 
    {
        return $this->id;
    }

    public function getNome(): string 
    {
        return $this->nome;
    }

    public function getImage(): ?string 
    {
        return $this->image;
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

    public function getCpf(): string 
    {
        return $this->CPF;
    }

    public function getStatus(): string 
    {
        return $this->status;
    }

    public function getUid(): int 
    {
        return $this->Uid;
    }

    public function getAddresses(): array 
    {
        return $this->addresses;
    }

    public static function findByData(string $search, $value): ?PessoaModel 
    {
        $pdo = ConnectionFactory::getConnection('read_only');

        $columns = ['id', 'uid', 'email'];
        if (!in_array($search, $columns)) {
            // Allow searching by other columns if needed
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
            $row['image'],
            $row['senha'],
            $row['email'],
            $row['dt_nascimento'] ?? null,
            $row['dt_criacao'],
            $row['CPF'],
            $row['status'],
            (int)$row['uid']
        );
    }

    public function updatePessoa(string $nome = null, string $email = null, string $image = null): bool
    {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE pessoa SET nome = :nome, email = :email, image = :image WHERE id = :id");
        
        return $stmt->execute([
            'id' => $this->id,
            'nome' => $nome ?? $this->nome,
            'email' => $email ?? $this->email,
            'image' => $image ?? $this->image
        ]);
    }

    public static function registerPessoa(string $nome, string $email, string $senha, string $cpf, ?string $image = null): ?PessoaModel
    {
        $pdo = ConnectionFactory::getConnection('default');
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                INSERT INTO pessoa (nome, image, senha, email, CPF, dt_criacao, status, uid) 
                VALUES (:nome, :image, :senha, :email, :cpf, NOW(), '1', :uid)
            ");

            $uid = self::generateUID();

            $stmt->execute([
                'nome' => $nome,
                'image' => $image,
                'senha' => $senha,
                'email' => $email,
                'cpf' => $cpf,
                'uid' => $uid
            ]);

            $newId = $pdo->lastInsertId();

            $pdo->commit();

            return self::findByData('id', $newId);

        } catch (\Exception $e) {
            $pdo->rollBack();
            file_put_contents(__DIR__ . '/../../debug_register.txt', "Exception: " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }

    private static function generateUID(): int
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $uid = 0;
        do {
            $uid = random_int(10000000, 2147483647);
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM pessoa WHERE uid = ?");
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

    public static function updateUser(int $id, string $nome, string $email, string $cpf, string $status, string $password = '', string $image = null): bool
    {
        $pdo = ConnectionFactory::getConnection('default');
        
        $sql = "UPDATE pessoa SET nome = :nome, email = :email, CPF = :cpf, status = :status";
        $params = [
            'id' => $id,
            'nome' => $nome,
            'email' => $email,
            'cpf' => $cpf,
            'status' => $status
        ];

        if (!empty($password)) {
            $sql .= ", senha = :senha";
            $params['senha'] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($image !== null) {
            $sql .= ", image = :image";
            $params['image'] = $image;
        }

        $sql .= " WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }
}