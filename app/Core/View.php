<?php

namespace App\Core;

/**
 * Clase View - Motor de plantillas simple
 * 
 * Maneja el renderizado de vistas con un sistema de plantillas
 * simple y eficiente.
 */
class View
{
    private string $viewPath;
    private string $layoutPath;
    private array $globalData = [];
    private string $defaultLayout = 'layouts/main';

    public function __construct()
    {
        $this->viewPath = __DIR__ . '/../Views/';
        $this->layoutPath = $this->viewPath;
    }

    /**
     * Renderiza una vista
     * 
     * @param string $template
     * @param array $data
     * @param string|null $layout
     * @return void
     */
    public function render(string $template, array $data = [], ?string $layout = null): void
    {
        $layout = $layout ?? $this->defaultLayout;
        
        // Combinar datos globales con datos específicos de la vista
        $viewData = array_merge($this->globalData, $data);
        
        // Generar contenido de la vista
        $content = $this->renderView($template, $viewData);
        
        // Si no hay layout, mostrar contenido directamente
        if ($layout === null) {
            echo $content;
            return;
        }
        
        // Renderizar con layout
        $viewData['content'] = $content;
        echo $this->renderView($layout, $viewData);
    }

    /**
     * Renderiza una vista sin layout
     * 
     * @param string $template
     * @param array $data
     * @return string
     */
    public function renderPartial(string $template, array $data = []): string
    {
        return $this->renderView($template, array_merge($this->globalData, $data));
    }

    /**
     * Renderiza una vista y retorna el contenido
     * 
     * @param string $template
     * @param array $data
     * @return string
     */
    private function renderView(string $template, array $data = []): string
    {
        $viewFile = $this->viewPath . str_replace('.', '/', $template) . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("Vista no encontrada: {$template}");
        }
        
        // Extraer variables para la vista
        extract($data, EXTR_SKIP);
        
        ob_start();
        include $viewFile;
        return ob_get_clean();
    }

    /**
     * Establece datos globales disponibles para todas las vistas
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function share(string $key, $value): void
    {
        $this->globalData[$key] = $value;
    }

    /**
     * Establece múltiples datos globales
     * 
     * @param array $data
     * @return void
     */
    public function shareMultiple(array $data): void
    {
        $this->globalData = array_merge($this->globalData, $data);
    }

    /**
     * Establece el layout por defecto
     * 
     * @param string $layout
     * @return void
     */
    public function setDefaultLayout(string $layout): void
    {
        $this->defaultLayout = $layout;
    }

    /**
     * Incluye una vista parcial
     * 
     * @param string $partial
     * @param array $data
     * @return void
     */
    public function include(string $partial, array $data = []): void
    {
        echo $this->renderPartial($partial, $data);
    }

    /**
     * Escapa HTML de forma segura
     * 
     * @param string $string
     * @return string
     */
    public function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Alias para escape()
     * 
     * @param string $string
     * @return string
     */
    public function e(string $string): string
    {
        return $this->escape($string);
    }

    /**
     * Genera una URL absoluta
     * 
     * @param string $path
     * @return string
     */
    public function url(string $path = ''): string
    {
        $baseUrl = rtrim(Config::app('url', 'http://localhost'), '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Genera una URL para un asset
     * 
     * @param string $asset
     * @return string
     */
    public function asset(string $asset): string
    {
        return $this->url('assets/' . ltrim($asset, '/'));
    }

    /**
     * Obtiene un mensaje flash de la sesión
     * 
     * @param string $type
     * @return string|null
     */
    public function flash(string $type): ?string
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
     * Verifica si existe un mensaje flash
     * 
     * @param string $type
     * @return bool
     */
    public function hasFlash(string $type): bool
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        return isset($_SESSION['flash'][$type]);
    }

    /**
     * Genera un token CSRF
     * 
     * @return string
     */
    public function csrfToken(): string
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Genera un campo hidden con el token CSRF
     * 
     * @return string
     */
    public function csrfField(): string
    {
        $tokenName = Config::app('security.csrf_token_name', '_token');
        $token = $this->csrfToken();
        
        return "<input type=\"hidden\" name=\"{$tokenName}\" value=\"{$token}\">";
    }

    /**
     * Obtiene el valor anterior de un campo del formulario
     * 
     * @param string $field
     * @param string $default
     * @return string
     */
    public function old(string $field, string $default = ''): string
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $oldData = $_SESSION['old_input'] ?? [];
        return isset($oldData[$field]) ? $this->escape($oldData[$field]) : $default;
    }

    /**
     * Formatea una fecha
     * 
     * @param string|null $date
     * @param string $format
     * @return string
     */
    public function formatDate(?string $date, string $format = 'd/m/Y H:i'): string
    {
        if (!$date) {
            return '';
        }
        
        try {
            $dateTime = new \DateTime($date);
            return $dateTime->format($format);
        } catch (\Exception $e) {
            return $date;
        }
    }

    /**
     * Trunca un texto
     * 
     * @param string $text
     * @param int $length
     * @param string $suffix
     * @return string
     */
    public function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        
        return mb_substr($text, 0, $length) . $suffix;
    }
}
