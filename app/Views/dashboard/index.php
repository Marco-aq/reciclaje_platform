<?php
$additional_css = '
<style>
    .dashboard-header {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid var(--primary-color);
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: var(--primary-color);
        line-height: 1;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: #6c757d;
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-icon {
        font-size: 2rem;
        color: var(--primary-color);
        opacity: 0.7;
        float: right;
    }
    
    .achievement-badge {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
        color: white;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-block;
        margin: 0.25rem;
    }
    
    .notification-item {
        background: #f8f9fa;
        border-left: 4px solid #0dcaf0;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    
    .notification-item.success {
        border-left-color: #198754;
        background: #d1e7dd;
    }
    
    .notification-item.warning {
        border-left-color: #ffc107;
        background: #fff3cd;
    }
    
    .notification-item.achievement {
        border-left-color: #fd7e14;
        background: #ffe5d0;
    }
    
    .recent-activity {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .activity-item {
        border-bottom: 1px solid #eee;
        padding: 1rem 0;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .material-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
    }
    
    .progress-ring {
        transform: rotate(-90deg);
    }
    
    .progress-ring-background {
        fill: transparent;
        stroke: #e9ecef;
        stroke-width: 8;
    }
    
    .progress-ring-progress {
        fill: transparent;
        stroke: var(--primary-color);
        stroke-width: 8;
        stroke-linecap: round;
        transition: stroke-dasharray 0.5s ease;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 1rem;
    }
    
    .quick-action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .quick-action-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
    }
    
    .quick-action-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        text-decoration: none;
        color: inherit;
    }
    
    .quick-action-icon {
        font-size: 2.5rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 1.5rem;
            text-align: center;
        }
        
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .stat-number {
            font-size: 2rem;
        }
        
        .quick-action-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
';

$inline_js = '
    // Crear gráfico de rosquilla para materiales
    function createMaterialsChart(data) {
        const ctx = document.getElementById("materialsChart");
        if (!ctx || !data.length) return;
        
        new Chart(ctx, {
            type: "doughnut",
            data: {
                labels: data.map(item => item.tipo_material.charAt(0).toUpperCase() + item.tipo_material.slice(1)),
                datasets: [{
                    data: data.map(item => item.cantidad_total),
                    backgroundColor: [
                        "#28a745", "#20c997", "#0dcaf0", "#6f42c1", 
                        "#fd7e14", "#ffc107", "#dc3545", "#6c757d"
                    ],
                    borderWidth: 2,
                    borderColor: "#fff"
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }
    
    // Crear gráfico de línea para actividad mensual
    function createActivityChart(data) {
        const ctx = document.getElementById("activityChart");
        if (!ctx || !data.length) return;
        
        new Chart(ctx, {
            type: "line",
            data: {
                labels: data.map(item => {
                    const [year, month] = item.mes.split("-");
                    return new Date(year, month - 1).toLocaleDateString("es", { month: "short", year: "numeric" });
                }),
                datasets: [{
                    label: "Reportes",
                    data: data.map(item => item.cantidad_reportes),
                    borderColor: "#28a745",
                    backgroundColor: "rgba(40, 167, 69, 0.1)",
                    tension: 0.4,
                    fill: true
                }, {
                    label: "Materiales (kg)",
                    data: data.map(item => item.cantidad_materiales),
                    borderColor: "#20c997",
                    backgroundColor: "rgba(32, 201, 151, 0.1)",
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: "top"
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    
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
    
    // Inicializar dashboard
    document.addEventListener("DOMContentLoaded", function() {
        // Animar números de estadísticas
        const statNumbers = document.querySelectorAll(".stat-number");
        statNumbers.forEach(element => {
            const target = parseInt(element.getAttribute("data-target"));
            if (target) {
                animateNumber(element, target);
            }
        });
        
        // Crear gráficos si hay datos
        const materialsData = ' . json_encode($user_stats['tipos_materiales'] ?? []) . ';
        const activityData = ' . json_encode($chart_data['reportes_por_mes'] ?? []) . ';
        
        createMaterialsChart(materialsData);
        createActivityChart(activityData);
        
        // Auto-refresh cada 5 minutos
        setInterval(function() {
            fetch("/api/dashboard/data")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateDashboardStats(data.data);
                    }
                })
                .catch(console.error);
        }, 300000);
    });
    
    function updateDashboardStats(data) {
        // Actualizar números de estadísticas
        if (data.user_stats) {
            const stats = data.user_stats;
            document.querySelector("[data-stat=\'total_reportes\']").textContent = stats.total_reportes || 0;
            document.querySelector("[data-stat=\'total_materiales\']").textContent = Math.round(stats.total_materiales || 0);
            document.querySelector("[data-stat=\'puntos\']").textContent = stats.puntos || 0;
            document.querySelector("[data-stat=\'streak_dias\']").textContent = stats.streak_dias || 0;
        }
    }
';
?>

<!-- Dashboard Header -->
<div class="dashboard-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="mb-2">
                <i class="fas fa-tachometer-alt me-3"></i>
                ¡Hola, <?= $this->escape($user['nombre']) ?>!
            </h1>
            <p class="mb-0 opacity-75">
                Bienvenido a tu dashboard personal. Aquí puedes ver tu impacto en el reciclaje.
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="d-flex gap-2 justify-content-md-end justify-content-start mt-3 mt-md-0">
                <a href="<?= $this->url('/reportes/crear') ?>" class="btn btn-light">
                    <i class="fas fa-plus me-1"></i>
                    Nuevo Reporte
                </a>
                <a href="<?= $this->url('/estadisticas') ?>" class="btn btn-outline-light">
                    <i class="fas fa-chart-bar me-1"></i>
                    Ver Más
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas Principales -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-number" data-target="<?= $user_stats['total_reportes'] ?? 0 ?>" data-stat="total_reportes">0</div>
                <div class="stat-label">Reportes Totales</div>
            </div>
            <i class="fas fa-clipboard-list stat-icon"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-number" data-target="<?= round($user_stats['total_materiales'] ?? 0) ?>" data-stat="total_materiales">0</div>
                <div class="stat-label">Kg Reciclados</div>
            </div>
            <i class="fas fa-weight stat-icon"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-number" data-target="<?= $user_stats['puntos'] ?? 0 ?>" data-stat="puntos">0</div>
                <div class="stat-label">Puntos Verdes</div>
            </div>
            <i class="fas fa-star stat-icon"></i>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div class="stat-number" data-target="<?= $user_stats['streak_dias'] ?? 0 ?>" data-stat="streak_dias">0</div>
                <div class="stat-label">Días Consecutivos</div>
            </div>
            <i class="fas fa-fire stat-icon"></i>
        </div>
    </div>
</div>

<!-- Acciones Rápidas -->
<div class="quick-action-grid">
    <a href="<?= $this->url('/reportes/crear') ?>" class="quick-action-card">
        <div class="quick-action-icon">
            <i class="fas fa-plus-circle"></i>
        </div>
        <h5>Crear Reporte</h5>
        <small class="text-muted">Registra tu actividad de reciclaje</small>
    </a>
    
    <a href="<?= $this->url('/reportes') ?>" class="quick-action-card">
        <div class="quick-action-icon">
            <i class="fas fa-list"></i>
        </div>
        <h5>Mis Reportes</h5>
        <small class="text-muted">Ver todos mis reportes</small>
    </a>
    
    <a href="<?= $this->url('/estadisticas') ?>" class="quick-action-card">
        <div class="quick-action-icon">
            <i class="fas fa-chart-pie"></i>
        </div>
        <h5>Estadísticas</h5>
        <small class="text-muted">Análisis detallado</small>
    </a>
    
    <a href="<?= $this->url('/dashboard/profile') ?>" class="quick-action-card">
        <div class="quick-action-icon">
            <i class="fas fa-user-cog"></i>
        </div>
        <h5>Mi Perfil</h5>
        <small class="text-muted">Configurar cuenta</small>
    </a>
</div>

<!-- Contenido Principal -->
<div class="row">
    <!-- Gráficos -->
    <div class="col-lg-8">
        <!-- Actividad por Mes -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Mi Actividad de Reciclaje
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Materiales por Tipo -->
        <?php if (!empty($user_stats['tipos_materiales'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-recycle me-2"></i>
                    Distribución de Materiales
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="materialsChart"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Actividad Reciente -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Mis Reportes Recientes
                </h5>
            </div>
            <div class="card-body">
                <div class="recent-activity">
                    <?php if (!empty($recent_reports)): ?>
                        <?php foreach ($recent_reports as $report): ?>
                            <div class="activity-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="d-flex align-items-center mb-1">
                                            <strong class="me-2"><?= $this->formatNumber($report['cantidad']) ?> kg</strong>
                                            <span class="material-badge bg-primary text-white">
                                                <?= ucfirst($this->escape($report['tipo_material'])) ?>
                                            </span>
                                        </div>
                                        <div class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?= $this->escape($report['ubicacion']) ?>
                                        </div>
                                        <?php if (!empty($report['descripcion'])): ?>
                                            <div class="text-muted mt-1">
                                                <small><?= $this->truncate($this->escape($report['descripcion']), 100) ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">
                                            <?= $this->formatDate($report['fecha_reporte']) ?>
                                        </small>
                                        <div class="mt-1">
                                            <a href="<?= $this->url('/reportes/' . $report['id']) ?>" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center mt-3">
                            <a href="<?= $this->url('/reportes') ?>" class="btn btn-outline-primary">
                                Ver Todos los Reportes
                                <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle text-muted mb-3" style="font-size: 3rem;"></i>
                            <h5 class="text-muted">Aún no tienes reportes</h5>
                            <p class="text-muted">Comienza tu viaje de reciclaje creando tu primer reporte.</p>
                            <a href="<?= $this->url('/reportes/crear') ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Crear Mi Primer Reporte
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Notificaciones -->
        <?php if (!empty($notifications)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bell me-2"></i>
                    Notificaciones
                </h5>
            </div>
            <div class="card-body">
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?= $notification['type'] ?>">
                        <div class="d-flex">
                            <div class="me-2">
                                <?php
                                $icons = [
                                    'info' => 'info-circle',
                                    'success' => 'check-circle',
                                    'warning' => 'exclamation-triangle',
                                    'achievement' => 'trophy'
                                ];
                                $icon = $icons[$notification['type']] ?? 'info-circle';
                                ?>
                                <i class="fas fa-<?= $icon ?>"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div><?= $this->escape($notification['message']) ?></div>
                                <?php if (isset($notification['action_url'])): ?>
                                    <div class="mt-2">
                                        <a href="<?= $this->escape($notification['action_url']) ?>" class="btn btn-sm btn-outline-primary">
                                            Ir
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Impacto Ambiental -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-leaf me-2"></i>
                    Mi Impacto Ambiental
                </h5>
            </div>
            <div class="card-body">
                <?php if (isset($user_stats['impacto_ambiental'])): ?>
                    <div class="text-center">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <h3 class="text-success mb-1">
                                    <?= $this->formatNumber($user_stats['impacto_ambiental']['co2_evitado']) ?> kg
                                </h3>
                                <small class="text-muted">CO₂ Evitado</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-primary mb-1">
                                    <?= $user_stats['impacto_ambiental']['arboles_equivalente'] ?>
                                </h4>
                                <small class="text-muted">Árboles</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-info mb-1">
                                    <?= $user_stats['impacto_ambiental']['autos_equivalente'] ?>
                                </h4>
                                <small class="text-muted">Autos</small>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center">
                        Comienza a reciclar para ver tu impacto ambiental.
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Ranking -->
        <?php if (isset($user_ranking)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-trophy me-2"></i>
                    Mi Ranking
                </h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-3">
                    <h2 class="text-warning">
                        #<?= $user_ranking['position'] ?>
                    </h2>
                    <small class="text-muted">
                        de <?= $this->formatNumber($user_ranking['total_users']) ?> usuarios
                    </small>
                </div>
                <div class="progress mb-2">
                    <div 
                        class="progress-bar bg-warning" 
                        style="width: <?= $user_ranking['percentile'] ?>%"
                    ></div>
                </div>
                <small class="text-muted">
                    Top <?= $user_ranking['percentile'] ?>%
                </small>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Próximos Objetivos -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-target me-2"></i>
                    Próximos Objetivos
                </h5>
            </div>
            <div class="card-body">
                <?php
                $totalReportes = $user_stats['total_reportes'] ?? 0;
                $objectives = [
                    ['target' => 10, 'label' => 'Primer Milestone'],
                    ['target' => 25, 'label' => 'Reciclador Activo'],
                    ['target' => 50, 'label' => 'Reciclador Dedicado'],
                    ['target' => 100, 'label' => 'Héroe Verde']
                ];
                
                $nextObjective = null;
                foreach ($objectives as $obj) {
                    if ($totalReportes < $obj['target']) {
                        $nextObjective = $obj;
                        break;
                    }
                }
                ?>
                
                <?php if ($nextObjective): ?>
                    <div class="text-center">
                        <h4><?= $nextObjective['label'] ?></h4>
                        <p class="text-muted">
                            <?= $nextObjective['target'] - $totalReportes ?> reportes más
                        </p>
                        <div class="progress mb-2">
                            <?php $progress = ($totalReportes / $nextObjective['target']) * 100; ?>
                            <div 
                                class="progress-bar bg-success" 
                                style="width: <?= min($progress, 100) ?>%"
                            ></div>
                        </div>
                        <small class="text-muted">
                            <?= round($progress) ?>% completado
                        </small>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <i class="fas fa-crown text-warning mb-2" style="font-size: 2rem;"></i>
                        <h5>¡Eres un Héroe Verde!</h5>
                        <p class="text-muted">Has alcanzado todos los objetivos disponibles.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
