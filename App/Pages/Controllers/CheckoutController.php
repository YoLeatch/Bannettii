<?php
/**
 * CheckoutController - Controlador da página de checkout
 * 
 * Gerencia o processo de finalização de compra.
 * 
 * @package App\Pages\Controllers
 */

namespace App\Pages\Controllers;

use Core\ViewerPlace;
use App\Product\Models\ProductModel;
use App\Product\Models\VendaModel;
use App\Payment\CardModel;
use App\Services\Models\EnderecoModel;

class CheckoutController {
    
    /**
     * Exibe a página de checkout
     */
    public function handle() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        // Verifica se há itens no carrinho
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            header('Location: /carrinho');
            exit;
        }
        
        // Busca endereços do usuário
        $enderecos = EnderecoModel::getByPessoa($userId);
        
        // Busca cartões do usuário
        $cartoes = CardModel::findByUsuario($userId);
        
        // Calcula totais do carrinho
        $subtotal = 0;
        $itensHtml = '';
        
        foreach ($cart as $produtoId => $item) {
            // Usa dados armazenados no carrinho
            $nome = $item['name'] ?? 'Produto';
            $preco = $item['price'] ?? 0;
            $quantidade = $item['quantity'] ?? 1;
            $imagem = $item['image'] ?? '/assets/image/placeholder.png';
            
            $itemTotal = $preco * $quantidade;
            $subtotal += $itemTotal;
            
            $itensHtml .= '
            <div class="checkout-item">
                <div class="item-image">
                    <img src="' . htmlspecialchars($imagem) . '" alt="' . htmlspecialchars($nome) . '">
                </div>
                <div class="item-details">
                    <h4>' . htmlspecialchars($nome) . '</h4>
                    <p class="item-quantity">Quantidade: ' . $quantidade . '</p>
                </div>
                <div class="item-price">R$ ' . number_format($itemTotal, 2, ',', '.') . '</div>
            </div>';
        }
        
        // Calcula frete (simplificado) - valores em reais
        $frete = $subtotal > 200 ? 0 : 15.00; // Frete grátis acima de R$ 200
        
        // Cupom
        $cupom = $_SESSION['cupom'] ?? null;
        $desconto = 0;
        if ($cupom) {
            $desconto = ($subtotal * ($cupom['percentual'] ?? 0)) / 100;
        }
        
        $total = $subtotal + $frete - $desconto;
        
        // Monta HTML dos endereços
        $enderecosHtml = $this->buildEnderecosSelect($enderecos);
        
        // Monta HTML dos cartões
        $cartoesHtml = $this->buildCartoesSelect($cartoes);
        
        // Mensagens de feedback
        $errorMessage = $_SESSION['CHECKOUT_ERROR'] ?? '';
        unset($_SESSION['CHECKOUT_ERROR']);
        
        echo ViewerPlace::render('checkout', [
            'itens_html' => $itensHtml,
            'total_itens' => count($cart),
            'subtotal' => 'R$ ' . number_format($subtotal, 2, ',', '.'),
            'frete' => $frete > 0 ? 'R$ ' . number_format($frete, 2, ',', '.') : 'Grátis',
            'desconto' => $desconto > 0 ? '- R$ ' . number_format($desconto, 2, ',', '.') : '',
            'cupom_nome' => $cupom ? htmlspecialchars($cupom['codigo'] ?? '') : '',
            'cupom_display' => $cupom ? '' : 'style="display: none"',
            'total' => 'R$ ' . number_format($total, 2, ',', '.'),
            'enderecos_select' => $enderecosHtml,
            'cartoes_select' => $cartoesHtml,
            'error_message' => $errorMessage ? '<div class="error-message">' . htmlspecialchars($errorMessage) . '</div>' : ''
        ]);
    }
    
    /**
     * Processa a finalização do pedido
     */
    public function processar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) {
            header('Location: /carrinho');
            exit;
        }
        
        $enderecoId = (int) ($_POST['endereco_id'] ?? 0);
        $pagamentoTipo = $_POST['pagamento_tipo'] ?? '';
        $cartaoId = (int) ($_POST['cartao_id'] ?? 0);
        
        // Validações
        if ($enderecoId <= 0) {
            $_SESSION['CHECKOUT_ERROR'] = 'Selecione um endereço de entrega.';
            header('Location: /checkout');
            exit;
        }
        
        if (empty($pagamentoTipo)) {
            $_SESSION['CHECKOUT_ERROR'] = 'Selecione uma forma de pagamento.';
            header('Location: /checkout');
            exit;
        }
        
        if ($pagamentoTipo === 'cartao' && $cartaoId <= 0) {
            $_SESSION['CHECKOUT_ERROR'] = 'Selecione um cartão de crédito.';
            header('Location: /checkout');
            exit;
        }
        
        // Verifica se o endereço pertence ao usuário
        $endereco = EnderecoModel::findById($enderecoId);
        if (!$endereco || $endereco->getPessoaId() !== $userId) {
            $_SESSION['CHECKOUT_ERROR'] = 'Endereço inválido.';
            header('Location: /checkout');
            exit;
        }
        
        // Se for cartão, verifica se pertence ao usuário
        if ($pagamentoTipo === 'cartao') {
            $cartao = CardModel::findById($cartaoId);
            if (!$cartao || $cartao->getUsuario() !== $userId) {
                $_SESSION['CHECKOUT_ERROR'] = 'Cartão inválido.';
                header('Location: /checkout');
                exit;
            }
            
            // Verifica se não está vencido
            if ($cartao->isExpired()) {
                $_SESSION['CHECKOUT_ERROR'] = 'O cartão selecionado está vencido.';
                header('Location: /checkout');
                exit;
            }
        }
        
        // Cria a venda
        $venda = VendaModel::create($userId, $enderecoId);
        
        if (!$venda) {
            $_SESSION['CHECKOUT_ERROR'] = 'Erro ao processar pedido. Tente novamente.';
            header('Location: /checkout');
            exit;
        }
        
        // Adiciona itens à venda
        foreach ($cart as $produtoId => $item) {
            $preco = $item['price'] ?? 0;
            $quantidade = $item['quantity'] ?? 1;
            
            // Converte preço para centavos (banco armazena em int)
            $valorEmCentavos = (int) ($preco * 100);
            
            $venda->addItem($produtoId, $userId, $valorEmCentavos * $quantidade, $quantidade);
        }
        
        // O total já foi recalculado automaticamente pelo addItem()
        
        // Limpa o carrinho e cupom
        unset($_SESSION['cart']);
        unset($_SESSION['cupom']);
        
        // Salva código do pedido para exibir na confirmação
        $_SESSION['ultimo_pedido'] = $venda->getCod();
        
        header('Location: /compra-efetuada');
        exit;
    }
    
    /**
     * Exibe a página de compra efetuada com sucesso
     */
    public function compraEfetuada() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        $codigoPedido = $_SESSION['ultimo_pedido'] ?? null;
        
        // Se não tem código do pedido na sessão, redireciona para home
        if (!$codigoPedido) {
            header('Location: /');
            exit;
        }
        
        // Busca informações da venda
        $venda = VendaModel::findByCod($codigoPedido);
        
        if (!$venda) {
            header('Location: /');
            exit;
        }
        
        // Busca email do usuário
        $userEmail = $_SESSION['user_email'] ?? 'seu email';
        
        // Limpa o código do pedido da sessão
        unset($_SESSION['ultimo_pedido']);
        
        echo ViewerPlace::render('compra-efetuada', [
            'pedido_codigo' => htmlspecialchars($venda->getCod()),
            'pedido_data' => $venda->getDataFormatada(),
            'pedido_valor' => $venda->getValorTotalFormatado(),
            'user_email' => htmlspecialchars($userEmail)
        ]);
    }
    
    // ========== MÉTODOS PRIVADOS ==========
    
    private function buildEnderecosSelect(array $enderecos): string {
        if (empty($enderecos)) {
            return '<p class="no-data">Nenhum endereço cadastrado. <a href="/enderecos">Adicionar endereço</a></p>';
        }
        
        $html = '<div class="address-options">';
        
        foreach ($enderecos as $index => $endereco) {
            $checked = $index === 0 ? 'checked' : '';
            $html .= '
            <label class="address-option">
                <input type="radio" name="endereco_id" value="' . $endereco->getId() . '" ' . $checked . '>
                <div class="address-card">
                    <div class="address-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <div class="address-info">
                        <strong>' . htmlspecialchars($endereco->getLogradouro()) . '</strong>
                        <span>' . htmlspecialchars($endereco->getCidade()['cidade'] ?? '') . ' - ' . htmlspecialchars($endereco->getEstado()['estado'] ?? '') . '</span>
                        <span class="address-cep">CEP: ' . htmlspecialchars($endereco->getCepFormatado()) . '</span>
                    </div>
                </div>
            </label>';
        }
        
        $html .= '</div>';
        $html .= '<a href="/enderecos" class="add-new-link">+ Adicionar novo endereço</a>';
        
        return $html;
    }
    
    private function buildCartoesSelect(array $cartoes): string {
        $html = '<div class="payment-options">';
        
        // Opção PIX
        $html .= '
        <label class="payment-option">
            <input type="radio" name="pagamento_tipo" value="pix" checked onchange="toggleCartaoSelect()">
            <div class="payment-card">
                <div class="payment-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="#4ade80" stroke-width="2">
                        <path d="M7 15l5-5 5 5"/>
                        <path d="M7 9l5-5 5 5"/>
                    </svg>
                </div>
                <div class="payment-info">
                    <strong>PIX</strong>
                    <span>Pagamento instantâneo</span>
                </div>
            </div>
        </label>';
        
        // Opção Boleto
        $html .= '
        <label class="payment-option">
            <input type="radio" name="pagamento_tipo" value="boleto" onchange="toggleCartaoSelect()">
            <div class="payment-card">
                <div class="payment-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="#60a5fa" stroke-width="2">
                        <rect x="3" y="4" width="18" height="4"></rect>
                        <rect x="3" y="12" width="18" height="8"></rect>
                    </svg>
                </div>
                <div class="payment-info">
                    <strong>Boleto Bancário</strong>
                    <span>Vencimento em 3 dias</span>
                </div>
            </div>
        </label>';
        
        // Opção Cartão
        $html .= '
        <label class="payment-option">
            <input type="radio" name="pagamento_tipo" value="cartao" onchange="toggleCartaoSelect()">
            <div class="payment-card">
                <div class="payment-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="#D0D558" stroke-width="2">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                </div>
                <div class="payment-info">
                    <strong>Cartão de Crédito</strong>
                    <span>Até 12x sem juros</span>
                </div>
            </div>
        </label>';
        
        $html .= '</div>';
        
        // Select de cartões (oculto por padrão)
        $html .= '<div id="cartaoSelectContainer" class="cartao-select-container" style="display: none;">';
        
        if (empty($cartoes)) {
            $html .= '<p class="no-data">Nenhum cartão cadastrado. <a href="/cartoes">Adicionar cartão</a></p>';
        } else {
            $html .= '<label>Selecione o cartão:</label>';
            $html .= '<select name="cartao_id" class="form-control">';
            $html .= '<option value="">Selecione...</option>';
            
            foreach ($cartoes as $cartao) {
                if (!$cartao->isExpired()) {
                    $html .= '<option value="' . $cartao->getId() . '">' . 
                             $cartao->getBandeira() . ' •••• ' . $cartao->getUltimosDigitos() . 
                             ' - ' . htmlspecialchars($cartao->getNome()) . 
                             ' (Validade: ' . $cartao->getValidadeFormatada() . ')' .
                             '</option>';
                }
            }
            
            $html .= '</select>';
            $html .= '<a href="/cartoes" class="add-new-link">+ Adicionar novo cartão</a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
