<?php
/**
 * CupomModel - Modelo para tabela 'cupom'
 * 
 * Gerencia cupons de desconto com relacionamento N:N com sub_categorias.
 * 
 * Tabelas: cupom, cupom_sub_categoria
 * 
 * @package App\Sales
 */

namespace App\Sales;

use Core\ConnectionFactory;
use PDO;

class CupomModel {
    /** @var int ID do cupom */
    protected int $id;
    
    /** @var string Código do cupom */
    protected string $cod;
    
    /** @var string Data de criação */
    protected string $criacao;
    
    /** @var string Data de validade */
    protected string $validade;
    
    /** @var string Status ('1' = ativo, '0' = inativo) */
    protected string $status;
    
    /** @var array Subcategorias vinculadas ao cupom */
    protected array $subCategorias = [];

    /**
     * Construtor
     */
    public function __construct(int $id, string $cod, string $criacao, string $validade, string $status) {
        $this->id = $id;
        $this->cod = $cod;
        $this->criacao = $criacao;
        $this->validade = $validade;
        $this->status = $status;
        $this->subCategorias = $this->loadSubCategorias();
    }

    // === GETTERS ===
    
    public function getId(): int { return $this->id; }
    public function getCod(): string { return $this->cod; }
    public function getCriacao(): string { return $this->criacao; }
    public function getValidade(): string { return $this->validade; }
    public function getStatus(): string { return $this->status; }
    public function getSubCategorias(): array { return $this->subCategorias; }
    
    /**
     * Verifica se cupom está expirado
     */
    public function isExpired(): bool {
        return strtotime($this->validade) < strtotime(date('Y-m-d'));
    }
    
    /**
     * Verifica se cupom é válido (ativo e não expirado)
     */
    public function isValid(): bool {
        return $this->status === '1' && !$this->isExpired();
    }

    // === MÉTODOS DE SUB_CATEGORIA ===
    
    /**
     * Carrega subcategorias vinculadas ao cupom
     */
    private function loadSubCategorias(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("
            SELECT sc.id, sc.sub_categoria, sc.categoria, c.categoria as categoria_nome
            FROM sub_categoria sc
            INNER JOIN cupom_sub_categoria csc ON csc.sub_categoria_id = sc.id
            INNER JOIN categoria c ON c.id = sc.categoria
            WHERE csc.cupom_id = ?
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adiciona subcategoria ao cupom
     */
    public function addSubCategoria(int $subCategoriaId): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        // Verifica se já existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cupom_sub_categoria WHERE cupom_id = ? AND sub_categoria_id = ?");
        $stmt->execute([$this->id, $subCategoriaId]);
        if ($stmt->fetchColumn() > 0) return true;
        
        $stmt = $pdo->prepare("INSERT INTO cupom_sub_categoria (cupom_id, sub_categoria_id) VALUES (?, ?)");
        $result = $stmt->execute([$this->id, $subCategoriaId]);
        if ($result) $this->subCategorias = $this->loadSubCategorias();
        return $result;
    }

    /**
     * Remove subcategoria do cupom
     */
    public function removeSubCategoria(int $subCategoriaId): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("DELETE FROM cupom_sub_categoria WHERE cupom_id = ? AND sub_categoria_id = ?");
        $result = $stmt->execute([$this->id, $subCategoriaId]);
        if ($result) $this->subCategorias = $this->loadSubCategorias();
        return $result;
    }

    /**
     * Remove todas subcategorias do cupom
     */
    public function clearSubCategorias(): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("DELETE FROM cupom_sub_categoria WHERE cupom_id = ?");
        $result = $stmt->execute([$this->id]);
        if ($result) $this->subCategorias = [];
        return $result;
    }

    // === MÉTODOS DE BUSCA ===
    
    /**
     * Busca cupom por ID
     */
    public static function findById(int $id): ?CupomModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM cupom WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::createFromRow($row) : null;
    }

    /**
     * Busca cupom por código
     */
    public static function findByCod(string $cod): ?CupomModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM cupom WHERE cod = ? AND status = '1'");
        $stmt->execute([$cod]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::createFromRow($row) : null;
    }

    /**
     * Retorna todos cupons
     */
    public static function findAll(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->query("SELECT * FROM cupom ORDER BY criacao DESC");
        $cupons = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cupons[] = self::createFromRow($row);
        }
        return $cupons;
    }

    /**
     * Retorna apenas cupons válidos
     */
    public static function findAllValid(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM cupom WHERE status = '1' AND validade >= ? ORDER BY validade");
        $stmt->execute([date('Y-m-d')]);
        $cupons = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cupons[] = self::createFromRow($row);
        }
        return $cupons;
    }

    /**
     * Busca cupons por subcategoria
     */
    public static function findBySubCategoria(int $subCategoriaId): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("
            SELECT c.* FROM cupom c
            INNER JOIN cupom_sub_categoria csc ON csc.cupom_id = c.id
            WHERE csc.sub_categoria_id = ? AND c.status = '1'
        ");
        $stmt->execute([$subCategoriaId]);
        $cupons = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cupons[] = self::createFromRow($row);
        }
        return $cupons;
    }

    private static function createFromRow(array $row): CupomModel {
        return new CupomModel($row['id'], $row['cod'], $row['criacao'], $row['validade'], $row['status']);
    }

    // === MÉTODOS DE CRIAÇÃO ===
    
    /**
     * Cria novo cupom
     */
    public static function create(string $cod, string $validade, array $subCategoriasIds = []): ?CupomModel {
        $pdo = ConnectionFactory::getConnection('default');
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO cupom (cod, criacao, validade, status) VALUES (:cod, :criacao, :validade, '1')");
            $stmt->execute(['cod' => $cod, 'criacao' => date('Y-m-d'), 'validade' => $validade]);
            $cupomId = (int)$pdo->lastInsertId();
            
            // Vincula subcategorias
            foreach ($subCategoriasIds as $subCatId) {
                $stmt = $pdo->prepare("INSERT INTO cupom_sub_categoria (cupom_id, sub_categoria_id) VALUES (?, ?)");
                $stmt->execute([$cupomId, $subCatId]);
            }
            
            $pdo->commit();
            return self::findById($cupomId);
        } catch (\Exception $e) {
            $pdo->rollBack();
            return null;
        }
    }

    // === MÉTODOS DE ATUALIZAÇÃO ===
    
    /**
     * Atualiza cupom
     */
    public function update(string $cod, string $validade): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE cupom SET cod = :cod, validade = :validade WHERE id = :id");
        $result = $stmt->execute(['cod' => $cod, 'validade' => $validade, 'id' => $this->id]);
        if ($result) {
            $this->cod = $cod;
            $this->validade = $validade;
        }
        return $result;
    }

    public function deactivate(): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE cupom SET status = '0' WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    public function activate(): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE cupom SET status = '1' WHERE id = ?");
        return $stmt->execute([$this->id]);
    }

    /**
     * Remove cupom (soft delete - seta status = '0')
     * 
     * Como a tabela cupom possui campo status, o delete apenas
     * desativa o registro. Os vínculos com subcategorias são mantidos.
     */
    public static function delete(int $id): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE cupom SET status = '0' WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
