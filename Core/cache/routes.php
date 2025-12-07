<?php return array (
  0 => 
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
  1 => 
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
  2 => 
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
  3 => 
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
  4 => 
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
  5 => 
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
  6 => 
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
  7 => 
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
  8 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\CompleteRegistrationController@showForm',
    'regex' => '#^/completar-cadastro/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  9 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\User\\Controllers\\CompleteRegistrationController@complete',
    'regex' => '#^/completar-cadastro/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  10 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\HomeController@index',
    'regex' => '#^/?$#',
    'middleware' => 
    array (
    ),
  ),
  11 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\HomeController@index',
    'regex' => '#^/home/?$#',
    'middleware' => 
    array (
    ),
  ),
  12 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\ProductController@show',
    'regex' => '#^/produto/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
    ),
  ),
  13 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\CatalogController@index',
    'regex' => '#^/catalogo/?$#',
    'middleware' => 
    array (
    ),
  ),
  14 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminController@dashboard',
    'regex' => '#^/admin/dashboard/?$#',
    'middleware' => 
    array (
    ),
  ),
  15 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminController@teamList',
    'regex' => '#^/admin/team-list/?$#',
    'middleware' => 
    array (
    ),
  ),
  16 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminController@carousel',
    'regex' => '#^/admin/carousel/?$#',
    'middleware' => 
    array (
    ),
  ),
  17 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\User\\Controllers\\AdminController@addSlide',
    'regex' => '#^/admin/carousel/add/?$#',
    'middleware' => 
    array (
    ),
  ),
  18 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\User\\Controllers\\AdminController@deleteSlide',
    'regex' => '#^/admin/carousel/delete/?$#',
    'middleware' => 
    array (
    ),
  ),
  19 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminController@news',
    'regex' => '#^/admin/news/?$#',
    'middleware' => 
    array (
    ),
  ),
  20 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminController@bestsellers',
    'regex' => '#^/admin/bestsellers/?$#',
    'middleware' => 
    array (
    ),
  ),
  21 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminController@settings',
    'regex' => '#^/admin/settings/?$#',
    'middleware' => 
    array (
    ),
  ),
  22 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminProductController@index',
    'regex' => '#^/admin/products/?$#',
    'middleware' => 
    array (
    ),
  ),
  23 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminProductController@create',
    'regex' => '#^/admin/products/create/?$#',
    'middleware' => 
    array (
    ),
  ),
  24 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\User\\Controllers\\AdminProductController@store',
    'regex' => '#^/admin/products/store/?$#',
    'middleware' => 
    array (
    ),
  ),
  25 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminProductController@edit',
    'regex' => '#^/admin/products/edit/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
    ),
  ),
  26 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\User\\Controllers\\AdminProductController@update',
    'regex' => '#^/admin/products/update/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
    ),
  ),
  27 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminProductController@delete',
    'regex' => '#^/admin/products/delete/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
    ),
  ),
  28 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminCategoryController@index',
    'regex' => '#^/admin/categories/?$#',
    'middleware' => 
    array (
    ),
  ),
  29 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminCategoryController@create',
    'regex' => '#^/admin/categories/create/?$#',
    'middleware' => 
    array (
    ),
  ),
  30 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\User\\Controllers\\AdminCategoryController@store',
    'regex' => '#^/admin/categories/store/?$#',
    'middleware' => 
    array (
    ),
  ),
  31 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminCategoryController@edit',
    'regex' => '#^/admin/categories/edit/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
    ),
  ),
  32 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\User\\Controllers\\AdminCategoryController@update',
    'regex' => '#^/admin/categories/update/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
    ),
  ),
  33 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminCategoryController@delete',
    'regex' => '#^/admin/categories/delete/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
    ),
  ),
  34 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminSubCategoryController@index',
    'regex' => '#^/admin/subcategories/?$#',
    'middleware' => 
    array (
    ),
  ),
  35 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminSubCategoryController@create',
    'regex' => '#^/admin/subcategories/create/?$#',
    'middleware' => 
    array (
    ),
  ),
  36 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\User\\Controllers\\AdminSubCategoryController@store',
    'regex' => '#^/admin/subcategories/store/?$#',
    'middleware' => 
    array (
    ),
  ),
  37 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminSubCategoryController@edit',
    'regex' => '#^/admin/subcategories/edit/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
    ),
  ),
  38 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\User\\Controllers\\AdminSubCategoryController@update',
    'regex' => '#^/admin/subcategories/update/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
    ),
  ),
  39 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminSubCategoryController@delete',
    'regex' => '#^/admin/subcategories/delete/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
    ),
  ),
  40 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminSubCategoryController@getByCategory',
    'regex' => '#^/admin/subcategories/by-category/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
    ),
  ),
  41 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminCustomerController@index',
    'regex' => '#^/admin/customers/?$#',
    'middleware' => 
    array (
    ),
  ),
  42 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminCustomerController@search',
    'regex' => '#^/admin/customers/search/?$#',
    'middleware' => 
    array (
    ),
  ),
  43 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminOrderController@index',
    'regex' => '#^/admin/orders/?$#',
    'middleware' => 
    array (
    ),
  ),
  44 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminOrderController@show',
    'regex' => '#^/admin/orders/show/(?P<id>[^/]+)/?$#',
    'middleware' => 
    array (
    ),
  ),
  45 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\User\\Controllers\\AdminOrderController@updateStatus',
    'regex' => '#^/admin/orders/update-status/?$#',
    'middleware' => 
    array (
    ),
  ),
  46 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminLogController@index',
    'regex' => '#^/admin/gerenciar-logs/?$#',
    'middleware' => 
    array (
    ),
  ),
  47 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\PromoteController@index',
    'regex' => '#^/admin/promotions/?$#',
    'middleware' => 
    array (
    ),
  ),
  48 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\User\\Controllers\\PromoteController@search',
    'regex' => '#^/admin/promotions/search/?$#',
    'middleware' => 
    array (
    ),
  ),
  49 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\User\\Controllers\\PromoteController@promote',
    'regex' => '#^/admin/promotions/promote/?$#',
    'middleware' => 
    array (
    ),
  ),
  50 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminReviewController@index',
    'regex' => '#^/admin/reviews/?$#',
    'middleware' => 
    array (
    ),
  ),
  51 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminReportController@index',
    'regex' => '#^/admin/reports/?$#',
    'middleware' => 
    array (
    ),
  ),
  52 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\User\\Controllers\\AdminController@dashboard',
    'regex' => '#^/admin/?$#',
    'middleware' => 
    array (
    ),
  ),
  53 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\TabelaOrdersController@index',
    'regex' => '#^/admin/tabela-order/?$#',
    'middleware' => 
    array (
    ),
  ),
  54 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\GerenciarProdutoController@index',
    'regex' => '#^/admin/registrar-produto/?$#',
    'middleware' => 
    array (
    ),
  ),
  55 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Pages\\Controllers\\GerenciarProdutoController@add',
    'regex' => '#^/admin/new/produto/?$#',
    'middleware' => 
    array (
    ),
  ),
);