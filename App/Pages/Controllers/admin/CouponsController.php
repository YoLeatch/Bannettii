<?php
/**
 * CouponsController - Gerenciamento de Cupons (Admin)
 * 
 * @package App\Pages\Controllers\admin
 */

namespace App\Pages\Controllers\admin;

use Core\ViewerPlace;
use Core\ConnectionFactory;

class CouponsController
{
    /**
     * Lista todos os cupons
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

        $pdo = ConnectionFactory::getConnection('read_only');
        
        // Busca cupons
        $stmt = $pdo->query("SELECT * FROM cupom ORDER BY criacao DESC");
        $cupons = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Busca subcategorias de cada cupom
        foreach ($cupons as &$cupom) {
            $stmt = $pdo->prepare("
                SELECT sc.sub_categoria 
                FROM cupom_sub_categoria csc 
                JOIN sub_categoria sc ON csc.sub_categoria_id = sc.id 
                WHERE csc.cupom_id = :cupom_id
            ");
            $stmt->execute(['cupom_id' => $cupom['id']]);
            $subs = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            $cupom['subcategorias'] = $subs;
        }
        
        // Busca todas subcategorias para o formulário
        $stmt = $pdo->query("SELECT sc.*, c.categoria as categoria_nome FROM sub_categoria sc LEFT JOIN categoria c ON sc.categoria = c.id ORDER BY c.categoria, sc.sub_categoria");
        $subcategorias = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $subcategoriasOptions = $this->generateSubcategoriasOptions($subcategorias);
        
        $cuponsRows = $this->generateCuponsRows($cupons);
        $messages = $this->getMessages();

        echo ViewerPlace::render('admin-cupons', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'cupons_rows' => $cuponsRows,
            'messages' => $messages,
            'subcategorias_options' => $subcategoriasOptions
        ]);
    }

    /**
     * Armazena novo cupom
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

        $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
        $desconto = (int)($_POST['desconto'] ?? 0);
        $tipo = $_POST['tipo'] ?? 'percentual';
        $validade = $_POST['validade'] ?? '';
        $subcategorias = $_POST['subcategorias'] ?? [];

        if (empty($codigo) || empty($validade)) {
            header('Location: /admin/cupons?error=empty');
            exit;
        }

        try {
            $pdo = ConnectionFactory::getConnection('write');
            
            // Verifica se código já existe
            $stmt = $pdo->prepare("SELECT id FROM cupom WHERE cod = :cod");
            $stmt->execute(['cod' => $codigo]);
            if ($stmt->fetch()) {
                header('Location: /admin/cupons?error=duplicate');
                exit;
            }
            
            // Insere cupom
            $stmt = $pdo->prepare("
                INSERT INTO cupom (cod, criacao, validade, status)
                VALUES (:cod, CURDATE(), :validade, '1')
            ");
            $stmt->execute([
                'cod' => $codigo,
                'validade' => $validade
            ]);
            
            $cupomId = $pdo->lastInsertId();
            
            // Insere relacionamentos com subcategorias
            if (!empty($subcategorias)) {
                $stmt = $pdo->prepare("INSERT INTO cupom_sub_categoria (cupom_id, sub_categoria_id) VALUES (:cupom_id, :sub_id)");
                foreach ($subcategorias as $subId) {
                    $stmt->execute([
                        'cupom_id' => $cupomId,
                        'sub_id' => (int)$subId
                    ]);
                }
            }
            
            header('Location: /admin/cupons?success=created');
        } catch (\PDOException $e) {
            header('Location: /admin/cupons?error=failed');
        }
        exit;
    }

    /**
     * Toggle status do cupom
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

        try {
            $pdo = ConnectionFactory::getConnection('write');
            
            $stmt = $pdo->prepare("SELECT status FROM cupom WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $cupom = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($cupom) {
                $newStatus = $cupom['status'] === '1' ? '0' : '1';
                $stmt = $pdo->prepare("UPDATE cupom SET status = :status WHERE id = :id");
                $stmt->execute(['status' => $newStatus, 'id' => $id]);
            }
            
            header('Location: /admin/cupons?success=toggled');
        } catch (\PDOException $e) {
            header('Location: /admin/cupons?error=failed');
        }
        exit;
    }

    /**
     * Deleta cupom
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

        try {
            $pdo = ConnectionFactory::getConnection('write');
            
            // Remove relacionamentos
            $stmt = $pdo->prepare("DELETE FROM cupom_sub_categoria WHERE cupom_id = :id");
            $stmt->execute(['id' => $id]);
            
            // Remove cupom
            $stmt = $pdo->prepare("DELETE FROM cupom WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            header('Location: /admin/cupons?success=deleted');
        } catch (\PDOException $e) {
            header('Location: /admin/cupons?error=failed');
        }
        exit;
    }

    private function generateSubcategoriasOptions(array $subcategorias): string
    {
        $html = '';
        $currentCategoria = '';
        
        foreach ($subcategorias as $sc) {
            $catNome = $sc['categoria_nome'] ?? 'Sem categoria';
            
            if ($catNome !== $currentCategoria) {
                if (!empty($currentCategoria)) {
                    $html .= '</optgroup>';
                }
                $html .= '<optgroup label="' . htmlspecialchars($catNome) . '">';
                $currentCategoria = $catNome;
            }
            
            $html .= '<option value="' . $sc['id'] . '">' . htmlspecialchars($sc['sub_categoria']) . '</option>';
        }
        
        if (!empty($html)) {
            $html .= '</optgroup>';
        }
        
        return $html;
    }

    private function generateCuponsRows(array $cupons): string
    {
        if (empty($cupons)) {
            return '<tr><td colspan="6" style="text-align:center;color:#888;">Nenhum cupom cadastrado</td></tr>';
        }

        $html = '';
        foreach ($cupons as $cupom) {
            $statusClass = $cupom['status'] === '1' ? 'status-active' : 'status-inactive';
            $statusText = $cupom['status'] === '1' ? 'Ativo' : 'Inativo';
            $toggleText = $cupom['status'] === '1' ? 'Desativar' : 'Ativar';
            
            $criacao = date('d/m/Y', strtotime($cupom['criacao']));
            $validade = date('d/m/Y', strtotime($cupom['validade']));
            
            // Verifica se expirado
            $expirado = strtotime($cupom['validade']) < time();
            if ($expirado) {
                $statusClass = 'status-expired';
                $statusText = 'Expirado';
            }
            
            // Subcategorias
            $subsHtml = '';
            if (!empty($cupom['subcategorias'])) {
                $subsText = implode(', ', array_slice($cupom['subcategorias'], 0, 3));
                if (count($cupom['subcategorias']) > 3) {
                    $subsText .= ' +' . (count($cupom['subcategorias']) - 3);
                }
                $subsHtml = "<small style='color:#888;display:block;margin-top:3px;'>{$subsText}</small>";
            } else {
                $subsHtml = "<small style='color:#4ade80;display:block;margin-top:3px;'>Todas subcategorias</small>";
            }
            
            $html .= "<tr>
                <td><strong>{$cupom['cod']}</strong>{$subsHtml}</td>
                <td>{$criacao}</td>
                <td>{$validade}</td>
                <td><span class='status-badge {$statusClass}'>{$statusText}</span></td>
                <td class='actions'>
                    <a href='/admin/cupons/{$cupom['id']}/toggle' class='btn-toggle'>{$toggleText}</a>
                    <a href='/admin/cupons/{$cupom['id']}/delete' class='btn-delete' onclick='return confirm(\"Deseja remover este cupom?\")'>Remover</a>
                </td>
            </tr>";
        }
        return $html;
    }

    private function getMessages(): string
    {
        $msg = '';
        
        if (isset($_GET['success'])) {
            $text = match($_GET['success']) {
                'created' => 'Cupom criado com sucesso!',
                'deleted' => 'Cupom removido com sucesso!',
                'toggled' => 'Status do cupom alterado!',
                default => 'Operação realizada com sucesso!'
            };
            $msg = '<div class="alert alert-success">' . $text . '</div>';
        }
        
        if (isset($_GET['error'])) {
            $text = match($_GET['error']) {
                'empty' => 'Código e validade são obrigatórios.',
                'duplicate' => 'Este código de cupom já existe.',
                'failed' => 'Ocorreu um erro. Tente novamente.',
                default => 'Ocorreu um erro.'
            };
            $msg = '<div class="alert alert-error">' . $text . '</div>';
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
