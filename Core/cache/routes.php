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
    'handler' => 'App\\User\\Controllers\\ForgotPasswordController@showForgotPasswordForm',
    'regex' => '#^/forgot-password/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\GuestMiddleware',
      1 => 'handle',
    ),
  ),
  7 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\User\\Controllers\\ForgotPasswordController@sendResetLinkEmail',
    'regex' => '#^/forgot-password/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\GuestMiddleware',
      1 => 'handle',
    ),
  ),
  8 => 
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
  9 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Cart\\CartController@index',
    'regex' => '#^/cart/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  10 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Cart\\CartController@add',
    'regex' => '#^/cart/add/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  11 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Cart\\CartController@remove',
    'regex' => '#^/cart/remove/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  12 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Cart\\CartController@update',
    'regex' => '#^/cart/update/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  13 => 
  array (
    'method' => 'POST',
    'handler' => 'App\\Cart\\CartController@clear',
    'regex' => '#^/cart/clear/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
  14 => 
  array (
    'method' => 'GET',
    'handler' => 'App\\Pages\\Controllers\\TeamController@index',
    'regex' => '#^/gerenciarfuncionarios/?$#',
    'middleware' => 
    array (
      0 => 'App\\User\\Middlewares\\AuthMiddleware',
      1 => 'handle',
    ),
  ),
);