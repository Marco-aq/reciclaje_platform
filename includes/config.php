<?php
/**
 * EcoCusco - Configuración Simple
 * 
 * Sistema de configuración simplificado que carga variables de entorno
 * y proporciona valores por defecto seguros.
 */

// Cargar variables de entorno
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Saltar comentarios
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remover comillas si existen
        if (preg_match('/^(["\'])(.*)\\1$/m', $value, $matches)) {
            $value = $matches[2];
        }
        
        // Solo asignar si no existe ya en $_ENV
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }
    }
    
    return true;
}

// Cargar archivo .env
$envPath = dirname(__DIR__) . '/.env';
loadEnv($envPath);

// Función helper para obtener variables de entorno con valores por defecto
function env($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

// Configuración de la aplicación
define('APP_NAME', env('APP_NAME', 'EcoCusco'));
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_DEBUG', env('APP_DEBUG', 'false') === 'true');
define('APP_URL', env('APP_URL', 'http://localhost'));

// Configuración de base de datos con fallbacks seguros
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_DATABASE', env('DB_DATABASE', 'reciclaje_platform'));
define('DB_USERNAME', env('DB_USERNAME', 'root'));
define('DB_PASSWORD', env('DB_PASSWORD', ''));

// Configuración de sesiones
define('SESSION_LIFETIME', (int)env('SESSION_LIFETIME', 120));
define('SESSION_NAME', env('SESSION_NAME', 'ecocusco_session'));

// Configuración de archivos
define('UPLOAD_MAX_SIZE', (int)env('UPLOAD_MAX_SIZE', 10485760));
define('ALLOWED_FILE_TYPES', env('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,pdf'));

// Configuración de seguridad
define('BCRYPT_ROUNDS', (int)env('BCRYPT_ROUNDS', 10));

// Configuración de zona horaria
date_default_timezone_set('America/Lima');

// Configuración de errores según el entorno
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Configurar sesiones si no están iniciadas
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME * 60,
        'path' => '/',
        'domain' => '',
        'secure' => false, // Cambiar a true en HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_name(SESSION_NAME);
    session_start();
}
?>
