<?php
/**
 * Header Component - Navegación Principal
 */

// Obtener mensajes flash para mostrar
$flashMessages = getFlashMessages();
?>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand fw-bold" href="<?= url() ?>">
            <i class="fas fa-recycle me-2 text-success"></i>
            EcoCusco
        </a>

        <!-- Mobile toggle button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation items -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= url() ?>">
                        <i class="fas fa-home me-1"></i>Inicio
                    </a>
                </li>
                
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('../pages/dashboard.php') ?>">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('../pages/reportes.php') ?>">
                            <i class="fas fa-chart-bar me-1"></i>Reportes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('../pages/estadisticas.php') ?>">
                            <i class="fas fa-chart-pie me-1"></i>Estadísticas
                        </a>
                    </li>
                    
                    <?php if (isAdmin()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cog me-1"></i>Administración
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?= url('../pages/admin/usuarios.php') ?>">
                                    <i class="fas fa-users me-2"></i>Usuarios
                                </a></li>
                                <li><a class="dropdown-item" href="<?= url('../pages/admin/configuracion.php') ?>">
                                    <i class="fas fa-cogs me-2"></i>Configuración
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= url('../pages/admin/logs.php') ?>">
                                    <i class="fas fa-file-alt me-2"></i>Logs del Sistema
                                </a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <!-- User menu -->
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <?php $currentUser = getCurrentUser(); ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?= e($currentUser['nombre'] ?? 'Usuario') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?= url('../pages/perfil.php') ?>">
                                    <i class="fas fa-user me-2"></i>Mi Perfil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= url('../pages/configuracion.php') ?>">
                                    <i class="fas fa-cog me-2"></i>Configuración
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?= url('../pages/logout.php') ?>" onclick="return confirm('¿Estás seguro de que quieres cerrar sesión?')">
                                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('../pages/login.php') ?>">
                            <i class="fas fa-sign-in-alt me-1"></i>Iniciar Sesión
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-success btn-sm text-white ms-2" href="<?= url('../pages/register.php') ?>">
                            <i class="fas fa-user-plus me-1"></i>Registrarse
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
<?php if (!empty($flashMessages)): ?>
    <div class="container mt-3">
        <?php foreach ($flashMessages as $message): ?>
            <div class="alert alert-<?= $message['type'] === 'error' ? 'danger' : $message['type'] ?> alert-dismissible fade show" role="alert">
                <?php
                $icon = match($message['type']) {
                    'success' => 'fas fa-check-circle',
                    'error' => 'fas fa-exclamation-triangle',
                    'warning' => 'fas fa-exclamation-circle',
                    'info' => 'fas fa-info-circle',
                    default => 'fas fa-info-circle'
                };
                ?>
                <i class="<?= $icon ?> me-2"></i>
                <?= e($message['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Breadcrumb (opcional, para páginas internas) -->
<?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
    <div class="container mt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= url() ?>" class="text-decoration-none">
                        <i class="fas fa-home me-1"></i>Inicio
                    </a>
                </li>
                <?php foreach ($breadcrumbs as $breadcrumb): ?>
                    <?php if (isset($breadcrumb['url'])): ?>
                        <li class="breadcrumb-item">
                            <a href="<?= e($breadcrumb['url']) ?>" class="text-decoration-none">
                                <?= e($breadcrumb['title']) ?>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= e($breadcrumb['title']) ?>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
    </div>
<?php endif; ?>
