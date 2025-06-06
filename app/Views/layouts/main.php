<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <title><?= $this->escape($title ?? 'Plataforma de Reciclaje') ?></title>
    
    <!-- Meta tags para SEO -->
    <meta name="description" content="Plataforma de reciclaje para reportar y gestionar actividades de reciclaje">
    <meta name="keywords" content="reciclaje, sostenibilidad, medio ambiente, gestión de residuos">
    <meta name="author" content="Plataforma de Reciclaje">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= $this->escape($title ?? 'Plataforma de Reciclaje') ?>">
    <meta property="og:description" content="Plataforma de reciclaje para reportar y gestionar actividades de reciclaje">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= $this->asset('img/favicon.png') ?>">
    
    <!-- CSS Principal -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- CSS Personalizado -->
    <style>
        :root {
            --primary-color: #28a745;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color) !important;
        }

        .navbar-nav .nav-link {
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: box-shadow 0.15s ease-in-out;
        }

        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
        }

        .stats-card {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border-radius: 15px;
        }

        .stats-card .card-body {
            padding: 1.5rem;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .footer {
            background-color: var(--dark-color);
            color: white;
            padding: 2rem 0;
            margin-top: 4rem;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .pagination .page-link {
            color: var(--primary-color);
            border-radius: 8px;
            margin: 0 2px;
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .breadcrumb {
            background-color: transparent;
            padding: 0;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            color: var(--primary-color);
        }

        .sidebar {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .sidebar .nav-link {
            color: var(--dark-color);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .loading {
            display: none;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }

        @media (max-width: 768px) {
            .stats-number {
                font-size: 2rem;
            }
            
            .card-body {
                padding: 1rem;
            }
        }
    </style>

    <!-- CSS adicional por página -->
    <?php if (isset($additional_css)): ?>
        <?= $additional_css ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navegación -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="<?= $this->url('/') ?>">
                <i class="fas fa-recycle me-2"></i>
                <?= env('APP_NAME', 'Plataforma de Reciclaje') ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $this->url('/') ?>">
                            <i class="fas fa-home me-1"></i>Inicio
                        </a>
                    </li>
                    
                    <?php if ($this->isAuth()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $this->url('/dashboard') ?>">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $this->url('/reportes') ?>">
                                <i class="fas fa-clipboard-list me-1"></i>Mis Reportes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $this->url('/estadisticas') ?>">
                                <i class="fas fa-chart-bar me-1"></i>Estadísticas
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($this->isAuth()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?= $this->escape($this->user()['nombre'] ?? 'Usuario') ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= $this->url('/dashboard/profile') ?>">
                                    <i class="fas fa-user-edit me-2"></i>Mi Perfil
                                </a></li>
                                <li><a class="dropdown-item" href="<?= $this->url('/reportes/crear') ?>">
                                    <i class="fas fa-plus me-2"></i>Nuevo Reporte
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="<?= $this->url('/logout') ?>" class="d-inline">
                                        <?= $this->csrfField() ?>
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $this->url('/login') ?>">
                                <i class="fas fa-sign-in-alt me-1"></i>Iniciar Sesión
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= $this->url('/register') ?>">
                                <i class="fas fa-user-plus me-1"></i>Registrarse
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumbs (opcional) -->
    <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
        <div class="container mt-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <?php foreach ($breadcrumbs as $breadcrumb): ?>
                        <?php if (isset($breadcrumb['url'])): ?>
                            <li class="breadcrumb-item">
                                <a href="<?= $this->escape($breadcrumb['url']) ?>">
                                    <?= $this->escape($breadcrumb['text']) ?>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="breadcrumb-item active">
                                <?= $this->escape($breadcrumb['text']) ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ol>
            </nav>
        </div>
    <?php endif; ?>

    <!-- Mensajes Flash -->
    <div class="container mt-3">
        <?php
        $flashTypes = ['success', 'error', 'warning', 'info'];
        foreach ($flashTypes as $type):
            $message = $this->flash($type);
            if ($message):
        ?>
            <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show" role="alert">
                <?php
                $icon = [
                    'success' => 'check-circle',
                    'error' => 'exclamation-triangle',
                    'warning' => 'exclamation-triangle',
                    'info' => 'info-circle'
                ][$type] ?? 'info-circle';
                ?>
                <i class="fas fa-<?= $icon ?> me-2"></i>
                <?= $this->escape($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; endforeach; ?>
    </div>

    <!-- Contenido Principal -->
    <main class="container my-4">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-3">
                    <h5><i class="fas fa-recycle me-2"></i><?= env('APP_NAME', 'Plataforma de Reciclaje') ?></h5>
                    <p class="text-muted">
                        Juntos construimos un futuro más sostenible a través del reciclaje responsable.
                    </p>
                </div>
                <div class="col-lg-2 mb-3">
                    <h6>Enlaces</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= $this->url('/') ?>" class="text-muted">Inicio</a></li>
                        <?php if ($this->isAuth()): ?>
                            <li><a href="<?= $this->url('/dashboard') ?>" class="text-muted">Dashboard</a></li>
                            <li><a href="<?= $this->url('/reportes') ?>" class="text-muted">Reportes</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-lg-2 mb-3">
                    <h6>Información</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= $this->url('/about') ?>" class="text-muted">Acerca de</a></li>
                        <li><a href="<?= $this->url('/contact') ?>" class="text-muted">Contacto</a></li>
                        <li><a href="<?= $this->url('/terms') ?>" class="text-muted">Términos</a></li>
                        <li><a href="<?= $this->url('/privacy') ?>" class="text-muted">Privacidad</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 mb-3">
                    <h6>Contacto</h6>
                    <p class="text-muted mb-1">
                        <i class="fas fa-envelope me-2"></i>
                        info@plataforma-reciclaje.com
                    </p>
                    <p class="text-muted mb-1">
                        <i class="fas fa-phone me-2"></i>
                        +52 (555) 123-4567
                    </p>
                    <div class="mt-3">
                        <a href="#" class="text-muted me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-muted me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-muted"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        &copy; <?= date('Y') ?> <?= env('APP_NAME', 'Plataforma de Reciclaje') ?>. Todos los derechos reservados.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">
                        <i class="fas fa-leaf me-1"></i>
                        Desarrollado con amor por el planeta
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    <!-- JavaScript personalizado -->
    <script>
        // Configuración global
        window.APP_CONFIG = {
            url: '<?= env('APP_URL', '') ?>',
            debug: <?= env('APP_DEBUG', false) ? 'true' : 'false' ?>,
            csrf_token: '<?= $this->csrfToken() ?>'
        };

        // Funciones utilitarias
        function showAlert(message, type = 'info') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="fas fa-info-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.container');
            const main = document.querySelector('main');
            container.insertBefore(alertDiv, main);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        function formatNumber(num) {
            return new Intl.NumberFormat('es-MX').format(num);
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-MX', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        // Auto-dismiss alerts
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.parentNode) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 5000);
            });
        });

        // Confirmación para eliminar
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
                if (!confirm('¿Estás seguro de que quieres eliminar este elemento?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });

        // Loading state for forms
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;
                const originalText = submitBtn.textContent;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';
                
                // Re-enable after 10 seconds as fallback
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }, 10000);
            }
        });
    </script>

    <!-- JavaScript adicional por página -->
    <?php if (isset($additional_js)): ?>
        <?= $additional_js ?>
    <?php endif; ?>

    <!-- JavaScript inline -->
    <?php if (isset($inline_js)): ?>
        <script>
            <?= $inline_js ?>
        </script>
    <?php endif; ?>
</body>
</html>
