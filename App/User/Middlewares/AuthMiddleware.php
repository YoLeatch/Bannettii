<?php

namespace App\User\Middlewares;

use App\User\PessoaModel;
use Core\Security;

class AuthMiddleware {
    public static function handle(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $isAuthenticated = (isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && ((isset($_SESSION['logged_in']) && !empty($_SESSION['logged_in']))));
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (!$isAuthenticated) {
            if(!isset($_COOKIE['remember_token']) || empty($_COOKIE['remember_token'])){
                header("Location: /login");
                exit;
            }
            $token = Security::getJWTPayload($_COOKIE['remember_token']);

            if (!($token && isset($token['user_uid']))) {
                setcookie('remember_token', '', time() - 3600, "/");
                header("Location: /login");
                exit;
            }
            $userUId = $token['user_uid'];
            $user = PessoaModel::findByData('uid', $userUId);

            if (!$user) {
                header("Location: /login");
                exit;
            }
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['logged_in'] = true;
            
            header("Location: /home");
            exit;
        }
        header("Location: /home");
        exit;
    }
}