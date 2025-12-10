<?php
/**
 * OrdersController - Gerenciamento de Pedidos (Admin)
 * 
 * @package App\Pages\Controllers\admin
 */

namespace App\Pages\Controllers\admin;

use Core\ViewerPlace;
use App\Product\Models\VendaModel;
use App\User\Models\ClienteModel;

class OrdersController
{
    /**
     * Lista todos os pedidos
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

        $pedidos = VendaModel::findAll();
        $orderRows = $this->generateOrderRows($pedidos);

        echo ViewerPlace::render('admin-tabela-pedidos', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'order_rows' => $orderRows
        ]);
    }

    /**
     * Visualiza detalhes do pedido
     */
    public function show($id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $pedido = VendaModel::findById($id);
        
        if (!$pedido) {
            header('Location: /admin/orders?error=notfound');
            exit;
        }

        // Buscar itens do pedido e cliente
        $cliente = ClienteModel::findById($pedido->getClienteId());
        $itens = $pedido->getItens();
        
        echo ViewerPlace::render('admin-pedido-detalhes', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'order_id' => $pedido->getId(),
            'order_code' => $pedido->getCodigo(),
            'order_date' => date('d/m/Y H:i', strtotime($pedido->getData())),
            'order_total' => 'R$ ' . number_format($pedido->getValorTotal(), 2, ',', '.'),
            'order_status' => $this->getStatusLabel($pedido->getStatus()),
            'customer_name' => $cliente ? htmlspecialchars($cliente->getNome()) : 'N/A',
            'customer_email' => $cliente ? htmlspecialchars($cliente->getEmail()) : 'N/A',
            'order_items' => $this->generateOrderItemsHtml($itens)
        ]);
    }

    /**
     * Atualiza status do pedido
     */
    public function updateStatus($id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $status = $_POST['status'] ?? '';
        
        if (!empty($status)) {
            VendaModel::updateStatus($id, $status);
        }

        header('Location: /admin/orders/' . $id);
        exit;
    }

    private function generateOrderRows(array $pedidos): string
    {
        if (empty($pedidos)) {
            return '<tr><td colspan="6" style="text-align:center;color:#a0a0a0;">Nenhum pedido encontrado</td></tr>';
        }

        $html = '';
        foreach ($pedidos as $p) {
            $id = $p->getId();
            $data = date('d/m/Y H:i', strtotime($p->getData()));
            $valor = 'R$ ' . number_format($p->getValorTotal(), 2, ',', '.');
            $status = $this->getStatusBadge($p->getStatus());
            
            // Buscar nome do cliente
            $cliente = ClienteModel::findById($p->getClienteId());
            $nomeCliente = $cliente ? htmlspecialchars($cliente->getNome()) : 'N/A';
            
            $html .= "<tr>
                <td>#{$id}</td>
                <td>{$data}</td>
                <td>{$nomeCliente}</td>
                <td>{$valor}</td>
                <td>{$status}</td>
                <td class='actions'>
                    <a href='/admin/orders/{$id}' class='btn-view'>Ver Detalhes</a>
                </td>
            </tr>";
        }
        return $html;
    }

    private function generateOrderItemsHtml(array $itens): string
    {
        if (empty($itens)) {
            return '<p>Nenhum item encontrado</p>';
        }

        $html = '<table class="order-items-table"><thead><tr><th>Produto</th><th>Qtd</th><th>Valor</th></tr></thead><tbody>';
        foreach ($itens as $item) {
            $html .= "<tr>
                <td>{$item['produto_nome']}</td>
                <td>{$item['quantidade']}</td>
                <td>R$ " . number_format($item['valor'], 2, ',', '.') . "</td>
            </tr>";
        }
        $html .= '</tbody></table>';
        return $html;
    }

    private function getStatusBadge(string $status): string
    {
        $badges = [
            '1' => '<span class="badge status-active">Ativo</span>',
            '0' => '<span class="badge status-inactive">Cancelado</span>',
            'pendente' => '<span class="badge status-pending">Pendente</span>',
            'processando' => '<span class="badge status-processing">Processando</span>',
            'enviado' => '<span class="badge status-shipped">Enviado</span>',
            'entregue' => '<span class="badge status-delivered">Entregue</span>',
            'cancelado' => '<span class="badge status-cancelled">Cancelado</span>'
        ];
        return $badges[$status] ?? '<span class="badge">' . ucfirst($status) . '</span>';
    }

    private function getStatusLabel(string $status): string
    {
        $labels = [
            '1' => 'Ativo',
            '0' => 'Cancelado',
            'pendente' => 'Pendente',
            'processando' => 'Processando',
            'enviado' => 'Enviado',
            'entregue' => 'Entregue',
            'cancelado' => 'Cancelado'
        ];
        return $labels[$status] ?? ucfirst($status);
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
