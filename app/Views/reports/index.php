<!-- Page Header -->
<div class="bg-success text-white py-4 mb-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="h2 mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Reportes de Residuos
                </h1>
                <p class="mb-0 opacity-75">Gestiona y supervisa los reportes de la comunidad</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= $this->url('/reportes/crear') ?>" class="btn btn-light">
                    <i class="fas fa-plus me-2"></i>Nuevo Reporte
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?= $stats['total'] ?? 0 ?></h4>
                            <small>Total Reportes</small>
                        </div>
                        <i class="fas fa-clipboard-list fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?= $stats['por_estado']['pendiente'] ?? 0 ?></h4>
                            <small>Pendientes</small>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?= $stats['por_estado']['en_proceso'] ?? 0 ?></h4>
                            <small>En Proceso</small>
                        </div>
                        <i class="fas fa-cog fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?= $stats['por_estado']['resuelto'] ?? 0 ?></h4>
                            <small>Resueltos</small>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= $this->url('/reportes') ?>" class="row g-3">
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" <?= $filters['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="en_proceso" <?= $filters['estado'] === 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                        <option value="resuelto" <?= $filters['estado'] === 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
                        <option value="rechazado" <?= $filters['estado'] === 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="urgencia" class="form-label">Urgencia</label>
                    <select class="form-select" id="urgencia" name="urgencia">
                        <option value="">Todas las urgencias</option>
                        <option value="1" <?= $filters['urgencia'] === '1' ? 'selected' : '' ?>>Baja</option>
                        <option value="2" <?= $filters['urgencia'] === '2' ? 'selected' : '' ?>>Media</option>
                        <option value="3" <?= $filters['urgencia'] === '3' ? 'selected' : '' ?>>Alta</option>
                        <option value="4" <?= $filters['urgencia'] === '4' ? 'selected' : '' ?>>Crítica</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="buscar" class="form-label">Buscar</label>
                    <input type="text" class="form-control" id="buscar" name="buscar" 
                           placeholder="Buscar por ubicación o descripción..." 
                           value="<?= $this->escape($filters['buscar'] ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Lista de Reportes
            </h5>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary" id="viewGrid">
                    <i class="fas fa-th-large"></i>
                </button>
                <button class="btn btn-outline-secondary active" id="viewList">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($reports)): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Ubicación</th>
                                <th>Tipo</th>
                                <th>Urgencia</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reports as $report): ?>
                            <tr>
                                <td>
                                    <strong>#<?= $report['id'] ?></strong>
                                </td>
                                <td>
                                    <i class="fas fa-map-marker-alt text-muted me-1"></i>
                                    <?= $this->escape($this->truncate($report['ubicacion'], 30)) ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= $this->escape($report['tipo_residuo']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $urgencyColors = [1 => 'success', 2 => 'warning', 3 => 'danger', 4 => 'dark'];
                                    $urgencyNames = [1 => 'Baja', 2 => 'Media', 3 => 'Alta', 4 => 'Crítica'];
                                    $urgency = (int) $report['urgencia'];
                                    ?>
                                    <span class="badge bg-<?= $urgencyColors[$urgency] ?? 'secondary' ?>">
                                        <?= $urgencyNames[$urgency] ?? 'N/A' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'pendiente' => 'warning',
                                        'en_proceso' => 'info',
                                        'resuelto' => 'success',
                                        'rechazado' => 'danger'
                                    ];
                                    ?>
                                    <span class="badge bg-<?= $statusColors[$report['estado']] ?? 'secondary' ?>">
                                        <?= $this->escape(ucfirst($report['estado'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= $this->formatDate($report['created_at'], 'd/m/Y') ?>
                                    </small>
                                </td>
                                <td>
                                    <?php if (isset($report['nombre'])): ?>
                                        <small>
                                            <?= $this->escape($report['nombre'] . ' ' . ($report['apellidos'] ?? '')) ?>
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">N/A</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= $this->url('/reportes/' . $report['id']) ?>" 
                                           class="btn btn-outline-primary" 
                                           title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($currentUser && $currentUser['tipo_usuario'] === 'admin'): ?>
                                        <button type="button" 
                                                class="btn btn-outline-success" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#statusModal" 
                                                data-report-id="<?= $report['id'] ?>"
                                                title="Cambiar estado">
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
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay reportes disponibles</h5>
                    <p class="text-muted">No se encontraron reportes con los filtros aplicados.</p>
                    <a href="<?= $this->url('/reportes/crear') ?>" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Crear Primer Reporte
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php if (isset($pagination) && $pagination): ?>
    <nav aria-label="Paginación de reportes" class="mt-4">
        <ul class="pagination justify-content-center">
            <!-- Previous Page -->
            <?php if ($pagination['current_page'] > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Page Numbers -->
            <?php
            $start = max(1, $pagination['current_page'] - 2);
            $end = min($pagination['last_page'], $pagination['current_page'] + 2);
            ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <!-- Next Page -->
            <?php if ($pagination['has_more']): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<!-- Status Change Modal (Admin Only) -->
<?php if ($currentUser && $currentUser['tipo_usuario'] === 'admin'): ?>
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Estado del Reporte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                <div class="modal-body">
                    <input type="hidden" id="reportId" name="report_id">
                    
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">Nuevo Estado</label>
                        <select class="form-select" id="newStatus" name="estado" required>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="resuelto">Resuelto</option>
                            <option value="rechazado">Rechazado</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="statusComment" class="form-label">Comentario (opcional)</label>
                        <textarea class="form-control" id="statusComment" name="comentario" rows="3"
                                  placeholder="Agrega un comentario sobre el cambio de estado..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Estado</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle status modal
    const statusModal = document.getElementById('statusModal');
    if (statusModal) {
        statusModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const reportId = button.getAttribute('data-report-id');
            document.getElementById('reportId').value = reportId;
        });

        // Handle status form submission
        document.getElementById('statusForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const reportId = document.getElementById('reportId').value;
            const formData = new FormData(this);
            
            fetch(`<?= $this->url('/reportes/') ?>${reportId}/estado`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    EcoCusco.utils.notify('Estado actualizado correctamente', 'success');
                    location.reload();
                } else {
                    EcoCusco.utils.notify(data.message || 'Error al actualizar el estado', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                EcoCusco.utils.notify('Error de conexión', 'danger');
            });
        });
    }

    // View toggle functionality
    document.getElementById('viewGrid')?.addEventListener('click', function() {
        // TODO: Implement grid view
        this.classList.add('active');
        document.getElementById('viewList').classList.remove('active');
    });

    document.getElementById('viewList')?.addEventListener('click', function() {
        this.classList.add('active');
        document.getElementById('viewGrid').classList.remove('active');
    });
});
</script>
