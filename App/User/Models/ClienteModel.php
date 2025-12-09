<?php
/**
 * ClienteModel - Modelo para tabela 'cliente'
 * 
 * Gerencia dados do cliente, estendendo PessoaModel.
 * 
 * Tabelas: cliente, pessoa
 * 
 * @package App\User\Models
 */

namespace App\User\Models;


use Core\ConnectionFactory;
use PDO;

class ClienteModel extends PessoaModel {
    /** @var int ID do cliente */
    protected int $clienteId;
    
    /** @var int|null Idade do cliente */
    protected ?int $idade;
    
    /** @var string Status do cliente */
    protected string $clienteStatus;

    /**
     * Construtor
     */
    public function __construct(
        int $id, string $nome, ?string $image, string $senha,
        string $email, ?string $dt_nascimento, string $dt_criacao,
        string $CPF, string $status, int $Uid,
        ?int $idade, string $clienteStatus
    ) {
        parent::__construct($id, $nome, $image, $senha, $email, 
            $dt_nascimento, $dt_criacao, $CPF, $status, $Uid);
        
        $this->clienteId = $id;
        $this->idade = $idade;
        $this->clienteStatus = $clienteStatus;
    }

    // === GETTERS ===
    
    public function getClienteId(): int { return $this->clienteId; }
    public function getIdade(): ?int { return $this->idade; }
    public function getClienteStatus(): string { return $this->clienteStatus; }

    /**
     * Calcula idade pela data de nascimento
     */
    public function calcularIdade(): ?int {
        if (!$this->dt_nascimento) return null;
        return (new \DateTime())->diff(new \DateTime($this->dt_nascimento))->y;
    }

    // === MÉTODOS DE BUSCA ===
    
    /**
     * Busca cliente por ID
     */
    public static function findById(int $id): ?ClienteModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("
            SELECT p.*, c.idade, c.status as cliente_status
            FROM pessoa p INNER JOIN cliente c ON c.id = p.id
            WHERE p.id = ? AND p.status = '1' AND c.status = '1'
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::createFromRow($row) : null;
    }

    /**
     * Busca cliente por email
     */
    public static function findByEmail(string $email): ?ClienteModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("
            SELECT p.*, c.idade, c.status as cliente_status
            FROM pessoa p INNER JOIN cliente c ON c.id = p.id
            WHERE p.email = ? AND p.status = '1' AND c.status = '1'
        ");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::createFromRow($row) : null;
    }

    /**
     * Retorna todos clientes ativos
     */
    public static function findAll(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->query("
            SELECT p.*, c.idade, c.status as cliente_status
            FROM pessoa p INNER JOIN cliente c ON c.id = p.id
            WHERE p.status = '1' AND c.status = '1' ORDER BY p.nome
        ");
        $clientes = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $clientes[] = self::createFromRow($row);
        }
        return $clientes;
    }

    /**
     * Cria objeto a partir de linha do banco
     */
    private static function createFromRow(array $row): ClienteModel {
        return new ClienteModel(
            $row['id'], $row['nome'], $row['image'], $row['senha'],
            $row['email'], $row['dt_nascimento'] ?? null, $row['dt_criacao'],
            $row['CPF'], $row['status'], (int)$row['uid'],
            $row['idade'] ?? null, $row['cliente_status']
        );
    }

    // === MÉTODOS DE CRIAÇÃO ===
    
    /**
     * Registra novo cliente
     */
    public static function register(
        string $nome, string $email, string $senha, string $cpf,
        ?int $idade = null, ?string $image = null
    ): ?ClienteModel {
        $pdo = ConnectionFactory::getConnection('default');
        $logFile = __DIR__ . '/../../../debug_register.txt';
        
        try {
            file_put_contents($logFile, "ClienteModel::register - Iniciando transação\n", FILE_APPEND);
            $pdo->beginTransaction();
            
            file_put_contents($logFile, "ClienteModel::register - Chamando registerPessoa\n", FILE_APPEND);
            $pessoa = PessoaModel::registerPessoa($nome, $email, $senha, $cpf, $image);
            
            if (!$pessoa) {
                file_put_contents($logFile, "ClienteModel::register - registerPessoa retornou null\n", FILE_APPEND);
                throw new \Exception("Erro ao criar pessoa");
            }
            
            file_put_contents($logFile, "ClienteModel::register - Pessoa criada com ID: " . $pessoa->getId() . "\n", FILE_APPEND);
            
            $stmt = $pdo->prepare("INSERT INTO cliente (id, idade, status) VALUES (:id, :idade, '1')");
            $stmt->execute(['id' => $pessoa->getId(), 'idade' => $idade]);
            
            file_put_contents($logFile, "ClienteModel::register - Cliente inserido, fazendo commit\n", FILE_APPEND);
            $pdo->commit();
            
            return self::findById($pessoa->getId());
        } catch (\Exception $e) {
            file_put_contents($logFile, "ClienteModel::register - ERRO: " . $e->getMessage() . "\n", FILE_APPEND);
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return null;
        }
    }

    // === MÉTODOS DE ATUALIZAÇÃO ===
    
    /**
     * Atualiza idade do cliente
     */
    public function updateIdade(?int $idade): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE cliente SET idade = :idade WHERE id = :id");
        $result = $stmt->execute(['idade' => $idade, 'id' => $this->clienteId]);
        if ($result) $this->idade = $idade;
        return $result;
    }

    /**
     * Desativa o cliente
     */
    public function deactivate(): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE cliente SET status = '0' WHERE id = ?");
        return $stmt->execute([$this->clienteId]);
    }

    /**
     * Reativa o cliente
     */
    public function activate(): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE cliente SET status = '1' WHERE id = ?");
        return $stmt->execute([$this->clienteId]);
    }
}
