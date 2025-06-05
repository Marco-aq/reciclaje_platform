<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de la Aplicación
    |--------------------------------------------------------------------------
    |
    | Esta configuración controla el comportamiento básico de la aplicación.
    |
    */
    
    'name' => $_ENV['APP_NAME'] ?? 'EcoCusco',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    
    /*
    |--------------------------------------------------------------------------
    | Configuración de Zona Horaria
    |--------------------------------------------------------------------------
    */
    
    'timezone' => 'America/Lima',
    
    /*
    |--------------------------------------------------------------------------
    | Configuración de Localización
    |--------------------------------------------------------------------------
    */
    
    'locale' => 'es',
    'charset' => 'UTF-8',
    
    /*
    |--------------------------------------------------------------------------
    | Configuración de Sesiones
    |--------------------------------------------------------------------------
    */
    
    'session' => [
        'lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 120),
        'driver' => $_ENV['SESSION_DRIVER'] ?? 'file',
        'name' => 'ecocusco_session',
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httponly' => true,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configuración de Archivos
    |--------------------------------------------------------------------------
    */
    
    'uploads' => [
        'max_size' => (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 10485760), // 10MB
        'allowed_types' => explode(',', $_ENV['ALLOWED_FILE_TYPES'] ?? 'jpg,jpeg,png,pdf'),
        'path' => 'uploads/',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configuración de Seguridad
    |--------------------------------------------------------------------------
    */
    
    'security' => [
        'csrf_token_name' => '_token',
        'password_min_length' => 8,
        'login_attempts' => 5,
        'lockout_duration' => 900, // 15 minutos
    ],
];
