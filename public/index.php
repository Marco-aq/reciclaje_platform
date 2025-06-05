<?php
/**
 * EcoCusco - Punto de Entrada Principal
 * 
 * Archivo de entrada simplificado que maneja la configuración básica
 * y muestra la página principal de la aplicación.
 */

// Incluir configuración
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Verificar conexión a base de datos
try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
} catch (Exception $e) {
    if (APP_DEBUG) {
        die("Error de conexión: " . $e->getMessage());
    } else {
        die("Error de conexión a la base de datos. Verifica la configuración.");
    }
}

// Obtener datos del usuario si está autenticado
$user = getCurrentUser();
$isAuthenticated = isLoggedIn();

// Título de la página
$pageTitle = APP_NAME . " - Gestión Inteligente de Residuos Sólidos";
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
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../components/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section bg-success text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-3">
                        <i class="fas fa-recycle me-2"></i>
                        EcoCusco
                    </h1>
                    <p class="lead mb-4">
                        Plataforma inteligente para la gestión de residuos sólidos urbanos en Cusco, Perú. 
                        Contribuye al desarrollo sostenible de nuestra ciudad histórica.
                    </p>
                    
                    <?php if (!$isAuthenticated): ?>
                        <div class="d-flex gap-3">
                            <a href="../pages/register.php" class="btn btn-light btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Registrarse
                            </a>
                            <a href="../pages/login.php" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="d-flex gap-3">
                            <a href="../pages/dashboard.php" class="btn btn-light btn-lg">
                                <i class="fas fa-tachometer-alt me-2"></i>Panel de Control
                            </a>
                            <a href="../pages/reportes.php" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-chart-bar me-2"></i>Ver Reportes
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="assets/img/eco-hero.svg" alt="EcoCusco" class="img-fluid" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-5 fw-bold text-success mb-3">
                        Características Principales
                    </h2>
                    <p class="lead text-muted">
                        Herramientas modernas para una gestión eficiente de residuos
                    </p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-chart-line text-success fa-2x"></i>
                            </div>
                            <h5 class="card-title fw-bold">Reportes Inteligentes</h5>
                            <p class="card-text text-muted">
                                Genera reportes detallados sobre la recolección y gestión de residuos en tiempo real.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-map-marked-alt text-info fa-2x"></i>
                            </div>
                            <h5 class="card-title fw-bold">Mapeo Urbano</h5>
                            <p class="card-text text-muted">
                                Visualiza rutas de recolección y puntos de acopio en un mapa interactivo de Cusco.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                <i class="fas fa-users text-warning fa-2x"></i>
                            </div>
                            <h5 class="card-title fw-bold">Gestión Comunitaria</h5>
                            <p class="card-text text-muted">
                                Involucra a la comunidad en la gestión sostenible de residuos urbanos.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-12 mb-5">
                    <h2 class="display-5 fw-bold text-success mb-3">
                        Impacto en Cusco
                    </h2>
                    <p class="lead text-muted">
                        Datos actuales del sistema de gestión de residuos
                    </p>
                </div>
            </div>
            
            <div class="row g-4">
                <?php
                try {
                    // Obtener estadísticas básicas (simuladas por ahora)
                    $stats = [
                        ['icon' => 'fas fa-trash', 'number' => '1,250', 'label' => 'Toneladas Recolectadas', 'color' => 'success'],
                        ['icon' => 'fas fa-route', 'number' => '45', 'label' => 'Rutas Activas', 'color' => 'info'],
                        ['icon' => 'fas fa-recycle', 'number' => '78%', 'label' => 'Tasa de Reciclaje', 'color' => 'warning'],
                        ['icon' => 'fas fa-users', 'number' => '2,340', 'label' => 'Usuarios Registrados', 'color' => 'primary']
                    ];
                    
                    foreach ($stats as $stat):
                ?>
                    <div class="col-md-3 col-sm-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center p-4">
                                <div class="bg-<?= $stat['color'] ?> bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="<?= $stat['icon'] ?> text-<?= $stat['color'] ?> fa-lg"></i>
                                </div>
                                <h3 class="fw-bold text-<?= $stat['color'] ?> mb-2"><?= $stat['number'] ?></h3>
                                <p class="text-muted mb-0 small"><?= $stat['label'] ?></p>
                            </div>
                        </div>
                    </div>
                <?php 
                    endforeach;
                } catch (Exception $e) {
                    // En caso de error, mostrar estadísticas por defecto
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <?php if (!$isAuthenticated): ?>
    <section class="py-5 bg-success text-white">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="display-5 fw-bold mb-3">
                        ¡Únete a EcoCusco Hoy!
                    </h2>
                    <p class="lead mb-4">
                        Contribuye al desarrollo sostenible de Cusco. Regístrate y forma parte del cambio hacia una ciudad más limpia y ecológica.
                    </p>
                    <a href="../pages/register.php" class="btn btn-light btn-lg">
                        <i class="fas fa-user-plus me-2"></i>
                        Crear Cuenta Gratis
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php include __DIR__ . '/../components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
</body>
</html>
