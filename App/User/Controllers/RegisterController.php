<?php 
namespace App\User\Controllers;

use Core\Security;
use App\User\PessoaModel;
use Core\ViewerPlace;

class RegisterController {

    public function showRegisterForm() {
        $token = Security::generateCSRFToken();
        $error = $_SESSION['ERROR'] ?? '';
        unset($_SESSION['ERROR']);
        echo ViewerPlace::render('register', ['csrf_token' => $token, 'error' => $error]);
    }

    public function register() {
        $username = Security::sanitizeInput($_POST['nome'] ?? '');
        $email = Security::sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $cpf = Security::sanitizeInput($_POST['cpf'] ?? '');

        try {
            Security::validateCSRFToken($_POST['csrf_token'] ?? '');

            if (!Security::validateEmail($email)) {
                $_SESSION['ERROR'] = 'E-mail inválido.';
                header("Location: /register");
                exit;
            }

            if ($password !== $confirmPassword) {
                $_SESSION['ERROR'] = 'As senhas não coincidem.';
                header("Location: /register");
                exit;
            }

            $passwordHash = Security::hashPassword($password);

            // Using email as login (usuario)
            $user = PessoaModel::registerPessoa($username, $email, $email, $passwordHash, $cpf);

            if ($user) {
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['logged_in'] = true;

                if (isset($_POST['remember_me'])) {
                    $UID = [
                        'user_uid' => $user->getUid(), 
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