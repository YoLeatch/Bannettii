<?php 
namespace App\User\Controllers;

use Core\Security;
use Core\Recaptcha;
use App\User\ClienteModel;
use Core\ViewerPlace;

class RegisterController {

    public function showRegisterForm() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(0);
            session_start();
        }
        $token = Security::generateCSRFToken();
        $error = $_SESSION['ERROR'] ?? '';
        unset($_SESSION['ERROR']);
        
        // Passa a chave do reCAPTCHA para o template
        echo ViewerPlace::render('cadastro', [
            'csrf_token' => $token, 
            'error' => $error,
            'recaptcha_site_key' => Recaptcha::getSiteKey()
        ]);
    }

    public function register() {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(0);
            session_start();
        }
        
        $logFile = __DIR__ . '/../../../debug_register.txt';
        file_put_contents($logFile, "Register attempt started\n", FILE_APPEND);

        $username = Security::sanitizeInput($_POST['nome'] ?? '');
        $email = Security::sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $cpf = Security::sanitizeInput($_POST['CPF'] ?? '');
        $idade = (int) ($_POST['idade'] ?? 0);

        try {
            Security::validateCSRFToken($_POST['csrf_token'] ?? '');
            file_put_contents($logFile, "CSRF Validated\n", FILE_APPEND);

            // Validação do reCAPTCHA
            $recaptchaToken = $_POST['g-recaptcha-response'] ?? '';
            if (!Recaptcha::verify($recaptchaToken)) {
                $_SESSION['ERROR'] = 'Por favor, confirme que você não é um robô.';
                file_put_contents($logFile, "reCAPTCHA failed\n", FILE_APPEND);
                header("Location: /register");
                exit;
            }
            file_put_contents($logFile, "reCAPTCHA Validated\n", FILE_APPEND);

            if (!Security::validateEmail($email)) {
                $_SESSION['ERROR'] = 'E-mail inválido.';
                file_put_contents($logFile, "Invalid Email\n", FILE_APPEND);
                header("Location: /register");
                exit;
            }

            if ($password !== $confirmPassword) {
                $_SESSION['ERROR'] = 'As senhas não coincidem.';
                file_put_contents($logFile, "Passwords do not match\n", FILE_APPEND);
                header("Location: /register");
                exit;
            }

            $passwordHash = Security::hashPassword($password);

            // Criar registro em pessoa E cliente usando ClienteModel
            //$user = ClienteModel::createCliente($username, $email, $passwordHash, $cpf, $idade);
//
            //if ($user) {
            //    file_put_contents($logFile, "User created successfully: " . $user->getId() . "\n", FILE_APPEND);
            //    $_SESSION['user_id'] = $user->getId();
            //    $_SESSION['logged_in'] = true;
//
            //    if (isset($_POST['remember_me'])) {
            //        $UID = [
            //            'user_uid' => $user->getUid(),
            //            'exp' => time() + (86400 * 30)
            //        ];
//
            //        $token = Security::generateJWT($UID);
//
            //        setcookie('remember_token', $token, [
            //            'expires' => time() + (86400 * 30),
            //            'path' => '/',
            //            'httponly' => true,
            //        ]);
            //    }
            //    header("Location: /home");
            //    exit;
            //} else {
            //    file_put_contents($logFile, "ClienteModel::createCliente returned null\n", FILE_APPEND);
            //    $_SESSION['ERROR'] = 'Falha ao registrar usuário (Model retornou null).';
            //    header("Location: /register");
            //    exit;
            //}

        } catch (\Exception $e) {
            file_put_contents($logFile, "Exception caught: " . $e->getMessage() . "\n", FILE_APPEND);
            $_SESSION['ERROR'] = 'Erro interno: ' . $e->getMessage();
            header("Location: /register");
            exit;
        }
    }
}