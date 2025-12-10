<?php
/**
 * CustomersController - Gerenciamento de Clientes (Admin)
 * 
 * @package App\Pages\Controllers\admin
 */

namespace App\Pages\Controllers\admin;

use Core\ViewerPlace;
use App\User\Models\ClienteModel;
use App\User\Models\PessoaModel;

class CustomersController
{
    /**
     * Lista todos os clientes
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

        $clientes = ClienteModel::findAll();
        $customerRows = $this->generateCustomerRows($clientes);

        echo ViewerPlace::render('admin-tabela-clientes', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'customer_rows' => $customerRows
        ]);
    }

    /**
     * Busca clientes
     */
    public function search()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $termo = trim($_GET['q'] ?? '');
        $clientes = [];
        
        if (!empty($termo)) {
            $clientes = ClienteModel::search($termo);
        }
        
        $customerRows = $this->generateCustomerRows($clientes);

        echo ViewerPlace::render('admin-tabela-clientes', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'customer_rows' => $customerRows,
            'search_term' => htmlspecialchars($termo)
        ]);
    }

    /**
     * Visualiza detalhes do cliente
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

        $cliente = ClienteModel::findById($id);
        
        if (!$cliente) {
            header('Location: /admin/customers?error=notfound');
            exit;
        }

        // Busca dados adicionais
        $enderecos = $cliente->getAddresses();
        $telefones = $cliente->getTelefones();
        
        // Pega o primeiro endereço como principal (se houver)
        $endereco = !empty($enderecos) ? $enderecos[0] : null;
        
        // Pega o primeiro telefone (se houver)
        $telefone = !empty($telefones) ? $telefones[0]['numero'] ?? '' : '';

        // Formatação de dados
        $dtNasc = $cliente->getDtNascimento(); // Formato YYYY-MM-DD do banco
        
        // Estatísticas (Placeholder/Todo: integrar com VendaModel)
        $totalCompras = 0; // VendaModel::countByCliente($id);
        $valorTotal = '0,00'; // number_format(VendaModel::totalSpentByCliente($id), 2, ',', '.');

        echo ViewerPlace::render('admin-gerenciar-cliente', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            
            // IDs e Status
            'id' => $cliente->getId(),
            'customer_id' => $cliente->getId(),
            
            // Dados Pessoais
            'nome' => htmlspecialchars($cliente->getNome()),
            'email' => htmlspecialchars($cliente->getEmail()),
            'cpf' => htmlspecialchars($cliente->getCpf() ?? ''),
            'telefone' => htmlspecialchars($telefone),
            'data_nascimento' => $dtNasc,
            'foto_perfil' => $cliente->getImage() ?? '/assets/image/placeholder_pfp.png',
            
            // Endereço (usando getters do array/objeto de endereço se disponível, ou strings vazias)
            'cep' => $endereco ? htmlspecialchars($endereco['cep'] ?? '') : '',
            'estado' => $endereco ? htmlspecialchars($endereco['estado'] ?? '') : '',
            'rua' => $endereco ? htmlspecialchars($endereco['rua'] ?? '') : '',
            'numero' => $endereco ? htmlspecialchars($endereco['numero'] ?? '') : '',
            'bairro' => $endereco ? htmlspecialchars($endereco['bairro'] ?? '') : '',
            'cidade' => $endereco ? htmlspecialchars($endereco['cidade'] ?? '') : '',
            
            // Configurações e Stats
            'status_active' => $cliente->getStatus() === '1' ? 'selected' : '',
            'status_inactive' => $cliente->getStatus() !== '1' ? 'selected' : '',
            'total_compras' => $totalCompras,
            'valor_total' => $valorTotal,
            'dt_criacao' => date('d/m/Y', strtotime($cliente->getDtCriacao()))
        ]);
    }

    /**
     * Ativa/Desativa cliente
     */
    public function toggleStatus($id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $cliente = ClienteModel::findById($id);
        
        if ($cliente) {
            if ($cliente->getStatus() === '1') {
                $cliente->deactivate();
            } else {
                $cliente->activate();
            }
        }

        header('Location: /admin/customers');
        exit;
    }

    private function generateCustomerRows(array $clientes): string
    {
        if (empty($clientes)) {
            return '<tr><td colspan="6" style="text-align:center;color:#666;">Nenhum cliente encontrado</td></tr>';
        }

        $html = '';
        foreach ($clientes as $c) {
            $id = $c->getId();
            $nome = htmlspecialchars($c->getNome());
            $email = htmlspecialchars($c->getEmail());
            $cpf = $c->getCpf() ?? 'N/A';
            $dataCriacao = date('d/m/Y', strtotime($c->getDtCriacao()));
            
            $html .= "<tr>
                <td>{$id}</td>
                <td>{$nome}</td>
                <td>{$email}</td>
                <td>{$cpf}</td>
                <td>{$dataCriacao}</td>
                <td class='actions'>
                    <a href='/admin/customers/{$id}' class='btn-view'>Ver</a>
                </td>
            </tr>";
        }
        return $html;
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
