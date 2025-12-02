<?php

namespace App\Pages\Controllers;

use App\User\FuncionarioModel;
use App\User\PessoaModel;
use App\User\CargoModel;
use Core\ViewerPlace;

class AdminAuthController
{

    public function register()
    {
        if (empty($_SESSION['admin_logged_in'])) {
            header('Location: /login');
            exit;
        }

        $currentAdminId = $_SESSION['admin_id'];
        $currentAdmin = FuncionarioModel::findByFuncionarioId($currentAdminId);
        
        if (!$currentAdmin || !$currentAdmin->hasPower(90)) {
            header('Location: /admin/products?error=access_denied');
            exit;
        }
        
        $data = ['error' => '', 'csrf_token' => $_SESSION['csrf_token'] ?? ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                $cargoId = (int)($_POST['cargo'] ?? 0);
                $carteirinha = strtoupper(bin2hex(random_bytes(4)));

                if (empty($email) || empty($password) || empty($cargoId)) {
                    $data['error'] = 'Preencha todos os campos.';
                } else {
                    $pessoa = PessoaModel::findByData('email', $email);

                    if (!$pessoa) {
                        $data['error'] = 'Usuário não encontrado. Cadastre-se como usuário comum primeiro.';
                    } elseif (!password_verify($password, $pessoa->getPasswordHash())) {
                        $data['error'] = 'Senha incorreta.';
                    } else {
                        if (FuncionarioModel::findByFuncionarioId($pessoa->getId())) {
                            $data['error'] = 'Usuário já é um administrador.';
                        } else {
                            if (FuncionarioModel::promote($pessoa->getId(), $carteirinha)) {
                                $funcionario = FuncionarioModel::findByFuncionarioId($pessoa->getId());
                                $funcionario->assignCargo($cargoId);
                                header('Location: /admin/users?success=promoted');
                                exit;
                            } else {
                                $data['error'] = 'Erro ao promover usuário a administrador.';
                            }
                        }
                    }
                }
        }

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $data['csrf_token'] = $_SESSION['csrf_token'];
        }

        echo ViewerPlace::render('admin-register', $data);
    }

    public function logout()
    {
        header('Location: /logout');
        exit;
    }
}
