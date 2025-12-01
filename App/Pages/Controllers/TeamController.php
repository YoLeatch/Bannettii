<?php

namespace App\Pages\Controllers;

use Core\ViewerPlace;
use App\User\FuncionarioModel;

class TeamController
{
    public function index()
    {
        $funcionarios = FuncionarioModel::fetchAll();
        $funcionarios_list = '';

        // Colors for random assignment to match design (mock logic)
        $colors = ['#579984', '#44826d', '#c2ccab', '#a0b192', '#2f6957', '#455541', '#223229', '#245e4c', '#1e4d3e', '#174536'];
        
        foreach ($funcionarios as $index => $func) {
            $color = $colors[$index % count($colors)];
            $statusClass = $func->getStatus() == 1 ? 'ativo' : 'inativo';
            $statusLabel = $func->getStatus() == 1 ? 'Ativo' : 'Inativo';
            
            // Mock role/department data since it's not in DB yet
            $role = 'Desenvolvedor'; 
            $dept = 'TI';

            $funcionarios_list .= '
            <div class="team-card">
                <div class="card-header" style="background-color: ' . $color . ';">
                    <div class="card-pfp">
                        <img src="/assets/image/placeholder_pfp.png" alt="' . $func->getNome() . '">
                    </div>
                    <div class="card-info">
                        <h3>' . $func->getNome() . '</h3>
                        <p>' . $role . '</p>
                        <p class="department">' . $dept . '</p>
                        <span class="status ' . $statusClass . '">' . $statusLabel . '</span>
                    </div>
                </div>
                <div class="card-actions">
                    <button class="btn-edit">Editar</button>
                    <button class="btn-profile">Ver Perfil</button>
                </div>
            </div>';
        }

        echo ViewerPlace::render('gerenciarfuncionarios', ['funcionarios_list' => $funcionarios_list, 'profile' => '/profile']);
    }
}
