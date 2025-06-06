<?php

namespace App\Core;

use Exception;

/**
 * Clase Router - Maneja el enrutamiento de la aplicación
 * Soporte para URLs amigables y parámetros dinámicos
 */
class Router
{
    private $routes = [];
    private $middlewares = [];
    private $currentRoute = null;

    /**
     * Registra una ruta GET
     */
    public function get($path, $action, $middleware = null)
    {
        $this->addRoute('GET', $path, $action, $middleware);
    }

    /**
     * Registra una ruta POST
     */
    public function post($path, $action, $middleware = null)
    {
        $this->addRoute('POST', $path, $action, $middleware);
    }

    /**
     * Registra una ruta PUT
     */
    public function put($path, $action, $middleware = null)
    {
        $this->addRoute('PUT', $path, $action, $middleware);
    }

    /**
     * Registra una ruta DELETE
     */
    public function delete($path, $action, $middleware = null)
    {
        $this->addRoute('DELETE', $path, $action, $middleware);
    }

    /**
     * Agrega una ruta al sistema
     */
    private function addRoute($method, $path, $action, $middleware = null)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->normalizePath($path),
            'action' => $action,
            'middleware' => $middleware,
            'pattern' => $this->createPattern($path)
        ];
    }

    /**
     * Normaliza el path de la ruta
     */
    private function normalizePath($path)
    {
        return '/' . trim($path, '/');
    }

    /**
     * Crea un patrón regex para la ruta
     */
    private function createPattern($path)
    {
        $path = $this->normalizePath($path);
        
        // Reemplazar parámetros con expresiones regulares
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        $pattern = str_replace('/', '\/', $pattern);
        
        return '/^' . $pattern . '$/';
    }

    /**
     * Resuelve la ruta actual
     */
    public function resolve()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getCurrentUri();

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches); // Remover la coincidencia completa
                
                $this->currentRoute = $route;
                
                // Ejecutar middleware si existe
                if ($route['middleware']) {
                    $this->executeMiddleware($route['middleware']);
                }
                
                return $this->executeAction($route['action'], $matches);
            }
        }

        // Si no se encuentra la ruta, mostrar 404
        return $this->handle404();
    }

    /**
     * Obtiene la URI actual
     */
    private function getCurrentUri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        
        // Remover query string
        if (strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        
        return $this->normalizePath($uri);
    }

    /**
     * Ejecuta la acción del controlador
     */
    private function executeAction($action, $params = [])
    {
        if (is_callable($action)) {
            // Si es una función callable
            return call_user_func_array($action, $params);
        }

        if (is_string($action)) {
            // Si es string, parsearlo como Controller@method
            $parts = explode('@', $action);
            
            if (count($parts) !== 2) {
                throw new Exception("Acción de ruta inválida: {$action}");
            }

            $controllerName = "App\\Controllers\\" . $parts[0];
            $methodName = $parts[1];

            if (!class_exists($controllerName)) {
                throw new Exception("Controlador no encontrado: {$controllerName}");
            }

            $controller = new $controllerName();

            if (!method_exists($controller, $methodName)) {
                throw new Exception("Método no encontrado: {$methodName} en {$controllerName}");
            }

            return call_user_func_array([$controller, $methodName], $params);
        }

        throw new Exception("Tipo de acción no soportado");
    }

    /**
     * Ejecuta middleware
     */
    private function executeMiddleware($middleware)
    {
        if (is_string($middleware)) {
            // Cargar middleware por nombre
            if (isset($this->middlewares[$middleware])) {
                $middlewareInstance = $this->middlewares[$middleware];
                if (method_exists($middlewareInstance, 'handle')) {
                    $middlewareInstance->handle();
                }
            }
        } elseif (is_callable($middleware)) {
            // Ejecutar middleware callable
            call_user_func($middleware);
        }
    }

    /**
     * Registra un middleware
     */
    public function middleware($name, $instance)
    {
        $this->middlewares[$name] = $instance;
    }

    /**
     * Maneja errores 404
     */
    private function handle404()
    {
        http_response_code(404);
        
        // Buscar vista 404 personalizada
        $errorView = APP_PATH . '/Views/errors/404.php';
        if (file_exists($errorView)) {
            include $errorView;
        } else {
            echo "<h1>404 - Página no encontrada</h1>";
            echo "<p>La página solicitada no existe.</p>";
            echo "<a href='/'>Volver al inicio</a>";
        }
    }

    /**
     * Genera URL para una ruta nombrada
     */
    public function url($name, $params = [])
    {
        // Este método se puede expandir para rutas nombradas
        return env('APP_URL', '') . $name;
    }

    /**
     * Redirecciona a una URL
     */
    public function redirect($url, $code = 302)
    {
        header("Location: {$url}", true, $code);
        exit;
    }

    /**
     * Obtiene la ruta actual
     */
    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }

    /**
     * Verifica si es una petición AJAX
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Obtiene todos los parámetros de la petición
     */
    public function getRequestData()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                return $_GET;
            case 'POST':
                return $_POST;
            case 'PUT':
            case 'DELETE':
                parse_str(file_get_contents('php://input'), $data);
                return $data;
            default:
                return [];
        }
    }
}
