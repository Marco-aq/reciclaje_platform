<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->escape($pageTitle ?? 'EcoCusco - Gestión de Residuos') ?></title>
    
    <!-- Meta tags -->
    <meta name="description" content="Plataforma colaborativa para reportar y gestionar residuos sólidos urbanos en Cusco">
    <meta name="keywords" content="reciclaje, residuos, Cusco, medio ambiente, sostenibilidad">
    <meta name="author" content="EcoCusco Team">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?= $this->escape($pageTitle ?? 'EcoCusco') ?>">
    <meta property="og:description" content="Plataforma colaborativa para la gestión de residuos en Cusco">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $this->url() ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= $this->asset('img/favicon.ico') ?>">
    <link rel="apple-touch-icon" href="<?= $this->asset('img/icon-192.png') ?>">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= $this->asset('css/main.css') ?>" rel="stylesheet">
    
    <!-- Additional CSS -->
    <?php if (isset($additionalCss)): ?>
        <?php foreach ($additionalCss as $css): ?>
            <link href="<?= $this->asset($css) ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#2E7D32">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-success" href="<?= $this->url('/') ?>">
                <i class="fas fa-leaf me-2"></i>EcoCusco
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
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $this->url('/reportes') ?>">
                            <i class="fas fa-exclamation-triangle me-1"></i>Reportes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $this->url('/estadisticas') ?>">
                            <i class="fas fa-chart-bar me-1"></i>Estadísticas
                        </a>
                    </li>
                </ul>
                
                <!-- User Menu -->
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                <?= $this->escape($_SESSION['user_name'] ?? 'Usuario') ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= $this->url('/dashboard') ?>">
                                    <i class="fas fa-tachometer-alt me-2"></i>Panel
                                </a></li>
                                <li><a class="dropdown-item" href="<?= $this->url('/dashboard/perfil') ?>">
                                    <i class="fas fa-user me-2"></i>Mi Perfil
                                </a></li>
                                <li><a class="dropdown-item" href="<?= $this->url('/reportes/crear') ?>">
                                    <i class="fas fa-plus me-2"></i>Nuevo Reporte
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="<?= $this->url('/logout') ?>" class="d-inline">
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
                                <i class="fas fa-sign-in-alt me-1"></i>Ingresar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-success btn-sm ms-2" href="<?= $this->url('/register') ?>">
                                <i class="fas fa-user-plus me-1"></i>Registrarse
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php foreach (['success', 'error', 'warning', 'info'] as $type): ?>
        <?php if ($message = $this->flash($type)): ?>
            <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show m-0" role="alert">
                <div class="container">
                    <i class="fas fa-<?= $this->getAlertIcon($type) ?> me-2"></i>
                    <?= $this->escape($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- Main Content -->
    <main class="flex-grow-1">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="text-success">
                        <i class="fas fa-leaf me-2"></i>EcoCusco
                    </h5>
                    <p class="text-light">
                        Trabajando juntos por una ciudad más limpia y sostenible. 
                        Plataforma colaborativa para la gestión de residuos sólidos urbanos.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-success me-3"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="text-success me-3"><i class="fab fa-twitter fa-lg"></i></a>
                        <a href="#" class="text-success me-3"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class="text-success"><i class="fab fa-linkedin fa-lg"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="text-uppercase fw-bold">Enlaces</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= $this->url('/') ?>" class="text-light text-decoration-none">Inicio</a></li>
                        <li><a href="<?= $this->url('/reportes') ?>" class="text-light text-decoration-none">Reportes</a></li>
                        <li><a href="<?= $this->url('/estadisticas') ?>" class="text-light text-decoration-none">Estadísticas</a></li>
                        <li><a href="<?= $this->url('/about') ?>" class="text-light text-decoration-none">Acerca de</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="text-uppercase fw-bold">Contacto</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2 text-success"></i>
                            contacto@ecocusco.pe
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2 text-success"></i>
                            +51 984 123 456
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2 text-success"></i>
                            Cusco, Perú
                        </li>
                    </ul>
                </div>
                
                <div class="col-lg-3 mb-4">
                    <h6 class="text-uppercase fw-bold">Legal</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= $this->url('/terminos') ?>" class="text-light text-decoration-none">Términos y Condiciones</a></li>
                        <li><a href="<?= $this->url('/privacidad') ?>" class="text-light text-decoration-none">Política de Privacidad</a></li>
                        <li><a href="<?= $this->url('/contacto') ?>" class="text-light text-decoration-none">Contacto</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="border-secondary">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small class="text-muted">
                        &copy; <?= date('Y') ?> EcoCusco. Todos los derechos reservados.
                    </small>
                </div>
                <div class="col-md-6 text-md-end">
                    <small class="text-muted">
                        Hecho con <i class="fas fa-heart text-success"></i> para un Cusco más verde
                    </small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $this->asset('js/main.js') ?>"></script>
    
    <!-- Additional Scripts -->
    <?php if (isset($additionalJs)): ?>
        <?php foreach ($additionalJs as $js): ?>
            <script src="<?= $this->asset($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Inline Scripts -->
    <?php if (isset($inlineJs)): ?>
        <script>
            <?= $inlineJs ?>
        </script>
    <?php endif; ?>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('SW registered: ', registration);
                    })
                    .catch(function(registrationError) {
                        console.log('SW registration failed: ', registrationError);
                    });
            });
        }
    </script>
</body>
</html>

<?php
// Helper method for alert icons
if (!method_exists($this, 'getAlertIcon')) {
    $this->getAlertIcon = function($type) {
        $icons = [
            'success' => 'check-circle',
            'error' => 'exclamation-circle',
            'warning' => 'exclamation-triangle',
            'info' => 'info-circle'
        ];
        return $icons[$type] ?? 'info-circle';
    };
}
?>
