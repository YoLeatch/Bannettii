<?php

namespace App\Pages\Controllers;

use Core\ViewerPlace;
use Core\ConnectionFactory;
use App\Product\Models\ProductModel;
use App\Pages\Models\BannerModel;
use App\User\Models\FuncionarioModel;
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

        // Busca banners visíveis usando o BannerModel (dados do JSON)
        $banners = BannerModel::findAllVisible();
        $carouselHtml = '';
        $activeClass = 'active';

        foreach ($banners as $banner) {
            $image = htmlspecialchars($banner->getImagem());
            $link = htmlspecialchars($banner->getLink() ?? '/catalogo');
            $titulo = htmlspecialchars($banner->getTitulo());
            $descricao = htmlspecialchars($banner->getDescricao() ?? 'Confira nossa coleção');
            
            $carouselHtml .= <<<HTML
            <div class="carousel-slide {$activeClass}" style="background-image: url('{$image}');">
                <div class="carousel-content">
                    <h2>{$titulo}</h2>
                    <p>{$descricao}</p>
                    <a href="{$link}" class="btn-primary">Ver Coleção</a>
                </div>
            </div>
HTML;
            $activeClass = ''; 
        }
        
        // Banner padrão caso não haja banners cadastrados
        if (empty($carouselHtml)) {
             $carouselHtml = <<<HTML
            <div class="carousel-slide active" style="background-image: url('https://placehold.co/1200x400/e0e0e0/333?text=Bem-vindo+a+Bennettii');">
                <div class="carousel-content">
                    <h2>Bem-vindo à Bennettii</h2>
                    <p>Moda com estilo e conforto</p>
                    <a href="/catalogo" class="btn-primary">Ver Produtos</a>
                </div>
            </div>
HTML;
        }

        if($logged_in){
            // Check if user is admin
            $isAdmin = $this->isUserFuncionario($_SESSION['user_id'] ?? 0);
            
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
                <a href="/pedidos" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    Meus Pedidos
                </a>
                <a href="/carrinho" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    Meu Carrinho
                </a>
                <a href="/enderecos" class="nav-item">
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

        // Busca todos os produtos ativos usando o novo modelo
        $allProducts = ProductModel::findAll();
        
        // Ordena por ID decrescente (mais novos primeiro)
        usort($allProducts, fn($a, $b) => $b->getId() <=> $a->getId());
        
        // Novidades: 4 produtos mais recentes
        $newArrivals = array_slice($allProducts, 0, 4);
        
        // Destaques: próximos 4 produtos (ou os primeiros se não houver mais)
        $featured = array_slice($allProducts, 0, 4); 
        if (count($allProducts) > 4) {
             $featured = array_slice($allProducts, 4, 4);
        }

        $newArrivalsHtml = $this->generateProductGrid($newArrivals);
        $featuredHtml = $this->generateProductGrid($featured);

        echo ViewerPlace::render('index', [
            'carousel_slides' => $carouselHtml,
            'new_arrivals' => $newArrivalsHtml,
            'featured_products' => $featuredHtml,
            'sidebar' => $sidebar
        ]);
    }

    /**
     * Verifica se o usuário é um administrador/funcionário
     * 
     * Utiliza o FuncionarioModel para verificar se o usuário
     * possui algum cargo ativo no sistema.
     * 
     * @param int $userId ID do usuário
     * @return bool Retorna true se for funcionário com cargo ativo
     */
    private function isUserFuncionario(int $userId): bool
    {
        // ID inválido não pode ser admin
        if ($userId <= 0) {
            return false;
        }
        
        try {
            // Busca o funcionário pelo ID usando o modelo
            $funcionario = FuncionarioModel::findById($userId);
            
            // Se não encontrou funcionário, não é admin
            if (!$funcionario) {
                return false;
            }
            
            // Verifica se possui algum cargo ativo
            return !empty($funcionario->getCargos());
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Gera o HTML do grid de produtos
     * 
     * @param array $products Lista de ProductModel
     * @return string HTML gerado
     */
    private function generateProductGrid(array $products): string
    {
        $html = '';
        foreach ($products as $product) {
            $id = $product->getId();
            $name = htmlspecialchars($product->getNome());
            
            // Usa preço final (com desconto aplicado se houver)
            $precoFinal = $product->getPrecoFinal();
            $price = number_format($precoFinal, 2, ',', '.');
            
            // Busca imagens usando o método correto
            $images = $product->getImagens();
            $image = !empty($images) ? $images[0]['imagem'] : '/assets/image/placeholder.png';
            
            // Mostra tag de desconto se houver
            $descontoTag = '';
            if ($product->getDesconto() && $product->getDesconto() > 0) {
                $descontoTag = '<span class="tag-discount">-' . $product->getDesconto() . '%</span>';
            }
            
            $html .= <<<HTML
            <div class="product-card">
                <div class="product-image">
                    <img src="{$image}" alt="{$name}">
                    {$descontoTag}
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