<?php
/**
 * EcoCusco - Dashboard Principal
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

// Obtener estadísticas del dashboard
$stats = [];
try {
    $db = Database::getInstance();
    
    // Estadísticas básicas (simuladas por ahora - en una implementación real vendrían de la BD)
    $stats = [
        'reportes_mes' => 15,
        'kg_reciclados' => 245.5,
        'puntos_acumulados' => 1250,
        'nivel_usuario' => 'Bronce',
        'posicion_ranking' => 47,
        'total_usuarios' => 2340
    ];
    
    // Últimos reportes del usuario (ejemplo)
    $ultimosReportes = [
        [
            'id' => 1,
            'tipo_residuo' => 'Plástico',
            'cantidad' => '5.2 kg',
            'fecha' => '2025-06-01',
            'estado' => 'Procesado',
            'puntos' => 52
        ],
        [
            'id' => 2,
            'tipo_residuo' => 'Papel',
            'cantidad' => '3.8 kg',
            'fecha' => '2025-05-28',
            'estado' => 'Pendiente',
            'puntos' => 38
        ],
        [
            'id' => 3,
            'tipo_residuo' => 'Vidrio',
            'cantidad' => '2.1 kg',
            'fecha' => '2025-05-25',
            'estado' => 'Procesado',
            'puntos' => 31
        ]
    ];
    
} catch (Exception $e) {
    error_log("Error cargando estadísticas del dashboard: " . $e->getMessage());
    setFlashMessage('warning', 'Algunos datos podrían no estar actualizados.');
}

// Configurar breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard']
];

$pageTitle = 'Dashboard - ' . APP_NAME;
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
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="bg-success text-white rounded p-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h1 class="h3 mb-2">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                ¡Bienvenido, <?= e($user['nombre']) ?>!
                            </h1>
                            <p class="mb-3 opacity-75">
                                Gestiona tus reportes de reciclaje y contribuye al desarrollo sostenible de Cusco
                            </p>
                            <div class="d-flex gap-2">
                                <a href="nuevo-reporte.php" class="btn btn-light">
                                    <i class="fas fa-plus me-2"></i>Nuevo Reporte
                                </a>
                                <a href="reportes.php" class="btn btn-outline-light">
                                    <i class="fas fa-chart-bar me-2"></i>Ver Reportes
                                </a>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="bg-white bg-opacity-20 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                <i class="fas fa-user fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas Principales -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fas fa-file-alt text-primary fa-xl"></i>
                            </div>
                            <div>
                                <h6 class="card-title text-muted mb-1">Reportes este mes</h6>
                                <h3 class="mb-0 text-primary"><?= formatNumber($stats['reportes_mes'], 0) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fas fa-recycle text-success fa-xl"></i>
                            </div>
                            <div>
                                <h6 class="card-title text-muted mb-1">Kg Reciclados</h6>
                                <h3 class="mb-0 text-success"><?= formatNumber($stats['kg_reciclados']) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fas fa-star text-warning fa-xl"></i>
                            </div>
                            <div>
                                <h6 class="card-title text-muted mb-1">Puntos Acumulados</h6>
                                <h3 class="mb-0 text-warning"><?= formatNumber($stats['puntos_acumulados'], 0) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                                <i class="fas fa-trophy text-info fa-xl"></i>
                            </div>
                            <div>
                                <h6 class="card-title text-muted mb-1">Nivel</h6>
                                <h3 class="mb-0 text-info"><?= e($stats['nivel_usuario']) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Gráfico de Reciclaje -->
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-line me-2 text-success"></i>
                            Progreso de Reciclaje Mensual
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="recyclingChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Actividad Reciente -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock me-2 text-primary"></i>
                            Actividad Reciente
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($ultimosReportes as $reporte): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?= e($reporte['tipo_residuo']) ?></h6>
                                            <p class="mb-1 text-muted small">
                                                <?= e($reporte['cantidad']) ?> • <?= formatDate($reporte['fecha'], 'd/m/Y') ?>
                                            </p>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-<?= $reporte['estado'] === 'Procesado' ? 'success' : 'warning' ?>">
                                                    <?= e($reporte['estado']) ?>
                                                </span>
                                                <small class="text-muted">
                                                    +<?= $reporte['puntos'] ?> puntos
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="card-footer bg-white text-center">
                            <a href="reportes.php" class="btn btn-sm btn-outline-primary">
                                Ver todos los reportes
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ranking y Metas -->
        <div class="row">
            <!-- Ranking -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-medal me-2 text-warning"></i>
                            Tu Posición en el Ranking
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="row">
                            <div class="col-6">
                                <div class="border-end">
                                    <h2 class="text-warning mb-1">#<?= $stats['posicion_ranking'] ?></h2>
                                    <p class="text-muted mb-0">Tu posición</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <h2 class="text-muted mb-1"><?= formatNumber($stats['total_usuarios'], 0) ?></h2>
                                <p class="text-muted mb-0">Total usuarios</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="ranking.php" class="btn btn-warning">
                                <i class="fas fa-trophy me-2"></i>Ver Ranking Completo
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt me-2 text-danger"></i>
                            Acciones Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="nuevo-reporte.php" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>Crear Nuevo Reporte
                            </a>
                            <a href="estadisticas.php" class="btn btn-info">
                                <i class="fas fa-chart-pie me-2"></i>Ver Estadísticas
                            </a>
                            <a href="perfil.php" class="btn btn-outline-primary">
                                <i class="fas fa-user me-2"></i>Editar Perfil
                            </a>
                            <?php if (isAdmin()): ?>
                                <a href="admin/panel.php" class="btn btn-outline-danger">
                                    <i class="fas fa-cogs me-2"></i>Panel de Administración
                                </a>
                            <?php endif; ?>
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
        // Configurar gráfico de reciclaje
        const ctx = document.getElementById('recyclingChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Kg Reciclados',
                        data: [20, 35, 45, 30, 55, 65],
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
                    },
                    elements: {
                        point: {
                            hoverRadius: 8
                        }
                    }
                }
            });
        }
        
        // Auto-refresh de datos cada 5 minutos (opcional)
        setInterval(function() {
            // Aquí se podría implementar una actualización automática vía AJAX
            console.log('Auto-refresh de datos del dashboard');
        }, 300000); // 5 minutos
    });
    </script>
</body>
</html>
