<?php
/**
 * TeamController - Controlador para gerenciamento da equipe (funcionários)
 * 
 * @package App\Pages\Controllers\admin
 */

namespace App\Pages\Controllers\admin;

use Core\ViewerPlace;
use App\User\Models\FuncionarioModel;

class TeamController
{
    /**
     * Lista todos os membros da equipe
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verifica se é admin
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }
        
        // Dados do admin logado
        $adminId = $_SESSION['admin_id'] ?? 0;
        $adminName = $_SESSION['admin_name'] ?? 'Administrador';
        $adminRole = $this->getAdminRole($adminId);
        
        // Avatar do admin - verifica se tem imagem
        $adminAvatar = $this->getAdminAvatar($adminName);
        $funcionarioAdmin = FuncionarioModel::findById($adminId);
        if ($funcionarioAdmin) {
            $image = $funcionarioAdmin->getImage();
            if ($image && !empty($image)) {
                $adminAvatar = '<img src="' . htmlspecialchars($image) . '" alt="' . htmlspecialchars($adminName) . '">';
            }
        }
        
        // Busca todos os funcionários
        $funcionarios = FuncionarioModel::findAll();
        
        // Gera HTML dos cards da equipe
        $teamRows = $this->generateTeamCards($funcionarios);
        
        // Busca todos os cargos para o filtro
        $cargos = FuncionarioModel::getAllCargos();
        $cargoOptions = '';
        foreach ($cargos as $cargo) {
            $cargoNome = $cargo['cargo'] ?? $cargo['nome'] ?? 'Sem nome';
            $cargoOptions .= '<option value="' . htmlspecialchars($cargoNome) . '">' . htmlspecialchars($cargoNome) . '</option>';
        }
        
        echo ViewerPlace::render('admin-gerenciar-equipe', [
            'admin_avatar' => $adminAvatar,
            'admin_name' => htmlspecialchars($adminName),
            'admin_role' => htmlspecialchars($adminRole),
            'team_rows' => $teamRows,
            'cargo_options' => $cargoOptions
        ]);
    }
    
    /**
     * Gera as iniciais do nome para o avatar
     */
    private function getAdminAvatar(string $nome): string
    {
        $partes = explode(' ', $nome);
        $iniciais = strtoupper(substr($partes[0], 0, 1));
        if (count($partes) > 1) {
            $iniciais .= strtoupper(substr(end($partes), 0, 1));
        }
        return $iniciais;
    }
    
    /**
     * Retorna o cargo do admin
     */
    private function getAdminRole(int $adminId): string
    {
        try {
            $funcionario = FuncionarioModel::findById($adminId);
            if ($funcionario) {
                $cargos = $funcionario->getCargos();
                if (!empty($cargos)) {
                    return $cargos[0]['cargo'] ?? $cargos[0]['nome'] ?? 'Administrador';
                }
            }
        } catch (\Exception $e) {
            // Ignora erro
        }
        return 'Administrador';
    }
    
    /**
     * Gera os cards HTML da equipe
     */
    private function generateTeamCards(array $funcionarios): string
    {
        if (empty($funcionarios)) {
            return '<div class="no-data">Nenhum membro da equipe encontrado</div>';
        }
        
        $html = '';
        foreach ($funcionarios as $funcionario) {
            $nome = htmlspecialchars($funcionario->getNome());
            $email = htmlspecialchars($funcionario->getEmail());
            $carteirinha = htmlspecialchars($funcionario->getCarteirinha());
            $status = $funcionario->getFuncionarioStatus();
            
            // Cargos
            $cargos = $funcionario->getCargos();
            $cargoPrincipal = 'Sem cargo';
            if (!empty($cargos)) {
                $cargoPrincipal = $cargos[0]['cargo'] ?? $cargos[0]['nome'] ?? 'Sem cargo';
            }
            $cargoEscaped = htmlspecialchars($cargoPrincipal);
            
            // Avatar
            $partes = explode(' ', $nome);
            $iniciais = strtoupper(substr($partes[0], 0, 1));
            if (count($partes) > 1) {
                $iniciais .= strtoupper(substr(end($partes), 0, 1));
            }
            $avatar = $iniciais;
            $image = $funcionario->getImage();
            if ($image && !empty($image)) {
                $avatar = '<img src="' . htmlspecialchars($image) . '" alt="' . $nome . '">';
            }
            
            // Status
            $statusClass = $status === '1' ? 'status-active' : 'status-inactive';
            $statusText = $status === '1' ? 'Ativo' : 'Inativo';
            
            // Data de criação
            $dataCriacao = date('d/m/Y', strtotime($funcionario->getDtCriacao()));
            
            $html .= <<<HTML
          <div class="team-card" data-name="{$nome}" data-cargo="{$cargoEscaped}" data-status="{$statusClass}">
            <div class="team-card-header">
              <div class="team-avatar">{$avatar}</div>
              <div>
                <h3 class="team-name">{$nome}</h3>
                <p class="team-role">{$cargoEscaped}</p>
              </div>
            </div>
            <div class="team-details">
              <div class="team-detail-item">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                  <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
                {$email}
              </div>
              <div class="team-detail-item">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                  <line x1="2" y1="10" x2="22" y2="10"></line>
                </svg>
                Carteirinha: {$carteirinha}
              </div>
              <div class="team-detail-item">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                Desde {$dataCriacao}
              </div>
              <div class="team-detail-item">
                <span class="team-status {$statusClass}">{$statusText}</span>
              </div>
            </div>
          </div>
HTML;
        }
        
        return $html;
    }
}
