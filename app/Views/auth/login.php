<div class="auth-container">
    <div class="container-fluid vh-100">
        <div class="row h-100">
            <!-- Left Panel - Login Form -->
            <div class="col-lg-6 d-flex align-items-center justify-content-center bg-light">
                <div class="auth-form-container">
                    <div class="text-center mb-4">
                        <img src="<?= $this->asset('img/logo-eco.png') ?>" 
                             alt="EcoCusco Logo" 
                             class="auth-logo mb-3"
                             style="width: 80px; height: 80px;">
                        <h2 class="fw-bold text-success">Bienvenido de nuevo</h2>
                        <p class="text-muted">Inicia sesión en tu cuenta de EcoCusco</p>
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

                    <!-- Login Form -->
                    <form method="POST" action="<?= $this->url('/login') ?>" class="auth-form">
                        <?= $this->csrfField() ?>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
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
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Tu contraseña"
                                       required>
                                <button class="btn btn-outline-secondary" 
                                        type="button" 
                                        id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="remember" 
                                           name="remember">
                                    <label class="form-check-label" for="remember">
                                        Recordarme
                                    </label>
                                </div>
                            </div>
                            <div class="col-6 text-end">
                                <a href="<?= $this->url('/forgot-password') ?>" 
                                   class="text-success text-decoration-none">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                        </button>

                        <div class="text-center">
                            <p class="mb-0">
                                ¿No tienes una cuenta? 
                                <a href="<?= $this->url('/register') ?>" class="text-success fw-bold text-decoration-none">
                                    Regístrate aquí
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Panel - Image/Info -->
            <div class="col-lg-6 d-none d-lg-block position-relative">
                <div class="auth-bg-image h-100 d-flex align-items-center justify-content-center"
                     style="background: linear-gradient(135deg, #2E7D32 0%, #4CAF50 100%);">
                    <div class="text-center text-white p-5">
                        <h3 class="display-6 fw-bold mb-4">
                            Únete a la Revolución Verde
                        </h3>
                        <p class="lead mb-4">
                            Ayuda a construir un Cusco más limpio y sostenible. 
                            Reporta, gestiona y haz seguimiento de los residuos en tu ciudad.
                        </p>
                        
                        <div class="row text-center mt-5">
                            <div class="col-4">
                                <div class="auth-stat">
                                    <h4 class="fw-bold">2,500+</h4>
                                    <small>Reportes</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="auth-stat">
                                    <h4 class="fw-bold">1,800+</h4>
                                    <small>Resueltos</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="auth-stat">
                                    <h4 class="fw-bold">15k+</h4>
                                    <small>Usuarios</small>
                                </div>
                            </div>
                        </div>
                    </div>
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
    max-width: 400px;
    padding: 2rem;
}

.auth-logo {
    border-radius: 50%;
    box-shadow: 0 4px 12px rgba(46, 125, 50, 0.2);
}

.auth-form {
    margin-top: 2rem;
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

.auth-bg-image {
    position: relative;
    overflow: hidden;
}

.auth-bg-image::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url('data:image/svg+xml,<svg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.1"><circle cx="30" cy="30" r="4"/></g></svg>');
    opacity: 0.1;
}

.auth-stat {
    background: rgba(255, 255, 255, 0.1);
    padding: 1rem;
    border-radius: 0.5rem;
    backdrop-filter: blur(10px);
}

@media (max-width: 991.98px) {
    .auth-form-container {
        max-width: 500px;
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
    
    // Form validation
    const form = document.querySelector('.auth-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Por favor, completa todos los campos.');
                return false;
            }
            
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Iniciando sesión...';
            submitBtn.disabled = true;
        });
    }
});
</script>
