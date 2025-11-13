<?php
namespace Core;

use App\Helpers\CacheHelper;

class Router {
    private static array $prefixStack = [];
    private static array $routes = [];

    public function __construct(){
        $cacheFile = __DIR__ . '/cache/routes.php';

        if (!CacheHelper::isCached($cacheFile)) {
            $rotasCompiladas = self::compileRoutes();
            CacheHelper::setCache($cacheFile, $rotasCompiladas);
            self::$routes = $rotasCompiladas;
        } else {
            $cached = CacheHelper::getCache($cacheFile);
            self::$routes = is_array($cached) ? $cached : [];
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
                'regex'   => $regex
            ];
        }

        return $compiledRoutes;
    }

    public static function group(string $groupPrefix, callable $callback): void {
        array_push(self::$prefixStack, $groupPrefix);
        call_user_func($callback);
        array_pop(self::$prefixStack);
    }

    public static function addRoute(string $method, string $path, string $handler): void {
        $fullPrefix = implode('', self::$prefixStack);
        $fullPath = $fullPrefix . $path;

        self::$routes[] = [
            'method' => strtoupper($method),
            'path' => $fullPath,
            'handler' => $handler
        ];
    }

    public function dispatch(string $method, string $uri): mixed {
        foreach (self::$routes as $route) {
            if ($route['method'] === strtoupper($method) && preg_match($route['regex'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                list($controller, $action) = explode('@', $route['handler']);
                $controllerInstance = new $controller();
                return call_user_func_array([$controllerInstance, $action], $params);
            }
        }
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
        exit;
    }
}