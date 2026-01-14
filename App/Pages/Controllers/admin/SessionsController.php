<?php
/**
 * SessionsController - Gerenciamento das Seções da Home (Admin)
 * 
 * @package App\Pages\Controllers\admin
 */

namespace App\Pages\Controllers\admin;

use Core\ViewerPlace;

class SessionsController
{
    private const SESSIONS_FILE = __DIR__ . '/../../../../storage/JSON/home_sections.json';
    
    // Opções de ordenação disponíveis
    private const ORDER_BY_OPTIONS = [
        'mais_vendidos' => 'Mais Vendidos',
        'maior_desconto' => 'Maior Desconto',
        'menor_preco' => 'Menor Preço',
        'maior_preco' => 'Maior Preço',
        'mais_recentes' => 'Mais Recentes',
        'mais_antigos' => 'Mais Antigos',
        'alfabetico_az' => 'Alfabético (A-Z)',
        'alfabetico_za' => 'Alfabético (Z-A)',
        'melhor_avaliacao' => 'Melhor Avaliação',
        'aleatorio' => 'Aleatório'
    ];

    /**
     * Lista e gerencia seções da home
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

        $sections = $this->loadSections();
        $sectionsRows = $this->generateSectionsRows($sections);
        $messages = $this->getMessages();
        $orderByOptionsJson = json_encode(self::ORDER_BY_OPTIONS);

        echo ViewerPlace::render('admin-sessions', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'sections_rows' => $sectionsRows,
            'messages' => $messages,
            'order_by_options_json' => $orderByOptionsJson
        ]);
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $orderByOptions = $this->generateOrderByOptions('');

        echo ViewerPlace::render('admin-sessions-create', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'order_by_options' => $orderByOptions
        ]);
    }

    /**
     * Armazena nova seção
     */
    public function store()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $nome = trim($_POST['nome'] ?? '');
        $titulo = trim($_POST['titulo'] ?? '');
        $subtitulo = trim($_POST['subtitulo'] ?? '');
        $orderBy = $_POST['order_by'] ?? 'mais_recentes';
        $limite = (int)($_POST['limite'] ?? 8);

        if (empty($nome) || empty($titulo)) {
            header('Location: /admin/sessions/create?error=empty');
            exit;
        }

        $sections = $this->loadSections();
        
