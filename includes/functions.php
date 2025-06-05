<?php
/**
 * EcoCusco - Funciones Utilitarias
 * 
 * Conjunto de funciones helper para la aplicación
 */

/**
 * Verificar si el usuario está autenticado
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Obtener datos del usuario actual
 * 
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $db = Database::getInstance();
        return $db->findById('usuarios', $_SESSION['user_id']);
    } catch (Exception $e) {
        error_log("Error al obtener usuario actual: " . $e->getMessage());
        return null;
    }
}

/**
 * Redirigir a una URL
 * 
 * @param string $url
 * @param int $statusCode
 */
function redirect($url, $statusCode = 302) {
    header("Location: {$url}", true, $statusCode);
    exit();
}

/**
 * Validar email
 * 
 * @param string $email
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Limpiar y sanitizar entrada de usuario
 * 
 * @param string $input
 * @return string
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validar contraseña (mínimo 6 caracteres)
 * 
 * @param string $password
 * @return bool
 */
function isValidPassword($password) {
    return strlen($password) >= 6;
}

/**
 * Hash de contraseña
 * 
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verificar contraseña
 * 
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generar token CSRF
 * 
 * @return string
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 * 
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Mostrar mensajes flash
 * 
 * @param string $type (success, error, warning, info)
 * @param string $message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Obtener y limpiar mensajes flash
 * 
 * @return array
 */
function getFlashMessages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Requerir autenticación
 * Redirige al login si no está autenticado
 */
function requireAuth() {
    if (!isLoggedIn()) {
        setFlashMessage('warning', 'Debes iniciar sesión para acceder a esta página.');
        redirect('/login.php');
    }
}

/**
 * Requerir que NO esté autenticado
 * Redirige al dashboard si ya está autenticado
 */
function requireGuest() {
    if (isLoggedIn()) {
        redirect('/dashboard.php');
    }
}

/**
 * Formatear fecha para mostrar
 * 
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'd/m/Y H:i') {
    if (empty($date)) {
        return '';
    }
    
    try {
        $dateTime = new DateTime($date);
        return $dateTime->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Formatear número con separadores de miles
 * 
 * @param float $number
 * @param int $decimals
 * @return string
 */
function formatNumber($number, $decimals = 2) {
    return number_format($number, $decimals, '.', ',');
}

/**
 * Escapar HTML para prevenir XSS
 * 
 * @param string $string
 * @return string
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Incluir vista con datos
 * 
 * @param string $view
 * @param array $data
 */
function includeView($view, $data = []) {
    extract($data);
    $viewPath = __DIR__ . "/../{$view}";
    
    if (file_exists($viewPath)) {
        include $viewPath;
    } else {
        throw new Exception("Vista no encontrada: {$view}");
    }
}

/**
 * Obtener URL base de la aplicación
 * 
 * @return string
 */
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script = dirname($_SERVER['SCRIPT_NAME']);
    return rtrim($protocol . $host . $script, '/');
}

/**
 * Generar URL relativa
 * 
 * @param string $path
 * @return string
 */
function url($path = '') {
    return getBaseUrl() . '/' . ltrim($path, '/');
}

/**
 * Verificar si el usuario es administrador
 * 
 * @return bool
 */
function isAdmin() {
    $user = getCurrentUser();
    return $user && isset($user['rol']) && $user['rol'] === 'administrador';
}

/**
 * Requerir rol de administrador
 */
function requireAdmin() {
    requireAuth();
    
    if (!isAdmin()) {
        setFlashMessage('error', 'No tienes permisos para acceder a esta página.');
        redirect('/dashboard.php');
    }
}

/**
 * Log de eventos del sistema
 * 
 * @param string $message
 * @param string $level
 */
function logEvent($message, $level = 'info') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    $logFile = dirname(__DIR__) . '/logs/app.log';
    $logDir = dirname($logFile);
    
    // Crear directorio de logs si no existe
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}
?>
