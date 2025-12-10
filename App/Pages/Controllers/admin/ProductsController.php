<?php
/**
 * ProductsController - Gerenciamento de Produtos (Admin)
 * 
 * @package App\Pages\Controllers\admin
 */

namespace App\Pages\Controllers\admin;

use Core\ViewerPlace;
use App\Product\Models\ProductModel;
use App\User\Models\FuncionarioModel;

class ProductsController
{
    /**
     * Lista todos os produtos
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

        $products = ProductModel::findAll();
        $productRows = $this->generateProductRows($products);

        echo ViewerPlace::render('admin-products-list', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'product_rows' => $productRows
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

        echo ViewerPlace::render('admin-products-create', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador'
        ]);
    }

    /**
     * Salva novo produto
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
        $preco = floatval($_POST['preco'] ?? 0);
        $codigo = trim($_POST['codigo'] ?? '');
        $categoria = (int)($_POST['categoria'] ?? 1);
        $subCategoria = (int)($_POST['sub_categoria'] ?? 1);
        $descricao = trim($_POST['descricao'] ?? '');
        $estoque = (int)($_POST['estoque'] ?? 0);
        $desconto = (int)($_POST['desconto'] ?? 0);

        $produto = ProductModel::createProduct(
            $nome, $preco, $codigo, $categoria, $subCategoria,
            null, null, null, $descricao, $desconto, $estoque
        );

        if ($produto) {
            header('Location: /admin/products?success=created');
        } else {
            header('Location: /admin/products/create?error=failed');
        }
        exit;
    }

    /**
     * Formulário de edição
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

        $produto = ProductModel::findById($id);
        
        if (!$produto) {
            header('Location: /admin/products?error=notfound');
            exit;
        }

        // Gera HTML das imagens existentes
        $imagesHtml = $this->generateProductImagesHtml($produto);

        echo ViewerPlace::render('admin-products-edit', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'product_id' => $produto->getId(),
            'product_name' => htmlspecialchars($produto->getNome()),
            'product_price' => $produto->getPreco(),
            'product_code' => htmlspecialchars($produto->getCod()),
            'product_description' => htmlspecialchars($produto->getDescricao() ?? ''),
            'product_stock' => $produto->getEstoque(),
            'product_discount' => $produto->getDesconto() ?? 0,
            'product_tamanho' => htmlspecialchars($produto->getTamanho() ?? ''),
            'product_cor' => htmlspecialchars($produto->getCor() ?? ''),
            'product_material' => htmlspecialchars($produto->getMaterial() ?? ''),
            'product_pesoliq' => htmlspecialchars($produto->getPesoLiq() ?? ''),
            'product_pesototal' => htmlspecialchars($produto->getPesoTotal() ?? ''),
            'product_images' => $imagesHtml,
            'product_status_checked' => $produto->getStatus() === '1' ? 'checked' : ''
        ]);
    }

    /**
     * Atualiza produto
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

        $produto = ProductModel::findById($id);
        
        if (!$produto) {
            header('Location: /admin/products?error=notfound');
            exit;
        }

        $produto->update([
            'nome' => trim($_POST['nome'] ?? $produto->getNome()),
            'preco' => floatval($_POST['preco'] ?? $produto->getPreco()),
            'descricao' => trim($_POST['descricao'] ?? $produto->getDescricao()),
            'estoque' => (int)($_POST['estoque'] ?? $produto->getEstoque()),
            'desconto' => (int)($_POST['desconto'] ?? $produto->getDesconto()),
            'tamanho' => trim($_POST['tamanho'] ?? $produto->getTamanho()),
            'cor' => trim($_POST['cor'] ?? $produto->getCor()),
            'material' => trim($_POST['material'] ?? $produto->getMaterial()),
            'pesoliq' => trim($_POST['pesoliq'] ?? $produto->getPesoLiq()),
            'pesototal' => trim($_POST['pesototal'] ?? $produto->getPesoTotal())
        ]);

        // Processa upload de novas imagens
        if (isset($_FILES['novas_imagens']) && !empty($_FILES['novas_imagens']['name'][0])) {
            $uploadDir = __DIR__ . '/../../../../public_html/assets/image/products/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            foreach ($_FILES['novas_imagens']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['novas_imagens']['error'][$key] === UPLOAD_ERR_OK) {
                    $originalName = $_FILES['novas_imagens']['name'][$key];
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    
                    // Valida extensão
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        continue;
                    }
                    
                    // Gera nome único
                    $filename = 'product_' . $id . '_' . time() . '_' . $key . '.' . $ext;
                    $uploadPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($tmpName, $uploadPath)) {
                        $imagePath = '/assets/image/products/' . $filename;
                        $produto->addImagem($imagePath);
                    }
                }
            }
        }

        header('Location: /admin/products/' . $id . '/edit?success=updated');
        exit;
    }

    /**
     * Deleta produto
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

        ProductModel::delete($id);
        
        header('Location: /admin/products?success=deleted');
        exit;
    }

    private function generateProductRows(array $products): string
    {
        if (empty($products)) {
            return '<tr><td colspan="7" style="text-align:center;color:#a0a0a0;">Nenhum produto encontrado</td></tr>';
        }

        $html = '';
        foreach ($products as $p) {
            $status = $p->getEstoque() > 0 ? 
                '<span class="badge status-active">Ativo</span>' : 
                '<span class="badge status-inactive">Esgotado</span>';
            
            $preco = 'R$ ' . number_format($p->getPreco(), 2, ',', '.');
            
            $html .= "<tr>
                <td>{$p->getId()}</td>
                <td>{$p->getNome()}</td>
                <td>{$p->getCod()}</td>
                <td>{$preco}</td>
                <td>{$p->getEstoque()}</td>
                <td>{$status}</td>
                <td class='actions'>
                    <a href='/admin/products/{$p->getId()}/edit' class='btn-edit'>Editar</a>
                    <a href='/admin/products/{$p->getId()}/delete' class='btn-delete' onclick='return confirm(\"Deseja excluir?\")'>Excluir</a>
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

    /**
     * Gera HTML das imagens do produto para edição
     */
    private function generateProductImagesHtml(ProductModel $produto): string
    {
        $imagens = $produto->getImagens();
        
        if (empty($imagens)) {
            return '<p style="color: #666; text-align: center; padding: 1rem;">Nenhuma imagem cadastrada</p>';
        }

        $html = '';
        $productId = $produto->getId();
        foreach ($imagens as $img) {
            $imgId = $img['id'];
            $imgPath = htmlspecialchars($img['imagem']);
            $html .= "
            <div class='image-item' style='position: relative; border-radius: 8px; overflow: hidden; aspect-ratio: 1; background: #1a1a1a;'>
                <img src='{$imgPath}' alt='Imagem do produto' style='width: 100%; height: 100%; object-fit: cover;'>
                <button type='button' onclick='confirmDeleteImage({$imgId}, {$productId})' 
                    style='position: absolute; top: 5px; right: 5px; background: #ff4444; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 14px;' title='Excluir imagem'>&times;</button>
            </div>";
        }
        return $html;
    }

