<?php
namespace User\Controllers;

use Core\Security;
use App\User\PessoaModel;
use Core\ViewerPlace;

class AuthController {

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
        $password = $_POST['password'] ?? '';

        try {
            if (!isset($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header("Location: /login");
                exit;
            }

            if (!isset($password)) {
                header("Location: /login");
                exit;
            }

            $Pessoa = PessoaModel::findByData('email', $email);

            if ($Pessoa && Security::verifyPassword($password, $Pessoa->getPasswordHash())) {
                $_SESSION['user_id'] = $Pessoa->getId();
                $_SESSION['logged_in'] = true;

                if (isset($_POST['remember_me']) && $_POST['remember_me'] === true) {
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
}