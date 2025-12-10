<?php
/**
 * ReportsController - Relatórios (Admin)
 * 
 * @package App\Pages\Controllers\admin
 */

namespace App\Pages\Controllers\admin;

use Core\ViewerPlace;
use Core\ConnectionFactory;

class ReportsController
{
    /**
     * Página principal de relatórios
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        // Estatísticas gerais - consultas diretas
        $pdo = ConnectionFactory::getConnection('read_only');
        
        // Total de pedidos
        $stmt = $pdo->query("SELECT COUNT(*) FROM venda");
        $totalOrders = $stmt->fetchColumn() ?: 0;
        
        // Total de vendas em valor
        $stmt = $pdo->query("SELECT COALESCE(SUM(valor_total), 0) FROM venda");
        $totalSales = number_format($stmt->fetchColumn() ?: 0, 2, ',', '.');
        
        // Ticket médio
        $stmt = $pdo->query("SELECT COALESCE(AVG(valor_total), 0) FROM venda");
        $avgTicket = number_format($stmt->fetchColumn() ?: 0, 2, ',', '.');
        
        // Top produtos (gera linhas HTML)
        $topProductsRows = $this->generateTopProductsRows($pdo);

        echo ViewerPlace::render('admin-reports', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'totalSales' => $totalSales,
            'avgTicket' => $avgTicket,
            'totalOrders' => $totalOrders,
            'topProductsRows' => $topProductsRows
        ]);
    }
    
    private function generateTopProductsRows($pdo): string
    {
        // Tenta buscar produtos mais vendidos
        $stmt = $pdo->query("
            SELECT p.nome, COALESCE(SUM(pv.quantidade), 0) as qtd, COALESCE(SUM(pv.valor), 0) as total
            FROM produto p 
            LEFT JOIN produto_vendas pv ON p.id = pv.produto_id
            GROUP BY p.id, p.nome
            ORDER BY qtd DESC
            LIMIT 5
        ");
        $produtos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if (empty($produtos)) {
            return '<tr><td colspan="3" style="text-align:center;color:#888;">Nenhum dado disponível</td></tr>';
        }
        
        $html = '';
        foreach ($produtos as $p) {
            $total = number_format($p['total'] ?? 0, 2, ',', '.');
            $html .= "<tr>
                <td>{$p['nome']}</td>
                <td>{$p['qtd']}</td>
                <td>R$ {$total}</td>
            </tr>";
        }
        return $html;
    }

    /**
     * Exporta relatório
     */
    public function export()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $tipo = $_GET['tipo'] ?? 'vendas';
        $periodo = $_GET['periodo'] ?? 'mes';
        
        // Gera CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="relatorio_' . $tipo . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para Excel reconhecer UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        $pdo = ConnectionFactory::getConnection('read_only');
        
        if ($tipo === 'vendas') {
            fputcsv($output, ['ID', 'Data', 'Cliente', 'Valor Total', 'Status'], ';');
            
            $stmt = $pdo->query("SELECT v.*, p.nome as cliente_nome FROM venda v LEFT JOIN pessoa p ON v.cliente = p.id ORDER BY v.data DESC");
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                fputcsv($output, [
                    $row['id'],
                    date('d/m/Y', strtotime($row['data'])),
                    $row['cliente_nome'] ?? 'N/A',
                    number_format($row['valor_total'] ?? 0, 2, ',', '.'),
                    $row['status'] ?? 'N/A'
                ], ';');
            }
        } elseif ($tipo === 'produtos') {
            fputcsv($output, ['ID', 'Nome', 'Código', 'Preço', 'Estoque', 'Status'], ';');
            
            $stmt = $pdo->query("SELECT * FROM produto ORDER BY nome ASC");
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                fputcsv($output, [
                    $row['id'],
                    $row['nome'],
                    $row['codigo'] ?? '',
                    number_format($row['preco'] ?? 0, 2, ',', '.'),
                    $row['estoque'] ?? 0,
                    ($row['estoque'] ?? 0) > 0 ? 'Ativo' : 'Esgotado'
                ], ';');
            }
        } elseif ($tipo === 'clientes') {
            fputcsv($output, ['ID', 'Nome', 'Email', 'CPF', 'Data Cadastro'], ';');
            
            $stmt = $pdo->query("SELECT c.*, p.nome, p.email, p.cpf, p.dt_criacao FROM cliente c JOIN pessoa p ON c.id = p.id ORDER BY p.nome ASC");
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                fputcsv($output, [
                    $row['id'],
                    $row['nome'],
                    $row['email'],
                    $row['cpf'] ?? 'N/A',
                    date('d/m/Y', strtotime($row['dt_criacao']))
                ], ';');
            }
        }
        
        fclose($output);
        exit;
    }

    private function getVendasPorMes(): array
    {
        // Retorna dados mockados - substitua por consulta real
        return [
            ['mes' => 'Jan', 'vendas' => 120],
            ['mes' => 'Fev', 'vendas' => 145],
            ['mes' => 'Mar', 'vendas' => 180],
            ['mes' => 'Abr', 'vendas' => 165],
            ['mes' => 'Mai', 'vendas' => 200],
            ['mes' => 'Jun', 'vendas' => 220],
            ['mes' => 'Jul', 'vendas' => 195],
            ['mes' => 'Ago', 'vendas' => 240],
            ['mes' => 'Set', 'vendas' => 260],
            ['mes' => 'Out', 'vendas' => 230],
            ['mes' => 'Nov', 'vendas' => 300],
            ['mes' => 'Dez', 'vendas' => 350]
        ];
    }

    private function getTopProdutos(): array
    {
        // Retorna dados mockados - substitua por consulta real
        return [
            ['nome' => 'Camiseta Verde', 'vendas' => 85],
            ['nome' => 'Calça Cargo', 'vendas' => 72],
            ['nome' => 'Jaqueta Premium', 'vendas' => 65],
            ['nome' => 'Bolsa Eco', 'vendas' => 58],
            ['nome' => 'Blusa Natureza', 'vendas' => 45]
        ];
    }

    private function getAdminAvatar(): string
    {
        $nome = $_SESSION['admin_name'] ?? 'Admin';
        $partes = explode(' ', $nome);
        $iniciais = strtoupper(substr($partes[0], 0, 1));
        if (count($partes) > 1) {
            $iniciais .= strtoupper(substr(end($partes), 0, 1));
        }
        return $iniciais;
    }
}
