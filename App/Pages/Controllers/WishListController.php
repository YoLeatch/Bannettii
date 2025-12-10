<?php
/**
 * WishListController - Controlador da Lista de Desejos
 * 
 * Gerencia favoritos usando cookies para armazenamento.
 * 
 * @package App\Pages\Controllers
 */

namespace App\Pages\Controllers;

use Core\ViewerPlace;
use App\Product\Models\ProductModel;

class WishListController
{
    private const COOKIE_NAME = 'bennettii_wishlist';
    private const COOKIE_EXPIRY = 60 * 60 * 24 * 30; // 30 dias

    /**
     * Exibe a página da lista de desejos
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Busca os IDs dos produtos favoritados
        $wishlistIds = $this->getWishlistIds();
        
        // Busca os produtos
        $products = [];
        foreach ($wishlistIds as $id) {
            $product = ProductModel::findById((int)$id);
            if ($product) {
                $products[] = $product;
            }
        }
        
        // Gera HTML dos produtos
        $productsHtml = $this->generateProductsHtml($products);
        
        // Conta total de itens
        $totalItems = count($products);
        
        echo ViewerPlace::render('wishlist', [
            'products' => $productsHtml,
            'total_items' => $totalItems,
            'empty_message' => $totalItems === 0 ? $this->getEmptyMessage() : ''
        ]);
    }

    /**
     * Adiciona produto à lista de desejos (AJAX)
     */
    public function add()
    {
        header('Content-Type: application/json');
        
        $productId = $_POST['product_id'] ?? $_GET['product_id'] ?? null;
        
        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'ID do produto não fornecido']);
            return;
        }
        
        // Verifica se o produto existe
        $product = ProductModel::findById((int)$productId);
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Produto não encontrado']);
            return;
        }
        
        $wishlistIds = $this->getWishlistIds();
        
        // Verifica se já está na lista
        if (in_array($productId, $wishlistIds)) {
            echo json_encode([
                'success' => true,
                'message' => 'Produto já está na lista de desejos',
                'action' => 'already_exists',
                'count' => count($wishlistIds)
            ]);
            return;
        }
        
        // Adiciona à lista
        $wishlistIds[] = $productId;
        $this->saveWishlistIds($wishlistIds);
        
        echo json_encode([
            'success' => true,
            'message' => 'Produto adicionado à lista de desejos!',
            'action' => 'added',
            'count' => count($wishlistIds)
        ]);
    }

    /**
     * Remove produto da lista de desejos (AJAX)
     */
    public function remove()
    {
        header('Content-Type: application/json');
        
        $productId = $_POST['product_id'] ?? $_GET['product_id'] ?? null;
        
        if (!$productId) {
            echo json_encode(['success' => false, 'message' => 'ID do produto não fornecido']);
            return;
        }
        
        $wishlistIds = $this->getWishlistIds();
        
        // Remove da lista
        $wishlistIds = array_filter($wishlistIds, fn($id) => $id != $productId);
        $wishlistIds = array_values($wishlistIds); // Reindexa
        
        $this->saveWishlistIds($wishlistIds);
        
        echo json_encode([
            'success' => true,
            'message' => 'Produto removido da lista de desejos',
            'action' => 'removed',
            'count' => count($wishlistIds)
        ]);
    }

    /**
     * Toggle - adiciona ou remove produto (AJAX)
     */
    public function toggle()
    {
        $productId = $_POST['product_id'] ?? $_GET['product_id'] ?? null;
        
        if (!$productId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID do produto não fornecido']);
            return;
        }
        
        $wishlistIds = $this->getWishlistIds();
        
        if (in_array($productId, $wishlistIds)) {
            // Remove
            $_POST['product_id'] = $productId;
            $this->remove();
        } else {
            // Adiciona
            $_POST['product_id'] = $productId;
            $this->add();
        }
    }

    /**
     * Verifica se produto está na lista (AJAX)
     */
    public function check()
    {
        header('Content-Type: application/json');
        
        $productId = $_GET['product_id'] ?? null;
        
        if (!$productId) {
            echo json_encode(['in_wishlist' => false]);
            return;
        }
        
        $wishlistIds = $this->getWishlistIds();
        
        echo json_encode([
            'in_wishlist' => in_array($productId, $wishlistIds),
            'count' => count($wishlistIds)
        ]);
    }

    /**
     * Retorna contagem de itens (AJAX)
     */
    public function count()
    {
        header('Content-Type: application/json');
        echo json_encode(['count' => count($this->getWishlistIds())]);
    }

    /**
     * Obtém IDs da wishlist do cookie
     */
    private function getWishlistIds(): array
    {
        if (!isset($_COOKIE[self::COOKIE_NAME])) {
            return [];
        }
        
        $ids = json_decode($_COOKIE[self::COOKIE_NAME], true);
        return is_array($ids) ? $ids : [];
    }

    /**
     * Salva IDs no cookie
     */
    private function saveWishlistIds(array $ids): void
    {
        $json = json_encode(array_values(array_unique($ids)));
        setcookie(self::COOKIE_NAME, $json, time() + self::COOKIE_EXPIRY, '/');
        $_COOKIE[self::COOKIE_NAME] = $json; // Atualiza para requisição atual
    }

    /**
     * Gera HTML dos produtos da wishlist
     */
    private function generateProductsHtml(array $products): string
    {
        if (empty($products)) {
            return '';
        }
        
        $html = '';
        foreach ($products as $product) {
            $id = $product->getId();
            $nome = htmlspecialchars($product->getNome());
            $preco = number_format($product->getPrecoFinal(), 2, ',', '.');
            
            $images = $product->getImagens();
            $image = !empty($images) ? $images[0]['imagem'] : '/assets/image/placeholder.png';
            
            $desconto = $product->getDesconto();
            $descontoTag = '';
            if ($desconto && $desconto > 0) {
                $descontoTag = '<span class="tag-discount">-' . $desconto . '%</span>';
            }
            
            $html .= <<<HTML
            <div class="wishlist-item" data-product-id="{$id}">
                <div class="wishlist-item-image">
                    <img src="{$image}" alt="{$nome}">
                    {$descontoTag}
                </div>
                <div class="wishlist-item-info">
                    <h3 class="wishlist-item-name">{$nome}</h3>
                    <p class="wishlist-item-price">R$ {$preco}</p>
                </div>
                <div class="wishlist-item-actions">
                    <form action="/carrinho/add" method="POST" class="wishlist-cart-form">
                        <input type="hidden" name="product_id" value="{$id}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn-add-cart-wishlist" title="Adicionar ao Carrinho">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                            Adicionar ao Carrinho
                        </button>
                    </form>
                    <button class="btn-remove-wishlist" onclick="removeFromWishlist({$id})" title="Remover da Lista">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </div>
            </div>
HTML;
        }
        
        return $html;
    }

    /**
     * Mensagem quando a lista está vazia
     */
    private function getEmptyMessage(): string
    {
        return <<<HTML
        <div class="wishlist-empty">
            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
            <h2>Sua lista de desejos está vazia</h2>
            <p>Adicione produtos aos seus favoritos clicando no ícone de coração</p>
            <a href="/catalogo" class="btn-browse">Explorar Produtos</a>
        </div>
HTML;
    }
}
