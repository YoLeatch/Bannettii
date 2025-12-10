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
        
        // Gera HTML das cores (usando campo cor do produto)
        $coresHtml = $this->buildCoresHtml($produto);
        
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
        
        // Gera HTML das imagens (thumbnails)
        $thumbnailsHtml = $this->buildThumbnailsHtml($imagens, $produto->getNome());

        // Renderiza a view
        echo ViewerPlace::render('produto', [
            'login_notify' => $loginNotify,
            'product_id' => $produto->getId(),
            'nome_produto' => htmlspecialchars($produto->getNome()),
            'nome' => htmlspecialchars($produto->getNome()),
            'main_image' => $imagemPrincipal,
            'imagem_principal' => $imagemPrincipal,
            'imagem_principal_nome' => 'Imagem principal',
            'thumbnails' => $thumbnailsHtml,
            'preco' => $precoFormatado,
            'media_rating' => number_format($mediaRating, 1),
            'quant_aval' => $quantAval,
            'vendidos' => $vendidos,
            'cores' => $coresHtml,
            'tamanhos' => $tamanhosHtml,
            'descricao' => $descricaoHtml,
            'lista_aval' => $listaAvalHtml,
            'material' => htmlspecialchars($produto->getMaterial() ?? 'Não informado'),
            'peso' => htmlspecialchars($produto->getPesoLiq() ?? 'Não informado')
        ]);
    }

    /**
     * Gera HTML dos thumbnails das imagens
     */
    private function buildThumbnailsHtml(array $imagens, string $nomeProduto): string {
        if (empty($imagens)) {
            // Se não tiver imagens, retorna pelo menos o placeholder como thumb ativo
            return '<div class="thumb active" onclick="changeImage(this, \'/assets/image/placeholder.png\')">
                        <img src="/assets/image/placeholder.png" alt="' . htmlspecialchars($nomeProduto) . '">
                    </div>';
        }

        $html = '';
        foreach ($imagens as $index => $img) {
            $imgPath = htmlspecialchars($img['imagem']);
            $activeClass = ($index === 0) ? 'active' : '';
            
            $html .= '<div class="thumb ' . $activeClass . '" onclick="changeImage(this, \'' . $imgPath . '\')">
                        <img src="' . $imgPath . '" alt="' . htmlspecialchars($nomeProduto) . ' - Imagem ' . ($index + 1) . '">
                      </div>';
        }
        return $html;
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
     * Gera HTML das cores disponíveis
     */
    private function buildCoresHtml(ProductModel $produto): string {
        $cor = $produto->getCor();
        
        if (empty($cor)) {
            return '<span style="color: #666; font-size: 0.9rem;">Cor única</span>';
        }
        
        // Cores podem estar separadas por vírgula
        $cores = array_map('trim', explode(',', $cor));
        
        $html = '';
        foreach ($cores as $corItem) {
            if (!empty($corItem)) {
                $html .= '<button type="button" class="btn-option color-option" onclick="selectProductOption(this, \'cor\', \'' . htmlspecialchars($corItem) . '\')">' . htmlspecialchars($corItem) . '</button>';
            }
        }
        
        return $html;
    }
    
    /**
     * Gera HTML dos tamanhos disponíveis baseado no campo tamanho
     */
    private function buildTamanhosHtml(ProductModel $produto): string {
        $tamanho = $produto->getTamanho();
        
        if (empty($tamanho)) {
            return '<span style="color: #666; font-size: 0.9rem;">Tamanho único</span>';
        }

        // Assume que pode haver múltiplos tamanhos separados por vírgula (ex: "P, M, G")
        $tamanhos = array_map('trim', explode(',', $tamanho));
        
        $html = '';
        foreach ($tamanhos as $tam) {
            if (!empty($tam)) {
                $html .= '<button type="button" class="btn-option size-option" onclick="selectProductOption(this, \'tamanho\', \'' . htmlspecialchars($tam) . '\')">' . htmlspecialchars($tam) . '</button>';
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
