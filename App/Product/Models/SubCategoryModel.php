<?php
/**
 * SubCategoryModel - Modelo para tabela 'sub_categoria'
 * 
 * Gerencia subcategorias de produtos.
 * 
 * Tabela: sub_categoria (FK: categoria)
 * 
 * @package App\Product\Models
 */

namespace App\Product\Models;

use Core\ConnectionFactory;
use PDO;

class SubCategoryModel {
    /** @var int ID da subcategoria */
    protected int $id;
    
    /** @var int ID da categoria pai */
    protected int $categoriaId;
    
    /** @var string Nome da subcategoria */
    protected string $subCategoria;

    public function __construct(int $id, int $categoriaId, string $subCategoria) {
        $this->id = $id;
        $this->categoriaId = $categoriaId;
        $this->subCategoria = $subCategoria;
    }

    // === GETTERS ===
    
    public function getId(): int { return $this->id; }
    public function getCategoriaId(): int { return $this->categoriaId; }
    public function getSubCategoria(): string { return $this->subCategoria; }

    /**
     * Retorna a categoria pai
     */
    public function getCategoria(): ?CategoryModel {
        return CategoryModel::findById($this->categoriaId);
    }

    /**
     * Conta produtos nesta subcategoria
     */
    public function countProdutos(): int {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM produto WHERE sub_categoria = ? AND status = '1'");
        $stmt->execute([$this->id]);
        return (int)$stmt->fetchColumn();
    }

    // === MÉTODOS DE BUSCA ===
    
    public static function findById(int $id): ?SubCategoryModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM sub_categoria WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new SubCategoryModel($row['id'], (int)$row['categoria'], $row['sub_categoria']) : null;
    }

    public static function findByNome(string $nome): ?SubCategoryModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM sub_categoria WHERE sub_categoria = ?");
        $stmt->execute([$nome]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new SubCategoryModel($row['id'], (int)$row['categoria'], $row['sub_categoria']) : null;
    }

    public static function findAll(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->query("
            SELECT sc.*, c.categoria as categoria_nome
            FROM sub_categoria sc
            INNER JOIN categoria c ON c.id = sc.categoria
            ORDER BY c.categoria, sc.sub_categoria
        ");
        $subCategorias = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $subCategorias[] = [
                'subCategoria' => new SubCategoryModel($row['id'], (int)$row['categoria'], $row['sub_categoria']),
                'categoria_nome' => $row['categoria_nome']
            ];
        }
        return $subCategorias;
    }

    /**
     * Busca subcategorias por categoria
     */
    public static function findByCategoria(int $categoriaId): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM sub_categoria WHERE categoria = ? ORDER BY sub_categoria");
        $stmt->execute([$categoriaId]);
        $subCategorias = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $subCategorias[] = new SubCategoryModel($row['id'], (int)$row['categoria'], $row['sub_categoria']);
        }
        return $subCategorias;
    }

    /**
     * Retorna subcategorias com contagem de produtos
     */
    public static function findAllWithCount(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->query("
            SELECT sc.*, c.categoria as categoria_nome, COUNT(p.id) as produto_count
            FROM sub_categoria sc
            INNER JOIN categoria c ON c.id = sc.categoria
            LEFT JOIN produto p ON p.sub_categoria = sc.id AND p.status = '1'
            GROUP BY sc.id
            ORDER BY c.categoria, sc.sub_categoria
        ");
        $subCategorias = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $subCategorias[] = [
                'subCategoria' => new SubCategoryModel($row['id'], (int)$row['categoria'], $row['sub_categoria']),
                'categoria_nome' => $row['categoria_nome'],
                'produto_count' => (int)$row['produto_count']
            ];
        }
        return $subCategorias;
    }

    // === MÉTODOS DE CRIAÇÃO ===
    
    public static function create(int $categoriaId, string $nome): ?SubCategoryModel {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("INSERT INTO sub_categoria (categoria, sub_categoria) VALUES (?, ?)");
        if ($stmt->execute([$categoriaId, $nome])) {
            return self::findById((int)$pdo->lastInsertId());
        }
        return null;
    }

    // === MÉTODOS DE ATUALIZAÇÃO ===
    
    public function update(string $nome, ?int $categoriaId = null): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE sub_categoria SET sub_categoria = ?, categoria = ? WHERE id = ?");
        $catId = $categoriaId ?? $this->categoriaId;
        $result = $stmt->execute([$nome, $catId, $this->id]);
        if ($result) {
            $this->subCategoria = $nome;
            $this->categoriaId = $catId;
        }
        return $result;
    }

    /**
     * Remove subcategoria (apenas se não tiver produtos)
     */
    public static function delete(int $id): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        // Verifica se tem produtos
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM produto WHERE sub_categoria = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) return false;
        
        // Remove vínculos com cupons
        $pdo->prepare("DELETE FROM cupom_sub_categoria WHERE sub_categoria_id = ?")->execute([$id]);
        
        $stmt = $pdo->prepare("DELETE FROM sub_categoria WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
