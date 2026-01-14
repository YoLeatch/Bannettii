<?php
/**
 * SupportController - Controlador de Suporte/Tickets
 * 
 * Gerencia abertura e visualização de tickets de suporte.
 * Armazena dados em JSON para simplicidade.
 * 
 * @package App\Pages\Controllers\admin
 */

namespace App\Pages\Controllers\admin;

use Core\ViewerPlace;

class SupportController
{
    private const TICKETS_FILE = __DIR__ . '/../../../../storage/JSON/tickets.json';

    /**
     * Página para abrir novo ticket (usuário)
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? null;
        $userName = $_SESSION['user_name'] ?? '';
        $userEmail = $_SESSION['user_email'] ?? '';
        
        // Mensagem de sucesso/erro
        $message = $_SESSION['support_message'] ?? '';
        $messageType = $_SESSION['support_message_type'] ?? '';
        unset($_SESSION['support_message'], $_SESSION['support_message_type']);
        
        // Busca tickets do usuário se estiver logado
        $userTickets = [];
        if ($userId) {
            $allTickets = $this->loadTickets();
            $userTickets = array_filter($allTickets, fn($t) => $t['user_id'] == $userId);
            $userTickets = array_values($userTickets);
        }
        
        $ticketsHtml = $this->generateUserTicketsHtml($userTickets);
        
        echo ViewerPlace::render('help', [
            'user_name' => htmlspecialchars($userName),
            'user_email' => htmlspecialchars($userEmail),
            'user_tickets' => $ticketsHtml,
            'message' => $message,
            'message_type' => $messageType,
            'is_logged_in' => $userId ? 'true' : 'false'
        ]);
    }

    /**
     * Processa criação de novo ticket
     */
    public function create()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $assunto = trim($_POST['assunto'] ?? '');
        $categoria = trim($_POST['categoria'] ?? 'geral');
        $mensagem = trim($_POST['mensagem'] ?? '');
        $prioridade = trim($_POST['prioridade'] ?? 'media');
        
        // Validações
        if (empty($nome) || empty($email) || empty($assunto) || empty($mensagem)) {
            $_SESSION['support_message'] = 'Por favor, preencha todos os campos obrigatórios.';
            $_SESSION['support_message_type'] = 'error';
            header('Location: /help');
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['support_message'] = 'Por favor, insira um email válido.';
            $_SESSION['support_message_type'] = 'error';
            header('Location: /help');
            exit;
        }
        
        // Cria o ticket
        $tickets = $this->loadTickets();
        
        $newId = 1;
        if (!empty($tickets)) {
            $maxId = max(array_column($tickets, 'id'));
            $newId = $maxId + 1;
        }
        
        $ticket = [
            'id' => $newId,
            'codigo' => 'TKT-' . date('Ymd') . '-' . str_pad($newId, 4, '0', STR_PAD_LEFT),
            'user_id' => $_SESSION['user_id'] ?? null,
            'nome' => $nome,
            'email' => $email,
            'assunto' => $assunto,
            'categoria' => $categoria,
            'mensagem' => $mensagem,
            'prioridade' => $prioridade,
            'status' => 'aberto',
            'respostas' => [],
            'criado_em' => date('Y-m-d H:i:s'),
            'atualizado_em' => date('Y-m-d H:i:s')
        ];
        
        $tickets[] = $ticket;
        $this->saveTickets($tickets);
        
        $_SESSION['support_message'] = 'Ticket #' . $ticket['codigo'] . ' criado com sucesso! Você receberá uma resposta em breve.';
        $_SESSION['support_message_type'] = 'success';
        
