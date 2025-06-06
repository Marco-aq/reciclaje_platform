<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Report;
use App\Models\User;
use Exception;

/**
 * Controlador de Reportes de Reciclaje
 */
class ReportController extends Controller
{
    private $reportModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->reportModel = new Report();
        $this->userModel = new User();
    }

    /**
     * Lista todos los reportes
     */
    public function index()
    {
        $this->requireAuth();

        try {
            $currentUser = $this->getCurrentUser();
            $page = (int)($_GET['page'] ?? 1);
            $perPage = 10;

            // Obtener filtros
            $filters = [
                'tipo_material' => $_GET['tipo_material'] ?? '',
                'fecha_desde' => $_GET['fecha_desde'] ?? '',
                'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
                'ubicacion' => $_GET['ubicacion'] ?? ''
            ];

            // Agregar filtro por usuario si no es admin
            $filters['usuario_id'] = $currentUser['id'];

            // Obtener reportes paginados
            $reportData = $this->reportModel->getPaginatedReports($page, $perPage, $filters);

            $data = [
                'title' => 'Mis Reportes',
                'reports' => $reportData['data'],
                'pagination' => [
                    'current_page' => $reportData['current_page'],
                    'total_pages' => $reportData['total_pages'],
                    'has_next' => $reportData['has_next'],
                    'has_prev' => $reportData['has_prev'],
                    'total' => $reportData['total']
                ],
                'filters' => $filters,
                'tipos_materiales' => Report::getTiposMateriales(),
                'user' => $currentUser
            ];

            return $this->viewWithLayout('reports.index', $data);

        } catch (Exception $e) {
            error_log("Error en ReportController::index: " . $e->getMessage());
            $this->setFlash('error', 'Error al cargar los reportes');
            return $this->redirect('/dashboard');
        }
    }

    /**
     * Muestra formulario para crear reporte
     */
    public function create()
    {
        $this->requireAuth();

        $data = [
            'title' => 'Crear Reporte',
            'tipos_materiales' => Report::getTiposMateriales(),
            'csrf_token' => $this->generateCsrfToken(),
            'user' => $this->getCurrentUser()
        ];

        return $this->viewWithLayout('reports.create', $data);
    }

    /**
     * Almacena un nuevo reporte
     */
    public function store()
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();
        $requestData = $this->getRequestData();

        // Verificar CSRF
        if (!isset($requestData['_token']) || !$this->verifyCsrfToken($requestData['_token'])) {
            $this->setFlash('error', 'Token de seguridad inválido');
            return $this->redirect('/reportes/crear');
        }

        // Validar datos
        $errors = $this->validate($requestData, [
            'tipo_material' => 'required',
            'cantidad' => 'required|numeric',
            'ubicacion' => 'required|min:5|max:255'
        ]);

        // Validaciones adicionales
        if (!empty($requestData['tipo_material']) && !array_key_exists($requestData['tipo_material'], Report::getTiposMateriales())) {
            $errors['tipo_material'][] = 'Tipo de material inválido';
        }

        if (!empty($requestData['cantidad']) && (float)$requestData['cantidad'] <= 0) {
            $errors['cantidad'][] = 'La cantidad debe ser mayor a 0';
        }

        if (!empty($requestData['descripcion']) && strlen($requestData['descripcion']) > 1000) {
            $errors['descripcion'][] = 'La descripción no puede tener más de 1000 caracteres';
        }

        if (!empty($errors)) {
            $this->setFlash('error', 'Por favor corrige los errores en el formulario');
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $requestData;
            return $this->redirect('/reportes/crear');
        }

        try {
            // Preparar datos del reporte
            $reportData = [
                'usuario_id' => $currentUser['id'],
                'tipo_material' => $requestData['tipo_material'],
                'cantidad' => (float)$requestData['cantidad'],
                'ubicacion' => $requestData['ubicacion'],
                'descripcion' => $requestData['descripcion'] ?? ''
            ];

            // Manejar upload de foto si existe
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                try {
                    $filename = $this->handleFileUpload('foto', ['jpg', 'jpeg', 'png', 'gif'], 5242880); // 5MB
                    $reportData['foto'] = $filename;
                } catch (Exception $e) {
                    $this->setFlash('error', 'Error al subir la imagen: ' . $e->getMessage());
                    $_SESSION['old'] = $requestData;
                    return $this->redirect('/reportes/crear');
                }
            }

            // Crear reporte
            $report = $this->reportModel->createReport($reportData);

            if ($report) {
                $this->setFlash('success', 'Reporte creado exitosamente. ¡Gracias por contribuir al reciclaje!');
                return $this->redirect('/reportes');
            } else {
                throw new Exception("Error al crear el reporte");
            }

        } catch (Exception $e) {
            error_log("Error creando reporte: " . $e->getMessage());
            $this->setFlash('error', 'Error al crear el reporte. Por favor intenta más tarde.');
            $_SESSION['old'] = $requestData;
            return $this->redirect('/reportes/crear');
        }
    }

    /**
     * Muestra un reporte específico
     */
    public function show($id)
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();

        try {
            $report = $this->reportModel->find($id);

            if (!$report) {
                $this->setFlash('error', 'Reporte no encontrado');
                return $this->redirect('/reportes');
            }

            // Verificar que el reporte pertenezca al usuario
            if ($report['usuario_id'] != $currentUser['id']) {
                $this->setFlash('error', 'No tienes permisos para ver este reporte');
                return $this->redirect('/reportes');
            }

            // Obtener información del usuario
            $reportUser = $this->userModel->find($report['usuario_id']);

            $data = [
                'title' => 'Detalle del Reporte',
                'report' => $report,
                'report_user' => $reportUser,
                'tipo_material_nombre' => Report::getTipoMaterialNombre($report['tipo_material']),
                'user' => $currentUser
            ];

            return $this->viewWithLayout('reports.show', $data);

        } catch (Exception $e) {
            error_log("Error en ReportController::show: " . $e->getMessage());
            $this->setFlash('error', 'Error al cargar el reporte');
            return $this->redirect('/reportes');
        }
    }

    /**
     * Muestra formulario para editar reporte
     */
    public function edit($id)
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();

        try {
            $report = $this->reportModel->find($id);

            if (!$report) {
                $this->setFlash('error', 'Reporte no encontrado');
                return $this->redirect('/reportes');
            }

            // Verificar que el reporte pertenezca al usuario
            if ($report['usuario_id'] != $currentUser['id']) {
                $this->setFlash('error', 'No tienes permisos para editar este reporte');
                return $this->redirect('/reportes');
            }

            $data = [
                'title' => 'Editar Reporte',
                'report' => $report,
                'tipos_materiales' => Report::getTiposMateriales(),
                'csrf_token' => $this->generateCsrfToken(),
                'user' => $currentUser
            ];

            return $this->viewWithLayout('reports.edit', $data);

        } catch (Exception $e) {
            error_log("Error en ReportController::edit: " . $e->getMessage());
            $this->setFlash('error', 'Error al cargar el reporte');
            return $this->redirect('/reportes');
        }
    }

    /**
     * Actualiza un reporte
     */
    public function update($id)
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();
        $requestData = $this->getRequestData();

        // Verificar CSRF
        if (!isset($requestData['_token']) || !$this->verifyCsrfToken($requestData['_token'])) {
            $this->setFlash('error', 'Token de seguridad inválido');
            return $this->redirect('/reportes/' . $id . '/editar');
        }

        try {
            $report = $this->reportModel->find($id);

            if (!$report) {
                $this->setFlash('error', 'Reporte no encontrado');
                return $this->redirect('/reportes');
            }

            // Verificar que el reporte pertenezca al usuario
            if ($report['usuario_id'] != $currentUser['id']) {
                $this->setFlash('error', 'No tienes permisos para editar este reporte');
                return $this->redirect('/reportes');
            }

            // Validar datos
            $errors = $this->validate($requestData, [
                'tipo_material' => 'required',
                'cantidad' => 'required|numeric',
                'ubicacion' => 'required|min:5|max:255'
            ]);

            if (!empty($errors)) {
                $this->setFlash('error', 'Por favor corrige los errores en el formulario');
                $_SESSION['errors'] = $errors;
                $_SESSION['old'] = $requestData;
                return $this->redirect('/reportes/' . $id . '/editar');
            }

            // Preparar datos para actualizar
            $updateData = [
                'tipo_material' => $requestData['tipo_material'],
                'cantidad' => (float)$requestData['cantidad'],
                'ubicacion' => $requestData['ubicacion'],
                'descripcion' => $requestData['descripcion'] ?? ''
            ];

            // Manejar nueva foto si se subió
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Eliminar foto anterior si existe
                    if (!empty($report['foto'])) {
                        $oldPhotoPath = PUBLIC_PATH . '/uploads/' . $report['foto'];
                        if (file_exists($oldPhotoPath)) {
                            unlink($oldPhotoPath);
                        }
                    }

                    $filename = $this->handleFileUpload('foto', ['jpg', 'jpeg', 'png', 'gif'], 5242880);
                    $updateData['foto'] = $filename;
                } catch (Exception $e) {
                    $this->setFlash('error', 'Error al subir la imagen: ' . $e->getMessage());
                    $_SESSION['old'] = $requestData;
                    return $this->redirect('/reportes/' . $id . '/editar');
                }
            }

            // Actualizar reporte
            $updatedReport = $this->reportModel->update($id, $updateData);

            if ($updatedReport) {
                $this->setFlash('success', 'Reporte actualizado exitosamente');
                return $this->redirect('/reportes/' . $id);
            } else {
                throw new Exception("Error al actualizar el reporte");
            }

        } catch (Exception $e) {
            error_log("Error actualizando reporte: " . $e->getMessage());
            $this->setFlash('error', 'Error al actualizar el reporte');
            $_SESSION['old'] = $requestData;
            return $this->redirect('/reportes/' . $id . '/editar');
        }
    }

    /**
     * Elimina un reporte
     */
    public function delete($id)
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();
        $requestData = $this->getRequestData();

        // Verificar CSRF
        if (!isset($requestData['_token']) || !$this->verifyCsrfToken($requestData['_token'])) {
            $this->setFlash('error', 'Token de seguridad inválido');
            return $this->redirect('/reportes');
        }

        try {
            $deleted = $this->reportModel->deleteReport($id, $currentUser['id']);

            if ($deleted) {
                $this->setFlash('success', 'Reporte eliminado exitosamente');
            } else {
                $this->setFlash('error', 'No se pudo eliminar el reporte');
            }

        } catch (Exception $e) {
            error_log("Error eliminando reporte: " . $e->getMessage());
            $this->setFlash('error', 'Error al eliminar el reporte: ' . $e->getMessage());
        }

        return $this->redirect('/reportes');
    }

    /**
     * API: Lista de reportes
     */
    public function apiIndex()
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();

        try {
            $page = (int)($_GET['page'] ?? 1);
            $perPage = min((int)($_GET['per_page'] ?? 10), 50); // Max 50 por página

            $filters = [
                'tipo_material' => $_GET['tipo_material'] ?? '',
                'fecha_desde' => $_GET['fecha_desde'] ?? '',
                'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
                'ubicacion' => $_GET['ubicacion'] ?? '',
                'usuario_id' => $currentUser['id']
            ];

            $reportData = $this->reportModel->getPaginatedReports($page, $perPage, $filters);

            return $this->jsonSuccess($reportData);

        } catch (Exception $e) {
            error_log("Error en API reports: " . $e->getMessage());
            return $this->jsonError('Error obteniendo reportes');
        }
    }

    /**
     * API: Crear reporte
     */
    public function apiStore()
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();
        $requestData = $this->getRequestData();

        // Validar datos
        $errors = $this->validate($requestData, [
            'tipo_material' => 'required',
            'cantidad' => 'required|numeric',
            'ubicacion' => 'required|min:5|max:255'
        ]);

        if (!empty($errors)) {
            return $this->jsonError('Datos inválidos', 400, $errors);
        }

        try {
            $reportData = [
                'usuario_id' => $currentUser['id'],
                'tipo_material' => $requestData['tipo_material'],
                'cantidad' => (float)$requestData['cantidad'],
                'ubicacion' => $requestData['ubicacion'],
                'descripcion' => $requestData['descripcion'] ?? ''
            ];

            $report = $this->reportModel->createReport($reportData);

            return $this->jsonSuccess([
                'report' => $report,
                'message' => 'Reporte creado exitosamente'
            ], 201);

        } catch (Exception $e) {
            error_log("Error en API create report: " . $e->getMessage());
            return $this->jsonError('Error al crear el reporte', 500);
        }
    }

    /**
     * API: Mostrar reporte
     */
    public function apiShow($id)
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();

        try {
            $report = $this->reportModel->find($id);

            if (!$report) {
                return $this->jsonError('Reporte no encontrado', 404);
            }

            if ($report['usuario_id'] != $currentUser['id']) {
                return $this->jsonError('No autorizado', 403);
            }

            // Agregar información adicional
            $report['tipo_material_nombre'] = Report::getTipoMaterialNombre($report['tipo_material']);

            return $this->jsonSuccess(['report' => $report]);

        } catch (Exception $e) {
            error_log("Error en API show report: " . $e->getMessage());
            return $this->jsonError('Error obteniendo reporte', 500);
        }
    }

    /**
     * API: Actualizar reporte
     */
    public function apiUpdate($id)
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();
        $requestData = $this->getRequestData();

        try {
            $report = $this->reportModel->find($id);

            if (!$report) {
                return $this->jsonError('Reporte no encontrado', 404);
            }

            if ($report['usuario_id'] != $currentUser['id']) {
                return $this->jsonError('No autorizado', 403);
            }

            // Validar datos
            $errors = $this->validate($requestData, [
                'tipo_material' => 'required',
                'cantidad' => 'required|numeric',
                'ubicacion' => 'required|min:5|max:255'
            ]);

            if (!empty($errors)) {
                return $this->jsonError('Datos inválidos', 400, $errors);
            }

            $updateData = [
                'tipo_material' => $requestData['tipo_material'],
                'cantidad' => (float)$requestData['cantidad'],
                'ubicacion' => $requestData['ubicacion'],
                'descripcion' => $requestData['descripcion'] ?? ''
            ];

            $updatedReport = $this->reportModel->update($id, $updateData);

            return $this->jsonSuccess([
                'report' => $updatedReport,
                'message' => 'Reporte actualizado exitosamente'
            ]);

        } catch (Exception $e) {
            error_log("Error en API update report: " . $e->getMessage());
            return $this->jsonError('Error al actualizar el reporte', 500);
        }
    }

    /**
     * API: Eliminar reporte
     */
    public function apiDelete($id)
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();

        try {
            $deleted = $this->reportModel->deleteReport($id, $currentUser['id']);

            if ($deleted) {
                return $this->jsonSuccess(['message' => 'Reporte eliminado exitosamente']);
            } else {
                return $this->jsonError('No se pudo eliminar el reporte', 500);
            }

        } catch (Exception $e) {
            error_log("Error en API delete report: " . $e->getMessage());
            return $this->jsonError('Error al eliminar el reporte: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API: Obtener tipos de materiales
     */
    public function apiMaterialTypes()
    {
        return $this->jsonSuccess([
            'tipos_materiales' => Report::getTiposMateriales()
        ]);
    }

    /**
     * Búsqueda de reportes
     */
    public function search()
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();

        $query = $_GET['q'] ?? '';
        $page = (int)($_GET['page'] ?? 1);

        if (empty($query)) {
            return $this->redirect('/reportes');
        }

        try {
            $reports = $this->reportModel->query("
                SELECT 
                    r.*,
                    u.nombre as usuario_nombre
                FROM reportes r
                LEFT JOIN usuarios u ON r.usuario_id = u.id
                WHERE r.usuario_id = ?
                AND (
                    r.tipo_material LIKE ? OR
                    r.ubicacion LIKE ? OR
                    r.descripcion LIKE ?
                )
                ORDER BY r.fecha_reporte DESC
                LIMIT 20 OFFSET ?
            ", [
                $currentUser['id'],
                "%{$query}%",
                "%{$query}%", 
                "%{$query}%",
                ($page - 1) * 20
            ]);

            $data = [
                'title' => "Búsqueda: {$query}",
                'query' => $query,
                'reports' => $reports,
                'user' => $currentUser
            ];

            return $this->viewWithLayout('reports.search', $data);

        } catch (Exception $e) {
            error_log("Error en búsqueda de reportes: " . $e->getMessage());
            $this->setFlash('error', 'Error al realizar la búsqueda');
            return $this->redirect('/reportes');
        }
    }

    /**
     * Exportar reportes del usuario
     */
    public function export()
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();

        try {
            $reports = $this->reportModel->getReportsByUser($currentUser['id']);

            // Generar CSV
            $filename = "mis_reportes_" . date('Y-m-d') . ".csv";
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            // Encabezados
            fputcsv($output, [
                'Fecha',
                'Tipo de Material',
                'Cantidad',
                'Ubicación',
                'Descripción'
            ]);
            
            // Datos
            foreach ($reports as $report) {
                fputcsv($output, [
                    $report['fecha_reporte'],
                    Report::getTipoMaterialNombre($report['tipo_material']),
                    $report['cantidad'],
                    $report['ubicacion'],
                    $report['descripcion']
                ]);
            }
            
            fclose($output);
            exit;

        } catch (Exception $e) {
            error_log("Error exportando reportes: " . $e->getMessage());
            $this->setFlash('error', 'Error al exportar los reportes');
            return $this->redirect('/reportes');
        }
    }
}
