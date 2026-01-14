<?php
/**
 * Gerencia endereços com relacionamentos para cidade, estado e tipo.
 * 
 * Tabelas: endereco, cidade, estado, tipo
 * 
 * @package App\Services\Models
 */

namespace App\Services\Models;

use Core\ConnectionFactory;
use PDO;

class EnderecoModel {
    /** @var int ID do endereço */
    protected int $id;
    protected string $logradouro;
    protected int $pessoaId;
    protected int $cidadeId;
    protected string $data;
    protected string $status;
    protected string $cep;
    
    /** @var array Dados da cidade */
    protected array $cidade = [];
    
    /** @var array Dados do estado */
    protected array $estado = [];
    
    /** @var array Tipos de endereço */
    protected array $tipos = [];

    public function __construct(
        int $id, string $logradouro, int $pessoaId, int $cidadeId,
        string $data, string $status, string $cep
    ) {
        $this->id = $id;
        $this->logradouro = $logradouro;
        $this->pessoaId = $pessoaId;
        $this->cidadeId = $cidadeId;
        $this->data = $data;
        $this->status = $status;
        $this->cep = $cep;
        
        $this->loadCidadeEstado();
        $this->tipos = $this->loadTipos();
    }

    // === GETTERS ===
    
    public function getId(): int { return $this->id; }
    public function getLogradouro(): string { return $this->logradouro; }
    public function getPessoaId(): int { return $this->pessoaId; }
    public function getCidadeId(): int { return $this->cidadeId; }
    public function getData(): string { return $this->data; }
    public function getStatus(): string { return $this->status; }
    public function getCep(): string { return $this->cep; }
    public function getCidade(): array { return $this->cidade; }
    public function getEstado(): array { return $this->estado; }
    public function getTipos(): array { return $this->tipos; }

    /**
     * Retorna CEP formatado (XXXXX-XXX)
     */
    public function getCepFormatado(): string {
        return substr($this->cep, 0, 5) . '-' . substr($this->cep, 5);
    }

    /**
     * Retorna endereço completo formatado
     */
    public function getEnderecoCompleto(): string {
        $cidade = $this->cidade['cidade'] ?? '';
        $estado = $this->estado['estado'] ?? '';
        return "{$this->logradouro}, {$cidade} - {$estado}, {$this->getCepFormatado()}";
    }

    // === MÉTODOS AUXILIARES ===
    
    private function loadCidadeEstado(): void {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("
            SELECT c.id as cidade_id, c.cidade, e.id as estado_id, e.estado
            FROM cidade c
            INNER JOIN estado e ON e.id = c.estado
            WHERE c.id = ?
        ");
        $stmt->execute([$this->cidadeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->cidade = ['id' => $row['cidade_id'], 'cidade' => $row['cidade']];
            $this->estado = ['id' => $row['estado_id'], 'estado' => $row['estado']];
        }
    }

    private function loadTipos(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT id, tipo FROM tipo WHERE endereco = ?");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === MÉTODOS DE TIPO ===
    
    public function addTipo(string $tipo): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("INSERT INTO tipo (tipo, endereco) VALUES (?, ?)");
        $result = $stmt->execute([$tipo, $this->id]);
        if ($result) $this->tipos = $this->loadTipos();
        return $result;
    }

    public function removeTipo(int $tipoId): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("DELETE FROM tipo WHERE id = ? AND endereco = ?");
        $result = $stmt->execute([$tipoId, $this->id]);
        if ($result) $this->tipos = $this->loadTipos();
        return $result;
    }

    // === MÉTODOS DE BUSCA ===
    
    public static function findById(int $id): ?EnderecoModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM endereco WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::createFromRow($row) : null;
    }

