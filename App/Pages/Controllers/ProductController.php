<?php
/**
 * ProductController - Controlador de exibição de produtos
 * 
 * Exibe a página de detalhes de um produto específico.
 * 
 * @package App\Pages\Controllers
 */

namespace App\Pages\Controllers;

use Core\ViewerPlace;
use App\Product\Models\ProductModel;
use App\Product\Models\VendaModel;

class ProductController {
    
    /**
     * Exibe a página de detalhes do produto
     */
    public function show(int $id) {
        // Busca o produto
        $produto = ProductModel::findById($id);
        
        if (!$produto) {
            // Produto não encontrado - redireciona ou mostra erro
            header("Location: /catalogo");
            exit;
        }
        
        // Prepara as imagens
        $imagens = $produto->getImagens();
        $imagemPrincipal = !empty($imagens) ? $imagens[0]['imagem'] : '/assets/image/placeholder.png';
        
        // Prepara o preço formatado
        $precoFinal = $produto->getPrecoFinal();
        $precoFormatado = number_format($precoFinal, 2, ',', '.');
        
        // Prepara avaliações
        $avaliacoes = $produto->getAvaliacoes();
        $mediaRating = $produto->getMediaAvaliacoes();
        $quantAval = count($avaliacoes);
        
        // Prepara estatísticas de vendas
        $vendasStats = VendaModel::getStatsByProduto($id);
        $vendidos = $vendasStats['total_unidades'] ?? 0;
        
        // Gera HTML das avaliações
        $listaAvalHtml = $this->buildAvaliacoesList($avaliacoes);
        
        // Gera HTML das cores (placeholder por enquanto)
        $coresHtml = $this->buildCoresHtml();
        
        // Gera HTML dos tamanhos (usando dimensões do produto)
        $tamanhosHtml = $this->buildTamanhosHtml($produto);
        
        // Prepara descrição
        $descricaoHtml = $this->formatDescricao($produto->getDescricao());
        
        // Verifica notificação de avaliação
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $loginNotify = '';
        if (!isset($_SESSION['user_id'])) {
            $loginNotify = '
            <div class="login-notify">
                <div class="icon">i</div>
                <span>Faça <a href="/login" style="color: #D0D558; text-decoration: underline;">login</a> para avaliar este produto.</span>
            </div>';
        } else {
            $userId = $_SESSION['user_id'];
            if (!VendaModel::hasPurchased($userId, $id)) {
                $loginNotify = '
                <div class="login-notify">
                    <div class="icon">i</div>
                    <span>Por favor, efetue a compra deste produto para escrever uma avaliação.</span>
                </div>';
            } else {
                // Usuário comprou -> Exibe botão para avaliar (futuramente form)
                $loginNotify = '
                <div class="login-notify" style="background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.3); color: #4ade80;">
                    <div class="icon">✓</div>
                    <span>Você adquiriu este produto! Sua avaliação é muito importante.</span>
                </div>';
            }
        }
        
        // Renderiza a view
        echo ViewerPlace::render('produto', [
            'login_notify' => $loginNotify,
            'product_id' => $produto->getId(),
            'nome_produto' => htmlspecialchars($produto->getNome()),
            'nome' => htmlspecialchars($produto->getNome()),
            'main_image' => $imagemPrincipal,
            'imagem_principal' => $imagemPrincipal,
            'imagem_principal_nome' => 'Imagem principal',
            'preco' => $precoFormatado,
            'media_rating' => number_format($mediaRating, 1),
            'quant_aval' => $quantAval,
            'vendidos' => $vendidos,
            'cores' => $coresHtml,
            'tamanhos' => $tamanhosHtml,
            'descricao' => $descricaoHtml,
            'lista_aval' => $listaAvalHtml
        ]);
    }
    
    /**
     * Constrói o HTML da lista de avaliações
     */
    private function buildAvaliacoesList(array $avaliacoes): string {
        if (empty($avaliacoes)) {
            return '
            <div class="no-reviews" style="text-align: center; padding: 2rem; color: #888;">
                <p>Este produto ainda não possui avaliações.</p>
                <p style="font-size: 0.9rem;">Seja o primeiro a avaliar!</p>
            </div>';
        }
        
        $html = '';
        foreach ($avaliacoes as $avaliacao) {
            $nome = htmlspecialchars($avaliacao['cliente_nome'] ?? 'Cliente');
            $nota = (int) ($avaliacao['avalaliacao_num'] ?? 0);
            $titulo = htmlspecialchars($avaliacao['avaliacao_tit'] ?? '');
            $descricao = htmlspecialchars($avaliacao['avaliacao_desc'] ?? '');
            $data = isset($avaliacao['avaliacao_data']) 
                ? date('d M Y', strtotime($avaliacao['avaliacao_data'])) 
                : '';
            
            $stars = str_repeat('★', $nota) . str_repeat('☆', 5 - $nota);
            
            $html .= '
            <div class="review-card">
                <div class="reviewer-name">' . $nome . '</div>
                <div class="review-stars">
                    ' . $stars . ' <span class="date">' . $data . '</span>
                </div>';
            
            if (!empty($titulo)) {
                $html .= '<strong style="color: #D0D558;">' . $titulo . '</strong>';
            }
            
            if (!empty($descricao)) {
                $html .= '<p class="review-text">"' . $descricao . '"</p>';
            }
            
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * Gera HTML das cores disponíveis (placeholder)
     */
    private function buildCoresHtml(): string {
        return '
        <button class="btn-option color-option" style="background-color: #1a1a1a; color: white;">Preto</button>
        <button class="btn-option color-option" style="background-color: #ffffff; color: #1a1a1a;">Branco</button>
        <button class="btn-option color-option" style="background-color: #2c3e50; color: white;">Azul Marinho</button>';
    }
    
    /**
     * Gera HTML dos tamanhos disponíveis (placeholder)
     */
    /**
     * Gera HTML dos tamanhos disponíveis baseado nas dimensões
     */
    private function buildTamanhosHtml(ProductModel $produto): string {
        $dimensoes = $produto->getDimensoes();
        
        if (empty($dimensoes)) {
            return '<span style="color: #666; font-size: 0.9rem;">Tamanho único</span>';
        }

        // Assume que pode haver múltiplos tamanhos separados por vírgula (ex: "P, M, G")
        // ou apenas uma dimensão (ex: "30x40x10")
        $tamanhos = array_map('trim', explode(',', $dimensoes));
        
        $html = '';
        foreach ($tamanhos as $tamanho) {
            if (!empty($tamanho)) {
                // Adiciona classe 'active' ao primeiro como padrão se quiser, ou deixa sem seleção
                $html .= '<button class="btn-option">' . htmlspecialchars($tamanho) . '</button>';
            }
        }
        
        return $html;
    }
    
    /**
     * Formata a descrição do produto como lista HTML
     */
    private function formatDescricao(?string $descricao): string {
        if (empty($descricao)) {
            return '<li>Descrição não disponível.</li>';
        }
        
        $linhas = preg_split('/[\.\n]+/', $descricao);
        $linhas = array_filter(array_map('trim', $linhas));
        
        if (empty($linhas)) {
            return '<li>' . htmlspecialchars($descricao) . '</li>';
        }
        
        $html = '';
        foreach ($linhas as $linha) {
            if (!empty($linha)) {
                $html .= '<li>' . htmlspecialchars($linha) . '</li>';
            }
        }
        
        return $html;
    }
}
