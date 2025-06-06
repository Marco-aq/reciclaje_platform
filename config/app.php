<?php
/**
 * Configuración General de la Aplicación
 */

return [
    'name' => env('APP_NAME', 'Plataforma de Reciclaje'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'key' => env('APP_KEY', 'default-key-change-in-production'),
    'salt' => env('SALT', 'default-salt'),
    
    'session' => [
        'lifetime' => env('SESSION_LIFETIME', 7200),
        'secure' => env('SESSION_SECURE', false),
        'http_only' => env('SESSION_HTTP_ONLY', true),
    ],
    
    'upload' => [
        'max_size' => env('UPLOAD_MAX_SIZE', 5242880), // 5MB
        'allowed_types' => explode(',', env('UPLOAD_ALLOWED_TYPES', 'jpg,jpeg,png,gif,pdf')),
        'path' => PUBLIC_PATH . '/uploads/',
    ],
    
    'security' => [
        'password_min_length' => 6,
        'session_regenerate' => true,
        'csrf_protection' => true,
    ],
    
    'pagination' => [
        'per_page' => 10,
        'max_per_page' => 100,
    ],
];
