<?php

namespace FreelanceHub\Core;

/**
 * Router - Gestione routing HTTP
 */
class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private string $basePath = '';

    public function setBasePath(string $basePath): void
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function addMiddleware(callable $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function get(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    public function put(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    public function delete(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    public function group(string $prefix, callable $callback, array $middlewares = []): void
    {
        $previousBasePath = $this->basePath;
        $this->basePath .= $prefix;
        
        $previousMiddlewares = $this->middlewares;
        $this->middlewares = array_merge($this->middlewares, $middlewares);
        
        $callback($this);
        
        $this->basePath = $previousBasePath;
        $this->middlewares = $previousMiddlewares;
    }

    private function addRoute(string $method, string $path, $handler, array $middlewares): void
    {
        $fullPath = $this->basePath . $path;
        $pattern = $this->pathToRegex($fullPath);
        
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => array_merge($this->middlewares, $middlewares),
        ];
    }

    private function pathToRegex(string $path): string
    {
        // Converti {param} in gruppi regex nominati
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                // Estrai parametri dalla URL
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $request->setParams($params);

                // Esegui middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $result = $middleware($request);
                    if ($result instanceof Response) {
                        return $result;
                    }
                }

                // Esegui handler
                return $this->executeHandler($route['handler'], $request);
            }
        }

        // 404 Not Found
        return new Response(['error' => 'Not Found'], 404);
    }

    private function executeHandler($handler, Request $request): Response
    {
        if (is_callable($handler)) {
            return $handler($request);
        }

        if (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $method] = $handler;
            $controller = new $controllerClass();
            return $controller->$method($request);
        }

//        if (is_string($handler) && str_contains($handler, '@')) {
//            [$controllerClass, $method] = explode('@', $handler);
//            $controllerClass = "FreelanceHub\\Controllers\\{$controllerClass}";
//            $controller = new $controllerClass();
//            return $controller->$method($request);
//        }

        if (is_string($handler) && str_contains($handler, '@')) {
            [$controllerClass, $method] = explode('@', $handler);
            $controllerClass = "FreelanceHub\\Controllers\\{$controllerClass}";
            $controller = new $controllerClass();

            $params = array_values($request->getParams());
            return $controller->$method($request, ...$params);
        }

        throw new \RuntimeException('Invalid route handler');
    }
}
