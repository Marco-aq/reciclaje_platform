<?php
// Variables adicionales para el layout
$additional_css = '
<style>
    .hero-section {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 4rem 0;
        border-radius: 0 0 2rem 2rem;
        margin-bottom: 3rem;
    }
    
    .hero-title {
        font-size: 3rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }
    
    .hero-subtitle {
        font-size: 1.25rem;
        opacity: 0.9;
        margin-bottom: 2rem;
    }
    
    .feature-icon {
        font-size: 3rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }
    
    .impact-card {
        background: linear-gradient(135deg, #20c997, #17a2b8);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .impact-number {
        font-size: 2.5rem;
        font-weight: bold;
        display: block;
    }
    
    .impact-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }
    
    .recent-activity {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .activity-item {
        border-left: 3px solid var(--primary-color);
        padding-left: 1rem;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2rem;
        }
        
        .hero-subtitle {
            font-size: 1rem;
        }
        
        .impact-number {
            font-size: 2rem;
        }
    }
</style>
';

$inline_js = '
    // Animación de números
    function animateNumber(element, target) {
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current).toLocaleString();
        }, 20);
    }
    
    // Animar números cuando la página carga
    document.addEventListener("DOMContentLoaded", function() {
        const numberElements = document.querySelectorAll(".impact-number");
        numberElements.forEach(element => {
            const target = parseInt(element.getAttribute("data-target"));
            if (target) {
                animateNumber(element, target);
            }
        });
    });
';
?>

<!-- Sección Hero -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="hero-title">
                    <i class="fas fa-recycle me-3"></i>
                    Juntos por un Futuro Sostenible
                </h1>
                <p class="hero-subtitle">
                    Únete a nuestra comunidad de recicladores y ayuda a construir un mundo más limpio.
                    Reporta tus actividades de reciclaje y observa el impacto positivo que generas.
                </p>
                
                <?php if (!$this->isAuth()): ?>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="<?= $this->url('/register') ?>" class="btn btn-light btn-lg px-4">
                            <i class="fas fa-user-plus me-2"></i>
                            Comenzar Ahora
                        </a>
                        <a href="<?= $this->url('/login') ?>" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            Iniciar Sesión
                        </a>
                    </div>
                <?php else: ?>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="<?= $this->url('/reportes/crear') ?>" class="btn btn-light btn-lg px-4">
                            <i class="fas fa-plus me-2"></i>
                            Crear Reporte
                        </a>
                        <a href="<?= $this->url('/dashboard') ?>" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Mi Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-6 text-center">
                <div class="row">
                    <div class="col-6">
                        <div class="impact-card">
                            <span class="impact-number" data-target="<?= $stats['total_reportes'] ?? 0 ?>">0</span>
                            <div class="impact-label">Reportes Totales</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="impact-card">
                            <span class="impact-number" data-target="<?= $stats['total_usuarios'] ?? 0 ?>">0</span>
                            <div class="impact-label">Usuarios Activos</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="impact-card">
                            <span class="impact-number" data-target="<?= round($stats['total_materiales'] ?? 0) ?>">0</span>
                            <div class="impact-label">Kg Reciclados</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="impact-card">
                            <span class="impact-number" data-target="<?= round($stats['impacto_ambiental']['equivalente_arboles'] ?? 0) ?>">0</span>
                            <div class="impact-label">Árboles Salvados</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Características Principales -->
<section class="mb-5">
    <div class="container">
        <h2 class="text-center mb-5">¿Por qué elegir nuestra plataforma?</h2>
        
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h4>Seguimiento de Impacto</h4>
                <p class="text-muted">
                    Visualiza el impacto real de tus actividades de reciclaje con estadísticas detalladas y gráficos interactivos.
                </p>
            </div>
            
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h4>Comunidad Activa</h4>
                <p class="text-muted">
                    Únete a una comunidad comprometida con el medio ambiente y comparte tus logros con otros recicladores.
                </p>
            </div>
            
            <div class="col-md-4 text-center mb-4">
                <div class="feature-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h4>Fácil de Usar</h4>
                <p class="text-muted">
                    Interfaz intuitiva y responsive que funciona perfectamente en todos tus dispositivos.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Estadísticas e Información -->