    /**
     * Processa upload de novas imagens
     */
    public function uploadImages($id)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $produto = ProductModel::findById($id);
        
        if (!$produto) {
            header('Location: /admin/products?error=notfound');
            exit;
        }

        // Processa upload de novas imagens
        if (isset($_FILES['novas_imagens']) && !empty($_FILES['novas_imagens']['name'][0])) {
            $uploadDir = __DIR__ . '/../../../../public_html/assets/image/products/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            foreach ($_FILES['novas_imagens']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['novas_imagens']['error'][$key] === UPLOAD_ERR_OK) {
                    $originalName = $_FILES['novas_imagens']['name'][$key];
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    
                    // Valida extensão
                    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        continue;
                    }
                    
                    // Gera nome único
                    $filename = 'product_' . $id . '_' . time() . '_' . $key . '.' . $ext;
                    $uploadPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($tmpName, $uploadPath)) {
                        $imagePath = '/assets/image/products/' . $filename;
                        $produto->addImagem($imagePath);
                    }
                }
            }
        }

        header('Location: /admin/products/' . $id . '/edit?success=images_uploaded');
        exit;
    }

    /**
     * Deleta uma imagem do produto
     */
    public function deleteImage($productId, $imageId)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        ProductModel::deleteImagem($imageId);

        header('Location: /admin/products/' . $productId . '/edit?success=image_deleted');
        exit;
    }
}
