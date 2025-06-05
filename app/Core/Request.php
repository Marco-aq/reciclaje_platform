<?php

namespace App\Core;

/**
 * Clase Request - Manejo de requests HTTP
 * 
 * Proporciona una interfaz simple para acceder a los datos
 * de la petición HTTP de forma segura y consistente.
 */
class Request
{
    private array $data;
    private array $files;

    public function __construct()
    {
        $this->data = $this->sanitizeInput(array_merge($_GET, $_POST));
        $this->files = $_FILES;
    }

    /**
     * Obtiene un valor del request
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Obtiene todos los datos del request
     * 
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Obtiene solo los campos especificados
     * 
     * @param array $keys
     * @return array
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->data, array_flip($keys));
    }

    /**
     * Obtiene todos los campos excepto los especificados
     * 
     * @param array $keys
     * @return array
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->data, array_flip($keys));
    }

    /**
     * Verifica si existe una clave en el request
     * 
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Verifica si una clave existe y no está vacía
     * 
     * @param string $key
     * @return bool
     */
    public function filled(string $key): bool
    {
        return $this->has($key) && !empty($this->data[$key]);
    }

    /**
     * Obtiene el método HTTP
     * 
     * @return string
     */
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Verifica si el método es GET
     * 
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    /**
     * Verifica si el método es POST
     * 
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    /**
     * Verifica si el método es PUT
     * 
     * @return bool
     */
    public function isPut(): bool
    {
        return $this->method() === 'PUT';
    }

    /**
     * Verifica si el método es DELETE
     * 
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->method() === 'DELETE';
    }

    /**
     * Obtiene la URL actual
     * 
     * @return string
     */
    public function url(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        
        return "{$protocol}://{$host}{$uri}";
    }

    /**
     * Obtiene la URI sin query string
     * 
     * @return string
     */
    public function uri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        
        return $uri;
    }

    /**
     * Obtiene la dirección IP del cliente
     * 
     * @return string
     */
    public function ip(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if ($key === 'HTTP_X_FORWARDED_FOR') {
                    $ip = explode(',', $ip)[0];
                }
                return trim($ip);
            }
        }
        
        return '0.0.0.0';
    }

    /**
     * Obtiene el user agent
     * 
     * @return string
     */
    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * Verifica si la petición es AJAX
     * 
     * @return bool
     */
    public function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Obtiene un archivo subido
     * 
     * @param string $key
     * @return array|null
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Verifica si se subió un archivo
     * 
     * @param string $key
     * @return bool
     */
    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && 
               $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    /**
     * Obtiene todos los archivos subidos
     * 
     * @return array
     */
    public function files(): array
    {
        return $this->files;
    }

    /**
     * Sanitiza los datos de entrada
     * 
     * @param array $data
     * @return array
     */
    private function sanitizeInput(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } else {
                $sanitized[$key] = trim($value);
            }
        }
        
        return $sanitized;
    }

    /**
     * Obtiene headers de la petición
     * 
     * @param string|null $key
     * @return array|string|null
     */
    public function headers(?string $key = null)
    {
        $headers = [];
        
        foreach ($_SERVER as $serverKey => $value) {
            if (strpos($serverKey, 'HTTP_') === 0) {
                $headerKey = str_replace('_', '-', substr($serverKey, 5));
                $headers[strtolower($headerKey)] = $value;
            }
        }
        
        if ($key !== null) {
            return $headers[strtolower($key)] ?? null;
        }
        
        return $headers;
    }

    /**
     * Guarda los datos actuales como "old input" en la sesión
     * 
     * @return void
     */
    public function saveOldInput(): void
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $_SESSION['old_input'] = $this->data;
    }
}
