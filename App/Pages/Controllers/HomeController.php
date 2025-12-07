<?php

namespace App\Pages\Controllers;

use Core\ViewerPlace;
use Core\ConnectionFactory;
use App\Product\ProductModel;
use App\Content\CarouselModel;
use App\User\Middlewares\AuthMiddleware;
use PDO;

class HomeController
{
    public function index()
    {
        $logged_in = AuthMiddleware::check();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Fetch Carousel
        $carouselModel = new CarouselModel();
        $slides = $carouselModel->getAll();
        $carouselHtml = '';
        $activeClass = 'active';

        foreach ($slides as $slide) {
            $image = htmlspecialchars($slide['image_url']);
            $link = htmlspecialchars($slide['link_url']);
            $caption = htmlspecialchars($slide['caption']);
            
            $carouselHtml .= <<<HTML
            <div class="carousel-slide {$activeClass}" style="background-image: url('{$image}');">
                <div class="carousel-content">
                    <h2>{$caption}</h2>
                    <p>Confira nossa coleção</p>
                    <a href="{$link}" class="btn-primary">Ver Coleção</a>
                </div>
            </div>
HTML;
            $activeClass = ''; 
        }
        
        if (empty($carouselHtml)) {
             $carouselHtml = <<<HTML
            <div class="carousel-slide active" style="background-image: url('https://placehold.co/1200x400/e0e0e0/333?text=Bem-vindo+a+Bennettii');">
                <div class="carousel-content">
                    <h2>Bem-vindo à Bennettii</h2>
                    <p>Moda com estilo e conforto</p>
                    <a href="/products" class="btn-primary">Ver Produtos</a>
                </div>
            </div>
HTML;
        }

        // Fetch Products
        $allProducts = ProductModel::fetchAll(1); 
        
        usort($allProducts, fn($a, $b) => $b->getId() <=> $a->getId());
        $newArrivals = array_slice($allProducts, 0, 4);
        
        $featured = array_slice($allProducts, 0, 4); 
        if (count($allProducts) > 4) {
             $featured = array_slice($allProducts, 4, 4);
        }

        $newArrivalsHtml = $this->generateProductGrid($newArrivals);
        $featuredHtml = $this->generateProductGrid($featured);

        if($logged_in){
            // Check if user is admin
            $isAdmin = $this->isUserAdmin($_SESSION['user_id'] ?? 0);
            
            $adminLink = '';
            if ($isAdmin) {
                $adminLink = '
                <a href="/admin/dashboard" class="nav-item admin-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    Dashboard Admin
                </a>';
            }
            
            $sidebar = ' <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <!--<h3>Menu</h3>-->
            <button id="closeSidebar" class="close-btn">&times;</button>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-group">
                ' . $adminLink . '
                <a href="/perfil" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Gerenciar Conta
                </a>
                <a href="/orders" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    Meus Pedidos
                </a>
                <a href="/addresses" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    Endereços de Entrega
                </a>
                <a href="/payments" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                    Formas de Pagamento
                </a>
                <a href="/wishlist" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                        </path>
                    </svg>
                    Lista de Desejos
                </a>
                <a href="/help" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    Central de Ajuda
                </a>
            </div>
            <div class="nav-footer">
                <a href="/logout" class="logout-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Sair
                </a>
            </div>
        </nav>
    </aside>';
        } else {
            $sidebar = ' <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <!--<h3>Menu</h3>-->
            <button id="closeSidebar" class="close-btn">&times;</button>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-group">
                <a href="/login" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Criar conta/Entrar
                </a>
            </div>
        </nav>
    </aside>';
        }

        echo ViewerPlace::render('index', [
            'carousel_slides' => $carouselHtml,
            'new_arrivals' => $newArrivalsHtml,
            'featured_products' => $featuredHtml,
            'sidebar' => $sidebar
        ]);
    }

    /**
     * Check if user is an administrator
     */
    private function isUserAdmin(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }
        
        try {
            $pdo = ConnectionFactory::getConnection('read_only');
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM funcionario f
                JOIN funcionario_cargo fc ON fc.funcionario_id = f.id
                JOIN cargo c ON c.id = fc.cargo_id
                WHERE f.id = ? AND f.status = '1' AND fc.status = '1'
            ");
            $stmt->execute([$userId]);
            return (int)$stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function generateProductGrid(array $products): string
    {
        $html = '';
        foreach ($products as $product) {
            $id = $product->getId();
            $name = htmlspecialchars($product->getNome());
            $price = number_format($product->getPreco(), 2, ',', '.');
            $images = $product->getImages();
            $image = !empty($images) ? $images[0]['imagem'] : '/assets/image/placeholder.png';
            
            $html .= <<<HTML
            <div class="product-card">
                <div class="product-image">
                    <img src="{$image}" alt="{$name}">
                    <!-- <span class="tag-new">Novo</span> -->
                </div>
                <div class="product-info">
                    <h3>{$name}</h3>
                    <p class="price">R$ {$price}</p>
                    <a href="/produto/{$id}" class="btn-add-cart" style="text-decoration:none;display:block;text-align:center;">Ver Detalhes</a>
                </div>
            </div>
HTML;
        }
        return $html;
    }
}