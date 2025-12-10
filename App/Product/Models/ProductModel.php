<?php
/**
 * ProductModel - Modelo para tabela 'produto'
 * 
 * Gerencia produtos com relacionamentos para categoria, subcategoria,
 * imagens, vendas e avaliações.
 * 
 * Tabelas: produto, imagem, produto_vendas, venda
 * 
 * @package App\Product\Models
 */

namespace App\Product\Models;

use Core\ConnectionFactory;
use PDO;

class ProductModel {
    /** @var int ID do produto */
    protected int $id;
    protected string $nome;
    protected float $preco;
    protected string $cod;
    protected int $categoria;
    protected int $sub_categoria;
    protected ?string $pesoliq;
    protected ?string $pesototal;
    protected ?string $dimensoes;
    protected ?string $descricao;
    protected ?string $tamanho;
    protected ?string $cor;
    protected ?string $material;
    protected string $data;
    protected string $status;
    protected ?int $desconto;
    protected ?int $estoque;
    
    /** @var array Imagens do produto */
    protected array $imagens = [];
    
    /** @var array Vendas do produto */
    protected array $vendas = [];
    
    /** @var array Avaliações do produto */
    protected array $avaliacoes = [];

    public function __construct(
        int $id, string $nome, float $preco, string $cod,
        int $categoria, int $sub_categoria, ?string $pesoliq,
        ?string $pesototal, ?string $dimensoes, ?string $descricao,
        ?string $tamanho, ?string $cor, ?string $material,
        string $data, string $status, ?int $desconto, ?int $estoque
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->preco = $preco;
        $this->cod = $cod;
        $this->categoria = $categoria;
        $this->sub_categoria = $sub_categoria;
        $this->pesoliq = $pesoliq;
        $this->pesototal = $pesototal;
        $this->dimensoes = $dimensoes;
        $this->descricao = $descricao;
        $this->tamanho = $tamanho;
        $this->cor = $cor;
        $this->material = $material;
        $this->data = $data;
        $this->status = $status;
        $this->desconto = $desconto;
        $this->estoque = $estoque;
        
        $this->imagens = $this->loadImagens();
        $this->avaliacoes = $this->loadAvaliacoes();
    }

    // === GETTERS ===
    public function getId(): int { return $this->id; }
    public function getNome(): string { return $this->nome; }
    public function getPreco(): float { return $this->preco; }
    public function getCod(): string { return $this->cod; }
    public function getCategoria(): int { return $this->categoria; }
    public function getSubCategoria(): int { return $this->sub_categoria; }
    public function getPesoLiq(): ?string { return $this->pesoliq; }
    public function getPesoTotal(): ?string { return $this->pesototal; }
    public function getDimensoes(): ?string { return $this->dimensoes; }
    public function getDescricao(): ?string { return $this->descricao; }
    public function getTamanho(): ?string { return $this->tamanho; }
    public function getCor(): ?string { return $this->cor; }
    public function getMaterial(): ?string { return $this->material; }
    public function getData(): string { return $this->data; }
    public function getStatus(): string { return $this->status; }
    public function getDesconto(): ?int { return $this->desconto; }
    public function getEstoque(): ?int { return $this->estoque; }
    public function getImagens(): array { return $this->imagens; }
    public function getAvaliacoes(): array { return $this->avaliacoes; }

    /**
     * Retorna preço com desconto aplicado
     */
    public function getPrecoFinal(): float {
        if ($this->desconto && $this->desconto > 0) {
            return $this->preco * (1 - $this->desconto / 100);
        }
        return $this->preco;
    }

    /**
     * Verifica se produto está em estoque
     */
    public function inStock(): bool {
        return $this->estoque !== null && $this->estoque > 0;
    }

    /**
     * Retorna média das avaliações
     */
    public function getMediaAvaliacoes(): float {
        if (empty($this->avaliacoes)) return 0;
        $soma = array_sum(array_column($this->avaliacoes, 'avalaliacao_num'));
        return $soma / count($this->avaliacoes);
    }

    // === MÉTODOS DE IMAGENS ===
    
    private function loadImagens(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT id, imagem FROM imagem WHERE produto = ?");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addImagem(string $imagemPath): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("INSERT INTO imagem (imagem, produto) VALUES (?, ?)");
        $result = $stmt->execute([$imagemPath, $this->id]);
        if ($result) $this->imagens = $this->loadImagens();
        return $result;
    }

