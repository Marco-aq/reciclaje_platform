<?php

namespace App\Core;

/**
 * Clase Router - Manejo de rutas de la aplicación
 * 
 * Maneja el enrutamiento de requests HTTP a los controladores
 * correspondientes usando el patrón MVC.
 */
class Router
{
    private array $routes = [];
    private string $defaultController = 'HomeController';
    private string $defaultMethod = 'index';

    /**
     * Registra una ruta GET
     * 
     * @param string $path
     * @param string $handler
     */
    public function get(string $path, string $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Registra una ruta POST
     * 
     * @param string $path
     * @param string $handler
     */
    public function post(string $path, string $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Registra una ruta PUT
     * 
     * @param string $path
     * @param string $handler
     */
    public function put(string $path, string $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Registra una ruta DELETE
     * 
     * @param string $path
     * @param string $handler
     */
    public function delete(string $path, string $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Agrega una ruta al enrutador
     * 
     * @param string $method
     * @param string $path
     * @param string $handler
     */
    private function addRoute(string $method, string $path, string $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    /**
     * Resuelve la ruta actual y ejecuta el controlador correspondiente
     */
    public function resolve(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = $this->getCleanUri();

        // Intentar encontrar una ruta exacta
        if (isset($this->routes[$requestMethod][$requestUri])) {
            $this->executeHandler($this->routes[$requestMethod][$requestUri]);
            return;
        }

        // Intentar encontrar una ruta con parámetros
        foreach ($this->routes[$requestMethod] ?? [] as $route => $handler) {
            if ($this->matchRoute($route, $requestUri)) {
                $this->executeHandler($handler, $this->extractParams($route, $requestUri));
                return;
            }
        }

        // Si no se encuentra la ruta, usar el routing por defecto
        $this->handleDefaultRouting();
    }

    /**
     * Obtiene la URI limpia sin query string
     * 
     * @return string
     */
    private function getCleanUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Remover query string
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        // Limpiar slashes
        return '/' . trim($uri, '/');
    }

    /**
     * Verifica si una ruta coincide con el patrón
     * 
     * @param string $route
     * @param string $uri
     * @return bool
     */
    private function matchRoute(string $route, string $uri): bool
    {
        $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';
        
        return (bool) preg_match($pattern, $uri);
    }

    /**
     * Extrae parámetros de la URI
     * 
     * @param string $route
     * @param string $uri
     * @return array
     */
    private function extractParams(string $route, string $uri): array
    {
        $pattern = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $uri, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return [];
    }

    /**
     * Ejecuta el manejador de la ruta
     * 
     * @param string $handler
     * @param array $params
     */
    private function executeHandler(string $handler, array $params = []): void
    {
        if (strpos($handler, '@') !== false) {
            list($controller, $method) = explode('@', $handler);
        } else {
            $controller = $handler;
            $method = $this->defaultMethod;
        }

        $this->callControllerMethod($controller, $method, $params);
    }

    /**
     * Maneja el enrutamiento por defecto basado en la URL
     */
    private function handleDefaultRouting(): void
    {
        $uri = $this->getCleanUri();
        $segments = array_filter(explode('/', $uri));

        $controller = $this->defaultController;
        $method = $this->defaultMethod;
        $params = [];

        if (!empty($segments)) {
            $controller = ucfirst(array_shift($segments)) . 'Controller';
            
            if (!empty($segments)) {
                $method = array_shift($segments);
            }

            $params = $segments;
        }

        $this->callControllerMethod($controller, $method, $params);
    }

    /**
     * Llama al método del controlador
     * 
     * @param string $controllerName
     * @param string $methodName
     * @param array $params
     */
    private function callControllerMethod(string $controllerName, string $methodName, array $params = []): void
    {
        $controllerClass = "App\\Controllers\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            $this->notFound("Controlador {$controllerName} no encontrado");
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $methodName)) {
            $this->notFound("Método {$methodName} no encontrado en {$controllerName}");
            return;
        }

        try {
            call_user_func_array([$controller, $methodName], $params);
        } catch (\Exception $e) {
            $this->error("Error al ejecutar {$controllerName}@{$methodName}: " . $e->getMessage());
        }
    }

    /**
     * Maneja errores 404
     * 
     * @param string $message
     */
    private function notFound(string $message = "Página no encontrada"): void
    {
        http_response_code(404);
        
        if (Config::app('debug')) {
            echo "<h1>Error 404</h1><p>{$message}</p>";
        } else {
            // En producción, mostrar una página de error personalizada
            $this->callControllerMethod('ErrorController', 'notFound');
        }
    }

    /**
     * Maneja errores 500
     * 
     * @param string $message
     */
    private function error(string $message = "Error interno del servidor"): void
    {
        http_response_code(500);
        error_log($message);
        
        if (Config::app('debug')) {
            echo "<h1>Error 500</h1><p>{$message}</p>";
        } else {
            // En producción, mostrar una página de error personalizada
            $this->callControllerMethod('ErrorController', 'serverError');
        }
    }

    /**
     * Define rutas de la aplicación
     */
    public function defineRoutes(): void
    {
        // Rutas principales
        $this->get('/', 'HomeController@index');
        $this->get('/home', 'HomeController@index');
        
        // Rutas de autenticación
        $this->get('/login', 'AuthController@showLogin');
        $this->post('/login', 'AuthController@login');
        $this->get('/register', 'AuthController@showRegister');
        $this->post('/register', 'AuthController@register');
        $this->post('/logout', 'AuthController@logout');
        
        // Rutas de reportes
        $this->get('/reportes', 'ReportsController@index');
        $this->post('/reportes', 'ReportsController@store');
        $this->get('/reportes/{id}', 'ReportsController@show');
        
        // Rutas de estadísticas
        $this->get('/estadisticas', 'StatisticsController@index');
        $this->get('/api/estadisticas', 'StatisticsController@apiData');
        
        // Rutas del dashboard
        $this->get('/dashboard', 'DashboardController@index');
    }
}
