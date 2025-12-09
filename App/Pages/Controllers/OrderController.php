<?php
/**
 * OrderController - Controlador de pedidos do usuário
 * 
 * Gerencia a visualização de pedidos e seus detalhes.
 * 
 * @package App\Pages\Controllers
 */

namespace App\Pages\Controllers;

use Core\ViewerPlace;
use App\Product\Models\VendaModel;
use App\Product\Models\ProductModel;
use App\Services\Models\EnderecoModel;

class OrderController {
    
    /**
     * Lista todos os pedidos do usuário
     */
    public function handle() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        // Busca pedidos do usuário
        $pedidos = VendaModel::findByCliente($userId);
        
        // Gera HTML da lista de pedidos
        $pedidosHtml = $this->buildPedidosListHtml($pedidos);
        
        // Mensagens de feedback
        $successMessage = $_SESSION['ORDER_SUCCESS'] ?? '';
        $errorMessage = $_SESSION['ORDER_ERROR'] ?? '';
        unset($_SESSION['ORDER_SUCCESS'], $_SESSION['ORDER_ERROR']);
        
        echo ViewerPlace::render('meus-pedidos', [
            'pedidos_list' => $pedidosHtml,
            'total_pedidos' => count($pedidos),
            'success_message' => $successMessage ? '<div class="success-message">' . htmlspecialchars($successMessage) . '</div>' : '',
            'error_message' => $errorMessage ? '<div class="error-message">' . htmlspecialchars($errorMessage) . '</div>' : ''
        ]);
    }
    
    /**
     * Exibe detalhes de um pedido específico
     */
    public function detalhes($id = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        $pedidoId = (int) ($id ?? $_GET['id'] ?? 0);
        
        if ($pedidoId <= 0) {
            $_SESSION['ORDER_ERROR'] = 'Pedido inválido.';
            header('Location: /pedidos');
            exit;
        }
        
        // Busca o pedido
        $pedido = VendaModel::findById($pedidoId);
        
        if (!$pedido || $pedido->getClienteId() !== $userId) {
            $_SESSION['ORDER_ERROR'] = 'Pedido não encontrado.';
            header('Location: /pedidos');
            exit;
        }
        
        // Carrega itens do pedido
        $itens = $pedido->getItens();
        
        // Busca endereço
        $endereco = EnderecoModel::findById($pedido->getEnderecoId());
        
        // Gera HTML dos itens
        $itensHtml = $this->buildItensHtml($itens);
        
        // Gera HTML do endereço
        $enderecoHtml = $endereco ? $this->buildEnderecoHtml($endereco) : '<p class="no-data">Endereço não encontrado</p>';
        
        // Timeline baseada no status
        $timelineHtml = $this->buildTimelineHtml($pedido->getStatus());
        
        echo ViewerPlace::render('pedido-detalhes', [
            'pedido_id' => $pedido->getId(),
            'pedido_codigo' => htmlspecialchars($pedido->getCod()),
            'pedido_data' => $pedido->getDataFormatada(),
            'pedido_status' => $pedido->getStatusDescritivo(),
            'pedido_status_class' => $this->getStatusClass($pedido->getStatus()),
            'pedido_valor' => $pedido->getValorTotalFormatado(),
            'itens_html' => $itensHtml,
            'endereco_html' => $enderecoHtml,
            'timeline_html' => $timelineHtml,
            'total_itens' => count($itens)
        ]);
    }
    
    /**
     * Cancela um pedido
     */
    public function cancelar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        $pedidoId = (int) ($_POST['pedido_id'] ?? 0);
        
        if ($pedidoId <= 0) {
            $_SESSION['ORDER_ERROR'] = 'Pedido inválido.';
            header('Location: /pedidos');
            exit;
        }
        
        $pedido = VendaModel::findById($pedidoId);
        
        if (!$pedido || $pedido->getClienteId() !== $userId) {
            $_SESSION['ORDER_ERROR'] = 'Pedido não encontrado.';
            header('Location: /pedidos');
            exit;
        }
        
        // Verifica se pode cancelar (apenas pedidos pendentes ou em processamento)
        if (!in_array($pedido->getStatus(), ['1', 'P', 'E'])) {
            $_SESSION['ORDER_ERROR'] = 'Este pedido não pode ser cancelado.';
            header('Location: /pedidos/' . $pedidoId);
            exit;
        }
        
        if ($pedido->cancel()) {
            $_SESSION['ORDER_SUCCESS'] = 'Pedido cancelado com sucesso.';
        } else {
            $_SESSION['ORDER_ERROR'] = 'Erro ao cancelar pedido.';
        }
        
        header('Location: /pedidos');
        exit;
    }
    
    // ========== MÉTODOS PRIVADOS ==========
    
    private function buildPedidosListHtml(array $pedidos): string {
        if (empty($pedidos)) {
            return '
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.5">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
                <h3>Nenhum pedido encontrado</h3>
                <p>Você ainda não fez nenhum pedido.</p>
                <a href="/catalogo" class="btn-primary">Ir às Compras</a>
            </div>';
        }
        
        $html = '<div class="orders-list">';
        
        foreach ($pedidos as $pedido) {
            $statusClass = $this->getStatusClass($pedido->getStatus());
            
            $html .= '
            <div class="order-card">
                <div class="order-header">
                    <div class="order-info">
                        <h3>Pedido ' . htmlspecialchars($pedido->getCod()) . '</h3>
                        <span class="order-date">' . $pedido->getDataFormatada() . '</span>
                    </div>
                    <span class="order-status ' . $statusClass . '">' . $pedido->getStatusDescritivo() . '</span>
                </div>
                <div class="order-body">
                    <div class="order-value">
                        <span>Valor total</span>
                        <strong>' . $pedido->getValorTotalFormatado() . '</strong>
                    </div>
                    <div class="order-actions">
                        <a href="/pedidos/' . $pedido->getId() . '" class="btn-details">Ver Detalhes</a>
                    </div>
                </div>
            </div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    private function buildItensHtml(array $itens): string {
        if (empty($itens)) {
            return '<p class="no-data">Nenhum item encontrado.</p>';
        }
        
        $html = '<div class="items-list">';
        
        foreach ($itens as $item) {
            $produto = ProductModel::findById($item['produto_id']);
            $imagem = '/assets/image/placeholder.png';
            $nome = $item['produto_nome'] ?? 'Produto';
            
            if ($produto) {
                $imagens = $produto->getImagens();
                if (!empty($imagens)) {
                    $imagem = $imagens[0]['imagem'];
                }
            }
            
            $valorItem = ($item['valor'] ?? 0) / 100;
            $quantidade = $item['quantidade'] ?? 1;
            
            $html .= '
            <div class="order-item">
                <div class="item-image">
                    <img src="' . htmlspecialchars($imagem) . '" alt="' . htmlspecialchars($nome) . '">
                </div>
                <div class="item-info">
                    <h4>' . htmlspecialchars($nome) . '</h4>
                    <p>Quantidade: ' . $quantidade . '</p>
                </div>
                <div class="item-value">
                    R$ ' . number_format($valorItem, 2, ',', '.') . '
                </div>
            </div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    private function buildEnderecoHtml(EnderecoModel $endereco): string {
        $cidade = $endereco->getCidade()['cidade'] ?? '';
        $estado = $endereco->getEstado()['estado'] ?? '';
        
        return '
        <div class="address-card">
            <div class="address-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
            </div>
            <div class="address-info">
                <strong>' . htmlspecialchars($endereco->getLogradouro()) . '</strong>
                <span>' . htmlspecialchars($cidade) . ' - ' . htmlspecialchars($estado) . '</span>
                <span class="address-cep">CEP: ' . htmlspecialchars($endereco->getCepFormatado()) . '</span>
            </div>
        </div>';
    }
    
    private function buildTimelineHtml(string $status): string {
        $steps = [
            ['key' => 'confirmed', 'label' => 'Confirmado', 'icon' => '✓'],
            ['key' => 'preparing', 'label' => 'Preparando', 'icon' => '2'],
            ['key' => 'shipped', 'label' => 'Enviado', 'icon' => '3'],
            ['key' => 'delivered', 'label' => 'Entregue', 'icon' => '4']
        ];
        
        // Determina qual step está ativo
        $activeStep = match($status) {
            '1', 'P' => 0, // Confirmado/Pendente
            'E' => 1,      // Em processamento
            'F' => 3,      // Finalizado (entregue)
            '0' => -1,     // Cancelado
            default => 0
        };
        
        if ($status === '0') {
            return '
            <div class="order-cancelled">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
                    stroke="#f87171" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <p>Este pedido foi cancelado</p>
            </div>';
        }
        
        $html = '<div class="order-timeline">';
        
        foreach ($steps as $index => $step) {
            $isActive = $index <= $activeStep;
            $activeClass = $isActive ? 'active' : '';
            $icon = $isActive && $index < $activeStep ? '✓' : $step['icon'];
            
            $html .= '
            <div class="timeline-step ' . $activeClass . '">
                <div class="step-circle">' . $icon . '</div>
                <span class="step-label">' . $step['label'] . '</span>
            </div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
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
