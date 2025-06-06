<?php

namespace App\Core;

use Exception;

/**
 * Clase View - Sistema de renderizado de vistas
 * Maneja templates, layouts y escape automático de datos
 */
class View
{
    private $viewsPath;
    private $layoutsPath;
    private $data = [];

    public function __construct()
    {
        $this->viewsPath = APP_PATH . '/Views';
        $this->layoutsPath = $this->viewsPath . '/layouts';
    }

    /**
     * Renderiza una vista
     */
    public function render($viewName, $data = [])
    {
        $this->data = array_merge($this->data, $data);
        
        $viewFile = $this->getViewFile($viewName);
        
        if (!file_exists($viewFile)) {
            throw new Exception("Vista no encontrada: {$viewName}");
        }

        // Extraer variables para la vista
        extract($this->data);
        
        // Capturar salida
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        
        return $content;
    }

    /**
     * Renderiza una vista con layout
     */
    public function renderWithLayout($viewName, $data = [], $layoutName = 'main')
    {
        // Renderizar contenido de la vista
        $content = $this->render($viewName, $data);
        
        // Renderizar layout con el contenido
        $layoutFile = $this->layoutsPath . '/' . $layoutName . '.php';
        
        if (!file_exists($layoutFile)) {
            throw new Exception("Layout no encontrado: {$layoutName}");
        }

        // Datos para el layout
        $layoutData = array_merge($this->data, $data, ['content' => $content]);
        extract($layoutData);
        
        ob_start();
        include $layoutFile;
        return ob_get_clean();
    }

    /**
     * Incluye una vista parcial
     */
    public function partial($partialName, $data = [])
    {
        $partialFile = $this->viewsPath . '/partials/' . $partialName . '.php';
        
        if (!file_exists($partialFile)) {
            throw new Exception("Parcial no encontrada: {$partialName}");
        }

        $partialData = array_merge($this->data, $data);
        extract($partialData);
        
        include $partialFile;
    }

    /**
     * Establece datos globales para todas las vistas
     */
    public function share($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
    }

    /**
     * Obtiene la ruta completa del archivo de vista
     */
    private function getViewFile($viewName)
    {
        // Convertir notación de puntos a rutas
        $path = str_replace('.', '/', $viewName);
        return $this->viewsPath . '/' . $path . '.php';
    }

    /**
     * Escapa HTML para prevenir XSS
     */
    public function escape($value)
    {
        if (is_null($value)) {
            return '';
        }
        
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Alias corto para escape
     */
    public function e($value)
    {
        return $this->escape($value);
    }

    /**
     * Muestra valor sin escapar (usar con cuidado)
     */
    public function raw($value)
    {
        return $value;
    }

    /**
     * Genera URL para assets
     */
    public function asset($path)
    {
        $baseUrl = env('APP_URL', '');
        return $baseUrl . '/public/assets/' . ltrim($path, '/');
    }

    /**
     * Genera URL para la aplicación
     */
    public function url($path = '')
    {
        $baseUrl = env('APP_URL', '');
        return $baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Obtiene el valor anterior de un campo (útil para formularios)
     */
    public function old($key, $default = '')
    {
        return $_SESSION['old'][$key] ?? $default;
    }

    /**
     * Guarda valores para la siguiente petición
     */
    public function setOld($data)
    {
        $_SESSION['old'] = $data;
    }

    /**
     * Limpia valores guardados
     */
    public function clearOld()
    {
        unset($_SESSION['old']);
    }

    /**
     * Obtiene errores de validación
     */
    public function errors($key = null)
    {
        if (!isset($_SESSION['errors'])) {
            return $key ? [] : [];
        }

        if ($key) {
            $errors = $_SESSION['errors'][$key] ?? [];
            unset($_SESSION['errors'][$key]);
            return $errors;
        }

        $allErrors = $_SESSION['errors'];
        unset($_SESSION['errors']);
        return $allErrors;
    }

    /**
     * Obtiene el primer error de un campo
     */
    public function firstError($key)
    {
        $errors = $this->errors($key);
        return !empty($errors) ? $errors[0] : '';
    }

    /**
     * Verifica si hay errores
     */
    public function hasErrors($key = null)
    {
        if (!isset($_SESSION['errors'])) {
            return false;
        }

        if ($key) {
            return isset($_SESSION['errors'][$key]) && !empty($_SESSION['errors'][$key]);
        }

        return !empty($_SESSION['errors']);
    }

    /**
     * Muestra mensajes flash
     */
    public function flash($type = null)
    {
        if (!isset($_SESSION['flash'])) {
            return $type ? '' : [];
        }

        if ($type) {
            $message = $_SESSION['flash'][$type] ?? '';
            unset($_SESSION['flash'][$type]);
            return $message;
        }

        $messages = $_SESSION['flash'];
        $_SESSION['flash'] = [];
        return $messages;
    }

    /**
     * Genera token CSRF
     */
    public function csrfToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Genera campo oculto para CSRF
     */
    public function csrfField()
    {
        return '<input type="hidden" name="_token" value="' . $this->csrfToken() . '">';
    }

    /**
     * Verifica si el usuario está autenticado
     */
    public function isAuth()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Obtiene el usuario autenticado
     */
    public function user()
    {
        return $_SESSION['user_data'] ?? null;
    }

    /**
     * Formatea fecha
     */
    public function formatDate($date, $format = 'd/m/Y H:i')
    {
        if (empty($date)) {
            return '';
        }
        
        $timestamp = is_numeric($date) ? $date : strtotime($date);
        return date($format, $timestamp);
    }

    /**
     * Formatea número
     */
    public function formatNumber($number, $decimals = 2)
    {
        return number_format($number, $decimals, '.', ',');
    }

    /**
     * Trunca texto
     */
    public function truncate($text, $length = 100, $suffix = '...')
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . $suffix;
    }

    /**
     * Convierte texto a mayúsculas
     */
    public function upper($text)
    {
        return strtoupper($text);
    }

    /**
     * Convierte texto a minúsculas
     */
    public function lower($text)
    {
        return strtolower($text);
    }

    /**
     * Capitaliza texto
     */
    public function title($text)
    {
        return ucwords(strtolower($text));
    }

    /**
     * Incluye archivos CSS
     */
    public function css($files)
    {
        if (!is_array($files)) {
            $files = [$files];
        }

        $output = '';
        foreach ($files as $file) {
            $url = $this->asset('css/' . $file . '.css');
            $output .= '<link rel="stylesheet" href="' . $url . '">' . "\n";
        }
        
        return $output;
    }

    /**
     * Incluye archivos JavaScript
     */
    public function js($files)
    {
        if (!is_array($files)) {
            $files = [$files];
        }

        $output = '';
        foreach ($files as $file) {
            $url = $this->asset('js/' . $file . '.js');
            $output .= '<script src="' . $url . '"></script>' . "\n";
        }
        
        return $output;
    }

    /**
     * Renderiza componente
     */
    public function component($name, $data = [])
    {
        $componentFile = $this->viewsPath . '/components/' . $name . '.php';
        
        if (!file_exists($componentFile)) {
            throw new Exception("Componente no encontrado: {$name}");
        }

        $componentData = array_merge($this->data, $data);
        extract($componentData);
        
        include $componentFile;
    }
}
