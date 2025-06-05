<?php
/**
 * EcoCusco - Página de Reportes
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

// Configuración de paginación
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

// Filtros
$filters = [
    'tipo_residuo' => $_GET['tipo_residuo'] ?? '',
    'estado' => $_GET['estado'] ?? '',
    'fecha_desde' => $_GET['fecha_desde'] ?? '',
    'fecha_hasta' => $_GET['fecha_hasta'] ?? ''
];

// Obtener reportes del usuario
$reportes = [];
$totalReportes = 0;

try {
    $db = Database::getInstance();
    
    // Por ahora, datos simulados (en implementación real vendrían de la BD)
    $reportesSimulados = [
        [
            'id' => 1,
            'tipo_residuo' => 'Plástico',
            'descripcion' => 'Botellas de plástico PET',
            'cantidad' => 5.2,
            'unidad' => 'kg',
            'fecha_reporte' => '2025-06-01 14:30:00',
            'ubicacion' => 'San Blas, Cusco',
            'estado' => 'Procesado',
            'puntos_obtenidos' => 52,
            'notas' => 'Recolección exitosa'
        ],
        [
            'id' => 2,
            'tipo_residuo' => 'Papel',
            'descripcion' => 'Papel de oficina y periódicos',
            'cantidad' => 3.8,
            'unidad' => 'kg',
            'fecha_reporte' => '2025-05-28 09:15:00',
            'ubicacion' => 'Centro Histórico, Cusco',
            'estado' => 'Pendiente',
            'puntos_obtenidos' => 0,
            'notas' => 'En proceso de verificación'
        ],
        [
            'id' => 3,
            'tipo_residuo' => 'Vidrio',
            'descripcion' => 'Botellas de vidrio transparente',
            'cantidad' => 2.1,
            'unidad' => 'kg',
            'fecha_reporte' => '2025-05-25 16:45:00',
            'ubicacion' => 'Wanchaq, Cusco',
            'estado' => 'Procesado',
            'puntos_obtenidos' => 31,
            'notas' => 'Excelente separación'
        ],
        [
            'id' => 4,
            'tipo_residuo' => 'Metal',
            'descripcion' => 'Latas de aluminio',
            'cantidad' => 1.5,
            'unidad' => 'kg',
            'fecha_reporte' => '2025-05-20 11:20:00',
            'ubicacion' => 'Santiago, Cusco',
            'estado' => 'Rechazado',
            'puntos_obtenidos' => 0,
            'notas' => 'Material contaminado'
        ],
        [
            'id' => 5,
            'tipo_residuo' => 'Orgánico',
            'descripcion' => 'Restos de comida para compostaje',
            'cantidad' => 8.7,
            'unidad' => 'kg',
            'fecha_reporte' => '2025-05-18 07:30:00',
            'ubicacion' => 'San Sebastián, Cusco',
            'estado' => 'Procesado',
            'puntos_obtenidos' => 87,
            'notas' => 'Perfecto para compostaje'
        ]
    ];
    
    // Aplicar filtros (simulación)
    $reportesFiltrados = array_filter($reportesSimulados, function($reporte) use ($filters) {
        if ($filters['tipo_residuo'] && $reporte['tipo_residuo'] !== $filters['tipo_residuo']) {
            return false;
        }
        if ($filters['estado'] && $reporte['estado'] !== $filters['estado']) {
            return false;
        }
        if ($filters['fecha_desde'] && date('Y-m-d', strtotime($reporte['fecha_reporte'])) < $filters['fecha_desde']) {
            return false;
        }
        if ($filters['fecha_hasta'] && date('Y-m-d', strtotime($reporte['fecha_reporte'])) > $filters['fecha_hasta']) {
            return false;
        }
        return true;
    });
    
    $totalReportes = count($reportesFiltrados);
    $reportes = array_slice($reportesFiltrados, $offset, $limit);
    
} catch (Exception $e) {
    error_log("Error cargando reportes: " . $e->getMessage());
    setFlashMessage('error', 'Error al cargar los reportes.');
}

// Calcular paginación
$totalPages = ceil($totalReportes / $limit);

// Configurar breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => 'dashboard.php'],
    ['title' => 'Reportes']
];

$pageTitle = 'Mis Reportes - ' . APP_NAME;
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
                            <i class="fas fa-chart-bar me-2 text-primary"></i>
                            Mis Reportes
                        </h1>
                        <p class="text-muted">Gestiona y revisa todos tus reportes de reciclaje</p>
                    </div>
                    <div>
                        <a href="nuevo-reporte.php" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Nuevo Reporte
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-filter me-2"></i>
                            Filtros de Búsqueda
                        </h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="" id="filterForm">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="tipo_residuo" class="form-label">Tipo de Residuo</label>
                                    <select class="form-select" id="tipo_residuo" name="tipo_residuo">
                                        <option value="">Todos los tipos</option>
                                        <option value="Plástico" <?= $filters['tipo_residuo'] === 'Plástico' ? 'selected' : '' ?>>Plástico</option>
                                        <option value="Papel" <?= $filters['tipo_residuo'] === 'Papel' ? 'selected' : '' ?>>Papel</option>
                                        <option value="Vidrio" <?= $filters['tipo_residuo'] === 'Vidrio' ? 'selected' : '' ?>>Vidrio</option>
                                        <option value="Metal" <?= $filters['tipo_residuo'] === 'Metal' ? 'selected' : '' ?>>Metal</option>
                                        <option value="Orgánico" <?= $filters['tipo_residuo'] === 'Orgánico' ? 'selected' : '' ?>>Orgánico</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="">Todos los estados</option>
                                        <option value="Pendiente" <?= $filters['estado'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="Procesado" <?= $filters['estado'] === 'Procesado' ? 'selected' : '' ?>>Procesado</option>
                                        <option value="Rechazado" <?= $filters['estado'] === 'Rechazado' ? 'selected' : '' ?>>Rechazado</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="fecha_desde" class="form-label">Desde</label>
                                    <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                                           value="<?= e($filters['fecha_desde']) ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="fecha_hasta" class="form-label">Hasta</label>
                                    <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                                           value="<?= e($filters['fecha_hasta']) ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-search me-1"></i>Filtrar
                                        </button>
                                        <a href="reportes.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i>Limpiar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen de Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <i class="fas fa-file-alt text-primary fa-2x mb-2"></i>
                        <h5 class="text-primary"><?= count($reportesSimulados) ?></h5>
                        <small class="text-muted">Total Reportes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                        <h5 class="text-success"><?= count(array_filter($reportesSimulados, fn($r) => $r['estado'] === 'Procesado')) ?></h5>
                        <small class="text-muted">Procesados</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <i class="fas fa-clock text-warning fa-2x mb-2"></i>
                        <h5 class="text-warning"><?= count(array_filter($reportesSimulados, fn($r) => $r['estado'] === 'Pendiente')) ?></h5>
                        <small class="text-muted">Pendientes</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm text-center">
                    <div class="card-body">
                        <i class="fas fa-star text-info fa-2x mb-2"></i>
                        <h5 class="text-info"><?= array_sum(array_column($reportesSimulados, 'puntos_obtenidos')) ?></h5>
                        <small class="text-muted">Puntos Total</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Reportes -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-list me-2"></i>
                                Lista de Reportes (<?= $totalReportes ?> resultados)
                            </h6>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" 
                                        id="exportDropdown" data-bs-toggle="dropdown">
                                    <i class="fas fa-download me-1"></i>Exportar
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="exportToPDF()">
                                        <i class="fas fa-file-pdf me-2"></i>PDF
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportToExcel()">
                                        <i class="fas fa-file-excel me-2"></i>Excel
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($reportes)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No se encontraron reportes</h5>
                                <p class="text-muted">Prueba ajustando los filtros o crea tu primer reporte.</p>
                                <a href="nuevo-reporte.php" class="btn btn-success">
                                    <i class="fas fa-plus me-2"></i>Crear Primer Reporte
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Tipo de Residuo</th>
                                            <th>Cantidad</th>
                                            <th>Fecha</th>
                                            <th>Ubicación</th>
                                            <th>Estado</th>
                                            <th>Puntos</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportes as $reporte): ?>
                                            <tr>
                                                <td class="fw-medium">#<?= $reporte['id'] ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php
                                                        $iconos = [
                                                            'Plástico' => 'fas fa-bottle-water text-primary',
                                                            'Papel' => 'fas fa-file-alt text-info',
                                                            'Vidrio' => 'fas fa-wine-bottle text-success',
                                                            'Metal' => 'fas fa-cog text-secondary',
                                                            'Orgánico' => 'fas fa-leaf text-success'
                                                        ];
                                                        $icono = $iconos[$reporte['tipo_residuo']] ?? 'fas fa-trash';
                                                        ?>
                                                        <i class="<?= $icono ?> me-2"></i>
                                                        <?= e($reporte['tipo_residuo']) ?>
                                                    </div>
                                                </td>
                                                <td><?= formatNumber($reporte['cantidad']) ?> <?= e($reporte['unidad']) ?></td>
                                                <td><?= formatDate($reporte['fecha_reporte']) ?></td>
                                                <td>
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        <?= e($reporte['ubicacion']) ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php
                                                    $badgeClass = match($reporte['estado']) {
                                                        'Procesado' => 'bg-success',
                                                        'Pendiente' => 'bg-warning',
                                                        'Rechazado' => 'bg-danger',
                                                        default => 'bg-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge <?= $badgeClass ?>">
                                                        <?= e($reporte['estado']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-medium text-success">
                                                        +<?= $reporte['puntos_obtenidos'] ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                                onclick="verDetalles(<?= $reporte['id'] ?>)" 
                                                                title="Ver detalles">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if ($reporte['estado'] === 'Pendiente'): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                    onclick="editarReporte(<?= $reporte['id'] ?>)" 
                                                                    title="Editar">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            <?php if ($totalPages > 1): ?>
                                <div class="card-footer bg-white">
                                    <nav aria-label="Paginación de reportes">
                                        <ul class="pagination pagination-sm justify-content-center mb-0">
                                            <?php if ($page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                                        <?= $i ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if ($page < $totalPages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para detalles del reporte -->
    <div class="modal fade" id="detallesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>
                        Detalles del Reporte
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detallesContent">
                    <!-- Contenido cargado dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../components/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
    // Ver detalles del reporte
    function verDetalles(reporteId) {
        // Simular carga de detalles (en implementación real sería una llamada AJAX)
        const reportes = <?= json_encode($reportesSimulados) ?>;
        const reporte = reportes.find(r => r.id === reporteId);
        
        if (reporte) {
            document.getElementById('detallesContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Información Básica</h6>
                        <table class="table table-sm">
                            <tr><td><strong>ID:</strong></td><td>#${reporte.id}</td></tr>
                            <tr><td><strong>Tipo:</strong></td><td>${reporte.tipo_residuo}</td></tr>
                            <tr><td><strong>Cantidad:</strong></td><td>${reporte.cantidad} ${reporte.unidad}</td></tr>
                            <tr><td><strong>Estado:</strong></td><td><span class="badge bg-${reporte.estado === 'Procesado' ? 'success' : (reporte.estado === 'Pendiente' ? 'warning' : 'danger')}">${reporte.estado}</span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Detalles Adicionales</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Fecha:</strong></td><td>${new Date(reporte.fecha_reporte).toLocaleString('es-PE')}</td></tr>
                            <tr><td><strong>Ubicación:</strong></td><td>${reporte.ubicacion}</td></tr>
                            <tr><td><strong>Puntos:</strong></td><td>+${reporte.puntos_obtenidos}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <h6>Descripción</h6>
                        <p class="text-muted">${reporte.descripcion}</p>
                        <h6>Notas</h6>
                        <p class="text-muted">${reporte.notas}</p>
                    </div>
                </div>
            `;
            
            new bootstrap.Modal(document.getElementById('detallesModal')).show();
        }
    }

    // Editar reporte
    function editarReporte(reporteId) {
        window.location.href = `editar-reporte.php?id=${reporteId}`;
    }

    // Exportar a PDF
    function exportToPDF() {
        alert('Función de exportación a PDF próximamente...');
    }

    // Exportar a Excel
    function exportToExcel() {
        alert('Función de exportación a Excel próximamente...');
    }

    // Auto-envío del formulario cuando cambian los filtros
    document.addEventListener('DOMContentLoaded', function() {
        const filterInputs = document.querySelectorAll('#filterForm select, #filterForm input');
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                // Optional: auto-submit on filter change
                // document.getElementById('filterForm').submit();
            });
        });
    });
    </script>
</body>
</html>
