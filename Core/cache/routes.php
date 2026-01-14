<?php return array (
  0 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\HomeController@index',
    'regex' => '#^/?$#',
    'middleware' => 
    array (
    ),
  ),
  1 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\HomeController@index',
    'regex' => '#^/home/?$#',
    'middleware' => 
    array (
    ),
  ),
  2 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AuthController@showLoginForm',
    'regex' => '#^/login/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\GuestMiddleware',
      1 => 'handle',
    ),
  ),
  3 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\User\\Controllers\\AuthController@login',
    'regex' => '#^/login/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\GuestMiddleware',
      1 => 'handle',
    ),
  ),
  4 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\RegisterController@showRegisterForm',
    'regex' => '#^/register/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\GuestMiddleware',
      1 => 'handle',
    ),
  ),
  5 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\User\\Controllers\\RegisterController@register',
    'regex' => '#^/register/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\GuestMiddleware',
      1 => 'handle',
    ),
  ),
  6 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AuthController@logout',
    'regex' => '#^/logout/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  7 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\ProfileController@handle',
    'regex' => '#^/perfil/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  8 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\ProfileController@update',
    'regex' => '#^/perfil/update/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  9 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\ProfileController@uploadAvatar',
    'regex' => '#^/perfil/upload-avatar/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  10 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\OrderController@handle',
    'regex' => '#^/pedidos/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  11 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\OrderController@detalhes',
    'regex' => '#^/pedidos/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  12 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\OrderController@cancelar',
    'regex' => '#^/pedidos/cancelar/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  13 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\ProductController@show',
    'regex' => '#^/produto/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
    ),
  ),
  14 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\CatalogController@handle',
    'regex' => '#^/catalogo/?$#',
    'middleware' => 
    array (
    ),
  ),
  15 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\CartController@handle',
    'regex' => '#^/carrinho/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  16 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\CartController@add',
    'regex' => '#^/carrinho/add/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  17 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\CartController@remove',
    'regex' => '#^/carrinho/remove/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  18 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\CartController@update',
    'regex' => '#^/carrinho/update/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  19 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\CartController@clear',
    'regex' => '#^/carrinho/clear/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  20 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\CartController@count',
    'regex' => '#^/carrinho/count/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  21 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\CartController@applyCupom',
    'regex' => '#^/carrinho/apply-cupom/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  22 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\CartController@removeCupom',
    'regex' => '#^/carrinho/remove-cupom/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  23 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\EnderecoController@handle',
    'regex' => '#^/enderecos/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  24 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\EnderecoController@add',
    'regex' => '#^/enderecos/add/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  25 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\EnderecoController@update',
    'regex' => '#^/enderecos/update/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  26 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\EnderecoController@delete',
    'regex' => '#^/enderecos/delete/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  27 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\EnderecoController@addTipo',
    'regex' => '#^/enderecos/add-tipo/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  28 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\EnderecoController@removeTipo',
    'regex' => '#^/enderecos/remove-tipo/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  29 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\EnderecoController@getCidades',
    'regex' => '#^/enderecos/cidades/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  30 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\MyCardsController@handle',
    'regex' => '#^/cartoes/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  31 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\MyCardsController@add',
    'regex' => '#^/cartoes/add/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  32 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\MyCardsController@delete',
    'regex' => '#^/cartoes/delete/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  33 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\CheckoutController@handle',
    'regex' => '#^/checkout/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  34 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\CheckoutController@processar',
    'regex' => '#^/checkout/processar/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  35 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\CheckoutController@compraEfetuada',
    'regex' => '#^/compra-efetuada/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  36 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\WishListController@index',
    'regex' => '#^/wishlist/?$#',
    'middleware' => 
    array (
    ),
  ),
  37 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\WishListController@add',
    'regex' => '#^/wishlist/add/?$#',
    'middleware' => 
    array (
    ),
  ),
  38 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\WishListController@remove',
    'regex' => '#^/wishlist/remove/?$#',
    'middleware' => 
    array (
    ),
  ),
  39 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\WishListController@toggle',
    'regex' => '#^/wishlist/toggle/?$#',
    'middleware' => 
    array (
    ),
  ),
  40 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\WishListController@check',
    'regex' => '#^/wishlist/check/?$#',
    'middleware' => 
    array (
    ),
  ),
  41 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\WishListController@count',
    'regex' => '#^/wishlist/count/?$#',
    'middleware' => 
    array (
    ),
  ),
  42 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\DashboardController@index',
    'regex' => '#^/admin/dashboard/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  43 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\TeamController@index',
    'regex' => '#^/admin/team-list/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  44 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\ProductsController@index',
    'regex' => '#^/admin/products/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  45 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\ProductsController@create',
    'regex' => '#^/admin/products/create/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  46 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\ProductsController@store',
    'regex' => '#^/admin/products/store/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  47 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\ProductsController@edit',
    'regex' => '#^/admin/products/(?P<id>[^/]+)/edit/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  48 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\ProductsController@update',
    'regex' => '#^/admin/products/(?P<id>[^/]+)/update/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  49 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\ProductsController@delete',
    'regex' => '#^/admin/products/(?P<id>[^/]+)/delete/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  50 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\ProductsController@deleteImage',
    'regex' => '#^/admin/products/(?P<id>[^/]+)/images/(?P<image_id>[^/]+)/delete/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  51 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CustomersController@index',
    'regex' => '#^/admin/customers/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  52 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CustomersController@search',
    'regex' => '#^/admin/customers/search/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  53 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CustomersController@show',
    'regex' => '#^/admin/customers/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  54 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\CustomersController@toggleStatus',
    'regex' => '#^/admin/customers/(?P<id>[^/]+)/toggle/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  55 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\OrdersController@index',
    'regex' => '#^/admin/orders/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  56 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\OrdersController@show',
    'regex' => '#^/admin/orders/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  57 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\OrdersController@updateStatus',
    'regex' => '#^/admin/orders/(?P<id>[^/]+)/status/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  58 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CategoriesController@index',
    'regex' => '#^/admin/categories/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  59 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CategoriesController@create',
    'regex' => '#^/admin/categories/create/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  60 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\CategoriesController@store',
    'regex' => '#^/admin/categories/store/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  61 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CategoriesController@edit',
    'regex' => '#^/admin/categories/(?P<id>[^/]+)/edit/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  62 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\CategoriesController@update',
    'regex' => '#^/admin/categories/(?P<id>[^/]+)/update/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  63 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CategoriesController@delete',
    'regex' => '#^/admin/categories/(?P<id>[^/]+)/delete/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  64 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CategoriesController@subcategorias',
    'regex' => '#^/admin/subcategorias/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  65 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CategoriesController@subcategoriasCreate',
    'regex' => '#^/admin/subcategorias/create/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  66 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\CategoriesController@subcategoriasStore',
    'regex' => '#^/admin/subcategorias/store/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  67 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CategoriesController@subcategoriasEdit',
    'regex' => '#^/admin/subcategorias/(?P<id>[^/]+)/edit/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  68 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\CategoriesController@subcategoriasUpdate',
    'regex' => '#^/admin/subcategorias/(?P<id>[^/]+)/update/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  69 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CategoriesController@subcategoriasDelete',
    'regex' => '#^/admin/subcategorias/(?P<id>[^/]+)/delete/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  70 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\SessionsController@index',
    'regex' => '#^/admin/sessions/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  71 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\SessionsController@create',
    'regex' => '#^/admin/sessions/create/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  72 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\SessionsController@store',
    'regex' => '#^/admin/sessions/store/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  73 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\SessionsController@update',
    'regex' => '#^/admin/sessions/update/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  74 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\SessionsController@toggle',
    'regex' => '#^/admin/sessions/(?P<id>[^/]+)/toggle/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  75 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\SessionsController@delete',
    'regex' => '#^/admin/sessions/(?P<id>[^/]+)/delete/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  76 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\SessionsController@reorder',
    'regex' => '#^/admin/sessions/reorder/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  77 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\ReviewsController@index',
    'regex' => '#^/admin/reviews/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  78 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\ReviewsController@approve',
    'regex' => '#^/admin/reviews/(?P<id>[^/]+)/approve/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  79 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\ReviewsController@reject',
    'regex' => '#^/admin/reviews/(?P<id>[^/]+)/reject/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  80 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CouponsController@index',
    'regex' => '#^/admin/cupons/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  81 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\CouponsController@store',
    'regex' => '#^/admin/cupons/store/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  82 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CouponsController@toggle',
    'regex' => '#^/admin/cupons/(?P<id>[^/]+)/toggle/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  83 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CouponsController@delete',
    'regex' => '#^/admin/cupons/(?P<id>[^/]+)/delete/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  84 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CarouselController@index',
    'regex' => '#^/admin/carousel/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  85 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\CarouselController@store',
    'regex' => '#^/admin/carousel/store/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  86 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CarouselController@edit',
    'regex' => '#^/admin/carousel/(?P<id>[^/]+)/edit/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  87 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\CarouselController@update',
    'regex' => '#^/admin/carousel/(?P<id>[^/]+)/update/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  88 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\CarouselController@delete',
    'regex' => '#^/admin/carousel/(?P<id>[^/]+)/delete/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  89 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\CarouselController@reorder',
    'regex' => '#^/admin/carousel/reorder/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  90 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\SettingsController@index',
    'regex' => '#^/admin/settings/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  91 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\SettingsController@update',
    'regex' => '#^/admin/settings/update/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  92 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\SettingsController@general',
    'regex' => '#^/admin/configuracoes/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  93 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\ReportsController@index',
    'regex' => '#^/admin/reports/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  94 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\ReportsController@export',
    'regex' => '#^/admin/reports/export/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  95 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\SupportController@index',
    'regex' => '#^/help/?$#',
    'middleware' => 
    array (
    ),
  ),
  96 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\SupportController@create',
    'regex' => '#^/help/create/?$#',
    'middleware' => 
    array (
    ),
  ),
  97 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\SupportController@show',
    'regex' => '#^/help/ticket/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
    ),
  ),
  98 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\SupportController@reply',
    'regex' => '#^/help/reply/?$#',
    'middleware' => 
    array (
    ),
  ),
  99 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\SupportController@adminList',
    'regex' => '#^/admin/tickets/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  100 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\SupportController@adminShow',
    'regex' => '#^/admin/tickets/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  101 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\SupportController@adminReply',
    'regex' => '#^/admin/tickets/reply/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  102 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\admin\\SupportController@adminUpdateStatus',
    'regex' => '#^/admin/tickets/status/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  103 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\SupportController@getUpdates',
    'regex' => '#^/api/tickets/(?P<id>[^/]+)/updates/?$#',
    'middleware' => 
    array (
    ),
  ),
  104 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\admin\\SupportController@getTicketsList',
    'regex' => '#^/api/tickets/list/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
);