<?php
$additional_css = '
<style>
    .auth-container {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        padding: 2rem 0;
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
    
    .password-strength {
        margin-top: 0.5rem;
    }
    
    .password-strength-bar {
        height: 4px;
        border-radius: 2px;
        background-color: #e9ecef;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }
    
    .password-strength-fill {
        height: 100%;
        border-radius: 2px;
        transition: all 0.3s ease;
        width: 0%;
    }
    
    .strength-weak { background-color: #dc3545; width: 33%; }
    .strength-medium { background-color: #ffc107; width: 66%; }
    .strength-strong { background-color: #28a745; width: 100%; }
    
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
    
    .terms-check {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .auth-container {
            padding: 1rem 0;
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
    function togglePassword(fieldId, iconId) {
        const passwordInput = document.getElementById(fieldId);
        const toggleIcon = document.getElementById(iconId);
        
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
    
    // Password strength checker
    function checkPasswordStrength(password) {
        let strength = 0;
        const strengthText = document.getElementById("strengthText");
        const strengthFill = document.getElementById("strengthFill");
        
        // Length check
        if (password.length >= 8) strength += 1;
        if (password.length >= 12) strength += 1;
        
        // Character variety checks
        if (/[a-z]/.test(password)) strength += 1;
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[0-9]/.test(password)) strength += 1;
        if (/[^A-Za-z0-9]/.test(password)) strength += 1;
        
        // Update visual indicator
        strengthFill.className = "password-strength-fill";
        
        if (strength <= 2) {
            strengthFill.classList.add("strength-weak");
            strengthText.textContent = "Débil";
            strengthText.className = "text-danger";
        } else if (strength <= 4) {
            strengthFill.classList.add("strength-medium");
            strengthText.textContent = "Media";
            strengthText.className = "text-warning";
        } else {
            strengthFill.classList.add("strength-strong");
            strengthText.textContent = "Fuerte";
            strengthText.className = "text-success";
        }
        
        return strength;
    }
    
    // Form validation
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("registerForm");
        const password = document.getElementById("password");
        const passwordConfirmation = document.getElementById("password_confirmation");
        
        // Password strength checking
        password.addEventListener("input", function() {
            checkPasswordStrength(this.value);
        });
        
        // Password confirmation matching
        function checkPasswordMatch() {
            if (passwordConfirmation.value && password.value !== passwordConfirmation.value) {
                passwordConfirmation.setCustomValidity("Las contraseñas no coinciden");
                passwordConfirmation.classList.add("is-invalid");
            } else {
                passwordConfirmation.setCustomValidity("");
                passwordConfirmation.classList.remove("is-invalid");
            }
        }
        
        password.addEventListener("input", checkPasswordMatch);
        passwordConfirmation.addEventListener("input", checkPasswordMatch);
        
        // Form submission
        form.addEventListener("submit", function(e) {
            let isValid = true;
            const nombre = document.getElementById("nombre");
            const email = document.getElementById("email");
            const terms = document.getElementById("terms");
            
            // Clear previous validation states
            [nombre, email, password, passwordConfirmation].forEach(input => {
                input.classList.remove("is-invalid");
            });
            
            // Validate name
            if (!nombre.value || nombre.value.length < 2) {
                nombre.classList.add("is-invalid");
                isValid = false;
            }
            
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
            
            // Validate password confirmation
            if (password.value !== passwordConfirmation.value) {
                passwordConfirmation.classList.add("is-invalid");
                isValid = false;
            }
            
            // Validate terms acceptance
            if (!terms.checked) {
                terms.classList.add("is-invalid");
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                showAlert("Por favor corrige los errores en el formulario", "error");
            }
        });
        
        // Auto-focus on first field
        document.getElementById("nombre").focus();
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
                            <i class="fas fa-user-plus me-2"></i>
                            Crear Cuenta
                        </h2>
                        <p class="mb-0 opacity-75">
                            Únete a nuestra comunidad de recicladores
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
                        
                        <form id="registerForm" method="POST" action="<?= $this->url('/register') ?>">
                            <?= $this->csrfField() ?>
                            
                            <!-- Nombre -->
                            <div class="form-floating">
                                <input 
                                    type="text" 
                                    class="form-control <?= $this->hasErrors('nombre') ? 'is-invalid' : '' ?>" 
                                    id="nombre" 
                                    name="nombre" 
                                    placeholder="Tu nombre completo"
                                    value="<?= $this->escape($this->old('nombre')) ?>"
                                    required
                                >
                                <label for="nombre">
                                    <i class="fas fa-user me-2"></i>
                                    Nombre Completo
                                </label>
                                <?php if ($this->hasErrors('nombre')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->firstError('nombre') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
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
                                    onclick="togglePassword(\'password\', \'toggleIcon1\')"
                                    tabindex="-1"
                                >
                                    <i class="fas fa-eye" id="toggleIcon1"></i>
                                </button>
                                <?php if ($this->hasErrors('password')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->firstError('password') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Password Strength Indicator -->
                            <div class="password-strength">
                                <div class="password-strength-bar">
                                    <div class="password-strength-fill" id="strengthFill"></div>
                                </div>
                                <small>
                                    Seguridad: <span id="strengthText" class="text-muted">-</span>
                                </small>
                            </div>
                            
                            <!-- Password Confirmation -->
                            <div class="form-floating position-relative">
                                <input 
                                    type="password" 
                                    class="form-control <?= $this->hasErrors('password_confirmation') ? 'is-invalid' : '' ?>" 
                                    id="password_confirmation" 
                                    name="password_confirmation" 
                                    placeholder="Confirmar contraseña"
                                    required
                                >
                                <label for="password_confirmation">
                                    <i class="fas fa-lock me-2"></i>
                                    Confirmar Contraseña
                                </label>
                                <button 
                                    type="button" 
                                    class="password-toggle" 
                                    onclick="togglePassword(\'password_confirmation\', \'toggleIcon2\')"
                                    tabindex="-1"
                                >
                                    <i class="fas fa-eye" id="toggleIcon2"></i>
                                </button>
                                <?php if ($this->hasErrors('password_confirmation')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->firstError('password_confirmation') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Terms and Conditions -->
                            <div class="terms-check">
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        value="" 
                                        id="terms"
                                        name="terms"
                                        required
                                    >
                                    <label class="form-check-label" for="terms">
                                        Acepto los 
                                        <a href="<?= $this->url('/terms') ?>" target="_blank" class="text-decoration-none">
                                            Términos y Condiciones
                                        </a>
                                        y la 
                                        <a href="<?= $this->url('/privacy') ?>" target="_blank" class="text-decoration-none">
                                            Política de Privacidad
                                        </a>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Newsletter Subscription -->
                            <div class="form-check mb-3">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    value="" 
                                    id="newsletter"
                                    name="newsletter"
                                    checked
                                >
                                <label class="form-check-label text-muted" for="newsletter">
                                    <small>
                                        Quiero recibir consejos sobre reciclaje y actualizaciones de la plataforma
                                    </small>
                                </label>
                            </div>
                            
                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary btn-auth w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>
                                Crear Mi Cuenta
                            </button>
                        </form>
                        
                        <!-- Help Text -->
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>
                                Tu información está protegida y nunca será compartida
                            </small>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="auth-footer">
                        <p class="mb-0">
                            ¿Ya tienes una cuenta?
                            <a href="<?= $this->url('/login') ?>" class="text-decoration-none fw-bold">
                                Inicia sesión aquí
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
