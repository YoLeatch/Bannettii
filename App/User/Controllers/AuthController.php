<?php
namespace App\User\Controllers;

use Core\Security;
use App\User\PessoaModel;
use Core\ViewerPlace;

class AuthController {

    public function showLoginForm() {
        $token = Security::generateCSRFToken();
        $error = $_SESSION['ERROR'] ?? '';
        unset($_SESSION['ERROR']);
        echo ViewerPlace::render('login', ['csrf_token' => $token, 'error' => $error]);
    }

    public function login() {
        $token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCSRFToken($token)) {
            $_SESSION['ERROR'] = 'Não foi possível processar o login.';
            $this->showLoginForm();     
            exit;
        }
        $email = Security::sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        try {
            if (!isset($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['ERROR'] = 'E-mail inválido.';
                $this->showLoginForm();
                exit;
            }

            if (!isset($password)) {
                $_SESSION['ERROR'] = 'Senha inválida.';
                $this->showLoginForm();
                exit;
            }
            
            if ($Pessoa = PessoaModel::findByData('email', $email)){
                if (Security::verifyPassword($password, $Pessoa->getPasswordHash())) {
                    $_SESSION['user_id'] = $Pessoa->getId();
                    $_SESSION['logged_in'] = true;

                    // Check if user is also an Admin (Funcionario)
                    $funcionario = \App\User\FuncionarioModel::findByFuncionarioId($Pessoa->getId());
                    if ($funcionario) {
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $funcionario->getId();
                        $_SESSION['admin_name'] = $funcionario->getNome();
                    }

                    if (isset($_POST['remember_me'])) {
                        $UID = [
                        'user_uid' => $Pessoa->getUid(), 
                        'exp' => time() + (86400 * 30)
                        ];
                        $token = Security::generateJWT($UID);

                        setcookie('remember_token', $token, [
                            'expires' => time() + (86400 * 30),
                            'path' => '/',
                            'httponly' => true,
                        ]);
                    } else {
                        if (isset($_COOKIE['remember_token'])) {
                            setcookie('remember_token', '', time() - 3600, "/");
                        }
                    }
                    header("Location: /home");
                    exit;
                } else {
                    $_SESSION['ERROR'] = 'Credenciais inválidas.';
                    $this->showLoginForm();
                    exit;
                }
            } else {
                $_SESSION['ERROR'] = 'Email não registrado';
                $this->showLoginForm();
                exit;
            }

        } catch (\Exception $e) {
            $_SESSION['ERROR'] = 'Não foi possível processar o login.';
            $this->showLoginForm();
            exit;
        }
    }

    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
        if (isset($_COOKIE['remember_token']) && !empty($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, "/");
        }
        header("Location: /login");
        exit;
    }
}