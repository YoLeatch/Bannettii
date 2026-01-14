<?php
/**
 * CatalogController - Controlador do catálogo de produtos
 * 
 * Exibe a página de catálogo com todos os produtos e filtros.
 * 
 * @package App\Pages\Controllers
 */

namespace App\Pages\Controllers;

use Core\ViewerPlace;
use App\Product\Models\ProductModel;
use App\Product\Models\CategoryModel;

class CatalogController {
    
    /**
     * Exibe o catálogo de produtos
     */
    public function handle() {
        // Obtém parâmetros de filtro
        $categoryFilter = $_GET['category'] ?? null;
        $searchTerm = $_GET['search'] ?? null;
        $sortBy = $_GET['sort'] ?? 'relevance';
        $minPrice = isset($_GET['min_price']) ? (float) $_GET['min_price'] : null;
        $maxPrice = isset($_GET['max_price']) ? (float) $_GET['max_price'] : null;
        
        // Busca produtos
        if ($searchTerm) {
            $produtos = ProductModel::search($searchTerm);
        } elseif ($categoryFilter) {
            // Busca categoria pelo nome
            $categoria = CategoryModel::findByNome($categoryFilter);
            $produtos = $categoria ? ProductModel::findByCategoria($categoria->getId()) : [];
        } else {
            $produtos = ProductModel::findAll();
        }
        
        // Filtro de preço
        if ($minPrice !== null || $maxPrice !== null) {
            $produtos = array_filter($produtos, function($produto) use ($minPrice, $maxPrice) {
                $preco = $produto->getPrecoFinal();
                if ($minPrice !== null && $preco < $minPrice) return false;
                if ($maxPrice !== null && $preco > $maxPrice) return false;
                return true;
            });
        }
        
        // Ordenação
        $produtos = $this->sortProducts($produtos, $sortBy);
        
        // Gera o HTML do grid de produtos
        $productsGridHtml = $this->buildProductsGrid($produtos);
        
        // Gera o HTML das categorias para filtro
        $categoriesHtml = $this->buildCategoriesFilter();
        
        // Contador de produtos
        $totalProdutos = count($produtos);
        
        // Renderiza a view
        echo ViewerPlace::render('catalogo', [
            'products_grid' => $productsGridHtml,
            'categories_filter' => $categoriesHtml,
            'total_produtos' => $totalProdutos,
            'search_term' => htmlspecialchars($searchTerm ?? ''),
            'current_category' => htmlspecialchars($categoryFilter ?? 'Todos')
        ]);
    }
    
    /**
     * Ordena produtos
     */
    private function sortProducts(array $produtos, string $sortBy): array {
        switch ($sortBy) {
            case 'price_asc':
                usort($produtos, fn($a, $b) => $a->getPrecoFinal() <=> $b->getPrecoFinal());
                break;
            case 'price_desc':
                usort($produtos, fn($a, $b) => $b->getPrecoFinal() <=> $a->getPrecoFinal());
                break;
            case 'newest':
                usort($produtos, fn($a, $b) => strtotime($b->getData()) <=> strtotime($a->getData()));
                break;
            // 'relevance' - mantém ordem padrão
        }
        return $produtos;
    }
    
    /**
     * Constrói o HTML do grid de produtos
     */
    private function buildProductsGrid(array $produtos): string {
        if (empty($produtos)) {
            return '
            <div class="no-products" style="text-align: center; padding: 3rem; grid-column: 1/-1;">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" 
                    stroke="#666" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <h3 style="color: #fff; margin-top: 1rem;">Nenhum produto encontrado</h3>
                <p style="color: #888;">Tente ajustar os filtros ou buscar por outro termo.</p>
            </div>';
        }
        
        $html = '<div class="products-grid">';
        
        foreach ($produtos as $produto) {
            // Imagem do produto
            $imagens = $produto->getImagens();
            $imagem = !empty($imagens) ? $imagens[0]['imagem'] : '/assets/image/placeholder.png';
            
            // Preço
            $precoOriginal = $produto->getPreco();
            $precoFinal = $produto->getPrecoFinal();
            $temDesconto = $produto->getDesconto() > 0;
            
            // Rating
            $rating = $produto->getMediaAvaliacoes();
            $stars = $this->generateStars($rating);
            
            // Estoque
            $inStock = $produto->inStock();
            $stockClass = $inStock ? 'in-stock' : 'out-stock';
            $stockText = $inStock ? 'Em estoque' : 'Esgotado';
            
            $html .= '
            <div class="product-card">
                <a href="/produto/' . $produto->getId() . '" class="product-link">
                    <div class="product-image">
                        <img src="' . htmlspecialchars($imagem) . '" alt="' . htmlspecialchars($produto->getNome()) . '">
                        ' . ($temDesconto ? '<span class="discount-badge">-' . $produto->getDesconto() . '%</span>' : '') . '
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">' . htmlspecialchars($produto->getNome()) . '</h3>
                        <div class="product-rating">
                            ' . $stars . '
                            <span class="rating-count">(' . count($produto->getAvaliacoes()) . ')</span>
                        </div>
                        <div class="product-price">';
            
            if ($temDesconto) {
                $html .= '
                            <span class="price-original">R$ ' . number_format($precoOriginal, 2, ',', '.') . '</span>
                            <span class="price-final">R$ ' . number_format($precoFinal, 2, ',', '.') . '</span>';
            } else {
                $html .= '
                            <span class="price-final">R$ ' . number_format($precoFinal, 2, ',', '.') . '</span>';
            }
            
            $html .= '
                        </div>
                        <span class="stock-status ' . $stockClass . '">' . $stockText . '</span>
                    </div>
                </a>
                <a href="/produto/' . $produto->getId() . '" class="btn-add-cart" style="text-decoration: none; text-align: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    Ver Detalhes
                </a>
            </div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    /**
     * Gera estrelas de rating
     */
    private function generateStars(float $rating): string {
        $fullStars = floor($rating);
        $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;
        
        $html = '';
        for ($i = 0; $i < $fullStars; $i++) {
            $html .= '<span class="star full">★</span>';
        }
        if ($halfStar) {
            $html .= '<span class="star half">★</span>';
        }
        for ($i = 0; $i < $emptyStars; $i++) {
            $html .= '<span class="star empty">☆</span>';
        }
        
        return $html;
    }
    
    /**
     * Constrói o HTML das categorias para filtro
     */
    private function buildCategoriesFilter(): string {
        $categorias = CategoryModel::findAll();
        
        if (empty($categorias)) {
            return '<p style="color: #888;">Nenhuma categoria disponível.</p>';
        }
        
        $currentCategory = $_GET['category'] ?? '';
        $html = '';
        
        foreach ($categorias as $categoria) {
            $nome = $categoria->getCategoria();
            $isActive = strtolower($currentCategory) === strtolower($nome) ? 'checked' : '';
            
            $html .= '
            <label class="filter-option">
                <input type="checkbox" name="category" value="' . htmlspecialchars(strtolower($nome)) . '" ' . $isActive . '>
                <span>' . htmlspecialchars($nome) . '</span>
            </label>';
        }
        
        return $html;
    }
}
