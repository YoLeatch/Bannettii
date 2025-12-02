<?php

namespace App\Pages\Controllers;

use App\Product\ProductModel;
use App\Product\CategoryModel;
use App\Product\SubCategoryModel;
use Core\ViewerPlace;

class AdminProductController
{
    public function index()
    {
        // Check admin session
        if (empty($_SESSION['admin_logged_in'])) {
            header('Location: /admin/login');
            exit;
        }

        $categories = CategoryModel::fetchAll();
        // Subcategories could be fetched via AJAX or loaded all
        $subCategories = SubCategoryModel::fetchAll();

        $categoryOptions = '';
        foreach ($categories as $cat) {
            $categoryOptions .= "<option value='{$cat->getId()}'>{$cat->getCategoria()}</option>";
        }

        $subCategoryOptions = ''; // Assuming subcategories are loaded or handled via AJAX. For now, empty or basic.
        // If we want to show all subcategories (might be too many), or just a placeholder.
        // Let's assume we pass all for now or let the user select category first (which requires JS).
        // For simplicity, I'll list all if available, or just leave empty if dependent on JS.
        if (!empty($subCategories)) {
             foreach ($subCategories as $sub) {
                $subCategoryOptions .= "<option value='{$sub->getId()}'>{$sub->getSubCategoria()}</option>";
            }
        }

        $data = [
            'admin_name' => $_SESSION['admin_name'] ?? 'Admin',
            'admin_role' => 'Administrador',
            'category_options' => $categoryOptions,
            'subcategory_options' => $subCategoryOptions,
            'error' => '',
            'success' => ''
        ];

        echo ViewerPlace::render('gerenciar-produto', $data);
    }

    public function store()
    {
        if (empty($_SESSION['admin_logged_in'])) {
            header('Location: /admin/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $_POST['nome'] ?? '';
            $descricao = $_POST['descricao'] ?? '';
            $preco = (float)($_POST['preco'] ?? 0);
            $estoque = (int)($_POST['estoque'] ?? 0);
            $desconto = (int)($_POST['desconto'] ?? 0);
            $categoriaId = (int)($_POST['categoria'] ?? 0);
            $subCategoriaId = (int)($_POST['sub_categoria'] ?? 0);
            
            // Extra fields mapped to existing columns
            $tamanho = $_POST['tamanho'] ?? ''; // Map to dimensoes
            $material = $_POST['material'] ?? ''; // Append to description
            $cor = $_POST['cor'] ?? ''; // Append to description
            
            $dimensoes = $tamanho;
            $fullDescription = $descricao . "\n\nMaterial: $material\nCor: $cor";
            
            $codigo = strtoupper(substr($nome, 0, 3)) . rand(1000, 9999);
            $pesoliq = $_POST['pesoliq'] ?? '0kg';
            $pesototal = $_POST['pesototal'] ?? '0kg';
            $status = '1';

            // Create Product
            $product = ProductModel::create(
                $nome, $preco, $codigo, $categoriaId, $subCategoriaId, 
                $pesoliq, $pesototal, $dimensoes, $fullDescription, 
                $status, $desconto, $estoque
            );

            if ($product) {
                // Handle Images
                if (!empty($_FILES['images']['name'][0])) {
                    $uploadedImages = $this->uploadImages($_FILES['images']);
                    $product->saveImages($uploadedImages);
                }

                // Redirect or show success
                header('Location: /admin/products?success=1');
                exit;
            } else {
                // Handle error
                echo "Erro ao criar produto.";
            }
        }
    }

    private function uploadImages($files): array
    {
        $uploadedPaths = [];
        $targetDir = __DIR__ . '/../../../Public/assets/uploads/';
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        foreach ($files['name'] as $key => $name) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $tmpName = $files['tmp_name'][$key];
                $fileName = uniqid() . '_' . basename($name);
                $targetFilePath = $targetDir . $fileName;
                
                if (move_uploaded_file($tmpName, $targetFilePath)) {
                    $uploadedPaths[] = '/assets/uploads/' . $fileName;
                }
            }
        }
        return $uploadedPaths;
    }
}
