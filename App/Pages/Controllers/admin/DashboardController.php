<?php
/**
 * DashboardController - Controlador do painel administrativo
 * 
 * Gerencia a exibição do dashboard com estatísticas e dados resumidos.
 * 
 * @package App\Pages\Controllers\admin
 */

namespace App\Pages\Controllers\admin;

use Core\ViewerPlace;
use App\User\Models\FuncionarioModel;
use App\User\Models\ClienteModel;
use App\Product\Models\ProductModel;
use App\Services\Models\VendaModel;

class DashboardController
{
    /**
     * Exibe o dashboard principal do admin
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verifica se é admin
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }
        
        // Dados do admin logado
        $adminId = $_SESSION['admin_id'] ?? 0;
        $adminName = $_SESSION['admin_name'] ?? 'Administrador';
        
        // Busca informações do funcionário
        $funcionario = FuncionarioModel::findById($adminId);
        $adminRole = 'Administrador';
        $adminAvatar = '';
        
        if ($funcionario) {
            $cargos = $funcionario->getCargos();
            if (!empty($cargos)) {
                $adminRole = $cargos[0]['cargo'] ?? $cargos[0]['nome'] ?? 'Administrador';
            }
            
            // Avatar do admin (iniciais como padrão)
            $partes = explode(' ', $adminName);
            $iniciais = strtoupper(substr($partes[0], 0, 1));
            if (count($partes) > 1) {
                $iniciais .= strtoupper(substr(end($partes), 0, 1));
            }
            
            // Verifica se tem imagem de perfil
            $image = $funcionario->getImage();
            if ($image && !empty($image)) {
                $adminAvatar = '<img src="' . htmlspecialchars($image) . '" alt="' . htmlspecialchars($adminName) . '">';
            } else {
                $adminAvatar = $iniciais;
            }
        }
        
        // Estatísticas
        $salesToday = $this->getSalesToday();
        $pendingOrders = $this->getPendingOrders();
        $newCustomers = $this->getNewCustomersToday();
        $avgRating = $this->getAverageRating();
        
        // Pedidos recentes
        $recentOrdersRows = $this->getRecentOrdersRows();
        
        // Dados para gráficos
        $salesChartData = $this->getSalesChartData();
        $topProductsData = $this->getTopProductsData();
        
        echo ViewerPlace::render('admin-index', [
            'admin_avatar' => $adminAvatar,
            'admin_name' => htmlspecialchars($adminName),
            'admin_role' => htmlspecialchars($adminRole),
            'sales_today' => 'R$ ' . number_format($salesToday, 2, ',', '.'),
            'pending_orders' => $pendingOrders,
            'new_customers' => $newCustomers,
            'avg_rating' => number_format($avgRating, 1) . '/5',
            'recent_orders_rows' => $recentOrdersRows,
            // Dados dos gráficos (JSON)
            'sales_chart_data' => json_encode($salesChartData['data']),
            'sales_chart_labels' => json_encode($salesChartData['labels']),
            'top_products_labels' => json_encode($topProductsData['labels']),
            'top_products_data' => json_encode($topProductsData['data'])
        ]);
    }
    
    /**
     * Retorna o valor total de vendas de hoje
     */
    private function getSalesToday(): float
    {
        try {
            // Usa VendaModel para buscar vendas reais
            $totalCentavos = \App\Product\Models\VendaModel::getSalesToday();
            return $totalCentavos / 100; // Converte centavos para reais
        } catch (\Exception $e) {
            return 0.0;
        }
    }
    
    /**
     * Retorna a quantidade de pedidos pendentes
     */
    private function getPendingOrders(): int
    {
        try {
            return \App\Product\Models\VendaModel::countPending();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Retorna a quantidade de novos clientes hoje
     */
    private function getNewCustomersToday(): int
    {
        try {
            return \App\User\Models\ClienteModel::countCreatedToday();
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Retorna a média das avaliações
     */
    private function getAverageRating(): float
    {
        try {
            return \App\Product\Models\VendaModel::getAverageRating();
        } catch (\Exception $e) {
            return 0.0;
        }
    }
    
    /**
     * Gera as linhas HTML da tabela de pedidos recentes
     */
    private function getRecentOrdersRows(): string
    {
        try {
            $recentOrders = \App\Product\Models\VendaModel::findRecent(5);
            
            if (empty($recentOrders)) {
                return '<tr><td colspan="6" style="text-align:center;padding:2rem;">Nenhum pedido encontrado</td></tr>';
            }
            
            $html = '';
            foreach ($recentOrders as $orderData) {
                $venda = $orderData['venda'];
                $clienteNome = htmlspecialchars($orderData['cliente_nome']);
                $id = '#' . $venda->getCod();
                $data = $venda->getDataFormatada();
                $valor = $venda->getValorTotalFormatado();
                $statusText = $venda->getStatusDescritivo();
                
                // Define classe CSS baseada no status
                $statusClass = match($venda->getStatus()) {
                    'P', '1' => 'pending',
                    'E' => 'processing',
                    'F' => 'delivered',
                    '0' => 'cancelled',
                    default => 'neutral'
                };
                
                $html .= <<<HTML
            <tr>
                <td>{$id}</td>
                <td>{$clienteNome}</td>
                <td>{$data}</td>
                <td>{$valor}</td>
                <td><span class="status-badge {$statusClass}">{$statusText}</span></td>
                <td>
                    <a href="/admin/orders/show/{$venda->getId()}" class="btn-view">Ver</a>
                </td>
            </tr>
HTML;
            }
            
            return $html;
        } catch (\Exception $e) {
            return '<tr><td colspan="6" style="text-align:center;padding:2rem;">Erro ao carregar pedidos</td></tr>';
        }
    }

    /**
     * Retorna dados para o gráfico de vendas (últimos 7 dias)
     */
    private function getSalesChartData(): array
    {
        try {
            $salesData = \App\Product\Models\VendaModel::getSalesLastDays(7);
            
            // Prepara os últimos 7 dias
            $labels = [];
            $data = [];
            $diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $dayOfWeek = date('w', strtotime($date));
                $labels[] = $diasSemana[$dayOfWeek];
                
                // Procura se há vendas nesse dia
                $found = false;
                foreach ($salesData as $sale) {
                    if ($sale['dia'] === $date) {
                        $data[] = round($sale['total'] / 100, 2); // Centavos para reais
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $data[] = 0;
                }
            }
            
            return ['labels' => $labels, 'data' => $data];
        } catch (\Exception $e) {
            return [
                'labels' => ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                'data' => [0, 0, 0, 0, 0, 0, 0]
            ];
        }
    }

    /**
     * Retorna dados dos produtos mais vendidos
     */
    private function getTopProductsData(): array
    {
        try {
            $pdo = \Core\ConnectionFactory::getConnection('read_only');
            $stmt = $pdo->query("
                SELECT p.nome, SUM(pv.quantidade) as total
                FROM produto_vendas pv
                INNER JOIN produto p ON p.id = pv.produto_id
                GROUP BY pv.produto_id
                ORDER BY total DESC
                LIMIT 5
            ");
            
            $labels = [];
            $data = [];
            
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $labels[] = $row['nome'];
                $data[] = (int) $row['total'];
            }
            
            // Se não houver dados, retorna placeholder
            if (empty($labels)) {
                return [
                    'labels' => ['Sem dados'],
                    'data' => [1]
                ];
            }
            
            return ['labels' => $labels, 'data' => $data];
        } catch (\Exception $e) {
            return [
                'labels' => ['Sem dados'],
                'data' => [1]
            ];
        }
    }
}
