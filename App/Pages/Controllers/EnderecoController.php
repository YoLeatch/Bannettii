<?php

namespace App\Pages\Controllers;

use Core\ViewerPlace;
use App\Services\Models\EnderecoModel;
use App\User\Middlewares\AuthMiddleware;

class EnderecoController {
    
    /**
     * Exibe a página de gerenciamento de endereços
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
        
        // Busca endereços do usuário
        $enderecos = EnderecoModel::getByPessoa($userId);
        
        // Busca estados para o formulário
        $estados = EnderecoModel::getAllEstados();
        
        // Mensagens de feedback
        $successMessage = $_SESSION['ADDRESS_SUCCESS'] ?? '';
        $errorMessage = $_SESSION['ADDRESS_ERROR'] ?? '';
        unset($_SESSION['ADDRESS_SUCCESS'], $_SESSION['ADDRESS_ERROR']);
        
        // Gera HTML dos endereços
        $enderecosHtml = $this->buildEnderecosHtml($enderecos);
        
        // Gera HTML dos estados (select)
        $estadosHtml = $this->buildEstadosSelect($estados);
        
        echo ViewerPlace::render('enderecos', [
            'enderecos_list' => $enderecosHtml,
            'estados_options' => $estadosHtml,
            'total_enderecos' => count($enderecos),
            'success_message' => $successMessage ? '<div class="success-message">' . htmlspecialchars($successMessage) . '</div>' : '',
            'error_message' => $errorMessage ? '<div class="error-message">' . htmlspecialchars($errorMessage) . '</div>' : ''
        ]);
    }
    /**
     * Adiciona novo endereço
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
        
        $logradouro = trim($_POST['logradouro'] ?? '');
        $cep = preg_replace('/\D/', '', $_POST['cep'] ?? '');
        $cidadeNome = trim($_POST['cidade'] ?? '');
        $estadoNome = strtoupper(trim($_POST['estado'] ?? ''));
        $tipos = $_POST['tipos'] ?? [];
        
        if (empty($logradouro) || empty($cep) || empty($cidadeNome) || empty($estadoNome)) {
            $_SESSION['ADDRESS_ERROR'] = 'Preencha todos os campos obrigatórios.';
            header('Location: /enderecos');
            exit;
        }
        
        // Busca ou cria o estado pela sigla (UF)
        $estadoId = EnderecoModel::findOrCreateEstado($estadoNome);
        
        // Busca ou cria a cidade
        $cidadeId = EnderecoModel::findOrCreateCidade($cidadeNome, $estadoId);
        
        // Cria o endereço
        $endereco = EnderecoModel::create($logradouro, $userId, $cidadeId, $cep, $tipos);
        
        if ($endereco) {
            $_SESSION['ADDRESS_SUCCESS'] = 'Endereço cadastrado com sucesso!';
        } else {
            $_SESSION['ADDRESS_ERROR'] = 'Erro ao cadastrar endereço.';
        }
        
        header('Location: /enderecos');
        exit;
    }
    
    /**
     * Atualiza endereço existente
     */
    public function update() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        $enderecoId = (int) ($_POST['endereco_id'] ?? 0);
        
        if (!$userId || $enderecoId <= 0) {
            header('Location: /enderecos');
            exit;
        }
        
        // Verifica se o endereço pertence ao usuário
        $endereco = EnderecoModel::findById($enderecoId);
        if (!$endereco || $endereco->getPessoaId() !== $userId) {
            $_SESSION['ADDRESS_ERROR'] = 'Endereço não encontrado.';
            header('Location: /enderecos');
            exit;
        }
        
        $logradouro = trim($_POST['logradouro'] ?? '');
        $cep = preg_replace('/\D/', '', $_POST['cep'] ?? '');
        $cidadeNome = trim($_POST['cidade'] ?? '');
        $estadoNome = strtoupper(trim($_POST['estado'] ?? ''));
        
        if (empty($logradouro) || empty($cep)) {
            $_SESSION['ADDRESS_ERROR'] = 'Preencha todos os campos obrigatórios.';
            header('Location: /enderecos');
            exit;
        }
        
        // Busca ou cria a cidade
        $cidadeId = $endereco->getCidadeId();
        if (!empty($cidadeNome) && !empty($estadoNome)) {
            $estadoId = EnderecoModel::findOrCreateEstado($estadoNome);
            $cidadeId = EnderecoModel::findOrCreateCidade($cidadeNome, $estadoId);
        }
        
        if ($endereco->update($logradouro, $cidadeId, $cep)) {
            $_SESSION['ADDRESS_SUCCESS'] = 'Endereço atualizado com sucesso!';
        } else {
            $_SESSION['ADDRESS_ERROR'] = 'Erro ao atualizar endereço.';
        }
        
