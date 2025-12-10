<?php
/**
 * ReportsController - Relatórios (Admin)
 * 
 * @package App\Pages\Controllers\admin
 */

namespace App\Pages\Controllers\admin;

use Core\ViewerPlace;
use App\Product\Models\VendaModel;
use App\User\Models\ClienteModel;
use App\Product\Models\ProductModel;

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

        // Estatísticas gerais
        $totalVendas = VendaModel::count();
        $vendasHoje = VendaModel::countToday();
        $totalClientes = ClienteModel::count();
        $totalProdutos = ProductModel::count();
        
        // Dados para gráficos
        $vendasPorMes = $this->getVendasPorMes();
        $topProdutos = $this->getTopProdutos();

        echo ViewerPlace::render('admin-reports', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'total_vendas' => $totalVendas,
            'vendas_hoje' => $vendasHoje,
            'total_clientes' => $totalClientes,
            'total_produtos' => $totalProdutos,
            'vendas_por_mes' => json_encode($vendasPorMes),
            'top_produtos' => json_encode($topProdutos)
        ]);
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
        
        if ($tipo === 'vendas') {
            fputcsv($output, ['ID', 'Data', 'Cliente', 'Valor Total', 'Status'], ';');
            
            $vendas = VendaModel::findAll();
            foreach ($vendas as $v) {
                $cliente = ClienteModel::findById($v->getClienteId());
                fputcsv($output, [
                    $v->getId(),
                    date('d/m/Y', strtotime($v->getData())),
                    $cliente ? $cliente->getNome() : 'N/A',
                    number_format($v->getValorTotal(), 2, ',', '.'),
                    $v->getStatus()
                ], ';');
            }
        } elseif ($tipo === 'produtos') {
            fputcsv($output, ['ID', 'Nome', 'Código', 'Preço', 'Estoque', 'Status'], ';');
            
            $produtos = ProductModel::findAll();
            foreach ($produtos as $p) {
                fputcsv($output, [
                    $p->getId(),
                    $p->getNome(),
                    $p->getCodigo(),
                    number_format($p->getPreco(), 2, ',', '.'),
                    $p->getEstoque(),
                    $p->getEstoque() > 0 ? 'Ativo' : 'Esgotado'
                ], ';');
            }
        } elseif ($tipo === 'clientes') {
            fputcsv($output, ['ID', 'Nome', 'Email', 'CPF', 'Data Cadastro'], ';');
            
            $clientes = ClienteModel::findAll();
            foreach ($clientes as $c) {
                fputcsv($output, [
                    $c->getId(),
                    $c->getNome(),
                    $c->getEmail(),
                    $c->getCpf() ?? 'N/A',
                    date('d/m/Y', strtotime($c->getDataCriacao()))
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
