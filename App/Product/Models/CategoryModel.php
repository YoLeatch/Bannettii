<?php
/**
 * CategoryModel - Modelo para tabela 'categoria'
 * 
 * Gerencia categorias de produtos.
 * 
 * Tabela: categoria
 * 
 * @package App\Product\Models
 */

namespace App\Product\Models;

use Core\ConnectionFactory;
use PDO;

class CategoryModel {
    /** @var int ID da categoria */
    protected int $id;
    
    /** @var string Nome da categoria */
    protected string $categoria;
    
    /** @var array Subcategorias da categoria */
    protected array $subCategorias = [];

    public function __construct(int $id, string $categoria) {
        $this->id = $id;
        $this->categoria = $categoria;
        $this->subCategorias = $this->loadSubCategorias();
    }

    // === GETTERS ===
    
    public function getId(): int { return $this->id; }
    public function getCategoria(): string { return $this->categoria; }
    public function getSubCategorias(): array { return $this->subCategorias; }

    /**
     * Conta produtos nesta categoria
     */
    public function countProdutos(): int {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM produto WHERE categoria = ? AND status = '1'");
        $stmt->execute([$this->id]);
        return (int)$stmt->fetchColumn();
    }

    // === MÉTODOS DE SUBCATEGORIA ===
    
    private function loadSubCategorias(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT id, sub_categoria FROM sub_categoria WHERE categoria = ?");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === MÉTODOS DE BUSCA ===
    
    public static function findById(int $id): ?CategoryModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM categoria WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new CategoryModel($row['id'], $row['categoria']) : null;
    }

    public static function findByNome(string $nome): ?CategoryModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM categoria WHERE categoria = ?");
        $stmt->execute([$nome]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? new CategoryModel($row['id'], $row['categoria']) : null;
    }

    public static function findAll(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->query("SELECT * FROM categoria ORDER BY categoria");
        $categorias = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categorias[] = new CategoryModel($row['id'], $row['categoria']);
        }
        return $categorias;
    }

    /**
     * Retorna categorias com contagem de produtos
     */
    public static function findAllWithCount(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->query("
            SELECT c.*, COUNT(p.id) as produto_count
            FROM categoria c
            LEFT JOIN produto p ON p.categoria = c.id AND p.status = '1'
            GROUP BY c.id
            ORDER BY c.categoria
        ");
        $categorias = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cat = new CategoryModel($row['id'], $row['categoria']);
            $categorias[] = [
                'categoria' => $cat,
                'produto_count' => (int)$row['produto_count']
            ];
        }
        return $categorias;
    }

    // === MÉTODOS DE CRIAÇÃO ===
    
    public static function create(string $nome): ?CategoryModel {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("INSERT INTO categoria (categoria) VALUES (?)");
        if ($stmt->execute([$nome])) {
            return self::findById((int)$pdo->lastInsertId());
        }
        return null;
    }

    // === MÉTODOS DE ATUALIZAÇÃO ===
    
    public function update(string $nome): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE categoria SET categoria = ? WHERE id = ?");
        $result = $stmt->execute([$nome, $this->id]);
        if ($result) $this->categoria = $nome;
        return $result;
    }

    /**
     * Remove categoria (apenas se não tiver produtos ou subcategorias)
     */
    public static function delete(int $id): bool {
        $pdo = ConnectionFactory::getConnection('default');
        
        // Verifica se tem produtos
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM produto WHERE categoria = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) return false;
        
        // Verifica se tem subcategorias
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM sub_categoria WHERE categoria = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) return false;
        
        $stmt = $pdo->prepare("DELETE FROM categoria WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
