<?php
namespace App\User\Controllers;

use Core\Security;
use Core\Recaptcha;
use App\User\Models\PessoaModel;
use App\User\Models\FuncionarioModel;
use Core\ViewerPlace;

class AuthController {

    public function showLoginForm() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(0);
            session_start();
        }
        $token = Security::generateCSRFToken();
        $error = $_SESSION['ERROR'] ?? '';
        unset($_SESSION['ERROR']);
        
        // Passa a chave do reCAPTCHA para o template
        echo ViewerPlace::render('login', [
            'csrf_token' => $token, 
            'error' => $error,
            'recaptcha_site_key' => Recaptcha::getSiteKey()
        ]);
    }

    public function login() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(0);
            session_start();
        }
        $token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCSRFToken($token)) {
            $_SESSION['ERROR'] = 'Não foi possível processar o login.';
            $this->showLoginForm();     
            exit;
        }

        // Validação do reCAPTCHA
        $recaptchaToken = $_POST['g-recaptcha-response'] ?? '';
        if (!Recaptcha::verify($recaptchaToken)) {
            $_SESSION['ERROR'] = 'Por favor, confirme que você não é um robô.';
            header("Location: /login");
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
                if (Security::verifyPassword($password, $Pessoa->getSenha())) {
                    $_SESSION['user_id'] = $Pessoa->getId();
                    $_SESSION['logged_in'] = true;

                    // Verifica se o usuário também é um Funcionário (Admin)
                    $funcionario = FuncionarioModel::findById($Pessoa->getId());
                    if ($funcionario) {
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $funcionario->getId();
                        $_SESSION['admin_name'] = $funcionario->getNome();
                        $_SESSION['is_employee'] = true;
                        
                        // Redireciona para o Dashboard Admin
                        header("Location: /admin/dashboard");
                        exit;
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