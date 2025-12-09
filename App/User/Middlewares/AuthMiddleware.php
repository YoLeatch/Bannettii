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
            // Se for requisição AJAX, retorna JSON ao invés de redirecionar
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Você precisa estar logado para realizar esta ação.',
                    'redirect' => '/login'
                ]);
                exit;
            }
            
            header("Location: /login");
            exit;
        }
        return true;
    }

    public static function check(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return (isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && ((isset($_SESSION['logged_in']) && !empty($_SESSION['logged_in']))));
    }
}