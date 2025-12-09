<?php
/**
 * VendaModel - Modelo para tabela 'venda'
 * 
 * Gerencia vendas e seus itens (produto_vendas).
 * 
 * Tabelas: venda, produto_vendas
 * 
 * @package App\Product\Models
 */

namespace App\Product\Models;

use Core\ConnectionFactory;
use PDO;

class VendaModel {
    /** @var int ID da venda */
    protected int $id;
    
    /** @var int Valor total da venda */
    protected int $valorTotal;
    
    /** @var int ID do cliente */
    protected int $clienteId;
    
    /** @var int ID do endereço de entrega */
    protected int $enderecoId;
    
    /** @var string Status da venda */
    protected string $status;
    
    /** @var string Data da venda */
    protected string $data;
    
    /** @var string Código da venda */
    protected string $cod;
    
    /** @var array Itens da venda (produtos) */
    protected array $itens = [];

    public function __construct(
        int $id, int $valorTotal, int $clienteId, int $enderecoId,
        string $status, string $data, string $cod
    ) {
        $this->id = $id;
        $this->valorTotal = $valorTotal;
        $this->clienteId = $clienteId;
        $this->enderecoId = $enderecoId;
        $this->status = $status;
        $this->data = $data;
        $this->cod = $cod;
        
        $this->itens = $this->loadItens();
    }

    // ============================
    // GETTERS
    // ============================
    
    public function getId(): int { return $this->id; }
    public function getValorTotal(): int { return $this->valorTotal; }
    public function getClienteId(): int { return $this->clienteId; }
    public function getEnderecoId(): int { return $this->enderecoId; }
    public function getStatus(): string { return $this->status; }
    public function getData(): string { return $this->data; }
    public function getCod(): string { return $this->cod; }
    public function getItens(): array { return $this->itens; }

    /**
     * Retorna o valor total formatado em reais
     */
    public function getValorTotalFormatado(): string {
        return 'R$ ' . number_format($this->valorTotal / 100, 2, ',', '.');
    }

    /**
     * Retorna a data formatada
     */
    public function getDataFormatada(): string {
        return date('d/m/Y H:i', strtotime($this->data));
    }

    /**
     * Retorna o status por extenso
     */
    public function getStatusDescritivo(): string {
        return match($this->status) {
            '1' => 'Ativa',
            '0' => 'Cancelada',
            'P' => 'Pendente',
            'E' => 'Em processamento',
            'F' => 'Finalizada',
            default => 'Desconhecido'
        };
    }

    // ============================
    // MÉTODOS DE ITENS
    // ============================
    
