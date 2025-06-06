<?php
/**
 * Bootstrap de la Aplicación MVC
 * Punto de entrada principal de la aplicación
 */

// Configurar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

// Definir constantes del sistema
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoloader manual (alternativa a composer)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = APP_PATH . '/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Cargar funciones de utilidad para el .env
function loadEnv($path = '.env') {
    if (!file_exists($path)) {
        throw new Exception("Archivo de configuración .env no encontrado en: " . $path);
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Saltar comentarios
        if (strpos($line, '#') === 0) {
            continue;
        }
        
        // Procesar variables de entorno
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remover comillas si existen
            $value = trim($value, '"\'');
            
            // Establecer la variable de entorno
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Función auxiliar para obtener variables de entorno
function env($key, $default = null) {
    $value = $_ENV[$key] ?? getenv($key);
    
    if ($value === false) {
        return $default;
    }
    
    // Convertir valores booleanos
    if (strtolower($value) === 'true') return true;
    if (strtolower($value) === 'false') return false;
    if (strtolower($value) === 'null') return null;
    
    return $value;
}

try {
    // Cargar variables de entorno
    loadEnv(ROOT_PATH . '/.env');
    
    // Incluir configuración de la aplicación
    require_once CONFIG_PATH . '/app.php';
    require_once CONFIG_PATH . '/database.php';
    
    // Inicializar y ejecutar la aplicación
    use App\Core\App;
    
    $app = new App();
    $app->run();
    
} catch (Exception $e) {
    // Manejo de errores robusto
    $error_message = "Error en la aplicación: " . $e->getMessage();
    error_log($error_message);
    
    if (env('APP_DEBUG', false)) {
        echo "<h1>Error de Aplicación</h1>";
        echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Archivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        echo "<h1>Lo sentimos, ha ocurrido un error</h1>";
        echo "<p>Por favor intenta más tarde o contacta al administrador.</p>";
    }
}
