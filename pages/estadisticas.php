<?php
/**
 * EcoCusco - Página de Estadísticas
 */

// Incluir configuración
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Requerir autenticación
requireAuth();

// Obtener usuario actual
$user = getCurrentUser();
if (!$user) {
    setFlashMessage('error', 'Error al cargar datos del usuario.');
    redirect('../pages/login.php');
}

// Obtener periodo seleccionado
$periodo = $_GET['periodo'] ?? 'mes';
$periodos_validos = ['semana', 'mes', 'trimestre', 'año'];
if (!in_array($periodo, $periodos_validos)) {
    $periodo = 'mes';
}

// Generar datos de estadísticas (en implementación real vendrían de la BD)
try {
    $db = Database::getInstance();
    
    // Estadísticas generales
    $estadisticas = [
        'total_reportes' => 25,
        'total_kg_reciclados' => 156.8,
        'puntos_acumulados' => 1568,
        'reportes_procesados' => 20,
        'reportes_pendientes' => 3,
        'reportes_rechazados' => 2,
        'promedio_kg_mes' => 52.3,
        'mejor_mes' => 'Mayo 2025'
    ];
    
    // Datos para gráficos por tipo de residuo
    $residuosPorTipo = [
        'Plástico' => ['cantidad' => 45.2, 'porcentaje' => 28.8, 'reportes' => 8],
        'Papel' => ['cantidad' => 38.7, 'porcentaje' => 24.7, 'reportes' => 6],
        'Orgánico' => ['cantidad' => 35.1, 'porcentaje' => 22.4, 'reportes' => 7],
        'Vidrio' => ['cantidad' => 25.3, 'porcentaje' => 16.1, 'reportes' => 3],
        'Metal' => ['cantidad' => 12.5, 'porcentaje' => 8.0, 'reportes' => 1]
    ];
    
    // Datos temporales según el periodo
    $datosTemporal = match($periodo) {
        'semana' => [
            'labels' => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
            'datos' => [5.2, 3.8, 7.1, 4.5, 6.3, 2.9, 8.7]
        ],
        'mes' => [
            'labels' => ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
            'datos' => [35.2, 42.1, 38.7, 40.8]
        ],
        'trimestre' => [
            'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
            'datos' => [25.3, 32.1, 28.7, 35.4, 42.8, 38.9]
        ],
        'año' => [
            'labels' => ['2023', '2024', '2025'],
            'datos' => [180.5, 220.3, 156.8]
        ]
    };
    
    // Ranking de usuarios (simulado)
    $ranking = [
        ['posicion' => 1, 'nombre' => 'María García', 'puntos' => 2450, 'kg' => 245.0],
        ['posicion' => 2, 'nombre' => 'Carlos Mendoza', 'puntos' => 2180, 'kg' => 218.0],
        ['posicion' => 3, 'nombre' => 'Ana Rodríguez', 'puntos' => 1890, 'kg' => 189.0],
        ['posicion' => 4, 'nombre' => 'José Quispe', 'puntos' => 1750, 'kg' => 175.0],
        ['posicion' => 5, 'nombre' => $user['nombre'] . ' ' . $user['apellido'], 'puntos' => 1568, 'kg' => 156.8, 'actual' => true]
    ];
    
} catch (Exception $e) {
    error_log("Error cargando estadísticas: " . $e->getMessage());
    setFlashMessage('error', 'Error al cargar las estadísticas.');
    $estadisticas = [];
    $residuosPorTipo = [];
}

// Configurar breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php'],
    ['title' => 'Estadísticas']
];

