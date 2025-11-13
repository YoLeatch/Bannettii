<?php
namespace App\User\Controllers;

use Core\Security;
use App\User\Middlewares\AuthMiddleware;
use App\User\UserModel;
use Core\ViewerPlace;

class AuthController {

    public function __construct() {
        if (AuthMiddleware::handle()) {
            header("Location: /home");
            exit;
        }
    }

    public function showLoginForm() {
        $token = Security::generateCSRFToken();

        return ViewerPlace::render('login.html', ['csrf_token' => $token]);
    }

    public function showRegisterForm() {
        $token = Security::generateCSRFToken();

        return ViewerPlace::render('register.html', ['csrf_token' => $token]);
    }

    public function login() {
        $email = Security::sanitizeInput($_POST['email'] ?? '');
        $password = Security::sanitizeInput($_POST['password'] ?? '');

        try {
            if (!Security::validateEmail($email)) {
                $_SESSION['E-MAIL_ERROR'] = 'E-mail inválido.';

                header("Location: /login");
                exit;
            }
            if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
                $_SESSION['CSRF_ERROR'] = 'CSRF token inválido.';

                header("Location: /login");
                exit;
            }
            $user = UserModel::findByData('email', $email);

            if ($user && Security::verifyPassword($password, $user->getPasswordHash())) {
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['logged_in'] = true;

                if (isset($_POST['remember_me']) && $_POST['remember_me'] === true) {
                    $UID = [
                    'user_uid' => $user->getUID(), 
                    'exp' => time() + (86400 * 30)
                    ];
                    $token = Security::generateJWT($UID);

                    setcookie('remember_token', $token, [
                        'expires' => time() + (86400 * 30),
                        'path' => '/',
                        'httponly' => true,
                    ]);
                }
                header("Location: /home");
                exit;
            }else {
                $_SESSION['LOGIN_ERROR'] = 'Credenciais inválidas.';

                header("Location: /login");
                exit;
            }

        } catch (\Exception $e) {
            $_SESSION['LOGIN_ERROR'] = 'Não foi possível processar o login.';
            header("Location: /login");
            exit;
        }
    }

    public function register() {
        $username = Security::sanitizeInput($_POST['username'] ?? '');
        $email = Security::sanitizeInput($_POST['email'] ?? '');
        $password = Security::sanitizeInput($_POST['password'] ?? '');
        $confirmPassword = Security::sanitizeInput($_POST['confirm_password'] ?? '');

        try {
            Security::validateCSRFToken($_POST['csrf_token'] ?? '');

            if (!Security::validateEmail($email)) {
                $_SESSION['EMAIL_ERROR'] = 'E-mail inválido.';
                header("Location: /register");
                exit;
            }

            if ($password !== $confirmPassword) {
                $_SESSION['PASSWORD_ERROR'] = 'As senhas não coincidem.';
                header("Location: /register");
                exit;
            }

            $passwordHash = Security::hashPassword($password);

            $user = UserModel::registerUser($username, $email, $passwordHash, $RG = '', $CPF_CNPJ = '');

            if ($user) {
                    $user = UserModel::findByData('email', $email);
                    $_SESSION['user_id'] = $user->getID();
                    $_SESSION['logged_in'] = true;

                    if (isset($_POST['remember_me']) && $_POST['remember_me'] === true) {
                    $UID = [
                    'user_uid' => $user->getUID(), 
                    'exp' => time() + (86400 * 30)
                    ];

                    $token = Security::generateJWT($UID);

                    setcookie('remember_token', $token, [
                        'expires' => time() + (86400 * 30),
                        'path' => '/',
                        'httponly' => true,
                    ]);
                }
                header("Location: /home");
                exit;
            } else {
                $_SESSION['REGISTER_ERROR'] = 'Falha ao registrar usuário.';
                header("Location: /register");
                exit;
            }

        } catch (\Exception $e) {
                $_SESSION['REGISTER_ERROR'] = 'Falha ao registrar usuário.';
                header("Location: /register");
                exit;
        }
    }
}