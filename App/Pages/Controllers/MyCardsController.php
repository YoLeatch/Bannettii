<?php

namespace App\Pages\Controllers;

use Core\ViewerPlace;
use App\Payment\CardModel;
use App\User\Middlewares\AuthMiddleware;

class MyCardsController {
    
    /**
     * Exibe a página de gerenciamento de cartões
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
        
        // Busca cartões do usuário
        $cartoes = CardModel::findByUsuario($userId);
        
        // Mensagens de feedback
        $successMessage = $_SESSION['CARD_SUCCESS'] ?? '';
        $errorMessage = $_SESSION['CARD_ERROR'] ?? '';
        unset($_SESSION['CARD_SUCCESS'], $_SESSION['CARD_ERROR']);
        
        // Gera HTML dos cartões
        $cartoesHtml = $this->buildCartoesHtml($cartoes);
        
        echo ViewerPlace::render('meus-cartoes', [
            'cards_list' => $cartoesHtml,
            'total_cartoes' => count($cartoes),
            'success_message' => $successMessage ? '<div class="success-message">' . htmlspecialchars($successMessage) . '</div>' : '',
            'error_message' => $errorMessage ? '<div class="error-message">' . htmlspecialchars($errorMessage) . '</div>' : ''
        ]);
    }
    
    /**
     * Adiciona novo cartão
     */
    public function add() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        $nomeTitular = strtoupper(trim($_POST['nome_titular'] ?? ''));
        $numeroCartao = preg_replace('/\D/', '', $_POST['numero_cartao'] ?? '');
        $validade = trim($_POST['validade'] ?? '');
        $cvv = preg_replace('/\D/', '', $_POST['cvv'] ?? '');
        
        // Validações
        if (empty($nomeTitular) || empty($numeroCartao) || empty($validade)) {
            $_SESSION['CARD_ERROR'] = 'Preencha todos os campos obrigatórios.';
            header('Location: /cartoes');
            exit;
        }
        
        // Valida tamanho do número
        if (strlen($numeroCartao) < 13 || strlen($numeroCartao) > 19) {
            $_SESSION['CARD_ERROR'] = 'Número de cartão inválido.';
            header('Location: /cartoes');
            exit;
        }
        
        // Valida número do cartão usando algoritmo de Luhn
        if (!CardModel::isValidNumber($numeroCartao)) {
            $_SESSION['CARD_ERROR'] = 'Número de cartão inválido. Verifique os dígitos informados.';
            header('Location: /cartoes');
            exit;
        }
        
        // Converte validade MM/AA para YYYY-MM-DD (último dia do mês)
        if (preg_match('/^(\d{2})\/(\d{2})$/', $validade, $matches)) {
            $mes = $matches[1];
            $ano = '20' . $matches[2];
            $validadeDate = $ano . '-' . $mes . '-' . date('t', strtotime($ano . '-' . $mes . '-01'));
        } else {
            $_SESSION['CARD_ERROR'] = 'Formato de validade inválido. Use MM/AA.';
            header('Location: /cartoes');
            exit;
        }
        
        // Verifica se não está vencido
        if (strtotime($validadeDate) < time()) {
            $_SESSION['CARD_ERROR'] = 'Cartão já está vencido.';
            header('Location: /cartoes');
            exit;
        }
        
        // Cria o cartão com o número (será usado para identificar a bandeira)
        $cartao = CardModel::create($nomeTitular, $numeroCartao, $validadeDate, $userId);
        
        if ($cartao) {
            $_SESSION['CARD_SUCCESS'] = 'Cartão cadastrado com sucesso!';
        } else {
            $_SESSION['CARD_ERROR'] = 'Erro ao cadastrar cartão.';
        }
        
