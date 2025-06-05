<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Config;
use App\Models\Report;
use App\Models\User;

/**
 * ReportsController - Controlador de reportes de residuos
 * 
 * Maneja la creación, visualización y gestión de reportes
 * de puntos de acumulación de residuos.
 */
class ReportsController extends Controller
{
    private Report $reportModel;
    private User $userModel;

    protected function init(): void
    {
        $this->reportModel = new Report();
        $this->userModel = new User();
    }

    /**
     * Lista todos los reportes con paginación
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $page = max(1, (int) $this->request->get('page', 1));
            $perPage = 15;
            $status = $this->request->get('estado');
            $urgency = $this->request->get('urgencia');
            $search = $this->request->get('buscar');

            // Obtener reportes con filtros
            if ($search) {
                $reports = $this->reportModel->searchReports($search);
                $pagination = null;
            } elseif ($status) {
                $reports = $this->reportModel->getReportsByStatus($status);
                $pagination = null;
            } elseif ($urgency) {
                $reports = $this->reportModel->getReportsByUrgency((int) $urgency);
                $pagination = null;
            } else {
                $pagination = $this->reportModel->paginate($page, $perPage);
                $reports = $pagination['data'];
            }

            // Obtener estadísticas básicas
            $stats = $this->reportModel->getStats();

            $data = [
                'pageTitle' => 'Reportes de Residuos - EcoCusco',
                'reports' => $reports,
                'pagination' => $pagination,
                'stats' => $stats,
                'filters' => [
                    'estado' => $status,
                    'urgencia' => $urgency,
                    'buscar' => $search
                ],
                'currentUser' => $this->getCurrentUser()
            ];

            $this->render('reports/index', $data);

        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Muestra el formulario de creación de reporte
     * 
     * @return void
     */
    public function create(): void
    {
        $this->requireAuth();

        $data = [
            'pageTitle' => 'Reportar Residuos - EcoCusco',
            'errors' => $this->getValidationErrors(),
            'csrfToken' => $this->generateCsrfToken(),
            'tiposResiduos' => $this->getTiposResiduos(),
            'nivelesUrgencia' => $this->getNivelesUrgencia()
        ];

        $this->render('reports/create', $data);
    }

    /**
     * Procesa la creación de un nuevo reporte
     * 
     * @return void
     */
    public function store(): void
    {
        $this->requireAuth();

        if (!$this->isMethod('POST')) {
            $this->redirect('/reportes/crear');
            return;
        }

        // Verificar token CSRF
        if (!$this->verifyCsrfToken()) {
            $this->setFlash('error', 'Token de seguridad inválido.');
            $this->redirect('/reportes/crear');
            return;
        }

        // Validar datos
        $validation = $this->validate($this->request->all(), [
            'ubicacion' => 'required|string|max:200',
            'tipo_residuo' => 'required|string',
            'descripcion' => 'required|string|max:1000',
            'urgencia' => 'required|integer|min:1|max:4',
            'latitud' => 'numeric',
            'longitud' => 'numeric',
            'direccion_exacta' => 'string|max:300'
        ]);

        if (!$validation['valid']) {
            $this->setValidationErrors($validation['errors']);
            $this->request->saveOldInput();
            $this->redirect('/reportes/crear');
            return;
        }

        try {
            $currentUser = $this->getCurrentUser();
            
            // Preparar datos del reporte
            $reportData = $this->request->only([
                'ubicacion', 'tipo_residuo', 'descripcion', 
                'urgencia', 'latitud', 'longitud', 'direccion_exacta'
            ]);
            
            $reportData['usuario_id'] = $currentUser['id'];

            // Manejar subida de imagen si existe
            if ($this->request->hasFile('imagen')) {
                $imageUrl = $this->handleImageUpload();
                if ($imageUrl) {
                    $reportData['imagen_url'] = $imageUrl;
                }
            }

            // Crear el reporte
            $reportId = $this->reportModel->createReport($reportData);

            if ($reportId) {
                $this->setFlash('success', 'Reporte creado exitosamente. Gracias por contribuir a una ciudad más limpia.');
                $this->redirect('/reportes/' . $reportId);
            } else {
                $this->setFlash('error', 'Error al crear el reporte. Intenta nuevamente.');
                $this->redirect('/reportes/crear');
            }

        } catch (\Exception $e) {
            error_log("Error al crear reporte: " . $e->getMessage());
            $this->setFlash('error', 'Error al procesar el reporte. Intenta nuevamente.');
            $this->redirect('/reportes/crear');
        }
    }

