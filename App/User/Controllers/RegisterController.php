<?php 
namespace User\Controllers;

use Core\Security;
use App\User\PessoaModel;
use Core\ViewerPlace;

class RegisterController {

        public function showRegisterForm() {
            $token = Security::generateCSRFToken();
    
            return ViewerPlace::render('register.html', ['csrf_token' => $token]);
        }

    public function register() {
        $username = Security::sanitizeInput($_POST['username'] ?? '');
        $email = Security::sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

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

            // Using username for both nome and usuario
            $user = PessoaModel::registerPessoa($username, $username, $email, $passwordHash);

            if ($user) {
                    $user = PessoaModel::findByData('email', $email);
                    $_SESSION['user_id'] = $user->getId();
                    $_SESSION['logged_in'] = true;

                    if (isset($_POST['remember_me']) && $_POST['remember_me'] === true) {
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