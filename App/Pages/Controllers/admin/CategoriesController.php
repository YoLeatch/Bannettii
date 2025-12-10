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

        echo ViewerPlace::render('admin-tabela-categorias', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'category_rows' => $categoryRows
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
            'admin_role' => 'Administrador'
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
            'category_name' => htmlspecialchars($categoria['categoria'])
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
        $stmt = $pdo->prepare("DELETE FROM categoria WHERE id = :id");
        $stmt->execute(['id' => $id]);

        header('Location: /admin/categories?success=deleted');
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

        echo ViewerPlace::render('admin-subcategorias-lista', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'subcategory_rows' => $subcategoryRows
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