        header('Location: /enderecos');
        exit;
    }
    
    /**
     * Remove endereço (soft delete)
     */
    public function delete() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        $enderecoId = (int) ($_POST['endereco_id'] ?? 0);
        
        if (!$userId || $enderecoId <= 0) {
            header('Location: /enderecos');
            exit;
        }
        
        // Verifica se o endereço pertence ao usuário
        $endereco = EnderecoModel::findById($enderecoId);
        if (!$endereco || $endereco->getPessoaId() !== $userId) {
            $_SESSION['ADDRESS_ERROR'] = 'Endereço não encontrado.';
            header('Location: /enderecos');
            exit;
        }
        
        if (EnderecoModel::delete($enderecoId)) {
            $_SESSION['ADDRESS_SUCCESS'] = 'Endereço removido com sucesso!';
        } else {
            $_SESSION['ADDRESS_ERROR'] = 'Erro ao remover endereço.';
        }
        
        header('Location: /enderecos');
        exit;
    }
    
    /**
     * Adiciona tipo ao endereço
     */
    public function addTipo() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        $enderecoId = (int) ($_POST['endereco_id'] ?? 0);
        $tipo = trim($_POST['tipo'] ?? '');
        
        if (!$userId || $enderecoId <= 0 || empty($tipo)) {
            header('Location: /enderecos');
            exit;
        }
        
        $endereco = EnderecoModel::findById($enderecoId);
        if (!$endereco || $endereco->getPessoaId() !== $userId) {
            $_SESSION['ADDRESS_ERROR'] = 'Endereço não encontrado.';
            header('Location: /enderecos');
            exit;
        }
        
        if ($endereco->addTipo($tipo)) {
            $_SESSION['ADDRESS_SUCCESS'] = 'Tipo adicionado com sucesso!';
        } else {
            $_SESSION['ADDRESS_ERROR'] = 'Erro ao adicionar tipo.';
        }
        
        header('Location: /enderecos');
        exit;
    }
    
    /**
     * Remove tipo do endereço
     */
    public function removeTipo() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'] ?? 0;
        $enderecoId = (int) ($_POST['endereco_id'] ?? 0);
        $tipoId = (int) ($_POST['tipo_id'] ?? 0);
        
        if (!$userId || $enderecoId <= 0 || $tipoId <= 0) {
            header('Location: /enderecos');
            exit;
        }
        
        $endereco = EnderecoModel::findById($enderecoId);
        if (!$endereco || $endereco->getPessoaId() !== $userId) {
            $_SESSION['ADDRESS_ERROR'] = 'Endereço não encontrado.';
            header('Location: /enderecos');
            exit;
        }
        
        if ($endereco->removeTipo($tipoId)) {
            $_SESSION['ADDRESS_SUCCESS'] = 'Tipo removido com sucesso!';
        } else {
            $_SESSION['ADDRESS_ERROR'] = 'Erro ao remover tipo.';
        }
        
        header('Location: /enderecos');
        exit;
    }
    
    /**
     * Retorna cidades de um estado (AJAX)
     */
    public function getCidades() {
        header('Content-Type: application/json');
        
        $estadoId = (int) ($_GET['estado_id'] ?? 0);
        if ($estadoId <= 0) {
            echo json_encode([]);
            exit;
        }
        
        $cidades = EnderecoModel::getCidadesByEstado($estadoId);
        echo json_encode($cidades);
        exit;
    }
    
    // ========== MÉTODOS PRIVADOS ==========
    
    private function buildEnderecosHtml(array $enderecos): string {
        if (empty($enderecos)) {
            return '<div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" 
                    stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <h3>Nenhum endereço cadastrado</h3>
                <p>Adicione seu primeiro endereço de entrega.</p>
            </div>';
        }
        
        $html = '<div class="enderecos-grid">';
        
        foreach ($enderecos as $endereco) {
            $id = $endereco->getId();
            $logradouro = htmlspecialchars($endereco->getLogradouro());
            $cep = htmlspecialchars($endereco->getCepFormatado());
            $cidade = htmlspecialchars($endereco->getCidade()['cidade'] ?? '');
            $estado = htmlspecialchars($endereco->getEstado()['estado'] ?? '');
            $tipos = $endereco->getTipos();
            
            // Gera HTML dos tipos
            $tiposHtml = '';
            foreach ($tipos as $tipo) {
                $tipoId = $tipo['id'];
                $tipoNome = htmlspecialchars($tipo['tipo']);
                $tiposHtml .= '<span class="tipo-badge">
                    ' . $tipoNome . '
                    <form action="/enderecos/remove-tipo" method="POST" style="display: inline;">
                        <input type="hidden" name="endereco_id" value="' . $id . '">
                        <input type="hidden" name="tipo_id" value="' . $tipoId . '">
                        <button type="submit" class="tipo-remove" title="Remover tipo">&times;</button>
                    </form>
                </span>';
            }
            
            $html .= '
            <div class="endereco-card">
                <div class="endereco-header">
                    <div class="endereco-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" 
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                    </div>
                    <div class="endereco-actions">
                        <button type="button" class="btn-edit" onclick="editEndereco(' . $id . ', \'' . addslashes($logradouro) . '\', \'' . $endereco->getCep() . '\', \'' . addslashes($cidade) . '\', \'' . addslashes($estado) . '\')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" 
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <form action="/enderecos/delete" method="POST" style="display: inline;" onsubmit="return confirm(\'Remover este endereço?\')">
                            <input type="hidden" name="endereco_id" value="' . $id . '">
                            <button type="submit" class="btn-delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" 
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="endereco-body">
                    <p class="endereco-logradouro">' . $logradouro . '</p>
                    <p class="endereco-cidade">' . $cidade . ' - ' . $estado . '</p>
                    <p class="endereco-cep">CEP: ' . $cep . '</p>
                </div>
                <div class="endereco-tipos">
                    ' . $tiposHtml . '
                    <form action="/enderecos/add-tipo" method="POST" class="add-tipo-form">
                        <input type="hidden" name="endereco_id" value="' . $id . '">
                        <input type="text" name="tipo" placeholder="Novo tipo..." class="tipo-input">
                        <button type="submit" class="btn-add-tipo">+</button>
                    </form>
                </div>
            </div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    private function buildEstadosSelect(array $estados): string {
        $html = '<option value="">Selecione o estado</option>';
        foreach ($estados as $estado) {
            $html .= '<option value="' . $estado['id'] . '">' . htmlspecialchars($estado['estado']) . '</option>';
        }
        return $html;
    }
}
