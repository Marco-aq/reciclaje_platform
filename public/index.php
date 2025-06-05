<?php

/**
 * EcoCusco - Plataforma de Gestión de Residuos Sólidos Urbanos
 * 
 * Punto de entrada principal de la aplicación.
 * Maneja la configuración inicial, autoloading y enrutamiento.
 */

// Configuración de la zona horaria
date_default_timezone_set('America/Lima');

// Configuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definir constantes de la aplicación
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', __DIR__);
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Autoloader simple para las clases de la aplicación
spl_autoload_register(function ($className) {
    // Convertir namespace a ruta de archivo
    $classFile = str_replace(['\\', 'App/'], ['/', ''], $className);
    $filePath = ROOT_PATH . '/app/' . $classFile . '.php';
    
    if (file_exists($filePath)) {
        require_once $filePath;
        return true;
    }
    
    return false;
});

// Cargar Composer autoloader si existe
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

// Inicializar la aplicación
try {
    // Cargar configuraciones
    \App\Core\Config::load();
    
    // Configurar manejo de errores
    if (!\App\Core\Config::app('debug', false)) {
        error_reporting(0);
        ini_set('display_errors', 0);
    }
    
    // Configurar sesiones
    if (!session_id()) {
        $sessionConfig = \App\Core\Config::app('session');
        
        session_set_cookie_params([
            'lifetime' => $sessionConfig['lifetime'] * 60,
            'path' => $sessionConfig['path'],
            'domain' => $sessionConfig['domain'],
            'secure' => $sessionConfig['secure'],
            'httponly' => $sessionConfig['httponly'],
        ]);
        
        session_name($sessionConfig['name']);
        session_start();
    }
    
    // Registrar bindings del factory
    \App\Core\Factory::registerDefaultBindings();
    
    // Crear y configurar el router
    $router = new \App\Core\Router();
    $router->defineRoutes();
    
    // Procesar la petición
    $router->resolve();
    
} catch (\Exception $e) {
    // Manejo de errores críticos
    error_log("Error crítico en la aplicación: " . $e->getMessage());
    
    if (\App\Core\Config::app('debug', false)) {
        echo "<h1>Error de Aplicación</h1>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
        echo "<h3>Stack Trace:</h3>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo "<h1>Error del Servidor</h1>";
        echo "<p>Ha ocurrido un error interno del servidor. Por favor, inténtelo más tarde.</p>";
    }
    
    exit(1);
}

// Limpiar buffer de salida si es necesario
if (ob_get_level()) {
    ob_end_flush();
}
