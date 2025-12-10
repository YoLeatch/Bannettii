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
use App\User\Models\PessoaModel;

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

Router::addRoute("GET", "/", "App\\Pages\\Controllers\\HomeController@index", []);
Router::addRoute("GET", "/home", "App\\Pages\\Controllers\\HomeController@index", []);

//sistema de autenticação 
Router::addRoute("GET", "/login", "App\\User\\Controllers\\AuthController@showLoginForm", [GuestMiddleware::class, 'handle']);
Router::addRoute("POST", "/login", "App\\User\\Controllers\\AuthController@login", [GuestMiddleware::class, 'handle']);
Router::addRoute("GET", "/register", "App\\User\\Controllers\\RegisterController@showRegisterForm", [GuestMiddleware::class, 'handle']);
Router::addRoute("POST", "/register", "App\\User\\Controllers\\RegisterController@register", [GuestMiddleware::class, 'handle']);
Router::addRoute("GET", "/logout", "App\\User\\Controllers\\AuthController@logout", [AuthMiddleware::class, 'handle']);

//sistema de dados do usuario
Router::addRoute("GET", "/perfil", "App\\Pages\\Controllers\\ProfileController@handle", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/perfil/update", "App\\Pages\\Controllers\\ProfileController@update", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/perfil/upload-avatar", "App\\Pages\\Controllers\\ProfileController@uploadAvatar", [AuthMiddleware::class, 'handle']);

//Pedidos do usuário
Router::addRoute("GET", "/pedidos", "App\\Pages\\Controllers\\OrderController@handle", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/pedidos/{id}", "App\\Pages\\Controllers\\OrderController@detalhes", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/pedidos/cancelar", "App\\Pages\\Controllers\\OrderController@cancelar", [AuthMiddleware::class, 'handle']);