<div class="row">
    <!-- Tipos de Materiales -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-recycle me-2"></i>
                    Materiales Más Reciclados
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($stats['tipos_materiales'])): ?>
                    <?php foreach (array_slice($stats['tipos_materiales'], 0, 5) as $material): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <strong><?= ucfirst($this->escape($material['tipo_material'])) ?></strong>
                                <br>
                                <small class="text-muted"><?= $this->formatNumber($material['cantidad_reportes']) ?> reportes</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary fs-6">
                                    <?= $this->formatNumber($material['cantidad_total']) ?> kg
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center py-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Aún no hay datos de materiales reciclados.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Actividad Reciente -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Actividad Reciente
                </h5>
            </div>
            <div class="card-body">
                <div class="recent-activity">
                    <?php if (!empty($recent_reports)): ?>
                        <?php foreach (array_slice($recent_reports, 0, 6) as $report): ?>
                            <div class="activity-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?= $this->escape($report['usuario_nombre']) ?></strong>
                                        <span class="text-muted">recicló</span>
                                        <strong><?= $this->formatNumber($report['cantidad']) ?> kg</strong>
                                        <span class="text-muted">de</span>
                                        <span class="badge bg-secondary"><?= ucfirst($this->escape($report['tipo_material'])) ?></span>
                                    </div>
                                    <small class="text-muted">
                                        <?= $this->formatDate($report['fecha_reporte']) ?>
                                    </small>
                                </div>
                                <div class="mt-1">
                                    <small class="text-muted">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?= $this->escape($report['ubicacion']) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-info-circle me-2"></i>
                            Aún no hay actividad reciente.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Impacto Ambiental -->
<?php if (!empty($stats['impacto_ambiental'])): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-leaf me-2"></i>
                    Impacto Ambiental Colectivo
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="impact-stat">
                            <h3 class="text-success mb-0">
                                <?= $this->formatNumber(round($stats['impacto_ambiental']['total_co2_evitado'] ?? 0)) ?> kg
                            </h3>
                            <small class="text-muted">CO₂ Evitado</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="impact-stat">
                            <h3 class="text-success mb-0">
                                <?= $this->formatNumber($stats['impacto_ambiental']['equivalente_arboles'] ?? 0) ?>
                            </h3>
                            <small class="text-muted">Árboles Equivalentes</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="impact-stat">
                            <h3 class="text-success mb-0">
                                <?= $this->formatNumber($stats['impacto_ambiental']['equivalente_autos'] ?? 0) ?>
                            </h3>
                            <small class="text-muted">Autos Menos en Circulación</small>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="impact-stat">
                            <h3 class="text-success mb-0">
                                <?= $this->formatNumber($stats['total_materiales'] ?? 0) ?> kg
                            </h3>
                            <small class="text-muted">Material Total Reciclado</small>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <p class="text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        Estos cálculos son estimaciones basadas en factores de conversión ambientales estándar.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Call to Action -->
<?php if (!$this->isAuth()): ?>
<div class="row">
    <div class="col-12">
        <div class="card bg-primary text-white">
            <div class="card-body text-center py-5">
                <h3 class="mb-3">¿Listo para hacer la diferencia?</h3>
                <p class="lead mb-4">
                    Únete a nuestra comunidad y comienza a registrar tus actividades de reciclaje hoy mismo.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="<?= $this->url('/register') ?>" class="btn btn-light btn-lg px-4">
                        <i class="fas fa-user-plus me-2"></i>
                        Crear Cuenta Gratis
                    </a>
                    <a href="<?= $this->url('/about') ?>" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Conocer Más
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
