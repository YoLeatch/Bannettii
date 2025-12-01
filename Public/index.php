<?php
require_once __DIR__ . '/../autoload.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use Core\Router;
use App\User\Middlewares\GuestMiddleware;
use App\User\Middlewares\AuthMiddleware;

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


$router = new Router();
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->dispatch($method, $uri);