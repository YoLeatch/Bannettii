<?php
require_once __DIR__ . '/../autoload.php';
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(0);
    session_start();
}

use Core\Router;
use App\User\Middlewares\GuestMiddleware;
use App\User\Middlewares\AuthMiddleware;
use Core\Security;
use App\User\PessoaModel;

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    if (isset($_COOKIE['remember_token']) && !empty($_COOKIE['remember_token'])) {
        $token = Security::getJWTPayload($_COOKIE['remember_token']);
        
        if ($token && isset($token['user_uid'])) {
            $user = PessoaModel::findByData('uid', $token['user_uid']);
            if ($user) {
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['logged_in'] = true;
            } else {
                setcookie('remember_token', '', time() - 3600, "/");
            }
        } else {
            setcookie('remember_token', '', time() - 3600, "/");
        }
    }
}

header("X-XSS-Protection: 1; mode=block");
header("X-Frame-Options: DENY");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("X-Content-Type-Options: nosniff");

Router::addRoute("GET", "/", "App\Pages\Controllers\HomeController@index", []);
Router::addRoute("GET", "/home", "App\Pages\Controllers\HomeController@index", []);
Router::addRoute("GET", "/login", "App\User\Controllers\AuthController@showLoginForm", [GuestMiddleware::class, 'handle']);
Router::addRoute("POST", "/login", "App\User\Controllers\AuthController@login", [GuestMiddleware::class, 'handle']);
Router::addRoute("GET", "/register", "App\User\Controllers\RegisterController@showRegisterForm", [GuestMiddleware::class, 'handle']);
Router::addRoute("POST", "/register", "App\User\Controllers\RegisterController@register", [GuestMiddleware::class, 'handle']);
Router::addRoute("GET", "/forgot-password", "App\User\Controllers\ForgotPasswordController@showForgotPasswordForm", [GuestMiddleware::class, 'handle']);
Router::addRoute("POST", "/forgot-password", "App\User\Controllers\ForgotPasswordController@sendResetLinkEmail", [GuestMiddleware::class, 'handle']);
Router::addRoute("GET", "/logout", "App\User\Controllers\AuthController@logout", [AuthMiddleware::class, 'handle']);

Router::addRoute("GET", "/cart", "App\Cart\CartController@index", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/cart/add", "App\Cart\CartController@add", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/cart/remove", "App\Cart\CartController@remove", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/cart/update", "App\Cart\CartController@update", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/cart/clear", "App\Cart\CartController@clear", [AuthMiddleware::class, 'handle']);

Router::addRoute("GET", "/gerenciarfuncionarios", "App\Pages\Controllers\TeamController@index", [AuthMiddleware::class, 'handle']);

// Admin Auth Routes
// Router::addRoute("GET", "/admin/login", "App\Pages\Controllers\AdminAuthController@login", []); // Removed
// Router::addRoute("POST", "/admin/login", "App\Pages\Controllers\AdminAuthController@login", []); // Removed
Router::addRoute("GET", "/admin/register", "App\Pages\Controllers\AdminAuthController@register", []);
Router::addRoute("POST", "/admin/register", "App\Pages\Controllers\AdminAuthController@register", []);
Router::addRoute("GET", "/admin/logout", "App\Pages\Controllers\AdminAuthController@logout", []);

// Admin System Routes
Router::addRoute("GET", "/admin/products", "App\Pages\Controllers\AdminProductController@index", []); // List or Dashboard
Router::addRoute("GET", "/tabela-produtos", "App\Pages\Controllers\AdminProductController@index", []); // Alias as per view link
Router::addRoute("GET", "/registrar-produto", "App\Pages\Controllers\AdminProductController@index", []); // Alias
Router::addRoute("POST", "/admin/products/store", "App\Pages\Controllers\AdminProductController@store", []);

Router::addRoute("GET", "/admin/users", "App\Pages\Controllers\AdminUserController@index", []);
Router::addRoute("GET", "/tabela-usuarios", "App\Pages\Controllers\AdminUserController@index", []); // Alias
Router::addRoute("GET", "/admin/users/edit", "App\Pages\Controllers\AdminUserController@edit", []);
Router::addRoute("POST", "/admin/users/update", "App\Pages\Controllers\AdminUserController@update", []);

Router::addRoute("GET", "/admin/reports", "App\Pages\Controllers\AdminReportController@index", []);
Router::addRoute("GET", "/orders-preview", "App\Pages\Controllers\AdminReportController@index", []); // Alias


$router = new Router();
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->dispatch($method, $uri);