    /**
     * Busca endereços por pessoa
     */
    public static function getByPessoa(int $pessoaId): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM endereco WHERE pessoa = ? AND status = '1' ORDER BY data DESC");
        $stmt->execute([$pessoaId]);
        $enderecos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $enderecos[] = self::createFromRow($row);
        }
        return $enderecos;
    }

    /**
     * Busca endereços por cidade
     */
    public static function findByCidade(int $cidadeId): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM endereco WHERE cidade = ? AND status = '1'");
        $stmt->execute([$cidadeId]);
        $enderecos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $enderecos[] = self::createFromRow($row);
        }
        return $enderecos;
    }

    private static function createFromRow(array $row): EnderecoModel {
        return new EnderecoModel(
            $row['id'], $row['logradouro'], (int)$row['pessoa'],
            (int)$row['cidade'], $row['data'], $row['status'], $row['cep']
        );
    }

    // === MÉTODOS ESTÁTICOS DE CIDADE/ESTADO ===
    
    /**
     * Retorna todos os estados
     */
    public static function getAllEstados(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        return $pdo->query("SELECT * FROM estado ORDER BY estado")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna cidades de um estado
     */
    public static function getCidadesByEstado(int $estadoId): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM cidade WHERE estado = ? ORDER BY cidade");
        $stmt->execute([$estadoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca ou cria estado
     */
    public static function findOrCreateEstado(string $nome): int {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("SELECT id FROM estado WHERE estado = ?");
        $stmt->execute([$nome]);
        $id = $stmt->fetchColumn();
        if ($id) return (int)$id;
        
        $stmt = $pdo->prepare("INSERT INTO estado (estado) VALUES (?)");
        $stmt->execute([$nome]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Busca ou cria cidade
     */
    public static function findOrCreateCidade(string $nome, int $estadoId): int {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("SELECT id FROM cidade WHERE cidade = ? AND estado = ?");
        $stmt->execute([$nome, $estadoId]);
        $id = $stmt->fetchColumn();
        if ($id) return (int)$id;
        
        $stmt = $pdo->prepare("INSERT INTO cidade (cidade, estado) VALUES (?, ?)");
        $stmt->execute([$nome, $estadoId]);
        return (int)$pdo->lastInsertId();
    }

    // === MÉTODOS DE CRIAÇÃO ===
    
    /**
     * Cria novo endereço
     */
    public static function create(
        string $logradouro, int $pessoaId, int $cidadeId, string $cep, array $tipos = []
    ): ?EnderecoModel {
        $pdo = ConnectionFactory::getConnection('default');
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("
                INSERT INTO endereco (logradouro, pessoa, cidade, data, status, cep)
                VALUES (:logradouro, :pessoa, :cidade, NOW(), '1', :cep)
            ");
            $stmt->execute([
                'logradouro' => $logradouro, 'pessoa' => $pessoaId,
                'cidade' => $cidadeId, 'cep' => $cep
            ]);
            $enderecoId = (int)$pdo->lastInsertId();
            
            // Adiciona tipos
            foreach ($tipos as $tipo) {
                $stmt = $pdo->prepare("INSERT INTO tipo (tipo, endereco) VALUES (?, ?)");
                $stmt->execute([$tipo, $enderecoId]);
            }
            
            $pdo->commit();
            return self::findById($enderecoId);
        } catch (\Exception $e) {
            $pdo->rollBack();
            return null;
        }
    }

    // === MÉTODOS DE ATUALIZAÇÃO ===
    
    public function update(string $logradouro, int $cidadeId, string $cep): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("
            UPDATE endereco SET logradouro = :logradouro, cidade = :cidade, cep = :cep WHERE id = :id
        ");
        return $stmt->execute([
            'logradouro' => $logradouro, 'cidade' => $cidadeId,
            'cep' => $cep, 'id' => $this->id
        ]);
    }

    public function deactivate(): bool {
        $pdo = ConnectionFactory::getConnection('default');
        return $pdo->prepare("UPDATE endereco SET status = '0' WHERE id = ?")->execute([$this->id]);
    }

    public function activate(): bool {
        $pdo = ConnectionFactory::getConnection('default');
        return $pdo->prepare("UPDATE endereco SET status = '1' WHERE id = ?")->execute([$this->id]);
    }

    /**
     * Remove endereço (soft delete - seta status = '0')
     * 
     * Como a tabela endereco possui campo status, o delete
     * apenas desativa o registro.
     */
    public static function delete(int $id): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE endereco SET status = '0' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // === MÉTODOS DE EXCLUSÃO (DELETE REAL - tabelas sem status) ===

    /**
     * Remove um estado (DELETE real - tabela não possui status)
     * Só permite se não houver cidades vinculadas
     */
    public static function deleteEstado(int $id): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        // Verifica se tem cidades vinculadas
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cidade WHERE estado = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) return false;
        
        $stmt = $pdo->prepare("DELETE FROM estado WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Remove uma cidade (DELETE real - tabela não possui status)
     * Só permite se não houver endereços vinculados ativos
     */
    public static function deleteCidade(int $id): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        // Verifica se tem endereços vinculados ativos
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM endereco WHERE cidade = ? AND status = '1'");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) return false;
        
        $stmt = $pdo->prepare("DELETE FROM cidade WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Remove um tipo de endereço (DELETE real - tabela não possui status)
     */
    public static function deleteTipo(int $id): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("DELETE FROM tipo WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Atualiza um tipo de endereço
     */
    public static function updateTipo(int $id, string $novoTipo): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE tipo SET tipo = ? WHERE id = ?");
        return $stmt->execute([$novoTipo, $id]);
    }
}