    /**
     * Muestra un reporte específico
     * 
     * @param int $id
     * @return void
     */
    public function show(int $id): void
    {
        try {
            $report = $this->reportModel->findById($id);

            if (!$report) {
                $this->setFlash('error', 'Reporte no encontrado.');
                $this->redirect('/reportes');
                return;
            }

            // Obtener información del usuario que hizo el reporte
            $reportUser = $this->userModel->findById($report['usuario_id']);

            $data = [
                'pageTitle' => 'Reporte #' . $id . ' - EcoCusco',
                'report' => $report,
                'reportUser' => $reportUser,
                'currentUser' => $this->getCurrentUser(),
                'canEdit' => $this->canEditReport($report),
                'urgencyName' => Report::getUrgencyName($report['urgencia']),
                'urgencyColor' => Report::getUrgencyColor($report['urgencia'])
            ];

            $this->render('reports/show', $data);

        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Muestra el formulario de edición de reporte
     * 
     * @param int $id
     * @return void
     */
    public function edit(int $id): void
    {
        $this->requireAuth();

        try {
            $report = $this->reportModel->findById($id);

            if (!$report) {
                $this->setFlash('error', 'Reporte no encontrado.');
                $this->redirect('/reportes');
                return;
            }

            if (!$this->canEditReport($report)) {
                $this->setFlash('error', 'No tienes permisos para editar este reporte.');
                $this->redirect('/reportes/' . $id);
                return;
            }

            $data = [
                'pageTitle' => 'Editar Reporte #' . $id . ' - EcoCusco',
                'report' => $report,
                'errors' => $this->getValidationErrors(),
                'csrfToken' => $this->generateCsrfToken(),
                'tiposResiduos' => $this->getTiposResiduos(),
                'nivelesUrgencia' => $this->getNivelesUrgencia()
            ];

            $this->render('reports/edit', $data);

        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Actualiza un reporte
     * 
     * @param int $id
     * @return void
     */
    public function update(int $id): void
    {
        $this->requireAuth();

        if (!$this->isMethod('POST')) {
            $this->redirect('/reportes/' . $id . '/editar');
            return;
        }

        try {
            $report = $this->reportModel->findById($id);

            if (!$report || !$this->canEditReport($report)) {
                $this->setFlash('error', 'No tienes permisos para editar este reporte.');
                $this->redirect('/reportes');
                return;
            }

            // Validar datos
            $validation = $this->validate($this->request->all(), [
                'ubicacion' => 'required|string|max:200',
                'tipo_residuo' => 'required|string',
                'descripcion' => 'required|string|max:1000',
                'urgencia' => 'required|integer|min:1|max:4',
                'direccion_exacta' => 'string|max:300'
            ]);

            if (!$validation['valid']) {
                $this->setValidationErrors($validation['errors']);
                $this->redirect('/reportes/' . $id . '/editar');
                return;
            }

            // Actualizar reporte
            $updateData = $this->request->only([
                'ubicacion', 'tipo_residuo', 'descripcion', 
                'urgencia', 'direccion_exacta'
            ]);

            $updated = $this->reportModel->update($id, $updateData);

            if ($updated) {
                $this->setFlash('success', 'Reporte actualizado exitosamente.');
            } else {
                $this->setFlash('warning', 'No se realizaron cambios en el reporte.');
            }

            $this->redirect('/reportes/' . $id);

        } catch (\Exception $e) {
            error_log("Error al actualizar reporte: " . $e->getMessage());
            $this->setFlash('error', 'Error al actualizar el reporte.');
            $this->redirect('/reportes/' . $id);
        }
    }

    /**
     * Actualiza el estado de un reporte
     * 
     * @param int $id
     * @return void
     */
    public function updateStatus(int $id): void
    {
        $this->requireAuth();

        if (!$this->isMethod('POST')) {
            $this->redirect('/reportes/' . $id);
            return;
        }

        try {
            $currentUser = $this->getCurrentUser();
            
            // Solo administradores pueden cambiar estado
            if ($currentUser['tipo_usuario'] !== 'admin') {
                $this->renderJson([
                    'success' => false,
                    'message' => 'No tienes permisos para cambiar el estado.'
                ], 403);
                return;
            }

            $newStatus = $this->request->get('estado');
            $comment = $this->request->get('comentario', '');

            $updated = $this->reportModel->updateStatus($id, $newStatus);

            if ($updated) {
                $this->renderJson([
                    'success' => true,
                    'message' => 'Estado actualizado correctamente.'
                ]);
            } else {
                $this->renderJson([
                    'success' => false,
                    'message' => 'Error al actualizar el estado.'
                ], 500);
            }

        } catch (\Exception $e) {
            $this->renderJson([
                'success' => false,
                'message' => 'Error del servidor.'
            ], 500);
        }
    }

    /**
     * API: Obtiene reportes para el mapa
     * 
     * @return void
     */
    public function apiMapData(): void
    {
        try {
            $reports = $this->reportModel->getReportsForMap();

            $this->renderJson([
                'success' => true,
                'data' => $reports
            ]);

        } catch (\Exception $e) {
            $this->renderJson([
                'success' => false,
                'message' => 'Error al obtener datos del mapa.'
            ], 500);
        }
    }

    /**
     * Verifica si el usuario puede editar un reporte
     * 
     * @param array $report
     * @return bool
     */
    private function canEditReport(array $report): bool
    {
        $currentUser = $this->getCurrentUser();
        
        if (!$currentUser) {
            return false;
        }

        // El admin puede editar cualquier reporte
        if ($currentUser['tipo_usuario'] === 'admin') {
            return true;
        }

        // El propietario puede editar solo si está pendiente
        if ($currentUser['id'] == $report['usuario_id'] && 
            $report['estado'] === Report::ESTADO_PENDIENTE) {
            return true;
        }

        return false;
    }

    /**
     * Maneja la subida de imagen
     * 
     * @return string|null
     */
    private function handleImageUpload(): ?string
    {
        $file = $this->request->file('imagen');
        
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return null;
        }

        // Validar tamaño (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return null;
        }

        try {
            $uploadDir = PUBLIC_PATH . '/uploads/reports/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = uniqid() . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                return '/uploads/reports/' . $fileName;
            }

        } catch (\Exception $e) {
            error_log("Error al subir imagen: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Obtiene los tipos de residuos disponibles
     * 
     * @return array
     */
    private function getTiposResiduos(): array
    {
        return [
            'Orgánicos' => 'Restos de comida, cáscaras, etc.',
            'Plástico' => 'Botellas, bolsas, envases plásticos',
            'Papel y Cartón' => 'Periódicos, cajas, documentos',
            'Vidrio' => 'Botellas, frascos, cristales',
            'Metal' => 'Latas, envases metálicos',
            'Electrónicos' => 'Dispositivos, cables, baterías',
            'Textiles' => 'Ropa, telas, zapatos',
            'Peligrosos' => 'Químicos, medicamentos, pilas',
            'Construcción' => 'Escombros, materiales de obra',
            'Otros' => 'Residuos no clasificados'
        ];
    }

    /**
     * Obtiene los niveles de urgencia
     * 
     * @return array
     */
    private function getNivelesUrgencia(): array
    {
        return [
            Report::URGENCIA_BAJA => [
                'nombre' => 'Baja',
                'descripcion' => 'Puede esperar varios días',
                'color' => 'success'
            ],
            Report::URGENCIA_MEDIA => [
                'nombre' => 'Media',
                'descripcion' => 'Requiere atención en pocos días',
                'color' => 'warning'
            ],
            Report::URGENCIA_ALTA => [
                'nombre' => 'Alta',
                'descripcion' => 'Requiere atención urgente',
                'color' => 'danger'
            ],
            Report::URGENCIA_CRITICA => [
                'nombre' => 'Crítica',
                'descripcion' => 'Emergencia sanitaria',
                'color' => 'dark'
            ]
        ];
    }
}
