<?php
/**
 * EcoCusco - Página de Registro
 */

// Incluir configuración
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Redirigir si ya está autenticado
requireGuest();

// Variables para el formulario
$formData = [
    'nombre' => '',
    'apellido' => '',
    'email' => '',
    'telefono' => '',
    'direccion' => ''
];
$errors = [];

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $formData['nombre'] = sanitizeInput($_POST['nombre'] ?? '');
    $formData['apellido'] = sanitizeInput($_POST['apellido'] ?? '');
    $formData['email'] = sanitizeInput($_POST['email'] ?? '');
    $formData['telefono'] = sanitizeInput($_POST['telefono'] ?? '');
    $formData['direccion'] = sanitizeInput($_POST['direccion'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Validar CSRF token
    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Token de seguridad inválido. Inténtalo nuevamente.';
    }
    
    // Validaciones
    if (empty($formData['nombre'])) {
        $errors[] = 'El nombre es requerido.';
    } elseif (strlen($formData['nombre']) < 2) {
        $errors[] = 'El nombre debe tener al menos 2 caracteres.';
    }
    
    if (empty($formData['apellido'])) {
        $errors[] = 'El apellido es requerido.';
    } elseif (strlen($formData['apellido']) < 2) {
        $errors[] = 'El apellido debe tener al menos 2 caracteres.';
    }
    
    if (empty($formData['email'])) {
        $errors[] = 'El email es requerido.';
    } elseif (!isValidEmail($formData['email'])) {
        $errors[] = 'El formato del email no es válido.';
    }
    
    if (empty($password)) {
        $errors[] = 'La contraseña es requerida.';
    } elseif (!isValidPassword($password)) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Las contraseñas no coinciden.';
    }
    
    if (!empty($formData['telefono']) && !preg_match('/^[+]?[0-9\s\-\(\)]+$/', $formData['telefono'])) {
        $errors[] = 'El formato del teléfono no es válido.';
    }
    
    if (!$terms) {
        $errors[] = 'Debes aceptar los términos y condiciones.';
    }
    
    // Verificar si el email ya existe
    if (empty($errors)) {
        try {
            $db = Database::getInstance();
            
            $existingUser = $db->fetchOne(
                "SELECT id FROM usuarios WHERE email = ?", 
                [$formData['email']]
            );
            
            if ($existingUser) {
                $errors[] = 'Ya existe una cuenta con este email.';
            }
            
        } catch (Exception $e) {
            error_log("Error verificando email existente: " . $e->getMessage());
            $errors[] = 'Error del sistema. Inténtalo más tarde.';
        }
    }
    
    // Crear usuario si no hay errores
    if (empty($errors)) {
        try {
            $db = Database::getInstance();
            
            // Preparar datos del usuario
            $userData = [
                'nombre' => $formData['nombre'],
                'apellido' => $formData['apellido'],
                'email' => $formData['email'],
                'password' => hashPassword($password),
                'telefono' => $formData['telefono'] ?: null,
                'direccion' => $formData['direccion'] ?: null,
                'rol' => 'usuario', // Rol por defecto
                'activo' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Insertar usuario
            $userId = $db->insert('usuarios', $userData);
            
            if ($userId) {
                // Registrar el evento
                logEvent("Nuevo usuario registrado: {$formData['email']} (ID: {$userId})", 'info');
                
                // Auto-login del usuario
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $formData['nombre'];
                $_SESSION['user_email'] = $formData['email'];
                $_SESSION['user_role'] = 'usuario';
                
                setFlashMessage('success', '¡Registro exitoso! Bienvenido a EcoCusco, ' . $formData['nombre'] . '!');
                redirect('../pages/dashboard.php');
                
            } else {
                $errors[] = 'Error al crear la cuenta. Inténtalo nuevamente.';
            }
            
        } catch (Exception $e) {
            error_log("Error en registro: " . $e->getMessage());
            $errors[] = 'Error del sistema. Inténtalo más tarde.';
        }
    }
}

// Configurar breadcrumbs
$breadcrumbs = [
    ['title' => 'Registro']
];

