<?php

namespace App\Core;

/**
 * Clase Config - Manejo centralizado de configuraciones
 * 
 * Permite cargar y acceder a las configuraciones de la aplicación
 * de forma centralizada y eficiente.
 */
class Config
{
    private static array $config = [];
    private static bool $loaded = false;

    /**
     * Carga todas las configuraciones de la aplicación
     */
    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        // Cargar variables de entorno
        self::loadEnvironment();

        // Cargar archivos de configuración
        $configPath = __DIR__ . '/../../config/';
        $configFiles = ['app', 'database'];

        foreach ($configFiles as $file) {
            $filepath = $configPath . $file . '.php';
            if (file_exists($filepath)) {
                self::$config[$file] = require $filepath;
            }
        }

        self::$loaded = true;
    }

    /**
     * Carga las variables de entorno desde el archivo .env
     */
    private static function loadEnvironment(): void
    {
        $envPath = __DIR__ . '/../../.env';
        
        if (!file_exists($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
            }
        }
    }

    /**
     * Obtiene un valor de configuración
     * 
     * @param string $key Clave en formato punto (ej: 'app.name', 'database.default')
     * @param mixed $default Valor por defecto si no existe la clave
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }

        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Establece un valor de configuración
     * 
     * @param string $key
     * @param mixed $value
     */
    public static function set(string $key, $value): void
    {
        if (!self::$loaded) {
            self::load();
        }

        $keys = explode('.', $key);
        $config = &self::$config;

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($config[$key]) || !is_array($config[$key])) {
                $config[$key] = [];
            }

            $config = &$config[$key];
        }

        $config[array_shift($keys)] = $value;
    }

    /**
     * Verifica si existe una clave de configuración
     * 
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        if (!self::$loaded) {
            self::load();
        }

        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtiene toda la configuración
     * 
     * @return array
     */
    public static function all(): array
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$config;
    }

    /**
     * Obtiene una configuración específica de la aplicación
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function app(string $key, $default = null)
    {
        return self::get("app.{$key}", $default);
    }

    /**
     * Obtiene una configuración específica de la base de datos
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function database(string $key, $default = null)
    {
        return self::get("database.{$key}", $default);
    }
}
