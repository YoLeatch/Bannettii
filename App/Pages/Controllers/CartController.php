<?php
/**
 * CartController - Controlador do carrinho de compras
 * 
 * Gerencia o carrinho usando sessão PHP.
 * 
 * @package App\Pages\Controllers
 */

namespace App\Pages\Controllers;

use Core\ViewerPlace;
use App\Product\Models\ProductModel;
use App\Sales\CupomModel;

class CartController {
    
    /**
     * Exibe o carrinho de compras
     */
    public function handle() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Inicializa carrinho se não existir
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Gera o HTML do carrinho
        $cartHtml = $this->buildCartHtml();
        $cartSummary = $this->buildCartSummary();
        $cartCount = $this->getCartCount();
        
        // Renderiza a view
        echo ViewerPlace::render('carrinho', [
            'cart_items' => $cartHtml,
            'cart_summary' => $cartSummary,
            'cart_count' => $cartCount
        ]);
    }
    
    /**
     * Adiciona produto ao carrinho
     */
    public function add() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verifica se é requisição AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 1);
        
        if ($productId <= 0) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Produto inválido.'
                ]);
                exit;
            }
            $_SESSION['CART_ERROR'] = 'Produto inválido.';
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }
        
        // Verifica se produto existe
        $produto = ProductModel::findById($productId);
        if (!$produto) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Produto não encontrado.'
                ]);
                exit;
            }
            $_SESSION['CART_ERROR'] = 'Produto não encontrado.';
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }
        
        // Inicializa carrinho se não existir
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Adiciona ou atualiza quantidade
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = [
                'id' => $productId,
                'name' => $produto->getNome(),
                'price' => $produto->getPrecoFinal(),
                'quantity' => $quantity,
                'image' => $this->getProductImage($produto)
            ];
        }
        
        $_SESSION['CART_SUCCESS'] = 'Produto adicionado ao carrinho!';
        
        // Retorna JSON se for AJAX
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Produto adicionado!',
                'cart_count' => $this->getCartCount()
            ]);
            exit;
        }
        
        header("Location: /carrinho");
        exit;
    }
    
    /**
     * Remove produto do carrinho
     */
    public function remove() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $productId = (int) ($_POST['product_id'] ?? 0);
        
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            $_SESSION['CART_SUCCESS'] = 'Produto removido do carrinho.';
        }
        
        // Retorna JSON se for AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'cart_count' => $this->getCartCount(),
                'cart_total' => $this->getCartTotal()
            ]);
            exit;
        }
        
        header("Location: /carrinho");
        exit;
    }
    
    /**
     * Atualiza quantidade de um item
     */
    public function update() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 1);
        
        if (isset($_SESSION['cart'][$productId])) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$productId]);
            } else {
                $_SESSION['cart'][$productId]['quantity'] = $quantity;
            }
        }
        
        // Retorna JSON se for AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            $item = $_SESSION['cart'][$productId] ?? null;
            echo json_encode([
                'success' => true,
                'cart_count' => $this->getCartCount(),
                'cart_total' => $this->getCartTotal(),
                'item_total' => $item ? $item['price'] * $item['quantity'] : 0
            ]);
            exit;
        }
        
        header("Location: /carrinho");
        exit;
    }
    
    /**
     * Limpa o carrinho
     */
    public function clear() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['cart'] = [];
        $_SESSION['CART_SUCCESS'] = 'Carrinho limpo!';
        
        header("Location: /carrinho");
        exit;
    }
    
    /**
     * Retorna contagem de itens no carrinho (API)
     */
    public function count() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        header('Content-Type: application/json');
        echo json_encode(['count' => $this->getCartCount()]);
        exit;
    }
    
    /**
     * Aplica cupom de desconto
     */
    public function applyCupom() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $cupomCode = trim($_POST['cupom_code'] ?? '');
        
        if (empty($cupomCode)) {
            $_SESSION['CUPOM_ERROR'] = 'Digite um código de cupom.';
            header("Location: /carrinho");
            exit;
        }
        
        try {
            // Busca cupom no banco de dados
            $cupom = CupomModel::findByCod($cupomCode);
            
            if (!$cupom) {
                $_SESSION['CUPOM_ERROR'] = 'Cupom não encontrado.';
                header("Location: /carrinho");
                exit;
            }
            
            // Verifica se cupom está válido (ativo e não expirado)
            if (!$cupom->isValid()) {
                $_SESSION['CUPOM_ERROR'] = 'Este cupom expirou ou está inativo.';
                header("Location: /carrinho");
                exit;
            }
            
            // Obtém subcategorias do cupom para verificar se aplica aos produtos do carrinho
            $subCategoriasDosCupom = $cupom->getSubCategorias();
            $cartTotal = $this->getCartTotal();
            
            // Se cupom tem subcategorias específicas, calcula desconto apenas para esses itens
            // Senão, aplica 10% no total
            $desconto = 0;
            $percentualDesconto = 10; // 10% padrão
            
            if (!empty($subCategoriasDosCupom) && !empty($_SESSION['cart'])) {
                // Calcula desconto apenas para produtos das subcategorias válidas
                $subCatIds = array_column($subCategoriasDosCupom, 'id');
                
                foreach ($_SESSION['cart'] as $item) {
                    $produto = ProductModel::findById($item['id']);
                    if ($produto && in_array($produto->getSubCategoria(), $subCatIds)) {
                        $desconto += ($item['price'] * $item['quantity']) * ($percentualDesconto / 100);
                    }
                }
                
                if ($desconto == 0) {
                    $_SESSION['CUPOM_ERROR'] = 'Este cupom não se aplica aos produtos do seu carrinho.';
                    header("Location: /carrinho");
                    exit;
                }
            } else {
                // Cupom geral - aplica no total
                $desconto = $cartTotal * ($percentualDesconto / 100);
            }
            
            // Armazena cupom na sessão
            $_SESSION['cupom'] = [
                'id' => $cupom->getId(),
                'codigo' => $cupom->getCod(),
                'desconto' => $desconto,
                'percentual' => $percentualDesconto
            ];
            
            $_SESSION['CART_SUCCESS'] = 'Cupom ' . $cupom->getCod() . ' aplicado! ' . $percentualDesconto . '% de desconto.';
            
        } catch (\Exception $e) {
            // Fallback: cupons de exemplo se houver erro no banco
            $cuponsValidos = [
                'BENNETTII10' => 10,
                'PRIMEIRACOMPRA' => 15,
                'DESCONTO20' => 20,
            ];
            
            $cupomUpper = strtoupper($cupomCode);
            if (isset($cuponsValidos[$cupomUpper])) {
                $percentual = $cuponsValidos[$cupomUpper];
                $desconto = $this->getCartTotal() * ($percentual / 100);
                
                $_SESSION['cupom'] = [
                    'id' => 0,
                    'codigo' => $cupomUpper,
                    'desconto' => $desconto,
                    'percentual' => $percentual
                ];
                
                $_SESSION['CART_SUCCESS'] = "Cupom $cupomUpper aplicado! $percentual% de desconto.";
            } else {
                $_SESSION['CUPOM_ERROR'] = 'Cupom inválido.';
            }
        }
        
        header("Location: /carrinho");
        exit;
    }
    
    /**
     * Remove cupom aplicado
     */
    public function removeCupom() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        unset($_SESSION['cupom']);
        $_SESSION['CART_SUCCESS'] = 'Cupom removido.';
        
        header("Location: /carrinho");
        exit;
    }
    
    // ============================
    // MÉTODOS AUXILIARES
    // ============================
    
    private function getProductImage(ProductModel $produto): string {
        $imagens = $produto->getImagens();
        return !empty($imagens) ? $imagens[0]['imagem'] : '/assets/image/placeholder.png';
    }
    
    private function getCartCount(): int {
        if (!isset($_SESSION['cart'])) return 0;
        return array_sum(array_column($_SESSION['cart'], 'quantity'));
    }
    
    private function getCartTotal(): float {
        if (!isset($_SESSION['cart'])) return 0;
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }
    
    private function buildCartHtml(): string {
        if (empty($_SESSION['cart'])) {
            return '
            <div class="empty-cart" style="text-align: center; padding: 3rem;">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" 
                    stroke="#666" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <h2 style="color: #fff; margin-top: 1.5rem;">Seu carrinho está vazio</h2>
                <p style="color: #888; margin-bottom: 2rem;">Adicione produtos para continuar comprando.</p>
                <a href="/catalogo" class="btn-primary" style="display: inline-block; padding: 1rem 2rem; 
                    background: linear-gradient(135deg, #D0D558, #a8ad3f); color: #1a1a2e; 
                    text-decoration: none; border-radius: 10px; font-weight: 600;">
                    Explorar Produtos
                </a>
            </div>';
        }
        
        $html = '<div class="cart-items">';
        
        foreach ($_SESSION['cart'] as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $newQtyMinus = max(0, $item['quantity'] - 1);
            $newQtyPlus = $item['quantity'] + 1;
            
            $html .= '
            <div class="cart-item" data-product-id="' . $item['id'] . '">
                <div class="cart-item-image">
                    <img src="' . htmlspecialchars($item['image']) . '" alt="' . htmlspecialchars($item['name']) . '">
                </div>
                <div class="cart-item-details">
                    <h3>' . htmlspecialchars($item['name']) . '</h3>
                    <p class="cart-item-price">R$ ' . number_format($item['price'], 2, ',', '.') . '</p>
                </div>
                <div class="cart-item-quantity">
                    <form action="/carrinho/update" method="POST" style="display: inline;">
                        <input type="hidden" name="product_id" value="' . $item['id'] . '">
                        <input type="hidden" name="quantity" value="' . $newQtyMinus . '">
                        <button type="submit" class="qty-btn">-</button>
                    </form>
                    <span>' . $item['quantity'] . '</span>
                    <form action="/carrinho/update" method="POST" style="display: inline;">
                        <input type="hidden" name="product_id" value="' . $item['id'] . '">
                        <input type="hidden" name="quantity" value="' . $newQtyPlus . '">
                        <button type="submit" class="qty-btn">+</button>
                    </form>
                </div>
                <div class="cart-item-subtotal">
                    <span>R$ ' . number_format($subtotal, 2, ',', '.') . '</span>
                </div>
                <div class="cart-item-remove">
                    <form action="/carrinho/remove" method="POST">
                        <input type="hidden" name="product_id" value="' . $item['id'] . '">
                        <button type="submit" class="btn-remove" title="Remover item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" 
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    private function buildCartSummary(): string {
        $total = $this->getCartTotal();
        $count = $this->getCartCount();
        
        if ($count === 0) {
            return '';
        }
        
        // Verifica se há cupom aplicado
        $cupomHtml = '';
        $desconto = 0;
        $cupomAplicado = $_SESSION['cupom'] ?? null;
        
        if ($cupomAplicado) {
            $desconto = $cupomAplicado['desconto'] ?? 0;
            $cupomHtml = '
            <div class="summary-row" style="color: #4ade80;">
                <span>Cupom: ' . htmlspecialchars($cupomAplicado['codigo']) . '</span>
                <span>-R$ ' . number_format($desconto, 2, ',', '.') . '</span>
            </div>
            <form action="/carrinho/remove-cupom" method="POST" style="margin-bottom: 1rem;">
                <button type="submit" style="background: transparent; border: none; color: #f87171; cursor: pointer; font-size: 0.85rem;">
                    Remover cupom
                </button>
            </form>';
        }
        
        $totalFinal = max(0, $total - $desconto);
        
        // Campo de cupom (se não houver cupom aplicado)
        $cupomInputHtml = '';
        if (!$cupomAplicado) {
            $cupomInputHtml = '
            <form action="/carrinho/apply-cupom" method="POST" class="cupom-form" style="margin-bottom: 1rem;">
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" name="cupom_code" placeholder="Código do cupom" 
                        style="flex: 1; padding: 0.75rem; border: 1px solid rgba(208, 213, 88, 0.3); 
                        background: rgba(255,255,255,0.05); color: #fff; border-radius: 8px;">
                    <button type="submit" style="padding: 0.75rem 1rem; background: rgba(208, 213, 88, 0.2); 
                        color: #D0D558; border: 1px solid rgba(208, 213, 88, 0.3); border-radius: 8px; cursor: pointer;">
                        Aplicar
                    </button>
                </div>
            </form>';
            
            // Mensagem de erro do cupom
            if (isset($_SESSION['CUPOM_ERROR'])) {
                $cupomInputHtml .= '<p style="color: #f87171; font-size: 0.85rem; margin-bottom: 1rem;">' . htmlspecialchars($_SESSION['CUPOM_ERROR']) . '</p>';
                unset($_SESSION['CUPOM_ERROR']);
            }
        }
        
        return '
        <div class="cart-summary">
            <h3>Resumo do Pedido</h3>
            
            <div class="summary-row">
                <span>Subtotal (' . $count . ' itens)</span>
                <span>R$ ' . number_format($total, 2, ',', '.') . '</span>
            </div>
            
            <div class="summary-row">
                <span>Frete</span>
                <span style="color: #4ade80;">Calcular no checkout</span>
            </div>

            ' . $cupomHtml . '
            
            ' . $cupomInputHtml . '

            <div class="summary-total">
                <span>Total</span>
                <span>R$ ' . number_format($totalFinal, 2, ',', '.') . '</span>
            </div>
            
            <a href="/checkout" class="btn-checkout">Finalizar Compra</a>
            <a href="/catalogo" class="btn-continue">Continuar Comprando</a>
        </div>';
    }
}
