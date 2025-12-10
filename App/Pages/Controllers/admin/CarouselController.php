<?php
/**
 * CarouselController - Gerenciamento do Carrossel (Admin)
 * 
 * @package App\Pages\Controllers\admin
 */

namespace App\Pages\Controllers\admin;

use Core\ViewerPlace;

class CarouselController
{
    private const BANNERS_FILE = __DIR__ . '/../../../../storage/JSON/banners.json';

    /**
     * Lista slides do carrossel
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

        $banners = $this->loadBanners();
        $carouselItems = $this->generateCarouselItems($banners);

        echo ViewerPlace::render('admin-carousel', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'slides_list' => $carouselItems,
            'messages' => $this->getMessages(),
            'total_slides' => count($banners)
        ]);
    }

    /**
     * Exibe formulário de edição
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

        $banners = $this->loadBanners();
        $banner = null;
        foreach ($banners as $b) {
            if ($b['id'] == $id) {
                $banner = $b;
                break;
            }
        }

        if (!$banner) {
            header('Location: /admin/carousel?error=notfound');
            exit;
        }

        echo ViewerPlace::render('admin-carousel-edit', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'id' => $banner['id'],
            'titulo' => htmlspecialchars($banner['titulo']),
            'descricao' => htmlspecialchars($banner['descricao']),
            'link' => htmlspecialchars($banner['link']),
            'imagem' => $banner['imagem'],
            'checked_ativo' => $banner['ativo'] ? 'checked' : ''
        ]);
    }

    private function getMessages(): string {
        $msg = '';
        if (isset($_GET['success'])) {
            $msg = '<div class="success-message">Operação realizada com sucesso!</div>';
        }
        if (isset($_GET['error'])) {
            $msg = '<div class="error-message">Ocorreu um erro.</div>';
        }
        return $msg;
    }

    /**
     * Adiciona novo slide
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

        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $link = trim($_POST['link'] ?? '#');
        $ativo = isset($_POST['ativo']) ? true : false;
        
        // Upload de imagem
        $imagem = '';
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../../public_html/uploads/banners/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
            $filename = 'banner_' . time() . '.' . $ext;
            $uploadPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $uploadPath)) {
                $imagem = '/uploads/banners/' . $filename;
            }
        }

        if (!empty($imagem)) {
            $banners = $this->loadBanners();
            $banners[] = [
                'id' => time(),
                'titulo' => $titulo,
                'descricao' => $descricao,
                'imagem' => $imagem,
                'link' => $link,
                'ativo' => $ativo,
                'ordem' => count($banners) + 1
            ];
            $this->saveBanners($banners);
        }

        header('Location: /admin/carousel?success=created');
        exit;
    }

    /**
     * Atualiza slide
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

        $banners = $this->loadBanners();
        
        foreach ($banners as &$banner) {
            if ($banner['id'] == $id) {
                $banner['titulo'] = trim($_POST['titulo'] ?? $banner['titulo']);
                $banner['descricao'] = trim($_POST['descricao'] ?? $banner['descricao']);
                $banner['link'] = trim($_POST['link'] ?? $banner['link']);
                $banner['ativo'] = isset($_POST['ativo']) ? true : false;
                
                // Upload nova imagem se fornecida
                if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../../../../public_html/uploads/banners/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
                    $filename = 'banner_' . time() . '.' . $ext;
                    $uploadPath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($_FILES['imagem']['tmp_name'], $uploadPath)) {
                        $banner['imagem'] = '/uploads/banners/' . $filename;
                    }
                }
                break;
            }
        }
        
        $this->saveBanners($banners);

        header('Location: /admin/carousel?success=updated');
        exit;
    }

    /**
     * Remove slide
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

        $banners = $this->loadBanners();
        $banners = array_filter($banners, fn($b) => $b['id'] != $id);
        $banners = array_values($banners);
        $this->saveBanners($banners);

        header('Location: /admin/carousel?success=deleted');
        exit;
    }

    /**
     * Altera ordem dos slides
     */
    public function reorder()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            echo json_encode(['success' => false]);
            return;
        }

        $ordem = json_decode(file_get_contents('php://input'), true);
        
        if (is_array($ordem)) {
            $banners = $this->loadBanners();
            $newBanners = [];
            
            foreach ($ordem as $index => $id) {
                foreach ($banners as $banner) {
                    if ($banner['id'] == $id) {
                        $banner['ordem'] = $index + 1;
                        $newBanners[] = $banner;
                        break;
                    }
                }
            }
            
            $this->saveBanners($newBanners);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    }

    private function loadBanners(): array
    {
        if (!file_exists(self::BANNERS_FILE)) {
            return [];
        }
        $content = file_get_contents(self::BANNERS_FILE);
        return json_decode($content, true) ?? [];
    }

    private function saveBanners(array $banners): void
    {
        $dir = dirname(self::BANNERS_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(self::BANNERS_FILE, json_encode($banners, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function generateCarouselItems(array $banners): string
    {
        if (empty($banners)) {
            return '<p class="no-items">Nenhum slide cadastrado. Adicione seu primeiro slide!</p>';
        }

        $html = '';
        foreach ($banners as $b) {
            $status = $b['ativo'] ? '<span class="badge status-active">Ativo</span>' : '<span class="badge status-inactive">Inativo</span>';
            
            $html .= "<div class='slide-item'>
                <img src='{$b['imagem']}' alt='{$b['titulo']}'>
                <div class='slide-info'>
                    <h3>{$b['titulo']}</h3>
                    <p>{$b['descricao']}</p>
                    <div style='margin-top: 10px;'>{$status}</div>
                </div>
                <div class='slide-actions'>
                    <a href='/admin/carousel/{$b['id']}/edit' class='btn-action'>Editar</a>
                    <a href='/admin/carousel/{$b['id']}/delete' class='btn-delete' onclick='return confirm(\"Remover este slide?\")'>Excluir</a>
                </div>
            </div>";
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
