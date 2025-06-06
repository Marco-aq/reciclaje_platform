<?php
$additional_css = '
<style>
    .reports-header {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .filter-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .report-card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }
    
    .report-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .report-header {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-radius: 10px 10px 0 0;
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
    }
    
    .material-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-weight: 600;
    }
    
    .report-image {
        max-width: 100px;
        max-height: 100px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .stats-bar {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 2rem;
    }
    
    .stat-item {
        text-align: center;
        padding: 0.5rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
        font-weight: bold;
        color: var(--primary-color);
        display: block;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
</style>
';
?>

<!-- Header -->
<div class="reports-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="mb-2">
                <i class="fas fa-clipboard-list me-3"></i>
                Mis Reportes de Reciclaje
            </h1>
            <p class="mb-0 opacity-75">
                Gestiona y revisa todos tus reportes de actividades de reciclaje
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="<?= $this->url('/reportes/crear') ?>" class="btn btn-light btn-lg">
                <i class="fas fa-plus me-2"></i>
                Nuevo Reporte
            </a>
        </div>
    </div>
</div>

<!-- Estadísticas Rápidas -->
<div class="stats-bar">
    <div class="row">
        <div class="col-md-3 col-6">
            <div class="stat-item">
                <span class="stat-number"><?= $pagination['total'] ?></span>
                <div class="stat-label">Total Reportes</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-item">
                <span class="stat-number">
                    <?= count(array_unique(array_column($reports, 'tipo_material'))) ?>
                </span>
                <div class="stat-label">Tipos de Material</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-item">
                <span class="stat-number">
                    <?= $this->formatNumber(array_sum(array_column($reports, 'cantidad'))) ?>
                </span>
                <div class="stat-label">Kg Totales</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-item">
                <span class="stat-number">
                    <?= count(array_unique(array_column($reports, 'ubicacion'))) ?>
                </span>
                <div class="stat-label">Ubicaciones</div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="filter-card">
    <h5 class="mb-3">
        <i class="fas fa-filter me-2"></i>
        Filtros de Búsqueda
    </h5>
    
    <form method="GET" action="<?= $this->url('/reportes') ?>">
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="tipo_material" class="form-label">Tipo de Material</label>
                <select class="form-select" id="tipo_material" name="tipo_material">
                    <option value="">Todos los tipos</option>
                    <?php foreach ($tipos_materiales as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $filters['tipo_material'] === $value ? 'selected' : '' ?>>
                            <?= $this->escape($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="fecha_desde" class="form-label">Fecha Desde</label>
                <input 
                    type="date" 
                    class="form-control" 
                    id="fecha_desde" 
                    name="fecha_desde"
                    value="<?= $this->escape($filters['fecha_desde']) ?>"
                >
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                <input 
                    type="date" 
                    class="form-control" 
                    id="fecha_hasta" 
                    name="fecha_hasta"
                    value="<?= $this->escape($filters['fecha_hasta']) ?>"
                >
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="ubicacion" class="form-label">Ubicación</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="ubicacion" 
                    name="ubicacion" 
                    placeholder="Buscar por ubicación..."
                    value="<?= $this->escape($filters['ubicacion']) ?>"
                >
            </div>
        </div>
        
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search me-1"></i>
                Filtrar
            </button>
            <a href="<?= $this->url('/reportes') ?>" class="btn btn-outline-secondary">
                <i class="fas fa-times me-1"></i>
                Limpiar
            </a>
            <a href="<?= $this->url('/reportes/export') ?>" class="btn btn-outline-success ms-auto">
                <i class="fas fa-download me-1"></i>
                Exportar
            </a>
        </div>
    </form>
</div>

<!-- Lista de Reportes -->
<?php if (!empty($reports)): ?>
    <div class="row">
        <?php foreach ($reports as $report): ?>
            <div class="col-lg-6 mb-3">
                <div class="report-card card h-100">
                    <div class="report-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="material-badge bg-primary text-white">
                                    <?= ucfirst($this->escape($report['tipo_material'])) ?>
                                </span>
                                <strong class="ms-2"><?= $this->formatNumber($report['cantidad']) ?> kg</strong>
                            </div>
                            <div class="text-muted">
                                <small><?= $this->formatDate($report['fecha_reporte']) ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-8">
                                <h6 class="card-title">
                                    <i class="fas fa-map-marker-alt me-1 text-muted"></i>
                                    <?= $this->escape($report['ubicacion']) ?>
                                </h6>
                                
                                <?php if (!empty($report['descripcion'])): ?>
                                    <p class="card-text text-muted">
                                        <?= $this->truncate($this->escape($report['descripcion']), 120) ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="d-flex gap-1 mt-3">
                                    <a href="<?= $this->url('/reportes/' . $report['id']) ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>
                                        Ver
                                    </a>
                                    <a href="<?= $this->url('/reportes/' . $report['id'] . '/editar') ?>" 
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-edit me-1"></i>
                                        Editar
                                    </a>
                                    <form method="POST" 
                                          action="<?= $this->url('/reportes/' . $report['id'] . '/eliminar') ?>" 
                                          class="d-inline"
                                          onsubmit="return confirm('¿Estás seguro de eliminar este reporte?')">
                                        <?= $this->csrfField() ?>
                                        <button type="submit" class="btn btn-outline-danger btn-sm btn-delete">
                                            <i class="fas fa-trash me-1"></i>
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="col-4 text-center">
                                <?php if (!empty($report['foto'])): ?>
                                    <img src="<?= $this->asset('uploads/' . $report['foto']) ?>" 
                                         alt="Foto del reporte" 
                                         class="report-image">
                                <?php else: ?>
                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <i class="fas fa-image text-muted" style="font-size: 2rem;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Paginación -->
    <?php if ($pagination['total_pages'] > 1): ?>
        <nav aria-label="Paginación de reportes">
            <ul class="pagination justify-content-center">
                <!-- Anterior -->
                <?php if ($pagination['has_prev']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>">
                            <i class="fas fa-chevron-left"></i>
                            Anterior
                        </a>
                    </li>
                <?php endif; ?>
                
                <!-- Páginas -->
                <?php
                $start = max(1, $pagination['current_page'] - 2);
                $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
                
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                
                <!-- Siguiente -->
                <?php if ($pagination['has_next']): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>">
                            Siguiente
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <div class="text-center text-muted">
            <small>
                Mostrando <?= count($reports) ?> de <?= $pagination['total'] ?> reportes
            </small>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- Estado vacío -->
    <div class="empty-state">
        <i class="fas fa-clipboard-list"></i>
        <h3>No tienes reportes aún</h3>
        <p class="lead">
            Comienza tu viaje de reciclaje creando tu primer reporte.
        </p>
        <a href="<?= $this->url('/reportes/crear') ?>" class="btn btn-primary btn-lg">
            <i class="fas fa-plus me-2"></i>
            Crear Mi Primer Reporte
        </a>
    </div>
<?php endif; ?>