        header('Location: /cartoes');
        exit;
    }
    
    /**
     * Remove cartão (soft delete)
     */
    public function delete() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        $cardId = (int) ($_POST['card_id'] ?? 0);
        
        if (!$userId || $cardId <= 0) {
            header('Location: /cartoes');
            exit;
        }
        
        // Verifica se o cartão pertence ao usuário
        $cartao = CardModel::findById($cardId);
        if (!$cartao || $cartao->getUsuario() !== $userId) {
            $_SESSION['CARD_ERROR'] = 'Cartão não encontrado.';
            header('Location: /cartoes');
            exit;
        }
        
        if (CardModel::delete($cardId)) {
            $_SESSION['CARD_SUCCESS'] = 'Cartão removido com sucesso!';
        } else {
            $_SESSION['CARD_ERROR'] = 'Erro ao remover cartão.';
        }
        
        header('Location: /cartoes');
        exit;
    }
    
    // ========== MÉTODOS PRIVADOS ==========
    
    private function buildCartoesHtml(array $cartoes): string {
        if (empty($cartoes)) {
            return '';
        }
        
        $html = '';
        
        foreach ($cartoes as $cartao) {
            $id = $cartao->getId();
            $nome = htmlspecialchars($cartao->getNome());
            $validade = htmlspecialchars($cartao->getValidadeFormatada());
            $isExpired = $cartao->isExpired();
            
            // Obtém bandeira do cartão pela numeração
            $bandeira = $cartao->getBandeira();
            
            // Número mascarado (•••• •••• •••• 1234)
            $numeroMascarado = $cartao->getNumeroMascarado();
            
            // Classe de status
            $expiredClass = $isExpired ? 'expired' : '';
            $expiredBadge = $isExpired ? '<span class="badge-expired">Vencido</span>' : '';
            
            // SVG da bandeira
            $brandSvg = $this->getBrandSvg($bandeira);
            
            $html .= '
            <div class="credit-card ' . $expiredClass . '">
                <div class="card-top">
                    <div class="chip"></div>
                    <div class="card-brand">' . $brandSvg . '</div>
                </div>
                <div class="card-number">' . $numeroMascarado . '</div>
                <div class="card-bottom">
                    <div class="card-holder">
                        <label>Titular</label>
                        <span>' . $nome . '</span>
                    </div>
                    <div class="card-expiry">
                        <label>Validade</label>
                        <span>' . $validade . ' ' . $expiredBadge . '</span>
                    </div>
                </div>
                <form action="/cartoes/delete" method="POST" onsubmit="return confirm(\'Remover este cartão?\')">
                    <input type="hidden" name="card_id" value="' . $id . '">
                    <button type="submit" class="btn-delete-card">&times;</button>
                </form>
            </div>';
        }
        
        return $html;
    }
    
    /**
     * Retorna o SVG da bandeira do cartão
     */
    private function getBrandSvg(string $bandeira): string {
        $svgs = [
            'VISA' => '<svg width="60" height="20" viewBox="0 0 750 471" xmlns="http://www.w3.org/2000/svg">
                <path d="M278.198 334.228l33.36-195.763h53.358l-33.384 195.763H278.198zm137.59-191.546c-10.57-3.966-27.135-8.222-47.822-8.222-52.726 0-89.863 26.551-90.18 64.604-.297 28.129 26.515 43.822 46.754 53.185 20.77 9.597 27.752 15.716 27.652 24.283-.133 13.123-16.586 19.115-31.924 19.115-21.355 0-32.701-2.967-50.225-10.274l-6.876-3.112-7.487 43.822c12.463 5.466 35.508 10.199 59.438 10.445 56.09 0 92.502-26.248 92.916-66.884.199-22.27-14.016-39.216-44.801-53.188-18.65-9.056-30.072-15.099-29.951-24.269 0-8.137 9.668-16.838 30.559-16.838 17.447-.271 30.088 3.534 39.936 7.5l4.781 2.259 7.23-42.426zm137.31-4.223h-41.232c-12.774 0-22.332 3.486-27.94 16.234l-79.245 179.402h56.031s9.159-24.121 11.231-29.418c6.124 0 60.555.084 68.336.084 1.596 6.854 6.492 29.334 6.492 29.334h49.512l-43.185-195.636zm-65.417 126.408c4.414-11.279 21.26-54.724 21.26-54.724-.314.521 4.381-11.334 7.074-18.684l3.606 16.878s10.217 46.729 12.353 56.527h-44.293zm-363.67-126.4l-52.239 133.496-5.566-27.129c-9.726-31.274-40.025-65.157-73.898-82.12l47.767 171.204 56.455-.063 84.004-195.39-56.523.001z" fill="#1A1F71"/>
                <path d="M146.92 138.465H60.879L60.3 142.149c67.06 16.234 111.471 55.439 129.85 102.548l-18.719-89.9c-3.223-12.357-12.586-16.047-24.511-16.332z" fill="#F9A533"/>
            </svg>',
            
            'MASTERCARD' => '<svg width="60" height="38" viewBox="0 0 152 100" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="45" fill="#EB001B"/>
                <circle cx="102" cy="50" r="45" fill="#F79E1B"/>
                <path d="M76 18a45 45 0 0 0 0 64 45 45 0 0 0 0-64z" fill="#FF5F00"/>
            </svg>',
            
            'ELO' => '<svg width="60" height="24" viewBox="0 0 100 40" xmlns="http://www.w3.org/2000/svg">
                <rect width="100" height="40" rx="5" fill="#00A4E0"/>
                <text x="50" y="28" font-family="Arial Black" font-size="22" fill="white" text-anchor="middle" font-weight="bold">elo</text>
            </svg>',
            
            'AMEX' => '<svg width="60" height="38" viewBox="0 0 100 60" xmlns="http://www.w3.org/2000/svg">
                <rect width="100" height="60" rx="5" fill="#006FCF"/>
                <text x="50" y="38" font-family="Arial" font-size="14" fill="white" text-anchor="middle" font-weight="bold">AMEX</text>
            </svg>',
            
            'HIPERCARD' => '<svg width="60" height="24" viewBox="0 0 100 40" xmlns="http://www.w3.org/2000/svg">
                <rect width="100" height="40" rx="5" fill="#822124"/>
                <text x="50" y="26" font-family="Arial" font-size="12" fill="white" text-anchor="middle" font-weight="bold">HIPERCARD</text>
            </svg>',
            
            'DINERS' => '<svg width="60" height="38" viewBox="0 0 100 60" xmlns="http://www.w3.org/2000/svg">
                <rect width="100" height="60" rx="5" fill="#0079BE"/>
                <circle cx="50" cy="30" r="20" fill="none" stroke="white" stroke-width="3"/>
                <text x="50" y="50" font-family="Arial" font-size="8" fill="white" text-anchor="middle">DINERS CLUB</text>
            </svg>',
            
            'DISCOVER' => '<svg width="60" height="24" viewBox="0 0 100 40" xmlns="http://www.w3.org/2000/svg">
                <rect width="100" height="40" rx="5" fill="#FF6000"/>
                <text x="50" y="26" font-family="Arial" font-size="12" fill="white" text-anchor="middle" font-weight="bold">DISCOVER</text>
            </svg>',
            
            'CARD' => '<svg width="50" height="35" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#D0D558" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                <line x1="1" y1="10" x2="23" y2="10"></line>
            </svg>'
        ];
        
        return $svgs[$bandeira] ?? $svgs['CARD'];
    }
}