        header('Location: /help');
        exit;
    }

    /**
     * Visualiza um ticket específico (usuário)
     */
    public function show($id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $tickets = $this->loadTickets();
        $ticket = null;
        
        foreach ($tickets as $t) {
            if ($t['id'] == $id || $t['codigo'] == $id) {
                $ticket = $t;
                break;
            }
        }
        
        if (!$ticket) {
            $_SESSION['support_message'] = 'Ticket não encontrado.';
            $_SESSION['support_message_type'] = 'error';
            header('Location: /help');
            exit;
        }
        
        // Gera HTML das respostas
        $respostasHtml = $this->generateRespostasHtml($ticket['respostas']);
        
        echo ViewerPlace::render('ticket-view', [
            'ticket_id' => $ticket['id'],
            'ticket_codigo' => $ticket['codigo'],
            'ticket_assunto' => htmlspecialchars($ticket['assunto']),
            'ticket_mensagem' => nl2br(htmlspecialchars($ticket['mensagem'])),
            'ticket_categoria' => ucfirst($ticket['categoria']),
            'ticket_prioridade' => ucfirst($ticket['prioridade']),
            'ticket_status' => $this->getStatusLabel($ticket['status']),
            'ticket_status_class' => $this->getStatusClass($ticket['status']),
            'ticket_data' => date('d/m/Y H:i', strtotime($ticket['criado_em'])),
            'respostas' => $respostasHtml
        ]);
    }

    /**
     * Adiciona resposta ao ticket (usuário)
     */
    public function reply()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $ticketId = $_POST['ticket_id'] ?? null;
        $mensagem = trim($_POST['mensagem'] ?? '');
        
        if (!$ticketId || empty($mensagem)) {
            header('Location: /help');
            exit;
        }
        
        $tickets = $this->loadTickets();
        
        foreach ($tickets as &$ticket) {
            if ($ticket['id'] == $ticketId) {
                $ticket['respostas'][] = [
                    'autor' => $_SESSION['user_name'] ?? 'Usuário',
                    'autor_tipo' => 'usuario',
                    'mensagem' => $mensagem,
                    'data' => date('Y-m-d H:i:s')
                ];
                $ticket['atualizado_em'] = date('Y-m-d H:i:s');
                $ticket['status'] = 'aguardando_resposta';
                break;
            }
        }
        
        $this->saveTickets($tickets);
        
        header('Location: /help/ticket/' . $ticketId);
        exit;
    }

    // =============================================
    // MÉTODOS ADMIN
    // =============================================

    /**
     * Lista todos os tickets (admin)
     */
    public function adminList()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }
        
        $tickets = $this->loadTickets();
        
        // Ordena por data (mais recentes primeiro)
        usort($tickets, fn($a, $b) => strtotime($b['criado_em']) <=> strtotime($a['criado_em']));
        
        // Filtra por status se solicitado
        $statusFilter = $_GET['status'] ?? '';
        if ($statusFilter) {
            $tickets = array_filter($tickets, fn($t) => $t['status'] === $statusFilter);
            $tickets = array_values($tickets);
        }
        
        $ticketsHtml = $this->generateAdminTicketsHtml($tickets);
        
        // Conta por status
        $allTickets = $this->loadTickets();
        $counts = [
            'total' => count($allTickets),
            'aberto' => count(array_filter($allTickets, fn($t) => $t['status'] === 'aberto')),
            'em_andamento' => count(array_filter($allTickets, fn($t) => $t['status'] === 'em_andamento')),
            'aguardando_resposta' => count(array_filter($allTickets, fn($t) => $t['status'] === 'aguardando_resposta')),
            'resolvido' => count(array_filter($allTickets, fn($t) => $t['status'] === 'resolvido')),
            'fechado' => count(array_filter($allTickets, fn($t) => $t['status'] === 'fechado'))
        ];
        
        echo ViewerPlace::render('admin-tickets', [
            'tickets' => $ticketsHtml,
            'count_total' => $counts['total'],
            'count_aberto' => $counts['aberto'],
            'count_em_andamento' => $counts['em_andamento'],
            'count_aguardando' => $counts['aguardando_resposta'],
            'count_resolvido' => $counts['resolvido'],
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador'
        ]);
    }

    /**
     * Visualiza ticket específico (admin)
     */
    public function adminShow($id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }
        
        $tickets = $this->loadTickets();
        $ticket = null;
        
        foreach ($tickets as $t) {
            if ($t['id'] == $id) {
                $ticket = $t;
                break;
            }
        }
        
        if (!$ticket) {
            header('Location: /admin/tickets');
            exit;
        }
        
        $respostasHtml = $this->generateRespostasHtml($ticket['respostas'], true);
        
        echo ViewerPlace::render('admin-ticket-view', [
            'ticket_id' => $ticket['id'],
            'ticket_codigo' => $ticket['codigo'],
            'ticket_nome' => htmlspecialchars($ticket['nome']),
            'ticket_email' => htmlspecialchars($ticket['email']),
            'ticket_assunto' => htmlspecialchars($ticket['assunto']),
            'ticket_mensagem' => nl2br(htmlspecialchars($ticket['mensagem'])),
            'ticket_categoria' => ucfirst($ticket['categoria']),
            'ticket_prioridade' => ucfirst($ticket['prioridade']),
            'ticket_prioridade_class' => $this->getPrioridadeClass($ticket['prioridade']),
            'ticket_status' => $this->getStatusLabel($ticket['status']),
            'ticket_status_class' => $this->getStatusClass($ticket['status']),
            'ticket_data' => date('d/m/Y H:i', strtotime($ticket['criado_em'])),
            'respostas' => $respostasHtml,
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador'
        ]);
    }

    /**
     * Admin responde ao ticket
     */
    public function adminReply()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }
        
        $ticketId = $_POST['ticket_id'] ?? null;
        $mensagem = trim($_POST['mensagem'] ?? '');
        $novoStatus = $_POST['status'] ?? null;
        
        if (!$ticketId || empty($mensagem)) {
            header('Location: /admin/tickets');
            exit;
        }
        
        $tickets = $this->loadTickets();
        
        foreach ($tickets as &$ticket) {
            if ($ticket['id'] == $ticketId) {
                $ticket['respostas'][] = [
                    'autor' => $_SESSION['admin_name'] ?? 'Suporte',
                    'autor_tipo' => 'admin',
                    'mensagem' => $mensagem,
                    'data' => date('Y-m-d H:i:s')
                ];
                $ticket['atualizado_em'] = date('Y-m-d H:i:s');
                
                if ($novoStatus) {
                    $ticket['status'] = $novoStatus;
                } else {
                    $ticket['status'] = 'em_andamento';
                }
                break;
            }
        }
        
        $this->saveTickets($tickets);
        
        header('Location: /admin/tickets/' . $ticketId);
        exit;
    }

    /**
     * Atualiza status do ticket (admin)
     */
    public function adminUpdateStatus()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }
        
        $ticketId = $_POST['ticket_id'] ?? null;
        $status = $_POST['status'] ?? null;
        
        if (!$ticketId || !$status) {
            header('Location: /admin/tickets');
            exit;
        }
        
        $tickets = $this->loadTickets();
        
        foreach ($tickets as &$ticket) {
            if ($ticket['id'] == $ticketId) {
                $ticket['status'] = $status;
                $ticket['atualizado_em'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        $this->saveTickets($tickets);
        
        header('Location: /admin/tickets/' . $ticketId);
        exit;
    }

    // =============================================
    // MÉTODOS AJAX PARA TEMPO REAL
    // =============================================

    /**
     * Retorna atualizações do ticket (polling AJAX)
     * Usado para atualização em tempo real
     */
    public function getUpdates($id)
    {
        header('Content-Type: application/json');
        
        $lastCheck = $_GET['last_check'] ?? null;
        
        $tickets = $this->loadTickets();
        $ticket = null;
        
        foreach ($tickets as $t) {
            if ($t['id'] == $id) {
                $ticket = $t;
                break;
            }
        }
        
        if (!$ticket) {
            echo json_encode(['success' => false, 'message' => 'Ticket não encontrado']);
            return;
        }
        
        // Verifica se há novas respostas desde o último check
        $newResponses = [];
        if ($lastCheck) {
            foreach ($ticket['respostas'] as $resposta) {
                if (strtotime($resposta['data']) > strtotime($lastCheck)) {
                    $newResponses[] = $resposta;
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'ticket_id' => $ticket['id'],
            'status' => $ticket['status'],
            'status_label' => $this->getStatusLabel($ticket['status']),
            'status_class' => $this->getStatusClass($ticket['status']),
            'updated_at' => $ticket['atualizado_em'],
            'response_count' => count($ticket['respostas']),
            'new_responses' => $newResponses,
            'has_updates' => !empty($newResponses) || ($lastCheck && strtotime($ticket['atualizado_em']) > strtotime($lastCheck)),
            'responses_html' => $this->generateRespostasHtml($ticket['respostas'])
        ]);
    }

    /**
     * Lista tickets com novas atualizações (para admin)
     */
    public function getTicketsList()
    {
        header('Content-Type: application/json');
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            echo json_encode(['success' => false, 'message' => 'Não autorizado']);
            return;
        }
        
        $tickets = $this->loadTickets();
        
        // Ordena por data de atualização (mais recentes primeiro)
        usort($tickets, fn($a, $b) => strtotime($b['atualizado_em']) <=> strtotime($a['atualizado_em']));
        
        $ticketsList = [];
        foreach ($tickets as $ticket) {
            $ticketsList[] = [
                'id' => $ticket['id'],
                'codigo' => $ticket['codigo'],
                'nome' => $ticket['nome'],
                'assunto' => $ticket['assunto'],
                'prioridade' => $ticket['prioridade'],
                'status' => $ticket['status'],
                'status_label' => $this->getStatusLabel($ticket['status']),
                'criado_em' => $ticket['criado_em'],
                'atualizado_em' => $ticket['atualizado_em'],
                'response_count' => count($ticket['respostas'])
            ];
        }
        
        echo json_encode([
            'success' => true,
            'tickets' => $ticketsList,
            'counts' => [
                'total' => count($tickets),
                'aberto' => count(array_filter($tickets, fn($t) => $t['status'] === 'aberto')),
                'em_andamento' => count(array_filter($tickets, fn($t) => $t['status'] === 'em_andamento')),
                'aguardando_resposta' => count(array_filter($tickets, fn($t) => $t['status'] === 'aguardando_resposta')),
                'resolvido' => count(array_filter($tickets, fn($t) => $t['status'] === 'resolvido'))
            ]
        ]);
    }

    // =============================================
    // MÉTODOS AUXILIARES
    // =============================================

    private function loadTickets(): array
    {
        if (!file_exists(self::TICKETS_FILE)) {
            return [];
        }
        $content = file_get_contents(self::TICKETS_FILE);
        return json_decode($content, true) ?? [];
    }

    private function saveTickets(array $tickets): void
    {
        $dir = dirname(self::TICKETS_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(self::TICKETS_FILE, json_encode($tickets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function getStatusClass(string $status): string
    {
        return match($status) {
            'aberto' => 'status-open',
            'em_andamento' => 'status-progress',
            'aguardando_resposta' => 'status-waiting',
            'resolvido' => 'status-resolved',
            'fechado' => 'status-closed',
            default => 'status-open'
        };
    }

    private function getPrioridadeClass(string $prioridade): string
    {
        return match($prioridade) {
            'baixa' => 'priority-low',
            'media' => 'priority-medium',
            'alta' => 'priority-high',
            'urgente' => 'priority-urgent',
            default => 'priority-medium'
        };
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

    private function generateUserTicketsHtml(array $tickets): string
    {
        if (empty($tickets)) {
            return '<p class="no-tickets">Você ainda não abriu nenhum ticket.</p>';
        }
        
        $html = '';
        foreach ($tickets as $ticket) {
            $statusClass = $this->getStatusClass($ticket['status']);
            $statusLabel = $this->getStatusLabel($ticket['status']);
            $data = date('d/m/Y', strtotime($ticket['criado_em']));
            
            $html .= <<<HTML
            <div class="ticket-item">
                <div class="ticket-info">
                    <span class="ticket-code">{$ticket['codigo']}</span>
                    <span class="ticket-subject">{$ticket['assunto']}</span>
                </div>
                <div class="ticket-meta">
                    <span class="ticket-date">{$data}</span>
                    <span class="ticket-status {$statusClass}">{$statusLabel}</span>
                    <a href="/help/ticket/{$ticket['id']}" class="btn-view-ticket">Ver</a>
                </div>
            </div>
HTML;
        }
        return $html;
    }

    private function generateAdminTicketsHtml(array $tickets): string
    {
        if (empty($tickets)) {
            return '<tr><td colspan="6" class="no-data">Nenhum ticket encontrado</td></tr>';
        }
        
        $html = '';
        foreach ($tickets as $ticket) {
            $statusClass = $this->getStatusClass($ticket['status']);
            $statusLabel = $this->getStatusLabel($ticket['status']);
            $prioridadeClass = $this->getPrioridadeClass($ticket['prioridade']);
            $prioridadeLabel = ucfirst($ticket['prioridade']);
            $data = date('d/m/Y H:i', strtotime($ticket['criado_em']));
            
            $html .= <<<HTML
            <tr>
                <td><strong>{$ticket['codigo']}</strong></td>
                <td>{$ticket['nome']}</td>
                <td class="ticket-subject-cell" title="{$ticket['assunto']}">{$ticket['assunto']}</td>
                <td><span class="priority-badge {$prioridadeClass}">{$prioridadeLabel}</span></td>
                <td><span class="status-badge {$statusClass}">{$statusLabel}</span></td>
                <td>{$data}</td>
                <td>
                    <a href="/admin/tickets/{$ticket['id']}" class="btn-view">Ver</a>
                </td>
            </tr>
HTML;
        }
        return $html;
    }

    private function generateRespostasHtml(array $respostas, bool $isAdmin = false): string
    {
        if (empty($respostas)) {
            return '<p class="no-responses">Nenhuma resposta ainda.</p>';
        }
        
        $html = '';
        foreach ($respostas as $resposta) {
            $isAuthorAdmin = $resposta['autor_tipo'] === 'admin';
            $authorClass = $isAuthorAdmin ? 'response-admin' : 'response-user';
            $authorLabel = $isAuthorAdmin ? 'Suporte' : 'Você';
            $data = date('d/m/Y H:i', strtotime($resposta['data']));
            $mensagem = nl2br(htmlspecialchars($resposta['mensagem']));
            
            $html .= <<<HTML
            <div class="response-item {$authorClass}">
                <div class="response-header">
                    <span class="response-author">{$resposta['autor']} ({$authorLabel})</span>
                    <span class="response-date">{$data}</span>
                </div>
                <div class="response-content">{$mensagem}</div>
            </div>
HTML;
        }
        return $html;
    }

    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'aberto' => 'Aberto',
            'em_andamento' => 'Em Andamento',
            'aguardando_resposta' => 'Aguardando Resposta',
            'resolvido' => 'Resolvido',
            'fechado' => 'Fechado',
            default => 'Aberto'
        };
    }
}