    /**
     * Carrega os itens da venda (produtos)
     */
    private function loadItens(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("
            SELECT pv.*, p.nome as produto_nome, p.preco as produto_preco
            FROM produto_vendas pv
            INNER JOIN produto p ON p.id = pv.produto_id
            WHERE pv.vendas_id = ?
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adiciona um item à venda
     */
    public function addItem(int $produtoId, int $pessoaId, int $valor, int $quantidade): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("
            INSERT INTO produto_vendas (produto_id, vendas_id, pessoa_id, valor, quantidade, status)
            VALUES (:produto_id, :vendas_id, :pessoa_id, :valor, :quantidade, '1')
        ");
        $result = $stmt->execute([
            'produto_id' => $produtoId,
            'vendas_id' => $this->id,
            'pessoa_id' => $pessoaId,
            'valor' => $valor,
            'quantidade' => $quantidade
        ]);
        
        if ($result) {
            $this->itens = $this->loadItens();
            $this->recalcularTotal();
        }
        return $result;
    }

    /**
     * Remove um item da venda
     */
    public function removeItem(int $produtoId): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("DELETE FROM produto_vendas WHERE vendas_id = ? AND produto_id = ?");
        $result = $stmt->execute([$this->id, $produtoId]);
        
        if ($result) {
            $this->itens = $this->loadItens();
            $this->recalcularTotal();
        }
        return $result;
    }

    /**
     * Recalcula o valor total da venda com base nos itens
     */
    private function recalcularTotal(): void {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("
            SELECT SUM(valor * quantidade) as total FROM produto_vendas WHERE vendas_id = ?
        ");
        $stmt->execute([$this->id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $novoTotal = (int) ($result['total'] ?? 0);
        
        $stmt = $pdo->prepare("UPDATE venda SET valor_total = ? WHERE id = ?");
        $stmt->execute([$novoTotal, $this->id]);
        $this->valorTotal = $novoTotal;
    }

    // ============================
    // MÉTODOS DE BUSCA
    // ============================
    
    /**
     * Busca venda por ID
     */
    public static function findById(int $id): ?VendaModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM venda WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::createFromRow($row) : null;
    }

    /**
     * Busca venda por código
     */
    public static function findByCod(string $cod): ?VendaModel {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM venda WHERE cod = ?");
        $stmt->execute([$cod]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::createFromRow($row) : null;
    }

    /**
     * Busca todas as vendas de um cliente
     */
    public static function findByCliente(int $clienteId): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM venda WHERE cliente = ? ORDER BY data DESC");
        $stmt->execute([$clienteId]);
        return self::fetchAllRows($stmt);
    }

    /**
     * Busca todas as vendas ativas
     */
    public static function findAllActive(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->query("SELECT * FROM venda WHERE status = '1' ORDER BY data DESC");
        return self::fetchAllRows($stmt);
    }

    /**
     * Busca todas as vendas
     */
    public static function findAll(): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->query("SELECT * FROM venda ORDER BY data DESC");
        return self::fetchAllRows($stmt);
    }

    /**
     * Busca vendas por período
     */
    public static function findByPeriodo(string $dataInicio, string $dataFim): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("
            SELECT * FROM venda 
            WHERE data BETWEEN ? AND ? 
            ORDER BY data DESC
        ");
        $stmt->execute([$dataInicio, $dataFim]);
        return self::fetchAllRows($stmt);
    }

    /**
     * Busca vendas que contêm um produto específico
     */
    public static function findByProduto(int $produtoId): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("
            SELECT v.* FROM venda v
            INNER JOIN produto_vendas pv ON pv.vendas_id = v.id
            WHERE pv.produto_id = ?
            ORDER BY v.data DESC
        ");
        $stmt->execute([$produtoId]);
        return self::fetchAllRows($stmt);
    }

    /**
     * Retorna estatísticas de vendas de um produto
     */
    public static function getStatsByProduto(int $produtoId): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_vendas,
                SUM(quantidade) as total_unidades,
                SUM(valor * quantidade) as total_valor
            FROM produto_vendas
            WHERE produto_id = ?
        ");
        $stmt->execute([$produtoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna histórico de vendas de um produto
     */
    public static function getHistoricoByProduto(int $produtoId): array {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("
            SELECT pv.*, v.data as venda_data, v.cod as venda_cod, p.nome as cliente_nome
            FROM produto_vendas pv
            INNER JOIN venda v ON v.id = pv.vendas_id
            LEFT JOIN pessoa p ON p.id = pv.pessoa_id
            WHERE pv.produto_id = ?
            ORDER BY v.data DESC
        ");
        $stmt->execute([$produtoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica se o usuário comprou um produto específico
     */
    public static function hasPurchased(int $userId, int $produtoId): bool {
        $pdo = ConnectionFactory::getConnection('read_only');
        // Verifica na tabela produto_vendas se há registro para esse usuário e produto
        // com venda não cancelada (status != '0')
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM produto_vendas pv
            INNER JOIN venda v ON v.id = pv.vendas_id
            WHERE pv.pessoa_id = ? AND pv.produto_id = ? AND v.status != '0'
        ");
        $stmt->execute([$userId, $produtoId]);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    // ============================
    // MÉTODOS DE CRIAÇÃO
    // ============================
    
    /**
     * Cria uma nova venda
     */
    public static function create(int $clienteId, int $enderecoId, ?string $cod = null): ?VendaModel {
        $pdo = ConnectionFactory::getConnection('default');
        
        // Gera código se não fornecido
        if (!$cod) {
            $cod = 'VND-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO venda (valor_total, cliente, endereco, status, data, cod)
            VALUES (0, :cliente, :endereco, '1', NOW(), :cod)
        ");
        
        $result = $stmt->execute([
            'cliente' => $clienteId,
            'endereco' => $enderecoId,
            'cod' => $cod
        ]);
        
        return $result ? self::findById((int) $pdo->lastInsertId()) : null;
    }

    // ============================
    // MÉTODOS DE ATUALIZAÇÃO
    // ============================
    
    /**
     * Atualiza o status da venda
     */
    public function updateStatus(string $status): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE venda SET status = ? WHERE id = ?");
        $result = $stmt->execute([$status, $this->id]);
        if ($result) $this->status = $status;
        return $result;
    }

    /**
     * Atualiza o endereço da venda
     */
    public function updateEndereco(int $enderecoId): bool {
        $pdo = ConnectionFactory::getConnection('default');
        $stmt = $pdo->prepare("UPDATE venda SET endereco = ? WHERE id = ?");
        $result = $stmt->execute([$enderecoId, $this->id]);
        if ($result) $this->enderecoId = $enderecoId;
        return $result;
    }

    /**
     * Cancela a venda (soft delete)
     */
    public function cancel(): bool {
        return $this->updateStatus('0');
    }

    /**
     * Finaliza a venda
     */
    public function finalize(): bool {
        return $this->updateStatus('F');
    }

    // ============================
    // MÉTODOS AUXILIARES
    // ============================
    
    /**
     * Cria objeto a partir de uma linha do banco
     */
    private static function createFromRow(array $row): VendaModel {
        return new VendaModel(
            $row['id'],
            (int) $row['valor_total'],
            (int) $row['cliente'],
            (int) $row['endereco'],
            $row['status'],
            $row['data'],
            $row['cod']
        );
    }

    /**
     * Converte resultados do statement em array de VendaModel
     */
    private static function fetchAllRows($stmt): array {
        $vendas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $vendas[] = self::createFromRow($row);
        }
        return $vendas;
    }

    /**
     * Conta total de vendas
     */
    public static function count(): int {
        $pdo = ConnectionFactory::getConnection('read_only');
        return (int) $pdo->query("SELECT COUNT(*) FROM venda")->fetchColumn();
    }

    /**
     * Conta vendas ativas
     */
    public static function countActive(): int {
        $pdo = ConnectionFactory::getConnection('read_only');
        return (int) $pdo->query("SELECT COUNT(*) FROM venda WHERE status = '1'")->fetchColumn();
    }

    /**
     * Retorna total de vendas (valor) no período
     */
    public static function getTotalVendasPeriodo(string $dataInicio, string $dataFim): int {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("
            SELECT SUM(valor_total) FROM venda 
            WHERE data BETWEEN ? AND ? AND status != '0'
        ");
        $stmt->execute([$dataInicio, $dataFim]);
        return (int) $stmt->fetchColumn();
    }
}