$pageTitle = 'Registro - ' . APP_NAME;
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
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <!-- Header -->
                        <div class="text-center mb-4">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-user-plus text-success fa-2x"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-2">Crear Cuenta</h2>
                            <p class="text-muted">Únete a la comunidad EcoCusco</p>
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
                        <form method="POST" action="" id="registerForm">
                            <!-- CSRF Token -->
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            
                            <!-- Nombre y Apellido -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label fw-medium">
                                        <i class="fas fa-user me-2 text-muted"></i>Nombre *
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="nombre" 
                                        name="nombre" 
                                        value="<?= e($formData['nombre']) ?>"
                                        placeholder="Tu nombre"
                                        required
                                        autocomplete="given-name"
                                        autofocus
                                    >
                                </div>
                                <div class="col-md-6">
                                    <label for="apellido" class="form-label fw-medium">
                                        <i class="fas fa-user me-2 text-muted"></i>Apellido *
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="apellido" 
                                        name="apellido" 
                                        value="<?= e($formData['apellido']) ?>"
                                        placeholder="Tu apellido"
                                        required
                                        autocomplete="family-name"
                                    >
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label fw-medium">
                                    <i class="fas fa-envelope me-2 text-muted"></i>Email *
                                </label>
                                <input 
                                    type="email" 
                                    class="form-control" 
                                    id="email" 
                                    name="email" 
                                    value="<?= e($formData['email']) ?>"
                                    placeholder="tu@email.com"
                                    required
                                    autocomplete="email"
                                >
                            </div>

                            <!-- Teléfono y Dirección -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="telefono" class="form-label fw-medium">
                                        <i class="fas fa-phone me-2 text-muted"></i>Teléfono
                                    </label>
                                    <input 
                                        type="tel" 
                                        class="form-control" 
                                        id="telefono" 
                                        name="telefono" 
                                        value="<?= e($formData['telefono']) ?>"
                                        placeholder="+51 984 123 456"
                                        autocomplete="tel"
                                    >
                                </div>
                                <div class="col-md-6">
                                    <label for="direccion" class="form-label fw-medium">
                                        <i class="fas fa-map-marker-alt me-2 text-muted"></i>Dirección
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="direccion" 
                                        name="direccion" 
                                        value="<?= e($formData['direccion']) ?>"
                                        placeholder="Tu dirección en Cusco"
                                        autocomplete="street-address"
                                    >
                                </div>
                            </div>

                            <!-- Contraseñas -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-medium">
                                        <i class="fas fa-lock me-2 text-muted"></i>Contraseña *
                                    </label>
                                    <div class="input-group">
                                        <input 
                                            type="password" 
                                            class="form-control" 
                                            id="password" 
                                            name="password" 
                                            placeholder="Mínimo 6 caracteres"
                                            required
                                            autocomplete="new-password"
                                        >
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label fw-medium">
                                        <i class="fas fa-lock me-2 text-muted"></i>Confirmar Contraseña *
                                    </label>
                                    <div class="input-group">
                                        <input 
                                            type="password" 
                                            class="form-control" 
                                            id="confirm_password" 
                                            name="confirm_password" 
                                            placeholder="Repite tu contraseña"
                                            required
                                            autocomplete="new-password"
                                        >
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Términos y condiciones -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    Acepto los 
                                    <a href="../pages/terminos.php" class="text-success text-decoration-none" target="_blank">
                                        Términos y Condiciones
                                    </a> 
                                    y la 
                                    <a href="../pages/privacidad.php" class="text-success text-decoration-none" target="_blank">
                                        Política de Privacidad
                                    </a> *
                                </label>
                            </div>

                            <!-- Botón de registro -->
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-success btn-lg" id="registerBtn">
                                    <i class="fas fa-user-plus me-2"></i>
                                    Crear Cuenta
                                </button>
                            </div>

                            <!-- Enlace a login -->
                            <div class="text-center">
                                <p class="text-muted mb-0">
                                    ¿Ya tienes una cuenta? 
                                    <a href="login.php" class="text-success text-decoration-none fw-medium">
                                        Inicia sesión aquí
                                    </a>
                                </p>
                            </div>
                        </form>
                    </div>
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
        function setupPasswordToggle(toggleId, inputId) {
            const toggle = document.getElementById(toggleId);
            const input = document.getElementById(inputId);
            
            if (toggle && input) {
                toggle.addEventListener('click', function() {
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    
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
        }
        
        setupPasswordToggle('togglePassword', 'password');
        setupPasswordToggle('toggleConfirmPassword', 'confirm_password');
        
        // Form validation
        const registerForm = document.getElementById('registerForm');
        const registerBtn = document.getElementById('registerBtn');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        // Real-time password confirmation validation
        if (confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                if (password.value && confirmPassword.value) {
                    if (password.value === confirmPassword.value) {
                        confirmPassword.setCustomValidity('');
                        confirmPassword.classList.remove('is-invalid');
                        confirmPassword.classList.add('is-valid');
                    } else {
                        confirmPassword.setCustomValidity('Las contraseñas no coinciden');
                        confirmPassword.classList.remove('is-valid');
                        confirmPassword.classList.add('is-invalid');
                    }
                }
            });
        }
        
        // Form submission
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                // Disable button to prevent double submission
                if (registerBtn) {
                    registerBtn.disabled = true;
                    registerBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creando cuenta...';
                }
                
                // Re-enable button after 5 seconds in case of error
                setTimeout(function() {
                    if (registerBtn) {
                        registerBtn.disabled = false;
                        registerBtn.innerHTML = '<i class="fas fa-user-plus me-2"></i>Crear Cuenta';
                    }
                }, 5000);
            });
        }
        
        // Email validation
        const emailInput = document.getElementById('email');
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                const email = this.value.trim();
                if (email && !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                    this.setCustomValidity('Ingresa un email válido');
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                    if (email) this.classList.add('is-valid');
                }
            });
        }
    });
    </script>
</body>
</html>
