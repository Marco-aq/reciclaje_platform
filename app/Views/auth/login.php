<?php
$additional_css = '
<style>
    .auth-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
    }
    
    .auth-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .auth-header {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 2rem;
        text-align: center;
    }
    
    .auth-body {
        padding: 2rem;
    }
    
    .form-floating {
        margin-bottom: 1rem;
    }
    
    .form-floating .form-control {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .form-floating .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    
    .btn-auth {
        border-radius: 10px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .divider {
        text-align: center;
        margin: 1.5rem 0;
        position: relative;
    }
    
    .divider::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background-color: #dee2e6;
    }
    
    .divider span {
        background-color: white;
        padding: 0 1rem;
        color: #6c757d;
        font-size: 0.875rem;
    }
    
    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: none;
        color: #6c757d;
        cursor: pointer;
        z-index: 10;
    }
    
    .auth-footer {
        background-color: #f8f9fa;
        padding: 1.5rem 2rem;
        text-align: center;
        border-top: 1px solid #dee2e6;
    }
    
    .error-list {
        background-color: #fff5f5;
        border: 1px solid #fed7d7;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .error-list ul {
        margin: 0;
        padding-left: 1.5rem;
        color: #c53030;
    }
    
    @media (max-width: 768px) {
        .auth-container {
            padding: 1rem;
        }
        
        .auth-header,
        .auth-body,
        .auth-footer {
            padding: 1.5rem;
        }
    }
</style>
';

$inline_js = '
    // Toggle password visibility
    function togglePassword() {
        const passwordInput = document.getElementById("password");
        const toggleIcon = document.getElementById("toggleIcon");
        
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            toggleIcon.classList.remove("fa-eye");
            toggleIcon.classList.add("fa-eye-slash");
        } else {
            passwordInput.type = "password";
            toggleIcon.classList.remove("fa-eye-slash");
            toggleIcon.classList.add("fa-eye");
        }
    }
    
    // Form validation
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("loginForm");
        
        form.addEventListener("submit", function(e) {
            let isValid = true;
            const email = document.getElementById("email");
            const password = document.getElementById("password");
            
            // Clear previous validation states
            [email, password].forEach(input => {
                input.classList.remove("is-invalid");
            });
            
            // Validate email
            if (!email.value || !email.value.includes("@")) {
                email.classList.add("is-invalid");
                isValid = false;
            }
            
            // Validate password
            if (!password.value || password.value.length < 6) {
                password.classList.add("is-invalid");
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                showAlert("Por favor corrige los errores en el formulario", "error");
            }
        });
        
        // Auto-focus on first empty field
        const email = document.getElementById("email");
        const password = document.getElementById("password");
        
        if (!email.value) {
            email.focus();
        } else if (!password.value) {
            password.focus();
        }
    });
';
?>

<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card">
                    <!-- Header -->
                    <div class="auth-header">
                        <h2 class="mb-2">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Bienvenido de Vuelta
                        </h2>
                        <p class="mb-0 opacity-75">
                            Inicia sesión para continuar con tu impacto ambiental
                        </p>
                    </div>
                    
                    <!-- Body -->
                    <div class="auth-body">
                        <!-- Mostrar errores de validación -->
                        <?php if ($this->hasErrors()): ?>
                            <div class="error-list">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                    <strong class="text-danger">Por favor corrige los siguientes errores:</strong>
                                </div>
                                <?php foreach ($this->errors() as $field => $fieldErrors): ?>
                                    <ul>
                                        <?php foreach ($fieldErrors as $error): ?>
                                            <li><?= $this->escape($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form id="loginForm" method="POST" action="<?= $this->url('/login') ?>">
                            <?= $this->csrfField() ?>
                            
                            <!-- Email -->
                            <div class="form-floating">
                                <input 
                                    type="email" 
                                    class="form-control <?= $this->hasErrors('email') ? 'is-invalid' : '' ?>" 
                                    id="email" 
                                    name="email" 
                                    placeholder="nombre@ejemplo.com"
                                    value="<?= $this->escape($this->old('email')) ?>"
                                    required
                                >
                                <label for="email">
                                    <i class="fas fa-envelope me-2"></i>
                                    Correo Electrónico
                                </label>
                                <?php if ($this->hasErrors('email')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->firstError('email') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Password -->
                            <div class="form-floating position-relative">
                                <input 
                                    type="password" 
                                    class="form-control <?= $this->hasErrors('password') ? 'is-invalid' : '' ?>" 
                                    id="password" 
                                    name="password" 
                                    placeholder="Contraseña"
                                    required
                                >
                                <label for="password">
                                    <i class="fas fa-lock me-2"></i>
                                    Contraseña
                                </label>
                                <button 
                                    type="button" 
                                    class="password-toggle" 
                                    onclick="togglePassword()"
                                    tabindex="-1"
                                >
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                                <?php if ($this->hasErrors('password')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->firstError('password') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Remember Me & Forgot Password -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        value="" 
                                        id="remember"
                                        name="remember"
                                    >
                                    <label class="form-check-label text-muted" for="remember">
                                        Recordarme
                                    </label>
                                </div>
                                <a href="<?= $this->url('/forgot-password') ?>" class="text-decoration-none">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>
                            
                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary btn-auth w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Iniciar Sesión
                            </button>
                        </form>
                        
                        <!-- Divider -->
                        <div class="divider">
                            <span>o</span>
                        </div>
                        
                        <!-- Demo Login (optional) -->
                        <?php if (env('APP_DEBUG', false)): ?>
                            <div class="text-center mb-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="fillDemoCredentials()">
                                    <i class="fas fa-user me-1"></i>
                                    Usar credenciales de demo
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Help Text -->
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Tu información está protegida y segura
                            </small>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="auth-footer">
                        <p class="mb-0">
                            ¿No tienes una cuenta?
                            <a href="<?= $this->url('/register') ?>" class="text-decoration-none fw-bold">
                                Regístrate aquí
                            </a>
                        </p>
                        
                        <div class="mt-2">
                            <a href="<?= $this->url('/') ?>" class="text-muted text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>
                                Volver al inicio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (env('APP_DEBUG', false)): ?>
<script>
    function fillDemoCredentials() {
        document.getElementById('email').value = 'demo@ejemplo.com';
        document.getElementById('password').value = 'demo123';
        showAlert('Credenciales de demo cargadas', 'info');
    }
</script>
<?php endif; ?>