$pageTitle = 'Estadísticas - ' . APP_NAME;
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
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link href="../public/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../components/header.php'; ?>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-2">
                            <i class="fas fa-chart-pie me-2 text-info"></i>
                            Estadísticas de Reciclaje
                        </h1>
                        <p class="text-muted">Análisis detallado de tu actividad de reciclaje</p>
                    </div>
                    <div class="d-flex gap-2">
                        <!-- Selector de período -->
                        <div class="btn-group" role="group">
                            <?php foreach ($periodos_validos as $p): ?>
                                <a href="?periodo=<?= $p ?>" 
                                   class="btn btn-<?= $periodo === $p ? 'primary' : 'outline-primary' ?> btn-sm">
                                    <?= ucfirst($p) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <!-- Botón de exportar -->
                        <div class="dropdown">
                            <button class="btn btn-success btn-sm dropdown-toggle" type="button" 
                                    id="exportDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-1"></i>Exportar
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="exportarPDF()">
                                    <i class="fas fa-file-pdf me-2"></i>Reporte PDF
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="exportarExcel()">
                                    <i class="fas fa-file-excel me-2"></i>Datos Excel
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Principales -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card border-0 shadow-sm bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-file-alt fa-2x opacity-75"></i>
                            </div>
                            <div>
                                <h6 class="card-title opacity-75 mb-1">Total Reportes</h6>
                                <h3 class="mb-0"><?= formatNumber($estadisticas['total_reportes'], 0) ?></h3>
                                <small class="opacity-75">
                                    <i class="fas fa-arrow-up me-1"></i>+3 este mes
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card border-0 shadow-sm bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-recycle fa-2x opacity-75"></i>
                            </div>
                            <div>
                                <h6 class="card-title opacity-75 mb-1">Kg Reciclados</h6>
                                <h3 class="mb-0"><?= formatNumber($estadisticas['total_kg_reciclados']) ?></h3>
                                <small class="opacity-75">
                                    <i class="fas fa-arrow-up me-1"></i>Promedio: <?= formatNumber($estadisticas['promedio_kg_mes']) ?>/mes
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card border-0 shadow-sm bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-star fa-2x opacity-75"></i>
                            </div>
                            <div>
                                <h6 class="card-title opacity-75 mb-1">Puntos Acumulados</h6>
                                <h3 class="mb-0"><?= formatNumber($estadisticas['puntos_acumulados'], 0) ?></h3>
                                <small class="opacity-75">
                                    <i class="fas fa-medal me-1"></i>Nivel Bronce
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card border-0 shadow-sm bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-percentage fa-2x opacity-75"></i>
                            </div>
                            <div>
                                <h6 class="card-title opacity-75 mb-1">Tasa de Éxito</h6>
                                <h3 class="mb-0"><?= round(($estadisticas['reportes_procesados'] / $estadisticas['total_reportes']) * 100) ?>%</h3>
                                <small class="opacity-75">
                                    <?= $estadisticas['reportes_procesados'] ?>/<?= $estadisticas['total_reportes'] ?> procesados
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos Principales -->
        <div class="row mb-4">
            <!-- Evolución Temporal -->
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-line me-2 text-primary"></i>
                            Evolución de Reciclaje - <?= ucfirst($periodo) ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="evolucionChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Distribución por Tipo -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie me-2 text-success"></i>
                            Distribución por Tipo
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="tiposChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalles por Tipo de Residuo -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-trash me-2 text-warning"></i>
                            Detalles por Tipo de Residuo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($residuosPorTipo as $tipo => $datos): ?>
                                <div class="col-md-6 col-lg-4 col-xl mb-3">
                                    <div class="card border h-100">
                                        <div class="card-body text-center">
                                            <?php
                                            $iconos = [
                                                'Plástico' => 'fas fa-bottle-water text-primary',
                                                'Papel' => 'fas fa-file-alt text-info',
                                                'Vidrio' => 'fas fa-wine-bottle text-success',
                                                'Metal' => 'fas fa-cog text-secondary',
                                                'Orgánico' => 'fas fa-leaf text-success'
                                            ];
                                            $icono = $iconos[$tipo] ?? 'fas fa-trash';
                                            ?>
                                            <i class="<?= $icono ?> fa-2x mb-3"></i>
                                            <h6 class="card-title"><?= e($tipo) ?></h6>
                                            <h4 class="text-primary mb-2"><?= formatNumber($datos['cantidad']) ?> kg</h4>
                                            <p class="card-text">
                                                <span class="badge bg-light text-dark"><?= formatNumber($datos['porcentaje']) ?>%</span>
                                                <br>
                                                <small class="text-muted"><?= $datos['reportes'] ?> reportes</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estado de Reportes y Ranking -->
        <div class="row">
            <!-- Estado de Reportes -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tasks me-2 text-info"></i>
                            Estado de Reportes
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="estadoChart" height="250"></canvas>
                        <div class="mt-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <h6 class="text-success"><?= $estadisticas['reportes_procesados'] ?></h6>
                                    <small class="text-muted">Procesados</small>
                                </div>
                                <div class="col-4">
                                    <h6 class="text-warning"><?= $estadisticas['reportes_pendientes'] ?></h6>
                                    <small class="text-muted">Pendientes</small>
                                </div>
                                <div class="col-4">
                                    <h6 class="text-danger"><?= $estadisticas['reportes_rechazados'] ?></h6>
                                    <small class="text-muted">Rechazados</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ranking -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-trophy me-2 text-warning"></i>
                            Top 5 Recicladores
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($ranking as $usuario): ?>
                                <div class="list-group-item <?= isset($usuario['actual']) ? 'bg-light' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <?php if ($usuario['posicion'] <= 3): ?>
                                                    <?php 
                                                    $medals = [1 => 'gold', 2 => 'silver', 3 => '#cd7f32'];
                                                    $color = $medals[$usuario['posicion']];
                                                    ?>
                                                    <i class="fas fa-medal fa-lg" style="color: <?= $color ?>"></i>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?= $usuario['posicion'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 <?= isset($usuario['actual']) ? 'fw-bold' : '' ?>">
                                                    <?= e($usuario['nombre']) ?>
                                                    <?= isset($usuario['actual']) ? '<span class="text-primary">(Tú)</span>' : '' ?>
                                                </h6>
                                                <small class="text-muted"><?= formatNumber($usuario['kg']) ?> kg reciclados</small>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="fw-bold text-warning"><?= formatNumber($usuario['puntos'], 0) ?></span>
                                            <br>
                                            <small class="text-muted">puntos</small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-footer bg-white text-center">
                            <a href="ranking.php" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-trophy me-1"></i>Ver Ranking Completo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de Mejores Logros -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-award me-2 text-success"></i>
                            Logros y Metas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="fas fa-calendar-check text-success fa-2x"></i>
                                </div>
                                <h6>Mejor Mes</h6>
                                <p class="text-muted"><?= e($estadisticas['mejor_mes']) ?></p>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="fas fa-target text-info fa-2x"></i>
                                </div>
                                <h6>Meta Mensual</h6>
                                <div class="progress mb-2" style="height: 10px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: 75%"></div>
                                </div>
                                <p class="text-muted">75% completado</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="fas fa-trophy text-warning fa-2x"></i>
                                </div>
                                <h6>Próximo Nivel</h6>
                                <div class="progress mb-2" style="height: 10px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 60%"></div>
                                </div>
                                <p class="text-muted">432 puntos para Plata</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Datos para los gráficos
        const datosTemporal = <?= json_encode($datosTemporal) ?>;
        const residuosPorTipo = <?= json_encode($residuosPorTipo) ?>;
        const estadisticas = <?= json_encode($estadisticas) ?>;
        
        // Configuración común para gráficos
        Chart.defaults.font.family = 'system-ui, -apple-system, sans-serif';
        Chart.defaults.color = '#6c757d';
        
        // Gráfico de evolución temporal
        const ctxEvolucion = document.getElementById('evolucionChart');
        if (ctxEvolucion) {
            new Chart(ctxEvolucion, {
                type: 'line',
                data: {
                    labels: datosTemporal.labels,
                    datasets: [{
                        label: 'Kg Reciclados',
                        data: datosTemporal.datos,
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#198754',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6
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
                                color: 'rgba(0,0,0,0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value + ' kg';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
        
        // Gráfico de tipos de residuo
        const ctxTipos = document.getElementById('tiposChart');
        if (ctxTipos) {
            const tipos = Object.keys(residuosPorTipo);
            const cantidades = Object.values(residuosPorTipo).map(r => r.cantidad);
            const colores = ['#0d6efd', '#17a2b8', '#198754', '#6c757d', '#fd7e14'];
            
            new Chart(ctxTipos, {
                type: 'doughnut',
                data: {
                    labels: tipos,
                    datasets: [{
                        data: cantidades,
                        backgroundColor: colores,
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        }
        
        // Gráfico de estado de reportes
        const ctxEstado = document.getElementById('estadoChart');
        if (ctxEstado) {
            new Chart(ctxEstado, {
                type: 'doughnut',
                data: {
                    labels: ['Procesados', 'Pendientes', 'Rechazados'],
                    datasets: [{
                        data: [
                            estadisticas.reportes_procesados,
                            estadisticas.reportes_pendientes,
                            estadisticas.reportes_rechazados
                        ],
                        backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true
                            }
                        }
                    }
                }
            });
        }
    });
    
    // Funciones de exportación
    function exportarPDF() {
        alert('Exportación a PDF próximamente...');
    }
    
    function exportarExcel() {
        alert('Exportación a Excel próximamente...');
    }
    </script>
</body>
</html>
