<?php
$additional_css = '
<style>
    .stats-header {
        background: linear-gradient(135deg, #6f42c1, #e83e8c);
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
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .stat-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: var(--primary-color);
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: var(--primary-color);
        display: block;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: #6c757d;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.875rem;
    }
    
    .chart-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        margin-bottom: 2rem;
    }
    
    .chart-container {
        position: relative;
        height: 400px;
    }
    
    .impact-section {
        background: linear-gradient(135deg, #20c997, #17a2b8);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .impact-item {
        text-align: center;
        padding: 1rem;
    }
    
    .impact-number {
        font-size: 2rem;
        font-weight: bold;
        display: block;
        margin-bottom: 0.5rem;
    }
    
    .impact-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }
</style>
';

$inline_js = '
// Datos para gráficos
const chartData = ' . json_encode($chart_data ?? []) . ';

// Crear gráfico de materiales por tipo
function createMaterialsChart() {
    const ctx = document.getElementById("materialsChart");
    if (!ctx || !chartData.materiales_por_tipo) return;
    
    const data = chartData.materiales_por_tipo;
    
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
                borderWidth: 3,
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
                        usePointStyle: true,
                        font: {
                            size: 12
                        }
                    }
                }
            }
        }
    });
}

// Crear gráfico de actividad mensual
function createMonthlyChart() {
    const ctx = document.getElementById("monthlyChart");
    if (!ctx || !chartData.reportes_por_mes) return;
    
    const data = chartData.reportes_por_mes;
    
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
                fill: true,
                pointBackgroundColor: "#28a745",
                pointBorderColor: "#fff",
                pointBorderWidth: 2,
                pointRadius: 4
            }, {
                label: "Materiales (kg)",
                data: data.map(item => item.cantidad_materiales),
                borderColor: "#20c997",
                backgroundColor: "rgba(32, 201, 151, 0.1)",
                tension: 0.4,
                fill: true,
                pointBackgroundColor: "#20c997",
                pointBorderColor: "#fff",
                pointBorderWidth: 2,
                pointRadius: 4
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
                    beginAtZero: true,
                    grid: {
                        color: "rgba(0,0,0,0.1)"
                    }
                },
                x: {
                    grid: {
                        color: "rgba(0,0,0,0.1)"
                    }
                }
            }
        }
    });
}

// Crear gráfico de usuarios activos
function createUsersChart() {
    const ctx = document.getElementById("usersChart");
    if (!ctx || !chartData.usuarios_activos) return;
    
    const data = chartData.usuarios_activos;
    
    new Chart(ctx, {
        type: "bar",
        data: {
            labels: data.map(item => {
                const [year, month] = item.mes.split("-");
                return new Date(year, month - 1).toLocaleDateString("es", { month: "short" });
            }),
            datasets: [{
                label: "Usuarios Activos",
                data: data.map(item => item.usuarios_activos),
                backgroundColor: "rgba(111, 66, 193, 0.8)",
                borderColor: "#6f42c1",
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: "rgba(0,0,0,0.1)"
                    }
                },
                x: {
                    grid: {
                        color: "rgba(0,0,0,0.1)"
                    }
                }
            }
        }
    });
}

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", function() {
    createMaterialsChart();
    createMonthlyChart();
    createUsersChart();
    
    // Animar números
    const statNumbers = document.querySelectorAll(".stat-number");
    statNumbers.forEach(element => {
        const target = parseInt(element.getAttribute("data-target"));
        if (target) {
            animateNumber(element, target);
        }
    });
});

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
';
?>

<!-- Header -->
<div class="stats-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="mb-2">
                <i class="fas fa-chart-bar me-3"></i>
                Estadísticas y Análisis
            </h1>
            <p class="mb-0 opacity-75">
                Descubre el impacto colectivo de nuestras actividades de reciclaje
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="d-flex gap-2 justify-content-md-end">
                <a href="<?= $this->url('/estadisticas/advanced') ?>" class="btn btn-outline-light">
                    <i class="fas fa-chart-line me-1"></i>
                    Análisis Avanzado
                </a>
                <a href="<?= $this->url('/estadisticas/export') ?>" class="btn btn-light">
                    <i class="fas fa-download me-1"></i>
                    Exportar
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas Principales -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div class="stat-number" data-target="<?= $dashboard_stats['total_reportes'] ?? 0 ?>">0</div>
        <div class="stat-label">Reportes Totales</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-number" data-target="<?= $dashboard_stats['total_usuarios'] ?? 0 ?>">0</div>
        <div class="stat-label">Usuarios Registrados</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-weight"></i>
        </div>
        <div class="stat-number" data-target="<?= round($dashboard_stats['total_materiales'] ?? 0) ?>">0</div>
        <div class="stat-label">Kg Reciclados</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-number" data-target="<?= $dashboard_stats['reportes_hoy'] ?? 0 ?>">0</div>
        <div class="stat-label">Reportes Hoy</div>
    </div>
</div>

