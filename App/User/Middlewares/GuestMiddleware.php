<?php
namespace App\User\Middlewares;

use Core\Security;
use App\User\PessoaModel;

class GuestMiddleware {
    public static function handle(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $isAuthenticated = (isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true));

        if ($isAuthenticated) {
            header("Location: /home");
            exit;
        }

        // 2. Verifica o cookie "Lembrar-me"
        if (isset($_COOKIE['remember_token']) && !empty($_COOKIE['remember_token'])) {
            $token = Security::getJWTPayload($_COOKIE['remember_token']); //
            
            if (!($token && isset($token['user_uid']))) {
                setcookie('remember_token', '', time() - 3600, "/");
                return true;
            }
            $userUId = $token['user_uid'];
            $user = PessoaModel::findByData('uid', $userUId);

            if (!$user) {
                setcookie('remember_token', '', time() - 3600, "/");
                return true;
            }
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['logged_in'] = true;

                header("Location: /home");
                exit;
        }
        
        return true;
    }
}