        // Gera ID único
        $id = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $nome)) . '_' . time();
        
        // Pega a maior ordem e adiciona 1
        $maxOrdem = 0;
        foreach ($sections as $s) {
            if ($s['ordem'] > $maxOrdem) {
                $maxOrdem = $s['ordem'];
            }
        }

        $sections[] = [
            'id' => $id,
            'nome' => $nome,
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'ativo' => true,
            'ordem' => $maxOrdem + 1,
            'editavel' => true,
            'removivel' => true,
            'order_by' => $orderBy,
            'limite' => $limite
        ];

        $this->saveSections($sections);
        
        header('Location: /admin/sessions?success=created');
        exit;
    }

    /**
     * Atualiza uma seção
     */
    public function update()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $sectionId = $_POST['section_id'] ?? '';
        $titulo = trim($_POST['titulo'] ?? '');
        $subtitulo = trim($_POST['subtitulo'] ?? '');
        $ativo = isset($_POST['ativo']) ? true : false;
        $ordem = (int)($_POST['ordem'] ?? 0);
        $orderBy = $_POST['order_by'] ?? 'mais_recentes';
        $limite = (int)($_POST['limite'] ?? 8);

        $sections = $this->loadSections();
        
        foreach ($sections as &$section) {
            if ($section['id'] === $sectionId) {
                $section['titulo'] = $titulo;
                $section['subtitulo'] = $subtitulo;
                $section['ativo'] = $ativo;
                $section['ordem'] = $ordem;
                $section['order_by'] = $orderBy;
                $section['limite'] = $limite;
                break;
            }
        }

        $this->saveSections($sections);
        
        header('Location: /admin/sessions?success=updated');
        exit;
    }

    /**
     * Deleta uma seção
     */
    public function delete($id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $sections = $this->loadSections();
        $newSections = [];
        
        foreach ($sections as $section) {
            // Só permite deletar seções removíveis
            if ($section['id'] === $id) {
                if (!($section['removivel'] ?? true)) {
                    header('Location: /admin/sessions?error=protected');
                    exit;
                }
                continue; // Pula essa seção (não adiciona ao novo array)
            }
            $newSections[] = $section;
        }

        $this->saveSections($newSections);
        
        header('Location: /admin/sessions?success=deleted');
        exit;
    }

    /**
     * Toggle ativo/inativo
     */
    public function toggle($id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $sections = $this->loadSections();
        
        foreach ($sections as &$section) {
            if ($section['id'] === $id) {
                $section['ativo'] = !$section['ativo'];
                break;
            }
        }

        $this->saveSections($sections);
        
        header('Location: /admin/sessions?success=toggled');
        exit;
    }

    /**
     * Reordena seções
     */
    public function reorder()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $ordem = $_POST['ordem'] ?? [];
        $sections = $this->loadSections();
        
        foreach ($sections as &$section) {
            if (isset($ordem[$section['id']])) {
                $section['ordem'] = (int)$ordem[$section['id']];
            }
        }

        // Ordena por ordem
        usort($sections, fn($a, $b) => $a['ordem'] <=> $b['ordem']);

        $this->saveSections($sections);
        
        header('Location: /admin/sessions?success=reordered');
        exit;
    }

    private function loadSections(): array
    {
        if (!file_exists(self::SESSIONS_FILE)) {
            // Cria arquivo com seções padrão
            $defaultSections = [
                [
                    'id' => 'carousel',
                    'nome' => 'Carrossel',
                    'titulo' => 'Banner Principal',
                    'subtitulo' => '',
                    'ativo' => true,
                    'ordem' => 1,
                    'editavel' => false,
                    'removivel' => false,
                    'order_by' => '',
                    'limite' => 0
                ],
                [
                    'id' => 'promocoes',
                    'nome' => 'Promoções',
                    'titulo' => 'Promoções',
                    'subtitulo' => 'Ofertas imperdíveis para você',
                    'ativo' => true,
                    'ordem' => 2,
                    'editavel' => true,
                    'removivel' => false,
                    'order_by' => 'maior_desconto',
                    'limite' => 8
                ],
                [
                    'id' => 'novidades',
                    'nome' => 'Novidades',
                    'titulo' => 'Novidades',
                    'subtitulo' => 'Confira os últimos lançamentos',
                    'ativo' => true,
                    'ordem' => 3,
                    'editavel' => true,
                    'removivel' => false,
                    'order_by' => 'mais_recentes',
                    'limite' => 8
                ],
                [
                    'id' => 'mais_vendidos',
                    'nome' => 'Mais Vendidos',
                    'titulo' => 'Mais Vendidos',
                    'subtitulo' => 'Os produtos mais populares',
                    'ativo' => true,
                    'ordem' => 4,
                    'editavel' => true,
                    'removivel' => false,
                    'order_by' => 'mais_vendidos',
                    'limite' => 8
                ],
                [
                    'id' => 'categorias',
                    'nome' => 'Categorias',
                    'titulo' => 'Categorias',
                    'subtitulo' => 'Navegue por categoria',
                    'ativo' => true,
                    'ordem' => 5,
                    'editavel' => true,
                    'removivel' => false,
                    'order_by' => '',
                    'limite' => 0
                ]
            ];
            $this->saveSections($defaultSections);
            return $defaultSections;
        }

        $content = file_get_contents(self::SESSIONS_FILE);
        return json_decode($content, true) ?? [];
    }

    private function saveSections(array $sections): void
    {
        $dir = dirname(self::SESSIONS_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(self::SESSIONS_FILE, json_encode($sections, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function generateOrderByOptions(string $selected): string
    {
        $html = '';
        foreach (self::ORDER_BY_OPTIONS as $value => $label) {
            $sel = ($value === $selected) ? ' selected' : '';
            $html .= "<option value=\"{$value}\"{$sel}>{$label}</option>";
        }
        return $html;
    }

    private function generateSectionsRows(array $sections): string
    {
        if (empty($sections)) {
            return '<tr><td colspan="7" style="text-align:center;color:#888;">Nenhuma seção encontrada</td></tr>';
        }

        // Ordena por ordem
        usort($sections, fn($a, $b) => $a['ordem'] <=> $b['ordem']);

        $html = '';
        foreach ($sections as $section) {
            $statusClass = $section['ativo'] ? 'status-active' : 'status-inactive';
            $statusText = $section['ativo'] ? 'Ativo' : 'Inativo';
            $toggleText = $section['ativo'] ? 'Desativar' : 'Ativar';
            
            $orderByLabel = self::ORDER_BY_OPTIONS[$section['order_by'] ?? ''] ?? '-';
            $limite = $section['limite'] ?? 0;
            $orderByJson = htmlspecialchars(json_encode($section['order_by'] ?? ''));
            
            $editBtn = ($section['editavel'] ?? true) 
                ? "<button type='button' class='btn-edit' onclick='openEditModal(\"{$section['id']}\", \"{$section['titulo']}\", \"{$section['subtitulo']}\", {$section['ordem']}, {$orderByJson}, {$limite})'>Editar</button>"
                : '';
                
            $deleteBtn = ($section['removivel'] ?? true)
                ? "<a href='/admin/sessions/{$section['id']}/delete' class='btn-delete' onclick='return confirm(\"Deseja remover esta seção?\")'>Remover</a>"
                : '';
            
            $html .= "<tr data-id='{$section['id']}'>
                <td><span class='drag-handle'>☰</span> {$section['ordem']}</td>
                <td><strong>{$section['nome']}</strong></td>
                <td>{$section['titulo']}</td>
                <td>{$orderByLabel}</td>
                <td>{$limite}</td>
                <td><span class='status-badge {$statusClass}'>{$statusText}</span></td>
                <td class='actions'>
                    {$editBtn}
                    <a href='/admin/sessions/{$section['id']}/toggle' class='btn-toggle'>{$toggleText}</a>
                    {$deleteBtn}
                </td>
            </tr>";
        }
        return $html;
    }

    private function getMessages(): string
    {
        $msg = '';
        
        if (isset($_GET['success'])) {
            $type = $_GET['success'];
            $text = match($type) {
                'created' => 'Seção criada com sucesso!',
                'updated' => 'Seção atualizada com sucesso!',
                'deleted' => 'Seção removida com sucesso!',
                'toggled' => 'Status alterado com sucesso!',
                'reordered' => 'Ordem das seções atualizada!',
                default => 'Operação realizada com sucesso!'
            };
            $msg = '<div class="alert alert-success" style="background:#065f46;color:#d1fae5;padding:15px;border-radius:10px;margin-bottom:20px;">' . $text . '</div>';
        }
        
        if (isset($_GET['error'])) {
            $type = $_GET['error'];
            $text = match($type) {
                'notfound' => 'Seção não encontrada.',
                'protected' => 'Esta seção não pode ser removida.',
                'empty' => 'Nome e título são obrigatórios.',
                default => 'Ocorreu um erro. Tente novamente.'
            };
            $msg = '<div class="alert alert-error" style="background:#7f1d1d;color:#fecaca;padding:15px;border-radius:10px;margin-bottom:20px;">' . $text . '</div>';
        }
        
        return $msg;
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