//Produtos
Router::addRoute("GET", "/produto/{id}", "App\\Pages\\Controllers\\ProductController@show", []);
Router::addRoute("GET", "/catalogo", "App\\Pages\\Controllers\\CatalogController@handle", []);
Router::addRoute("GET", "/carrinho", "App\\Pages\\Controllers\\CartController@handle", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/carrinho/add", "App\\Pages\\Controllers\\CartController@add", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/carrinho/remove", "App\\Pages\\Controllers\\CartController@remove", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/carrinho/update", "App\\Pages\\Controllers\\CartController@update", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/carrinho/clear", "App\\Pages\\Controllers\\CartController@clear", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/carrinho/count", "App\\Pages\\Controllers\\CartController@count", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/carrinho/apply-cupom", "App\\Pages\\Controllers\\CartController@applyCupom", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/carrinho/remove-cupom", "App\\Pages\\Controllers\\CartController@removeCupom", [AuthMiddleware::class, 'handle']);

//Endereços do usuário
Router::addRoute("GET", "/enderecos", "App\\Pages\\Controllers\\EnderecoController@handle", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/enderecos/add", "App\\Pages\\Controllers\\EnderecoController@add", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/enderecos/update", "App\\Pages\\Controllers\\EnderecoController@update", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/enderecos/delete", "App\\Pages\\Controllers\\EnderecoController@delete", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/enderecos/add-tipo", "App\\Pages\\Controllers\\EnderecoController@addTipo", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/enderecos/remove-tipo", "App\\Pages\\Controllers\\EnderecoController@removeTipo", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/enderecos/cidades", "App\\Pages\\Controllers\\EnderecoController@getCidades", [AuthMiddleware::class, 'handle']);

//Cartões do usuário
Router::addRoute("GET", "/cartoes", "App\\Pages\\Controllers\\MyCardsController@handle", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/cartoes/add", "App\\Pages\\Controllers\\MyCardsController@add", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/cartoes/delete", "App\\Pages\\Controllers\\MyCardsController@delete", [AuthMiddleware::class, 'handle']);

//Checkout
Router::addRoute("GET", "/checkout", "App\\Pages\\Controllers\\CheckoutController@handle", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/checkout/processar", "App\\Pages\\Controllers\\CheckoutController@processar", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/compra-efetuada", "App\\Pages\\Controllers\\CheckoutController@compraEfetuada", [AuthMiddleware::class, 'handle']);

//Wishlist (Lista de Desejos) - usa cookies, não precisa de login
Router::addRoute("GET", "/wishlist", "App\\Pages\\Controllers\\WishListController@index", []);
Router::addRoute("POST", "/wishlist/add", "App\\Pages\\Controllers\\WishListController@add", []);
Router::addRoute("POST", "/wishlist/remove", "App\\Pages\\Controllers\\WishListController@remove", []);
Router::addRoute("POST", "/wishlist/toggle", "App\\Pages\\Controllers\\WishListController@toggle", []);
Router::addRoute("GET", "/wishlist/check", "App\\Pages\\Controllers\\WishListController@check", []);
Router::addRoute("GET", "/wishlist/count", "App\\Pages\\Controllers\\WishListController@count", []);


//Admin Dashboard
Router::addRoute("GET", "/admin/dashboard", "App\\Pages\\Controllers\\admin\\DashboardController@index", [AuthMiddleware::class, 'handle']);

//Admin Team
Router::addRoute("GET", "/admin/team-list", "App\\Pages\\Controllers\\admin\\TeamController@index", [AuthMiddleware::class, 'handle']);

//Admin Products
Router::addRoute("GET", "/admin/products", "App\\Pages\\Controllers\\admin\\ProductsController@index", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/products/create", "App\\Pages\\Controllers\\admin\\ProductsController@create", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/products/store", "App\\Pages\\Controllers\\admin\\ProductsController@store", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/products/{id}/edit", "App\\Pages\\Controllers\\admin\\ProductsController@edit", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/products/{id}/update", "App\\Pages\\Controllers\\admin\\ProductsController@update", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/products/{id}/delete", "App\\Pages\\Controllers\\admin\\ProductsController@delete", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/products/{id}/images/{image_id}/delete", "App\\Pages\\Controllers\\admin\\ProductsController@deleteImage", [AuthMiddleware::class, 'handle']);

//Admin Customers
Router::addRoute("GET", "/admin/customers", "App\\Pages\\Controllers\\admin\\CustomersController@index", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/customers/search", "App\\Pages\\Controllers\\admin\\CustomersController@search", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/customers/{id}", "App\\Pages\\Controllers\\admin\\CustomersController@show", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/customers/{id}/toggle", "App\\Pages\\Controllers\\admin\\CustomersController@toggleStatus", [AuthMiddleware::class, 'handle']);

//Admin Orders
Router::addRoute("GET", "/admin/orders", "App\\Pages\\Controllers\\admin\\OrdersController@index", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/orders/{id}", "App\\Pages\\Controllers\\admin\\OrdersController@show", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/orders/{id}/status", "App\\Pages\\Controllers\\admin\\OrdersController@updateStatus", [AuthMiddleware::class, 'handle']);

//Admin Categories
Router::addRoute("GET", "/admin/categories", "App\\Pages\\Controllers\\admin\\CategoriesController@index", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/categories/create", "App\\Pages\\Controllers\\admin\\CategoriesController@create", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/categories/store", "App\\Pages\\Controllers\\admin\\CategoriesController@store", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/categories/{id}/edit", "App\\Pages\\Controllers\\admin\\CategoriesController@edit", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/categories/{id}/update", "App\\Pages\\Controllers\\admin\\CategoriesController@update", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/categories/{id}/delete", "App\\Pages\\Controllers\\admin\\CategoriesController@delete", [AuthMiddleware::class, 'handle']);

//Admin Subcategories
Router::addRoute("GET", "/admin/subcategorias", "App\\Pages\\Controllers\\admin\\CategoriesController@subcategorias", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/subcategorias/create", "App\\Pages\\Controllers\\admin\\CategoriesController@subcategoriasCreate", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/subcategorias/store", "App\\Pages\\Controllers\\admin\\CategoriesController@subcategoriasStore", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/subcategorias/{id}/edit", "App\\Pages\\Controllers\\admin\\CategoriesController@subcategoriasEdit", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/subcategorias/{id}/update", "App\\Pages\\Controllers\\admin\\CategoriesController@subcategoriasUpdate", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/subcategorias/{id}/delete", "App\\Pages\\Controllers\\admin\\CategoriesController@subcategoriasDelete", [AuthMiddleware::class, 'handle']);

//Admin Sessions (Seções da Home)
Router::addRoute("GET", "/admin/sessions", "App\\Pages\\Controllers\\admin\\SessionsController@index", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/sessions/create", "App\\Pages\\Controllers\\admin\\SessionsController@create", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/sessions/store", "App\\Pages\\Controllers\\admin\\SessionsController@store", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/sessions/update", "App\\Pages\\Controllers\\admin\\SessionsController@update", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/sessions/{id}/toggle", "App\\Pages\\Controllers\\admin\\SessionsController@toggle", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/sessions/{id}/delete", "App\\Pages\\Controllers\\admin\\SessionsController@delete", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/sessions/reorder", "App\\Pages\\Controllers\\admin\\SessionsController@reorder", [AuthMiddleware::class, 'handle']);


//Admin Reviews
Router::addRoute("GET", "/admin/reviews", "App\\Pages\\Controllers\\admin\\ReviewsController@index", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/reviews/{id}/approve", "App\\Pages\\Controllers\\admin\\ReviewsController@approve", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/reviews/{id}/reject", "App\\Pages\\Controllers\\admin\\ReviewsController@reject", [AuthMiddleware::class, 'handle']);

//Admin Cupons
Router::addRoute("GET", "/admin/cupons", "App\\Pages\\Controllers\\admin\\CouponsController@index", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/cupons/store", "App\\Pages\\Controllers\\admin\\CouponsController@store", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/cupons/{id}/toggle", "App\\Pages\\Controllers\\admin\\CouponsController@toggle", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/cupons/{id}/delete", "App\\Pages\\Controllers\\admin\\CouponsController@delete", [AuthMiddleware::class, 'handle']);

//Admin Carousel
Router::addRoute("GET", "/admin/carousel", "App\\Pages\\Controllers\\admin\\CarouselController@index", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/carousel/store", "App\\Pages\\Controllers\\admin\\CarouselController@store", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/carousel/{id}/edit", "App\\Pages\\Controllers\\admin\\CarouselController@edit", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/carousel/{id}/update", "App\\Pages\\Controllers\\admin\\CarouselController@update", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/carousel/{id}/delete", "App\\Pages\\Controllers\\admin\\CarouselController@delete", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/carousel/reorder", "App\\Pages\\Controllers\\admin\\CarouselController@reorder", [AuthMiddleware::class, 'handle']);

//Admin Settings
Router::addRoute("GET", "/admin/settings", "App\\Pages\\Controllers\\admin\\SettingsController@index", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/settings/update", "App\\Pages\\Controllers\\admin\\SettingsController@update", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/configuracoes", "App\\Pages\\Controllers\\admin\\SettingsController@general", [AuthMiddleware::class, 'handle']);

//Admin Reports
Router::addRoute("GET", "/admin/reports", "App\\Pages\\Controllers\\admin\\ReportsController@index", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/reports/export", "App\\Pages\\Controllers\\admin\\ReportsController@export", [AuthMiddleware::class, 'handle']);

//Suporte - Usuário (não precisa de login para abrir ticket)
Router::addRoute("GET", "/help", "App\\Pages\\Controllers\\admin\\SupportController@index", []);
Router::addRoute("POST", "/help/create", "App\\Pages\\Controllers\\admin\\SupportController@create", []);
Router::addRoute("GET", "/help/ticket/{id}", "App\\Pages\\Controllers\\admin\\SupportController@show", []);
Router::addRoute("POST", "/help/reply", "App\\Pages\\Controllers\\admin\\SupportController@reply", []);

//Suporte - Admin
Router::addRoute("GET", "/admin/tickets", "App\\Pages\\Controllers\\admin\\SupportController@adminList", [AuthMiddleware::class, 'handle']);
Router::addRoute("GET", "/admin/tickets/{id}", "App\\Pages\\Controllers\\admin\\SupportController@adminShow", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/tickets/reply", "App\\Pages\\Controllers\\admin\\SupportController@adminReply", [AuthMiddleware::class, 'handle']);
Router::addRoute("POST", "/admin/tickets/status", "App\\Pages\\Controllers\\admin\\SupportController@adminUpdateStatus", [AuthMiddleware::class, 'handle']);

//Suporte - AJAX (polling tempo real)
Router::addRoute("GET", "/api/tickets/{id}/updates", "App\\Pages\\Controllers\\admin\\SupportController@getUpdates", []);
Router::addRoute("GET", "/api/tickets/list", "App\\Pages\\Controllers\\admin\\SupportController@getTicketsList", [AuthMiddleware::class, 'handle']);

//Admin (outras rotas - comentadas)
//Router::group("/admin", function () {
//    Router::addRoute("GET", "/dashboard", "App\\User\\Controllers\\AdminController@dashboard", []);
//    Router::addRoute("GET", "/team-list", "App\\User\\Controllers\\AdminController@teamList", []);
//    
//    // Carousel
//    Router::addRoute("GET", "/carousel", "App\\User\\Controllers\\AdminController@carousel", []);
//    Router::addRoute("POST", "/carousel/add", "App\\User\\Controllers\\AdminController@addSlide", []);
//    Router::addRoute("POST", "/carousel/delete", "App\\User\\Controllers\\AdminController@deleteSlide", []);
//    
//    // Content Management
//    Router::addRoute("GET", "/news", "App\\User\\Controllers\\AdminController@news", []);
//    Router::addRoute("GET", "/bestsellers", "App\\User\\Controllers\\AdminController@bestsellers", []);
//    Router::addRoute("GET", "/settings", "App\\User\\Controllers\\AdminController@settings", []);
//
//    // Products
//    Router::addRoute("GET", "/products", "App\\User\\Controllers\\AdminProductController@index", []);
//    Router::addRoute("GET", "/products/create", "App\\User\\Controllers\\AdminProductController@create", []);
//    Router::addRoute("POST", "/products/store", "App\\User\\Controllers\\AdminProductController@store", []);
//    Router::addRoute("GET", "/products/edit/{id}", "App\\User\\Controllers\\AdminProductController@edit", []);
//    Router::addRoute("POST", "/products/update/{id}", "App\\User\\Controllers\\AdminProductController@update", []);
//    Router::addRoute("GET", "/products/delete/{id}", "App\\User\\Controllers\\AdminProductController@delete", []);
//
//    // Categories
//    Router::addRoute("GET", "/categories", "App\\User\\Controllers\\AdminCategoryController@index", []);
//    Router::addRoute("GET", "/categories/create", "App\\User\\Controllers\\AdminCategoryController@create", []);
//    Router::addRoute("POST", "/categories/store", "App\\User\\Controllers\\AdminCategoryController@store", []);
//    Router::addRoute("GET", "/categories/edit/{id}", "App\\User\\Controllers\\AdminCategoryController@edit", []);
//    Router::addRoute("POST", "/categories/update/{id}", "App\\User\\Controllers\\AdminCategoryController@update", []);
//    Router::addRoute("GET", "/categories/delete/{id}", "App\\User\\Controllers\\AdminCategoryController@delete", []);
//
//    // Subcategories
//    Router::addRoute("GET", "/subcategories", "App\\User\\Controllers\\AdminSubCategoryController@index", []);
//    Router::addRoute("GET", "/subcategories/create", "App\\User\\Controllers\\AdminSubCategoryController@create", []);
//    Router::addRoute("POST", "/subcategories/store", "App\\User\\Controllers\\AdminSubCategoryController@store", []);
//    Router::addRoute("GET", "/subcategories/edit/{id}", "App\\User\\Controllers\\AdminSubCategoryController@edit", []);
//    Router::addRoute("POST", "/subcategories/update/{id}", "App\\User\\Controllers\\AdminSubCategoryController@update", []);
//    Router::addRoute("GET", "/subcategories/delete/{id}", "App\\User\\Controllers\\AdminSubCategoryController@delete", []);
//    Router::addRoute("GET", "/subcategories/by-category/{id}", "App\\User\\Controllers\\AdminSubCategoryController@getByCategory", []);
//
//    // Customers
//    Router::addRoute("GET", "/customers", "App\\User\\Controllers\\AdminCustomerController@index", []);
//    Router::addRoute("GET", "/customers/search", "App\\User\\Controllers\\AdminCustomerController@search", []);
//
//    // Orders
//    Router::addRoute("GET", "/orders", "App\\User\\Controllers\\AdminOrderController@index", []);
//    Router::addRoute("GET", "/orders/show/{id}", "App\\User\\Controllers\\AdminOrderController@show", []);
//    Router::addRoute("POST", "/orders/update-status", "App\\User\\Controllers\\AdminOrderController@updateStatus", []);
//
//    // Logs
//    Router::addRoute("GET", "/gerenciar-logs", "App\\User\\Controllers\\AdminLogController@index", []);
//    
//    // Promotions
//    Router::addRoute("GET", "/promotions", "App\\User\\Controllers\\PromoteController@index", []);
//    Router::addRoute("POST", "/promotions/search", "App\\User\\Controllers\\PromoteController@search", []);
//    Router::addRoute("POST", "/promotions/promote", "App\\User\\Controllers\\PromoteController@promote", []);
//    
//    // Reviews
//    Router::addRoute("GET", "/reviews", "App\\User\\Controllers\\AdminReviewController@index", []);
//    
//    // Reports
//    Router::addRoute("GET", "/reports", "App\\User\\Controllers\\AdminReportController@index", []);
//
//    Router::addRoute("GET", "/", "App\\User\\Controllers\\AdminController@dashboard", []);
//    Router::addRoute("GET", "/tabela-order", "App\\Pages\\Controllers\\TabelaOrdersController@index", []);
//    Router::addRoute("GET", "/registrar-produto", "App\\Pages\\Controllers\\GerenciarProdutoController@index", []);
//    Router::addRoute("POST", "/new/produto", "App\\Pages\\Controllers\\GerenciarProdutoController@add", []);
//});

$router = new Router();
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->dispatch($method, $uri);