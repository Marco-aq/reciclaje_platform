<?php
/**
 * Punto de Entrada Web - Plataforma de Reciclaje MVC
 * 
 * Este archivo maneja todas las peticiones web y redirige al bootstrap principal
 * Sirve como punto de entrada público para el servidor web
 */

// Configurar reporte de errores inicial
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

// Definir constantes de rutas
define('PUBLIC_PATH', __DIR__);
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('STORAGE_PATH', ROOT_PATH . '/storage');

// Verificar que el archivo principal existe
$mainIndex = ROOT_PATH . '/index.php';
if (!file_exists($mainIndex)) {
    die('Error: No se puede encontrar el archivo principal de la aplicación.');
}

// Incluir el bootstrap principal
require_once $mainIndex;
