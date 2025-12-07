<?php
namespace Core;

use Core\ViewerPlace;

class Router {
    private static array $prefixStack = [];
    private static array $routes = [];

    public function __construct(){
        $cacheDir = __DIR__ . '/cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $cacheFile = $cacheDir . '/routes.php';

        if (!file_exists($cacheFile)) {
            $rotasCompiladas = self::compileRoutes();
            file_put_contents($cacheFile, '<?php return ' . var_export($rotasCompiladas, true) . ';');
            self::$routes = $rotasCompiladas;
        } else {
            self::$routes = require $cacheFile;
        }
    }

    private static function compileRoutes(): array {
        $compiledRoutes = [];

        foreach (self::$routes as $route) {
            $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $route['path']);
            $regex = '#^' . rtrim($pattern, '/') . '/?$#';
            $compiledRoutes[] = [
                'method'  => $route['method'],
                'handler' => $route['handler'],
                'regex'   => $regex,
                'middleware' => $route['middleware'] ?? []
            ];
        }

        return $compiledRoutes;
    }

    public static function group(string $groupPrefix, callable $callback): void {
        array_push(self::$prefixStack, $groupPrefix);
        call_user_func($callback);
        array_pop(self::$prefixStack);
    }

    public static function addRoute(string $method, string $path, string $handler, array $middleware = []): void {
        $fullPrefix = implode('', self::$prefixStack);
        $fullPath = $fullPrefix . $path;

        self::$routes[] = [
            'method' => strtoupper($method),
            'path' => $fullPath,
            'handler' => $handler,
            'middleware' => $middleware
        ];
    }

    public function dispatch(string $method, string $uri): mixed {
        foreach (self::$routes as $route) {
            if ($route['method'] === strtoupper($method) && preg_match($route['regex'], $uri, $matches)) {
                
                if (!empty($route['middleware'])) {
                    $middleware = $route['middleware'];
                    // O middleware é um array [ClassName, methodName]
                    if (is_array($middleware) && count($middleware) === 2 && is_string($middleware[0]) && is_string($middleware[1])) {
                        call_user_func([$middleware[0], $middleware[1]]);
                    } elseif (is_callable($middleware)) {
                        call_user_func($middleware);
                    }
                }

                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                list($controller, $action) = explode('@', $route['handler']);
                $controllerInstance = new $controller();
                return call_user_func_array([$controllerInstance, $action], $params);
            }
        }
        header("HTTP/1.0 404 Not Found");
        echo ViewerPlace::render('error', [
            'error_code' => '404',
            'error_msg' => 'Página não encontrada',
            'error_mensage' => 'Página não encontrada. Por favor, <a href="/">clique aqui</a> para retornar à página inicial.'
        ]);
        exit;
    }
}