<div class="auth-container">
    <div class="container-fluid vh-100">
        <div class="row h-100">
            <!-- Left Panel - Info -->
            <div class="col-lg-6 d-none d-lg-block position-relative">
                <div class="auth-bg-image h-100 d-flex align-items-center justify-content-center"
                     style="background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);">
                    <div class="text-center text-white p-5">
                        <div class="mb-4">
                            <i class="fas fa-leaf fa-4x mb-3 opacity-75"></i>
                        </div>
                        <h3 class="display-6 fw-bold mb-4">
                            Sé Parte del Cambio
                        </h3>
                        <p class="lead mb-4">
                            Únete a miles de ciudadanos comprometidos con el medio ambiente. 
                            Tu participación hace la diferencia en la construcción de un Cusco más verde.
                        </p>
                        
                        <div class="features-list text-start mt-5">
                            <div class="feature-item d-flex align-items-center mb-3">
                                <div class="feature-icon me-3">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <strong>Reportes Geolocalizados</strong>
                                    <br><small class="opacity-75">Ubicación exacta de problemas</small>
                                </div>
                            </div>
                            <div class="feature-item d-flex align-items-center mb-3">
                                <div class="feature-icon me-3">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <div>
                                    <strong>Seguimiento en Tiempo Real</strong>
                                    <br><small class="opacity-75">Monitorea el progreso de tus reportes</small>
                                </div>
                            </div>
                            <div class="feature-item d-flex align-items-center">
                                <div class="feature-icon me-3">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <strong>Comunidad Activa</strong>
                                    <br><small class="opacity-75">Colabora con otros ciudadanos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel - Register Form -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center bg-light">
                <div class="auth-form-container">
                    <div class="text-center mb-4">
                        <img src="<?= $this->asset('img/logo-eco.png') ?>" 
                             alt="EcoCusco Logo" 
                             class="auth-logo mb-3"
                             style="width: 60px; height: 60px;">
                        <h2 class="fw-bold text-success">Crear Cuenta</h2>
                        <p class="text-muted">Únete a la comunidad EcoCusco</p>
                    </div>

                    <!-- Validation Errors -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $field => $fieldErrors): ?>
                                    <?php foreach ($fieldErrors as $error): ?>
                                        <li><?= $this->escape($error) ?></li>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Register Form -->
                    <form method="POST" action="<?= $this->url('/register') ?>" class="auth-form" id="registerForm">
                        <?= $this->csrfField() ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="nombre" 
                                           name="nombre" 
                                           placeholder="Tu nombre"
                                           value="<?= $this->old('nombre') ?>"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="apellidos" class="form-label">Apellidos *</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="apellidos" 
                                           name="apellidos" 
                                           placeholder="Tus apellidos"
                                           value="<?= $this->old('apellidos') ?>"
                                           required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico *</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="tu@email.com"
                                       value="<?= $this->old('email') ?>"
                                       required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <input type="tel" 
                                       class="form-control" 
                                       id="telefono" 
                                       name="telefono" 
                                       placeholder="+51 999 999 999"
                                       value="<?= $this->old('telefono') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="direccion" 
                                       name="direccion" 
                                       placeholder="Tu dirección"
                                       value="<?= $this->old('direccion') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Contraseña *</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Mínimo 8 caracteres"
                                           required>
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength mt-1">
                                    <div class="progress" style="height: 3px;">
                                        <div class="progress-bar" role="progressbar"></div>
                                    </div>
                                    <small class="text-muted" id="passwordHelp"></small>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirmar Contraseña *</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           placeholder="Repetir contraseña"
                                           required>
                                </div>
                                <small class="text-muted" id="passwordMatch"></small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="terminos" 
                                       name="terminos"
                                       required>
                                <label class="form-check-label" for="terminos">
                                    Acepto los 
                                    <a href="<?= $this->url('/terminos') ?>" target="_blank" class="text-success">
                                        Términos y Condiciones
                                    </a> 
                                    y la 
                                    <a href="<?= $this->url('/privacidad') ?>" target="_blank" class="text-success">
                                        Política de Privacidad
                                    </a>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 mb-3" id="submitBtn">
                            <i class="fas fa-user-plus me-2"></i>Crear Cuenta
                        </button>

                        <div class="text-center">
                            <p class="mb-0">
                                ¿Ya tienes una cuenta? 
                                <a href="<?= $this->url('/login') ?>" class="text-success fw-bold text-decoration-none">
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

