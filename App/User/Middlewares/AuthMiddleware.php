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
            header("Location: /login");
            exit;
        }
        return true;
    }
}