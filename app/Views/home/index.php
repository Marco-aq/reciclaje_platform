<!-- Hero Section -->
<section class="hero-section bg-light py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold text-dark mb-4">
                    Sistema de Gestión de 
                    <span class="text-success">Residuos Sólidos Urbanos</span> 
                    en Cusco
                </h1>
                <p class="lead text-muted mb-4">
                    Plataforma colaborativa para reportar y gestionar puntos de acumulación 
                    de residuos mediante geolocalización y mapas interactivos.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= $this->url('/reportes/crear') ?>" class="btn btn-success btn-lg">
                        <i class="fas fa-plus me-2"></i>Reportar Residuos
                    </a>
                    <a href="<?= $this->url('/reportes') ?>" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-map me-2"></i>Ver Reportes
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="<?= $this->asset('img/hero-image.png') ?>" 
                     alt="Gestión de Residuos" 
                     class="img-fluid rounded shadow"
                     style="max-height: 400px;">
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section bg-success text-white py-4">
    <div class="container">
        <div class="row text-center">
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <h3 class="fw-bold mb-1"><?= number_format($stats['reportes_totales'] ?? 0) ?></h3>
                <small>Reportes Realizados</small>
            </div>
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <h3 class="fw-bold mb-1"><?= number_format($stats['reportes_resueltos'] ?? 0) ?></h3>
                <small>Problemas Resueltos</small>
            </div>
            <div class="col-6 col-md-3">
                <h3 class="fw-bold mb-1"><?= number_format($stats['usuarios_activos'] ?? 0) ?></h3>
                <small>Usuarios Activos</small>
            </div>
            <div class="col-6 col-md-3">
                <h3 class="fw-bold mb-1"><?= $stats['tiempo_promedio_resolucion'] ?? 0 ?> días</h3>
                <small>Tiempo Promedio</small>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Características Principales</h2>
            <p class="lead text-muted">Herramientas poderosas para una gestión eficiente de residuos</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="fas fa-map-marker-alt fa-2x text-success"></i>
                        </div>
                        <h4 class="card-title">Reportes Geolocalizados</h4>
                        <p class="card-text text-muted">
                            Reporta puntos de acumulación de residuos con ubicación exacta 
                            y evidencia fotográfica para una respuesta más eficiente.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="fas fa-chart-line fa-2x text-success"></i>
                        </div>
                        <h4 class="card-title">Estadísticas en Tiempo Real</h4>
                        <p class="card-text text-muted">
                            Visualiza métricas actualizadas sobre el estado de la gestión 
                            de residuos en diferentes zonas de la ciudad.
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="fas fa-users fa-2x text-success"></i>
                        </div>
                        <h4 class="card-title">Colaboración Ciudadana</h4>
                        <p class="card-text text-muted">
                            Participa activamente en la mejora de tu ciudad conectando 
                            ciudadanos con empresas y autoridades locales.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Recent Activity Section -->
<?php if (!empty($recentReports)): ?>
<section class="recent-activity-section bg-light py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h3 class="mb-4">
                    <i class="fas fa-clock me-2 text-success"></i>Actividad Reciente
                </h3>
                
                <div class="row g-3">
                    <?php foreach (array_slice($recentReports, 0, 4) as $report): ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">
                                        <?= $this->truncate($this->escape($report['ubicacion']), 40) ?>
                                    </h6>
                                    <span class="badge bg-<?= $this->getStatusColor($report['estado']) ?>">
                                        <?= $this->escape(ucfirst($report['estado'])) ?>
                                    </span>
                                </div>
                                <p class="card-text small text-muted">
                                    <i class="fas fa-trash me-1"></i>
                                    <?= $this->escape($report['tipo_residuo']) ?>
                                </p>
                                <p class="card-text small text-muted mb-0">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?= $this->formatDate($report['created_at'], 'd/m/Y H:i') ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="text-center mt-4">
                    <a href="<?= $this->url('/reportes') ?>" class="btn btn-outline-success">
                        Ver Todos los Reportes <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-4">
                <h3 class="mb-4">
                    <i class="fas fa-chart-pie me-2 text-success"></i>Tipos de Residuos
                </h3>
                
                <?php if (!empty($stats['tipos_residuo_top'])): ?>
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($stats['tipos_residuo_top'], 0, 5) as $tipo): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center border-0 px-0">
                        <span><?= $this->escape($tipo['tipo_residuo']) ?></span>
                        <span class="badge bg-success rounded-pill"><?= $tipo['count'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <a href="<?= $this->url('/estadisticas') ?>" class="btn btn-outline-success w-100">
                        Ver Estadísticas Completas
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="cta-section bg-success text-white py-5">
    <div class="container text-center">
        <h2 class="mb-4">¿Listo para hacer la diferencia?</h2>
        <p class="lead mb-4">
            Únete a nuestra comunidad y ayuda a construir un Cusco más limpio y sostenible.
        </p>
        
        <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="<?= $this->url('/register') ?>" class="btn btn-light btn-lg">
                <i class="fas fa-user-plus me-2"></i>Registrarse Gratis
            </a>
            <a href="<?= $this->url('/reportes/crear') ?>" class="btn btn-outline-light btn-lg">
                <i class="fas fa-exclamation-triangle me-2"></i>Hacer un Reporte
            </a>
        </div>
        <?php else: ?>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="<?= $this->url('/reportes/crear') ?>" class="btn btn-light btn-lg">
                <i class="fas fa-plus me-2"></i>Nuevo Reporte
            </a>
            <a href="<?= $this->url('/dashboard') ?>" class="btn btn-outline-light btn-lg">
                <i class="fas fa-tachometer-alt me-2"></i>Mi Panel
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Map Preview Section -->
<section class="map-section py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h3>Mapa Interactivo de Reportes</h3>
            <p class="text-muted">Visualiza los reportes de residuos en tiempo real en toda la ciudad</p>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow">
                    <div class="card-body p-0">
                        <div id="home-map" style="height: 400px; border-radius: 0.375rem;">
                            <!-- Placeholder para el mapa -->
                            <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                                <div class="text-center text-muted">
                                    <i class="fas fa-map fa-3x mb-3"></i>
                                    <p>Mapa interactivo de reportes</p>
                                    <a href="<?= $this->url('/reportes') ?>" class="btn btn-success">
                                        Ver Mapa Completo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Aquí se podría cargar un mapa real con los reportes
    console.log('Home page loaded');
    
    // Ejemplo de carga de datos dinámicos
    loadHomeStats();
});

function loadHomeStats() {
    // Simulación de carga de estadísticas dinámicas
    // En un caso real, esto haría una petición AJAX al API
}
</script>

<?php
// Helper method para obtener color del estado
if (!method_exists($this, 'getStatusColor')) {
    $this->getStatusColor = function($status) {
        $colors = [
            'pendiente' => 'warning',
            'en_proceso' => 'info',
            'resuelto' => 'success',
            'rechazado' => 'danger'
        ];
        return $colors[$status] ?? 'secondary';
    };
}
?>