<style>
.auth-container {
    min-height: 100vh;
}

.auth-form-container {
    width: 100%;
    max-width: 500px;
    padding: 2rem;
    max-height: 100vh;
    overflow-y: auto;
}

.auth-logo {
    border-radius: 50%;
    box-shadow: 0 4px 12px rgba(46, 125, 50, 0.2);
}

.auth-form {
    margin-top: 1.5rem;
}

.input-group-text {
    background-color: #f8f9fa;
    border-right: none;
}

.form-control {
    border-left: none;
}

.form-control:focus {
    border-color: #2E7D32;
    box-shadow: 0 0 0 0.2rem rgba(46, 125, 50, 0.25);
}

.btn-success {
    background-color: #2E7D32;
    border-color: #2E7D32;
    padding: 12px;
}

.btn-success:hover {
    background-color: #1B5E20;
    border-color: #1B5E20;
}

.feature-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.password-strength .progress-bar {
    transition: all 0.3s ease;
}

.password-weak .progress-bar {
    background-color: #dc3545;
    width: 33%;
}

.password-medium .progress-bar {
    background-color: #ffc107;
    width: 66%;
}

.password-strong .progress-bar {
    background-color: #28a745;
    width: 100%;
}

@media (max-width: 991.98px) {
    .auth-form-container {
        max-width: 600px;
        padding: 1.5rem;
    }
}
</style>

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
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }
    
    // Password strength indicator
    const password = document.getElementById('password');
    const passwordStrength = document.querySelector('.password-strength');
    const passwordHelp = document.getElementById('passwordHelp');
    
    if (password && passwordStrength) {
        password.addEventListener('input', function() {
            const value = this.value;
            const strength = calculatePasswordStrength(value);
            
            passwordStrength.className = 'password-strength mt-1 ' + strength.class;
            passwordHelp.textContent = strength.text;
        });
    }
    
    // Password confirmation validation
    const passwordConfirmation = document.getElementById('password_confirmation');
    const passwordMatch = document.getElementById('passwordMatch');
    
    if (passwordConfirmation && passwordMatch) {
        passwordConfirmation.addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmation = this.value;
            
            if (confirmation === '') {
                passwordMatch.textContent = '';
                passwordMatch.className = 'text-muted';
            } else if (password === confirmation) {
                passwordMatch.textContent = '✓ Las contraseñas coinciden';
                passwordMatch.className = 'text-success';
            } else {
                passwordMatch.textContent = '✗ Las contraseñas no coinciden';
                passwordMatch.className = 'text-danger';
            }
        });
    }
    
    // Form validation
    const form = document.getElementById('registerForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creando cuenta...';
            submitBtn.disabled = true;
        });
    }
});

function calculatePasswordStrength(password) {
    let score = 0;
    let feedback = '';
    
    if (password.length >= 8) score++;
    if (password.match(/[a-z]/)) score++;
    if (password.match(/[A-Z]/)) score++;
    if (password.match(/[0-9]/)) score++;
    if (password.match(/[^a-zA-Z0-9]/)) score++;
    
    switch (score) {
        case 0:
        case 1:
        case 2:
            return { class: 'password-weak', text: 'Contraseña débil' };
        case 3:
        case 4:
            return { class: 'password-medium', text: 'Contraseña media' };
        case 5:
            return { class: 'password-strong', text: 'Contraseña fuerte' };
        default:
            return { class: '', text: '' };
    }
}

function validateForm() {
    const nombre = document.getElementById('nombre').value.trim();
    const apellidos = document.getElementById('apellidos').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const passwordConfirmation = document.getElementById('password_confirmation').value;
    const terminos = document.getElementById('terminos').checked;
    
    if (!nombre || !apellidos || !email || !password || !passwordConfirmation) {
        alert('Por favor, completa todos los campos obligatorios.');
        return false;
    }
    
    if (password !== passwordConfirmation) {
        alert('Las contraseñas no coinciden.');
        return false;
    }
    
    if (password.length < 8) {
        alert('La contraseña debe tener al menos 8 caracteres.');
        return false;
    }
    
    if (!terminos) {
        alert('Debes aceptar los términos y condiciones.');
        return false;
    }
    
    return true;
}
</script>
