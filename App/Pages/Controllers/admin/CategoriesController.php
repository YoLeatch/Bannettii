<?php
/**
 * CategoriesController - Gerenciamento de Categorias (Admin)
 * 
 * @package App\Pages\Controllers\admin
 */

namespace App\Pages\Controllers\admin;

use Core\ViewerPlace;
use Core\ConnectionFactory;
use PDO;

class CategoriesController
{
    /**
     * Lista todas as categorias
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

        $categorias = $this->getAllCategories();
        $categoryRows = $this->generateCategoryRows($categorias);
        $messages = $this->getMessages();

        echo ViewerPlace::render('admin-tabela-categorias', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'category_rows' => $categoryRows,
            'messages' => $messages
        ]);
    }

    /**
     * Formulário de criação de categoria
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

        echo ViewerPlace::render('admin-gerenciar-categoria-criar', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'categoria' => ''
        ]);
    }

    /**
     * Salva nova categoria
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

        $categoria = trim($_POST['categoria'] ?? '');
        
        if (!empty($categoria)) {
            $pdo = ConnectionFactory::getConnection('default');
            $stmt = $pdo->prepare("INSERT INTO categoria (categoria) VALUES (:categoria)");
            $stmt->execute(['categoria' => $categoria]);
        }

        header('Location: /admin/categories?success=created');
        exit;
    }

    /**
     * Formulário de edição de categoria
     */
    public function edit($id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $categoria = $this->getCategoryById($id);
        
        if (!$categoria) {
            header('Location: /admin/categories?error=notfound');
            exit;
        }

        echo ViewerPlace::render('admin-gerenciar-categoria-editar', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'category_id' => $categoria['id'],
            'categoria' => htmlspecialchars($categoria['categoria'])
        ]);
    }

    /**
     * Atualiza categoria
     */
    public function update($id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $categoria = trim($_POST['categoria'] ?? '');
        
        if (!empty($categoria)) {
            $pdo = ConnectionFactory::getConnection('default');
            $stmt = $pdo->prepare("UPDATE categoria SET categoria = :categoria WHERE id = :id");
            $stmt->execute(['categoria' => $categoria, 'id' => $id]);
        }

        header('Location: /admin/categories?success=updated');
        exit;
    }

    /**
     * Deleta categoria
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

        $pdo = ConnectionFactory::getConnection('default');
        
        // Verifica se há produtos usando esta categoria
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM produto WHERE categoria = :id");
        $stmt->execute(['id' => $id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            header('Location: /admin/categories?error=has_products&count=' . $count);
            exit;
        }
        
        // Verifica se há subcategorias usando esta categoria
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM sub_categoria WHERE categoria = :id");
        $stmt->execute(['id' => $id]);
        $subCount = $stmt->fetchColumn();
        
        if ($subCount > 0) {
            header('Location: /admin/categories?error=has_subcategories&count=' . $subCount);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM categoria WHERE id = :id");
            $stmt->execute(['id' => $id]);
            header('Location: /admin/categories?success=deleted');
        } catch (\PDOException $e) {
            header('Location: /admin/categories?error=delete_failed');
        }
        exit;
    }

    // =============================================
    // SUBCATEGORIAS
    // =============================================

    /**
     * Lista subcategorias
     */
    public function subcategorias()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $subcategorias = $this->getAllSubcategories();
        $subcategoryRows = $this->generateSubcategoryRows($subcategorias);
        $messages = $this->getSubcategoriaMessages();

        echo ViewerPlace::render('admin-subcategorias-lista', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'subcategory_rows' => $subcategoryRows,
            'messages' => $messages
        ]);
    }

    /**
     * Formulário de criação de subcategoria
     */
    public function subcategoriasCreate()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $categorias = $this->getAllCategories();
        $categoryOptions = $this->generateCategoryOptions($categorias);

        echo ViewerPlace::render('admin-subcategorias-criar', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'category_options' => $categoryOptions
        ]);
    }

    /**
     * Salva nova subcategoria
     */
    public function subcategoriasStore()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $subcategoria = trim($_POST['sub_categoria'] ?? '');
        $categoriaId = (int)($_POST['categoria'] ?? 0);
        
        if (!empty($subcategoria) && $categoriaId > 0) {
            $pdo = ConnectionFactory::getConnection('default');
            $stmt = $pdo->prepare("INSERT INTO sub_categoria (sub_categoria, categoria) VALUES (:sub_categoria, :categoria)");
            $stmt->execute(['sub_categoria' => $subcategoria, 'categoria' => $categoriaId]);
        }

        header('Location: /admin/subcategorias?success=created');
        exit;
    }

    /**
     * Formulário de edição de subcategoria
     */
    public function subcategoriasEdit($id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $subcategoria = $this->getSubcategoryById($id);
        
        if (!$subcategoria) {
            header('Location: /admin/subcategorias?error=notfound');
            exit;
        }

        $categorias = $this->getAllCategories();
        $categoryOptions = $this->generateCategoryOptionsWithSelected($categorias, $subcategoria['categoria']);

        echo ViewerPlace::render('admin-subcategorias-editar', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'subcategory_id' => $subcategoria['id'],
            'sub_categoria' => htmlspecialchars($subcategoria['sub_categoria']),
            'category_options' => $categoryOptions
        ]);
    }

    /**
     * Atualiza subcategoria
     */
    public function subcategoriasUpdate($id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $subcategoria = trim($_POST['sub_categoria'] ?? '');
        $categoriaId = (int)($_POST['categoria'] ?? 0);
        
        if (!empty($subcategoria) && $categoriaId > 0) {
            $pdo = ConnectionFactory::getConnection('default');
            $stmt = $pdo->prepare("UPDATE sub_categoria SET sub_categoria = :sub_categoria, categoria = :categoria WHERE id = :id");
            $stmt->execute(['sub_categoria' => $subcategoria, 'categoria' => $categoriaId, 'id' => $id]);
        }

        header('Location: /admin/subcategorias?success=updated');
        exit;
    }

    /**
     * Deleta subcategoria
     */
    public function subcategoriasDelete($id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $pdo = ConnectionFactory::getConnection('default');
        
        // Verifica se há produtos usando esta subcategoria
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM produto WHERE sub_categoria = :id");
        $stmt->execute(['id' => $id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            header('Location: /admin/subcategorias?error=has_products&count=' . $count);
            exit;
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM sub_categoria WHERE id = :id");
            $stmt->execute(['id' => $id]);
            header('Location: /admin/subcategorias?success=deleted');
        } catch (\PDOException $e) {
            error_log("Erro ao excluir subcategoria: " . $e->getMessage());
            header('Location: /admin/subcategorias?error=delete_failed');
        }
        exit;
    }

    // =============================================
    // HELPERS
    // =============================================

    private function getAllCategories(): array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->query("SELECT * FROM categoria ORDER BY categoria ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getCategoryById(int $id): ?array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT * FROM categoria WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    private function getAllSubcategories(): array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->query("SELECT sc.*, c.categoria as categoria_nome FROM sub_categoria sc LEFT JOIN categoria c ON sc.categoria = c.id ORDER BY sc.sub_categoria ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getSubcategoryById(int $id): ?array
    {
        $pdo = ConnectionFactory::getConnection('read_only');
        $stmt = $pdo->prepare("SELECT sc.*, c.categoria as categoria_nome FROM sub_categoria sc LEFT JOIN categoria c ON sc.categoria = c.id WHERE sc.id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    private function generateCategoryRows(array $categorias): string
    {
        if (empty($categorias)) {
            return '<tr><td colspan="3" style="text-align:center;color:#a0a0a0;">Nenhuma categoria encontrada</td></tr>';
        }

        $html = '';
        foreach ($categorias as $c) {
            $html .= "<tr>
                <td>{$c['id']}</td>
                <td>{$c['categoria']}</td>
                <td class='actions'>
                    <a href='/admin/categories/{$c['id']}/edit' class='btn-edit'>Editar</a>
                    <a href='/admin/categories/{$c['id']}/delete' class='btn-delete' onclick='return confirm(\"Deseja excluir?\")'>Excluir</a>
                </td>
            </tr>";
        }
        return $html;
    }

    private function generateSubcategoryRows(array $subcategorias): string
    {
        if (empty($subcategorias)) {
            return '<tr><td colspan="4" style="text-align:center;color:#a0a0a0;">Nenhuma subcategoria encontrada</td></tr>';
        }

        $html = '';
        foreach ($subcategorias as $sc) {
            $html .= "<tr>
                <td>{$sc['id']}</td>
                <td>{$sc['sub_categoria']}</td>
                <td>{$sc['categoria_nome']}</td>
                <td class='actions'>
                    <a href='/admin/subcategorias/{$sc['id']}/edit' class='btn-edit'>Editar</a>
                    <a href='/admin/subcategorias/{$sc['id']}/delete' class='btn-delete' onclick='return confirm(\"Deseja excluir?\")'>Excluir</a>
                </td>
            </tr>";
        }
        return $html;
    }

    private function generateCategoryOptions(array $categorias): string
    {
        $html = '';
        foreach ($categorias as $c) {
            $html .= "<option value=\"{$c['id']}\">{$c['categoria']}</option>";
        }
        return $html;
    }

    private function generateCategoryOptionsWithSelected(array $categorias, int $selectedId): string
    {
        $html = '';
        foreach ($categorias as $c) {
            $selected = ($c['id'] == $selectedId) ? ' selected' : '';
            $html .= "<option value=\"{$c['id']}\"{$selected}>{$c['categoria']}</option>";
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

    private function getMessages(): string
    {
        $msg = '';
        
        if (isset($_GET['success'])) {
            $type = $_GET['success'];
            $text = match($type) {
                'created' => 'Categoria criada com sucesso!',
                'updated' => 'Categoria atualizada com sucesso!',
                'deleted' => 'Categoria excluída com sucesso!',
                default => 'Operação realizada com sucesso!'
            };
            $msg = '<div class="alert alert-success" style="background:#065f46;color:#d1fae5;padding:15px;border-radius:10px;margin-bottom:20px;">' . $text . '</div>';
        }
        
        if (isset($_GET['error'])) {
            $type = $_GET['error'];
            $count = $_GET['count'] ?? 0;
            $text = match($type) {
                'has_products' => "Não é possível excluir esta categoria. Existem {$count} produto(s) vinculado(s) a ela.",
                'has_subcategories' => "Não é possível excluir esta categoria. Existem {$count} subcategoria(s) vinculada(s) a ela.",
                'delete_failed' => 'Erro ao excluir a categoria. Tente novamente.',
                'notfound' => 'Categoria não encontrada.',
                default => 'Ocorreu um erro. Tente novamente.'
            };
            $msg = '<div class="alert alert-error" style="background:#7f1d1d;color:#fecaca;padding:15px;border-radius:10px;margin-bottom:20px;">' . $text . '</div>';
        }
        
        return $msg;
    }

    private function getSubcategoriaMessages(): string
    {
        $msg = '';
        
        if (isset($_GET['success'])) {
            $type = $_GET['success'];
            $text = match($type) {
                'created' => 'Subcategoria criada com sucesso!',
                'updated' => 'Subcategoria atualizada com sucesso!',
                'deleted' => 'Subcategoria excluída com sucesso!',
                default => 'Operação realizada com sucesso!'
            };
            $msg = '<div class="alert alert-success" style="background:#065f46;color:#d1fae5;padding:15px;border-radius:10px;margin-bottom:20px;">' . $text . '</div>';
        }
        
        if (isset($_GET['error'])) {
            $type = $_GET['error'];
            $count = $_GET['count'] ?? 0;
            $text = match($type) {
                'has_products' => "Não é possível excluir esta subcategoria. Existem {$count} produto(s) vinculado(s) a ela.",
                'delete_failed' => 'Erro ao excluir a subcategoria. Tente novamente.',
                'notfound' => 'Subcategoria não encontrada.',
                default => 'Ocorreu um erro. Tente novamente.'
            };
            $msg = '<div class="alert alert-error" style="background:#7f1d1d;color:#fecaca;padding:15px;border-radius:10px;margin-bottom:20px;">' . $text . '</div>';
        }
        
        return $msg;
    }
}
