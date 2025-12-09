<?php
/**
 * ProfileController - Controlador do perfil do usuário
 * 
 * Gerencia a exibição e atualização dos dados do usuário logado.
 * 
 * @package App\Pages\Controllers
 */

namespace App\Pages\Controllers;

use Core\ViewerPlace;
use Core\Security;
use App\User\Models\ClienteModel;
use App\User\Models\PessoaModel;

class ProfileController {
    
    /**
     * Exibe a página de perfil do usuário
     */
    public function handle() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $userId = $_SESSION['user_id'];
        
        // Busca dados do usuário (cliente ou pessoa)
        $cliente = ClienteModel::findById($userId);
        
        if (!$cliente) {
            // Tenta buscar como pessoa comum
            $pessoa = PessoaModel::findByData('id', $userId);
            if (!$pessoa) {
                $_SESSION['ERROR'] = 'Usuário não encontrado.';
                header("Location: /login");
                exit;
            }
            $user = $pessoa;
        } else {
            $user = $cliente;
        }
        
        // Prepara o avatar
        $avatar = $this->prepareAvatar($user);
        
        // Prepara o nome (primeiro nome apenas)
        $fullName = $user->getNome();
        $firstName = explode(' ', $fullName)[0];
        
        // Prepara mensagens de feedback
        $messages = $this->prepareMessages();
        
        // Prepara o formulário de dados
        $userDataForm = $this->buildUserForm($user);
        
        // Renderiza a view
        echo ViewerPlace::render('perfil', [
            'user_avatar' => $avatar,
            'user_name' => htmlspecialchars($firstName),
            'messages' => $messages,
            'user_data_form' => $userDataForm
        ]);
    }
    
    /**
     * Atualiza os dados do perfil
     */
    public function update() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }
        
        try {
            Security::validateCSRFToken($_POST['csrf_token'] ?? '');
            
            $userId = $_SESSION['user_id'];
            $nome = Security::sanitizeInput($_POST['nome'] ?? '');
            $email = Security::sanitizeInput($_POST['email'] ?? '');
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            // Validações
            if (empty($nome) || empty($email)) {
                $_SESSION['ERROR'] = 'Nome e email são obrigatórios.';
                header("Location: /perfil");
                exit;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['ERROR'] = 'Email inválido.';
                header("Location: /perfil");
                exit;
            }

            // Busca dados atuais para manter o CPF e outros campos que não mudam
            $currentUser = PessoaModel::findByData('id', $userId);
            if (!$currentUser) {
                $_SESSION['ERROR'] = 'Usuário não encontrado.';
                header("Location: /login");
                exit;
            }
            $cpf = $currentUser->getCPF();
            
            // Verifica se quer trocar senha
            $password = '';
            if (!empty($newPassword)) {
                if ($newPassword !== $confirmPassword) {
                    $_SESSION['ERROR'] = 'As senhas não coincidem.';
                    header("Location: /perfil");
                    exit;
                }
                if (strlen($newPassword) < 6) {
                    $_SESSION['ERROR'] = 'A senha deve ter pelo menos 6 caracteres.';
                    header("Location: /perfil");
                    exit;
                }
                $password = $newPassword;
            }
            
            // Atualiza os dados
            $result = PessoaModel::updateUser(
                $userId,
                $nome,
                $email,
                $cpf,
                '1', // status ativo
                $password // vazio se não for alterar
            );
            
            if ($result) {
                $_SESSION['SUCCESS'] = 'Dados atualizados com sucesso!';
            } else {
                $_SESSION['ERROR'] = 'Erro ao atualizar dados.';
            }
            
        } catch (\Exception $e) {
            $_SESSION['ERROR'] = 'Erro: ' . $e->getMessage();
        }
        
        header("Location: /perfil");
        exit;
    }
    
    /**
     * Faz upload do avatar do usuário
     */
    public function uploadAvatar() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Não autenticado']);
            exit;
        }
        
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo']);
            exit;
        }
        
        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Tipo de arquivo não permitido']);
            exit;
        }
        
        // Limite de 5MB
        if ($file['size'] > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Arquivo muito grande (máximo 5MB)']);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
        
        $uploadDir = __DIR__ . '/../../../Public/uploads/avatars/';
        
        // Cria diretório se não existir
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadPath = $uploadDir . $filename;
        $publicPath = '/uploads/avatars/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Atualiza no banco de dados
            $pessoa = PessoaModel::findByData('id', $userId);
            if ($pessoa) {
                PessoaModel::updateUser(
                    $userId,
                    $pessoa->getNome(),
                    $pessoa->getEmail(),
                    $pessoa->getCPF(),
                    $pessoa->getStatus(),
                    '', // não altera senha
                    $publicPath // nova imagem
                );
            }
            
            echo json_encode(['success' => true, 'path' => $publicPath]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Falha ao salvar arquivo']);
        }
        exit;
    }
    
    /**
     * Prepara o HTML do avatar
     */
    private function prepareAvatar($user): string {
        $image = $user->getImage();
        
        if ($image && !empty($image)) {
            return '<img src="' . htmlspecialchars($image) . '" alt="Avatar">';
        }
        
        // Retorna as iniciais do nome
        $nome = $user->getNome();
        $partes = explode(' ', $nome);
        $iniciais = strtoupper(substr($partes[0], 0, 1));
        if (count($partes) > 1) {
            $iniciais .= strtoupper(substr(end($partes), 0, 1));
        }
        
        return $iniciais;
    }
    
    /**
     * Prepara mensagens de feedback
     */
    private function prepareMessages(): string {
        $html = '';
        
        if (isset($_SESSION['SUCCESS'])) {
            $html .= '<div class="success-message">' . htmlspecialchars($_SESSION['SUCCESS']) . '</div>';
            unset($_SESSION['SUCCESS']);
        }
        
        if (isset($_SESSION['ERROR'])) {
            $html .= '<div class="error-message">' . htmlspecialchars($_SESSION['ERROR']) . '</div>';
            unset($_SESSION['ERROR']);
        }
        
        return $html;
    }
    
    /**
     * Constrói o formulário de dados do usuário
     */
    private function buildUserForm($user): string {
        $csrfToken = Security::generateCSRFToken();
        $nome = htmlspecialchars($user->getNome());
        $email = htmlspecialchars($user->getEmail());
        $cpf = htmlspecialchars($user->getCPF());
        
        return <<<HTML
        <form action="/perfil/update" method="POST">
            <input type="hidden" name="csrf_token" value="{$csrfToken}">
            
            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" class="form-control" value="{$nome}" required>
            </div>
            
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" value="{$email}" required>
            </div>
            
            <div class="form-group">
                <label for="cpf">CPF</label>
                <input type="text" id="cpf" class="form-control" value="{$cpf}" disabled readonly title="O CPF não pode ser alterado">
                <small style="color: #666; font-size: 0.8em;">O CPF não pode ser alterado.</small>
            </div>
            
            <div class="form-group">
                <label for="new_password">Nova Senha (deixe em branco para não alterar)</label>
                <input type="password" id="new_password" name="new_password" class="form-control" minlength="6">
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmar Nova Senha</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control">
            </div>
            
            <button type="submit" class="btn-primary">Salvar Alterações</button>
        </form>
HTML;
    }
}