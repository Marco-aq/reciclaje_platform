<?php
/**
 * EcoCusco - Cerrar Sesión
 */

// Incluir configuración
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Verificar si hay una sesión activa
if (isLoggedIn()) {
    $user = getCurrentUser();
    $userEmail = $user['email'] ?? 'usuario desconocido';
    
    // Registrar el evento de logout
    logEvent("Usuario {$userEmail} cerró sesión", 'info');
    
    // Limpiar datos de sesión
    $_SESSION = array();
    
    // Destruir la cookie de sesión si existe
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Limpiar cookie de "recordar usuario" si existe
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        
        // Opcional: limpiar token de la base de datos
        try {
            if ($user) {
                $db = Database::getInstance();
                $db->query("UPDATE usuarios SET remember_token = NULL WHERE id = ?", [$user['id']]);
            }
        } catch (Exception $e) {
            error_log("Error limpiando remember_token: " . $e->getMessage());
        }
    }
    
    // Destruir la sesión
    session_destroy();
    
    // Establecer mensaje de confirmación
    session_start();
    setFlashMessage('success', '¡Sesión cerrada exitosamente! Gracias por usar EcoCusco.');
}

// Redirigir a la página principal
redirect('../public/index.php');
?>