    public function removeImagem(int $imagemId): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("DELETE FROM imagem WHERE id = ? AND produto = ?");
        $result = $stmt->execute([$imagemId, $this->id]);
        if ($result) $this->imagens = $this->loadImagens();
        return $result;
    }

    // === MÉTODOS DE AVALIAÇÕES (via produto_vendas) ===
    
    private function loadAvaliacoes(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("
            SELECT pv.*, p.nome as cliente_nome
            FROM produto_vendas pv
            LEFT JOIN pessoa p ON p.id = pv.pessoa_id
            WHERE pv.produto_id = ? AND pv.avalaliacao_num IS NOT NULL
            ORDER BY pv.avaliacao_data DESC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === MÉTODOS DE VENDAS (delegados ao VendaModel) ===
    
    /**
     * Retorna estatísticas de vendas do produto
     * @see VendaModel::getStatsByProduto()
     */
    public function getVendasStats(): array {
        return VendaModel::getStatsByProduto($this->id);
    }

    /**
     * Retorna histórico de vendas detalhado
     * @see VendaModel::getHistoricoByProduto()
     */
    public function getHistoricoVendas(): array {
        return VendaModel::getHistoricoByProduto($this->id);
    }

    // === MÉTODOS DE BUSCA ===
    
    public static function findById(int $id): ?ProductModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM produto WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::createFromRow($row) : null;
    }

    public static function findByCod(string $cod): ?ProductModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM produto WHERE cod = ? AND status = '1'");
        $stmt->execute([$cod]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::createFromRow($row) : null;
    }

    public static function findAll(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->query("SELECT * FROM produto WHERE status = '1' ORDER BY nome");
        $produtos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $produtos[] = self::createFromRow($row);
        }
        return $produtos;
    }

    public static function findByCategoria(int $categoriaId): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM produto WHERE categoria = ? AND status = '1' ORDER BY nome");
        $stmt->execute([$categoriaId]);
        $produtos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $produtos[] = self::createFromRow($row);
        }
        return $produtos;
    }

    public static function findBySubCategoria(int $subCategoriaId): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM produto WHERE sub_categoria = ? AND status = '1' ORDER BY nome");
        $stmt->execute([$subCategoriaId]);
        $produtos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $produtos[] = self::createFromRow($row);
        }
        return $produtos;
    }

    public static function search(string $termo): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM produto WHERE (nome LIKE ? OR descricao LIKE ?) AND status = '1'");
        $stmt->execute(["%$termo%", "%$termo%"]);
        $produtos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $produtos[] = self::createFromRow($row);
        }
        return $produtos;
    }

    private static function createFromRow(array $row): ProductModel {
        return new ProductModel(
            $row['id'], $row['nome'], (float)$row['preco'], $row['cod'],
            (int)$row['categoria'], (int)$row['sub_categoria'],
            $row['pesoliq'], $row['pesototal'], $row['dimensoes'],
            $row['descricao'], $row['tamanho'] ?? null, $row['cor'] ?? null,
            $row['material'] ?? null, $row['data'], $row['status'],
            $row['desconto'] ?? null, $row['estoque'] ?? null
        );
    }

    // === MÉTODOS DE CRIAÇÃO ===
    
    public static function create(array $data): ?ProductModel {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("
            INSERT INTO produto (nome, preco, cod, categoria, sub_categoria, pesoliq, 
                pesototal, dimensoes, descricao, tamanho, cor, material, data, status, desconto, estoque)
            VALUES (:nome, :preco, :cod, :categoria, :sub_categoria, :pesoliq,
                :pesototal, :dimensoes, :descricao, :tamanho, :cor, :material, NOW(), '1', :desconto, :estoque)
        ");
        $result = $stmt->execute([
            'nome' => $data['nome'],
            'preco' => $data['preco'],
            'cod' => $data['cod'],
            'categoria' => $data['categoria'],
            'sub_categoria' => $data['sub_categoria'],
            'pesoliq' => $data['pesoliq'] ?? null,
            'pesototal' => $data['pesototal'] ?? null,
            'dimensoes' => $data['dimensoes'] ?? null,
            'descricao' => $data['descricao'] ?? null,
            'tamanho' => $data['tamanho'] ?? null,
            'cor' => $data['cor'] ?? null,
            'material' => $data['material'] ?? null,
            'desconto' => $data['desconto'] ?? null,
            'estoque' => $data['estoque'] ?? null
        ]);
        return $result ? self::findById((int)$pdo->lastInsertId()) : null;
    }

    // === MÉTODOS DE ATUALIZAÇÃO ===
    
    public function update(array $data): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("
            UPDATE produto SET nome = :nome, preco = :preco, cod = :cod,
                categoria = :categoria, sub_categoria = :sub_categoria,
                pesoliq = :pesoliq, pesototal = :pesototal, dimensoes = :dimensoes,
                descricao = :descricao, tamanho = :tamanho, cor = :cor, material = :material,
                desconto = :desconto, estoque = :estoque
            WHERE id = :id
        ");
        return $stmt->execute([
            'id' => $this->id,
            'nome' => $data['nome'] ?? $this->nome,
            'preco' => $data['preco'] ?? $this->preco,
            'cod' => $data['cod'] ?? $this->cod,
            'categoria' => $data['categoria'] ?? $this->categoria,
            'sub_categoria' => $data['sub_categoria'] ?? $this->sub_categoria,
            'pesoliq' => $data['pesoliq'] ?? $this->pesoliq,
            'pesototal' => $data['pesototal'] ?? $this->pesototal,
            'dimensoes' => $data['dimensoes'] ?? $this->dimensoes,
            'descricao' => $data['descricao'] ?? $this->descricao,
            'tamanho' => $data['tamanho'] ?? $this->tamanho,
            'cor' => $data['cor'] ?? $this->cor,
            'material' => $data['material'] ?? $this->material,
            'desconto' => $data['desconto'] ?? $this->desconto,
            'estoque' => $data['estoque'] ?? $this->estoque
        ]);
    }

    public function updateEstoque(int $quantidade): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE produto SET estoque = :estoque WHERE id = :id");
        return $stmt->execute(['estoque' => $quantidade, 'id' => $this->id]);
    }

    public function deactivate(): bool {
        $pdo = ConnectionFactory::getConnection('default');
        return $pdo->prepare("UPDATE produto SET status = '0' WHERE id = ?")->execute([$this->id]);
    }

    public function activate(): bool {
        $pdo = ConnectionFactory::getConnection('default');
        return $pdo->prepare("UPDATE produto SET status = '1' WHERE id = ?")->execute([$this->id]);
    }

    /**
     * Remove produto (soft delete - seta status = '0')
     * 
     * Como a tabela produto possui campo status, o delete
     * apenas desativa o registro. As imagens são mantidas.
     */
    public static function delete(int $id): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE produto SET status = '0' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Remove uma imagem do produto (DELETE real - tabela imagem não possui status)
     */
    public static function deleteImagem(int $imagemId): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("DELETE FROM imagem WHERE id = ?");
        return $stmt->execute([$imagemId]);
    }
}