<!-- Impacto Ambiental -->
<?php if (!empty($environmental_impact)): ?>
<div class="impact-section">
    <h3 class="text-center mb-4">
        <i class="fas fa-leaf me-2"></i>
        Impacto Ambiental Colectivo
    </h3>
    
    <div class="row">
        <div class="col-md-3 col-6">
            <div class="impact-item">
                <span class="impact-number">
                    <?= $this->formatNumber(round($environmental_impact['total_co2_evitado'])) ?>
                </span>
                <div class="impact-label">kg CO₂ Evitado</div>
            </div>
        </div>
        
        <div class="col-md-3 col-6">
            <div class="impact-item">
                <span class="impact-number">
                    <?= $this->formatNumber($environmental_impact['equivalente_arboles']) ?>
                </span>
                <div class="impact-label">Árboles Equivalentes</div>
            </div>
        </div>
        
        <div class="col-md-3 col-6">
            <div class="impact-item">
                <span class="impact-number">
                    <?= $this->formatNumber($environmental_impact['equivalente_autos']) ?>
                </span>
                <div class="impact-label">Autos Fuera de Circulación</div>
            </div>
        </div>
        
        <div class="col-md-3 col-6">
            <div class="impact-item">
                <span class="impact-number">
                    <?= count($environmental_impact['impacto_por_material']) ?>
                </span>
                <div class="impact-label">Tipos de Material</div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Gráficos -->
<div class="row">
    <!-- Distribución de Materiales -->
    <div class="col-lg-6 mb-4">
        <div class="chart-card">
            <h5 class="mb-3">
                <i class="fas fa-chart-pie me-2 text-primary"></i>
                Distribución por Tipo de Material
            </h5>
            <div class="chart-container">
                <canvas id="materialsChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Actividad Mensual -->
    <div class="col-lg-6 mb-4">
        <div class="chart-card">
            <h5 class="mb-3">
                <i class="fas fa-chart-line me-2 text-primary"></i>
                Actividad por Mes
            </h5>
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Usuarios Activos -->
    <div class="col-lg-6 mb-4">
        <div class="chart-card">
            <h5 class="mb-3">
                <i class="fas fa-users me-2 text-primary"></i>
                Usuarios Activos por Mes
            </h5>
            <div class="chart-container">
                <canvas id="usersChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Top Ubicaciones -->
    <div class="col-lg-6 mb-4">
        <div class="chart-card">
            <h5 class="mb-3">
                <i class="fas fa-map-marker-alt me-2 text-primary"></i>
                Top Ubicaciones de Reciclaje
            </h5>
            
            <?php if (!empty($rankings['top_ubicaciones'])): ?>
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($rankings['top_ubicaciones'], 0, 8) as $index => $ubicacion): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold"><?= $this->escape($ubicacion['ubicacion']) ?></div>
                                <small class="text-muted">
                                    <?= $this->formatNumber($ubicacion['usuarios_unicos']) ?> usuarios únicos
                                </small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary rounded-pill">
                                    <?= $this->formatNumber($ubicacion['cantidad_reportes']) ?>
                                </span>
                                <br>
                                <small class="text-muted">
                                    <?= $this->formatNumber($ubicacion['cantidad_materiales']) ?> kg
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-muted text-center py-4">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay datos de ubicaciones disponibles.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Ranking de Usuarios -->
<?php if (!empty($rankings['top_usuarios'])): ?>
<div class="chart-card">
    <h5 class="mb-3">
        <i class="fas fa-trophy me-2 text-primary"></i>
        Ranking de Usuarios Más Activos
    </h5>
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Posición</th>
                    <th>Usuario</th>
                    <th>Reportes</th>
                    <th>Materiales (kg)</th>
                    <th>Puntos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rankings['top_usuarios'] as $index => $usuario): ?>
                    <tr>
                        <td>
                            <?php if ($index < 3): ?>
                                <i class="fas fa-medal text-<?= ['warning', 'secondary', 'warning'][$index] ?>"></i>
                            <?php endif; ?>
                            #<?= $index + 1 ?>
                        </td>
                        <td>
                            <strong><?= $this->escape($usuario['nombre']) ?></strong>
                        </td>
                        <td><?= $this->formatNumber($usuario['total_reportes']) ?></td>
                        <td><?= $this->formatNumber($usuario['total_materiales']) ?></td>
                        <td>
                            <span class="badge bg-success">
                                <?= $this->formatNumber($usuario['puntos_estimados']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Estadísticas del Usuario Actual -->
<?php if (!empty($user_stats)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>
                    Mis Estadísticas Personales
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-6 text-center mb-3">
                        <h3 class="text-primary"><?= $user_stats['total_reportes'] ?? 0 ?></h3>
                        <small class="text-muted">Mis Reportes</small>
                    </div>
                    <div class="col-md-3 col-6 text-center mb-3">
                        <h3 class="text-success"><?= round($user_stats['total_materiales'] ?? 0) ?> kg</h3>
                        <small class="text-muted">Material Reciclado</small>
                    </div>
                    <div class="col-md-3 col-6 text-center mb-3">
                        <h3 class="text-warning"><?= $user_stats['puntos'] ?? 0 ?></h3>
                        <small class="text-muted">Puntos Verdes</small>
                    </div>
                    <div class="col-md-3 col-6 text-center mb-3">
                        <h3 class="text-info">
                            <?php if (isset($user_stats['ranking_position'])): ?>
                                #<?= $user_stats['ranking_position'] ?>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </h3>
                        <small class="text-muted">Mi Posición</small>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="<?= $this->url('/dashboard') ?>" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        Ver Mi Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
