<?php
/**
 * EcoCusco - Página de Inicio de Sesión
 */

// Incluir configuración
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirigir si ya está autenticado
requireGuest();

// Variables para el formulario
$email = '';
$errors = [];

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Validar CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Token de seguridad inválido. Inténtalo nuevamente.';
    }
    
    // Validaciones
    if (empty($email)) {
        $errors[] = 'El email es requerido.';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'El formato del email no es válido.';
    }
    
    if (empty($password)) {
        $errors[] = 'La contraseña es requerida.';
    }
    
    // Intentar autenticación si no hay errores
    if (empty($errors)) {
        try {
            $db = Database::getInstance();
            
            // Buscar usuario por email
            $query = "SELECT id, nombre, apellido, email, password, rol, activo, created_at 
                     FROM usuarios 
                     WHERE email = ? AND activo = 1";
            $user = $db->fetchOne($query, [$email]);
            
            if ($user && verifyPassword($password, $user['password'])) {
                // Login exitoso
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['rol'];
                
                // Recordar usuario si se seleccionó la opción
                if ($remember) {
                    $token = bin2hex(random_bytes(16));
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                    
                    // Guardar token en la base de datos (opcional)
                    $db->query("UPDATE usuarios SET remember_token = ? WHERE id = ?", [$token, $user['id']]);
                }
                
                // Registrar el login
                logEvent("Usuario {$user['email']} inició sesión", 'info');
                
                // Actualizar último acceso
                $db->query("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?", [$user['id']]);
                
                setFlashMessage('success', '¡Bienvenido de vuelta, ' . $user['nombre'] . '!');
                
                // Redirigir al dashboard o página solicitada
                $redirect_url = $_GET['redirect'] ?? '../pages/dashboard.php';
                redirect($redirect_url);
                
            } else {
                $errors[] = 'Credenciales incorrectas. Verifica tu email y contraseña.';
                
                // Log del intento fallido
                logEvent("Intento de login fallido para email: {$email}", 'warning');
            }
            
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            $errors[] = 'Error del sistema. Inténtalo más tarde.';
        }
    }
}

// Configurar breadcrumbs
$breadcrumbs = [
    ['title' => 'Iniciar Sesión']
];

$pageTitle = 'Iniciar Sesión - ' . APP_NAME;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../public/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-sign-in-alt text-success fa-2x"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-2">Iniciar Sesión</h2>
                            <p class="text-muted">Accede a tu cuenta de EcoCusco</p>
                        </div>

                        <!-- Errores -->
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php if (count($errors) === 1): ?>
                                    <?= e($errors[0]) ?>
                                <?php else: ?>
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?= e($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario -->
                        <form method="POST" action="" id="loginForm">
                            <!-- CSRF Token -->
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            
                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-medium">
                                    <i class="fas fa-envelope me-2 text-muted"></i>Email
                                </label>
                                <input 
                                    type="email" 
                                    class="form-control form-control-lg" 
                                    id="email" 
                                    name="email" 
                                    value="<?= e($email) ?>"
                                    placeholder="tu@email.com"
                                    required
                                    autocomplete="email"
                                    autofocus
                                >
                            </div>

                            <!-- Contraseña -->
                            <div class="mb-3">
                                <label for="password" class="form-label fw-medium">
                                    <i class="fas fa-lock me-2 text-muted"></i>Contraseña
                                </label>
                                <div class="input-group">
                                    <input 
                                        type="password" 
                                        class="form-control form-control-lg" 
                                        id="password" 
                                        name="password" 
                                        placeholder="Tu contraseña"
                                        required
                                        autocomplete="current-password"
                                    >
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Recordar y recuperar contraseña -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">
                                        Recordarme
                                    </label>
                                </div>
                                <a href="recuperar-password.php" class="text-success text-decoration-none">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>

                            <!-- Botón de login -->
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-success btn-lg" id="loginBtn">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Iniciar Sesión
                                </button>
                            </div>

                            <!-- Enlace a registro -->
                            <div class="text-center">
                                <p class="text-muted mb-0">
                                    ¿No tienes una cuenta? 
                                    <a href="register.php" class="text-success text-decoration-none fw-medium">
                                        Regístrate aquí
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Información adicional -->
                <div class="text-center mt-4">
                    <p class="text-muted small">
                        Al iniciar sesión, aceptas nuestros 
                        <a href="../pages/terminos.php" class="text-success text-decoration-none">Términos de Uso</a> 
                        y 
                        <a href="../pages/privacidad.php" class="text-success text-decoration-none">Política de Privacidad</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                if (type === 'password') {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
        }
        
        // Form validation
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                // Disable button to prevent double submission
                if (loginBtn) {
                    loginBtn.disabled = true;
                    loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Iniciando sesión...';
                }
                
                // Re-enable button after 3 seconds in case of error
                setTimeout(function() {
                    if (loginBtn) {
                        loginBtn.disabled = false;
                        loginBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión';
                    }
                }, 3000);
            });
        }
        
        // Auto-focus on email field if empty
        const emailInput = document.getElementById('email');
        if (emailInput && !emailInput.value) {
            emailInput.focus();
        }
    });
    </script>
</body>
</html>
