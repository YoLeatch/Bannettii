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
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
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
);