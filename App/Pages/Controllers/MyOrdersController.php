<?php
/**
 * MyOrdersController - Controlador de pedidos do usuário
 * 
 * Exibe o histórico de pedidos do usuário logado.
 * 
 * @package App\Pages\Controllers
 */

namespace App\Pages\Controllers;

use Core\ViewerPlace;
use App\Product\Models\VendaModel;

class MyOrdersController {
    
    /**
     * Exibe o histórico de pedidos do usuário
     */
    public function handle() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'];
        
        // Busca os pedidos do cliente
        $vendas = VendaModel::findByCliente($userId);
        
        // Gera o HTML da lista de pedidos
        $ordersHtml = $this->buildOrdersList($vendas);
        
        // Renderiza a view
        echo ViewerPlace::render('historico-pedidos', [
            'orders_list' => $ordersHtml
        ]);
    }
    
    /**
     * Exibe detalhes de um pedido específico
     */
    public function show(int $id) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'];
        $venda = VendaModel::findById($id);
        
        // Verifica se o pedido pertence ao usuário
        if (!$venda || $venda->getClienteId() !== $userId) {
            $_SESSION['ERROR'] = 'Pedido não encontrado.';
            header("Location: /meus-pedidos");
            exit;
        }
        
        // Gera HTML detalhado do pedido
        $orderDetailHtml = $this->buildOrderDetail($venda);
        
        echo ViewerPlace::render('historico-pedidos', [
            'orders_list' => $orderDetailHtml
        ]);
    }
    
    /**
     * Constrói o HTML da lista de pedidos
     */
    private function buildOrdersList(array $vendas): string {
        if (empty($vendas)) {
            return '
            <div class="empty-orders" style="text-align: center; padding: 3rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" 
                    stroke="#666" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
                <h3 style="color: #fff; margin-top: 1rem;">Nenhum pedido encontrado</h3>
                <p style="color: #888;">Você ainda não realizou nenhuma compra.</p>
                <a href="/catalogo" style="display: inline-block; margin-top: 1rem; padding: 0.8rem 1.5rem; 
                    background: linear-gradient(135deg, #D0D558, #a8ad3f); color: #1a1a2e; 
                    text-decoration: none; border-radius: 8px; font-weight: 600;">
                    Explorar Produtos
                </a>
            </div>';
        }
        
        $html = '';
        foreach ($vendas as $venda) {
            $statusClass = $this->getStatusClass($venda->getStatus());
            $statusText = $venda->getStatusDescritivo();
            $itens = $venda->getItens();
            $totalItens = count($itens);
            
            $html .= '
            <div class="order-card" style="background: linear-gradient(145deg, #1a1a2e, #16213e); 
                border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem; 
                border: 1px solid rgba(208, 213, 88, 0.1);">
                <div class="order-header" style="display: flex; justify-content: space-between; 
                    align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <span style="color: #888; font-size: 0.9rem;">Pedido</span>
                        <h3 style="color: #D0D558; margin: 0; font-size: 1.1rem;">#' . htmlspecialchars($venda->getCod()) . '</h3>
                    </div>
                    <span class="status-badge ' . $statusClass . '" style="padding: 0.4rem 0.8rem; 
                        border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                        ' . $statusText . '
                    </span>
                </div>
                
                <div class="order-info" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); 
                    gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <span style="color: #888; font-size: 0.8rem; display: block;">Data</span>
                        <span style="color: #fff;">' . $venda->getDataFormatada() . '</span>
                    </div>
                    <div>
                        <span style="color: #888; font-size: 0.8rem; display: block;">Itens</span>
                        <span style="color: #fff;">' . $totalItens . ' produto(s)</span>
                    </div>
                    <div>
                        <span style="color: #888; font-size: 0.8rem; display: block;">Total</span>
                        <span style="color: #D0D558; font-weight: 600;">' . $venda->getValorTotalFormatado() . '</span>
                    </div>
                </div>
                
                <div class="order-actions" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="/meus-pedidos/' . $venda->getId() . '" 
                        style="padding: 0.6rem 1rem; background: rgba(208, 213, 88, 0.1); 
                        color: #D0D558; text-decoration: none; border-radius: 6px; font-size: 0.9rem;
                        border: 1px solid rgba(208, 213, 88, 0.3); transition: all 0.3s;">
                        Ver Detalhes
                    </a>
                </div>
            </div>';
        }
        
        return $html;
    }
    
    /**
     * Constrói o HTML detalhado de um pedido
     */
    private function buildOrderDetail(VendaModel $venda): string {
        $itens = $venda->getItens();
        
        $html = '
        <a href="/meus-pedidos" style="color: #D0D558; text-decoration: none; margin-bottom: 1rem; display: inline-block;">
            ← Voltar aos pedidos
        </a>
        
        <div class="order-detail" style="background: linear-gradient(145deg, #1a1a2e, #16213e); 
            border-radius: 12px; padding: 2rem; border: 1px solid rgba(208, 213, 88, 0.1);">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <span style="color: #888;">Pedido</span>
                    <h2 style="color: #D0D558; margin: 0;">#' . htmlspecialchars($venda->getCod()) . '</h2>
                </div>
                <span class="status-badge ' . $this->getStatusClass($venda->getStatus()) . '" 
                    style="padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600;">
                    ' . $venda->getStatusDescritivo() . '
                </span>
            </div>
            
            <div style="margin-bottom: 2rem;">
                <h3 style="color: #fff; margin-bottom: 1rem;">Itens do Pedido</h3>';
        
        if (empty($itens)) {
            $html .= '<p style="color: #888;">Nenhum item encontrado.</p>';
        } else {
            foreach ($itens as $item) {
                $html .= '
                <div style="display: flex; justify-content: space-between; align-items: center; 
                    padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 8px; margin-bottom: 0.5rem;">
                    <div>
                        <span style="color: #fff;">' . htmlspecialchars($item['produto_nome'] ?? 'Produto') . '</span>
                        <span style="color: #888; display: block; font-size: 0.9rem;">
                            Qtd: ' . $item['quantidade'] . '
                        </span>
                    </div>
                    <span style="color: #D0D558; font-weight: 600;">
                        R$ ' . number_format(($item['valor'] * $item['quantidade']) / 100, 2, ',', '.') . '
                    </span>
                </div>';
            }
        }
        
        $html .= '
            </div>
            
            <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: #fff; font-size: 1.2rem;">Total</span>
                    <span style="color: #D0D558; font-size: 1.5rem; font-weight: 700;">
                        ' . $venda->getValorTotalFormatado() . '
                    </span>
                </div>
            </div>
        </div>';
        
        return $html;
    }
    
    /**
     * Retorna a classe CSS para o status
     */
    private function getStatusClass(string $status): string {
        return match($status) {
            '1' => 'status-active',
            '0' => 'status-cancelled',
            'P' => 'status-pending',
            'E' => 'status-processing',
            'F' => 'status-completed',
            default => 'status-unknown'
        };
    }
}
