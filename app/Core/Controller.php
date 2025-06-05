<?php

namespace App\Core;

/**
 * Clase Controller base - Controlador base para todos los controladores
 * 
 * Proporciona funcionalidades comunes para todos los controladores
 * de la aplicación incluyendo renderizado de vistas, validación y redirección.
 */
abstract class Controller
{
    protected View $view;
    protected Request $request;
    protected array $data = [];

    public function __construct()
    {
        $this->view = new View();
        $this->request = new Request();
        $this->init();
    }

    /**
     * Método de inicialización que pueden sobrescribir los controladores hijos
     */
    protected function init(): void
    {
        // Método vacío que pueden sobrescribir los controladores hijos
    }

    /**
     * Renderiza una vista
     * 
     * @param string $template
     * @param array $data
     * @return void
     */
    protected function render(string $template, array $data = []): void
    {
        $this->view->render($template, array_merge($this->data, $data));
    }

    /**
     * Renderiza una vista como JSON
     * 
     * @param array $data
     * @param int $statusCode
     * @return void
     */
    protected function renderJson(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Redirige a otra URL
     * 
     * @param string $url
     * @param int $statusCode
     * @return void
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header("Location: {$url}");
        }
        exit;
    }

    /**
     * Redirige de vuelta a la página anterior
     * 
     * @return void
     */
    protected function redirectBack(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    /**
     * Establece un mensaje flash en la sesión
     * 
     * @param string $type
     * @param string $message
     * @return void
     */
    protected function setFlash(string $type, string $message): void
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Obtiene y elimina un mensaje flash de la sesión
     * 
     * @param string $type
     * @return string|null
     */
    protected function getFlash(string $type): ?string
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        
        return null;
    }

    /**
     * Valida los datos de entrada
     * 
     * @param array $data
     * @param array $rules
     * @return array
     */
    protected function validate(array $data, array $rules): array
    {
        $validator = new Validator();
        return $validator->validate($data, $rules);
    }

    /**
     * Verifica si el usuario está autenticado
     * 
     * @return bool
     */
    protected function isAuthenticated(): bool
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Requiere autenticación para acceder a la acción
     * 
     * @param string $redirectTo
     * @return void
     */
    protected function requireAuth(string $redirectTo = '/login'): void
    {
        if (!$this->isAuthenticated()) {
            $this->setFlash('warning', 'Debes iniciar sesión para acceder a esta página.');
            $this->redirect($redirectTo);
        }
    }

    /**
     * Obtiene el usuario actual
     * 
     * @return array|null
     */
    protected function getCurrentUser(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }
        
        $userModel = new \App\Models\User();
        return $userModel->findById($_SESSION['user_id']);
    }

    /**
     * Verifica si el método HTTP es el esperado
     * 
     * @param string $method
     * @return bool
     */
    protected function isMethod(string $method): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD']) === strtoupper($method);
    }

    /**
     * Verifica el token CSRF
     * 
     * @return bool
     */
    protected function verifyCsrfToken(): bool
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $tokenName = Config::app('security.csrf_token_name', '_token');
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $requestToken = $_POST[$tokenName] ?? $_GET[$tokenName] ?? '';
        
        return hash_equals($sessionToken, $requestToken);
    }

    /**
     * Genera un token CSRF
     * 
     * @return string
     */
    protected function generateCsrfToken(): string
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        
        return $token;
    }

    /**
     * Obtiene los errores de validación
     * 
     * @return array
     */
    protected function getValidationErrors(): array
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $errors = $_SESSION['validation_errors'] ?? [];
        unset($_SESSION['validation_errors']);
        
        return $errors;
    }

    /**
     * Establece errores de validación en la sesión
     * 
     * @param array $errors
     * @return void
     */
    protected function setValidationErrors(array $errors): void
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $_SESSION['validation_errors'] = $errors;
    }

    /**
     * Maneja errores y muestra página de error
     * 
     * @param \Exception $e
     * @param int $statusCode
     * @return void
     */
    protected function handleError(\Exception $e, int $statusCode = 500): void
    {
        http_response_code($statusCode);
        error_log($e->getMessage());
        
        if (Config::app('debug')) {
            $this->render('errors/debug', [
                'exception' => $e,
                'statusCode' => $statusCode
            ]);
        } else {
            $this->render('errors/500', [
                'message' => 'Ha ocurrido un error interno del servidor.'
            ]);
        }
    }
}
