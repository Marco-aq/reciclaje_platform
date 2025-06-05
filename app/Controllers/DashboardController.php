<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Report;
use App\Models\User;

/**
 * DashboardController - Controlador del panel de control
 * 
 * Maneja el dashboard personalizado para usuarios autenticados
 * mostrando información relevante según el tipo de usuario.
 */
class DashboardController extends Controller
{
    private Report $reportModel;
    private User $userModel;

    protected function init(): void
    {
        $this->reportModel = new Report();
        $this->userModel = new User();
        
        // Requerir autenticación para todas las acciones
        $this->requireAuth();
    }

    /**
     * Dashboard principal
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            $currentUser = $this->getCurrentUser();
            
            if ($currentUser['tipo_usuario'] === 'admin') {
                $this->adminDashboard();
            } else {
                $this->userDashboard();
            }

        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Dashboard para administradores
     * 
     * @return void
     */
    private function adminDashboard(): void
    {
        $currentUser = $this->getCurrentUser();
        
        // Estadísticas generales
        $generalStats = $this->getAdminStats();
        
        // Reportes urgentes pendientes
        $urgentReports = $this->reportModel->getUrgentReports();
        
        // Actividad reciente
        $recentActivity = $this->getRecentActivity();
        
        // Métricas del mes actual
        $monthlyMetrics = $this->getMonthlyMetrics();
        
        // Reportes por estado
        $reportsByStatus = $this->getReportsByStatus();
        
        // Top usuarios reportadores
        $topReporters = $this->getTopReporters();

        $data = [
            'pageTitle' => 'Panel de Administración - EcoCusco',
            'currentUser' => $currentUser,
            'generalStats' => $generalStats,
            'urgentReports' => $urgentReports,
            'recentActivity' => $recentActivity,
            'monthlyMetrics' => $monthlyMetrics,
            'reportsByStatus' => $reportsByStatus,
            'topReporters' => $topReporters,
            'dashboardType' => 'admin'
        ];

        $this->render('dashboard/admin', $data);
    }

    /**
     * Dashboard para usuarios regulares
     * 
     * @return void
     */
    private function userDashboard(): void
    {
        $currentUser = $this->getCurrentUser();
        
        // Reportes del usuario
        $userReports = $this->reportModel->getReportsByUser($currentUser['id']);
        
        // Estadísticas personales
        $personalStats = $this->getUserPersonalStats($currentUser['id']);
        
        // Reportes recientes en la zona del usuario
        $nearbyReports = $this->getNearbyReports($currentUser);
        
        // Progreso del usuario
        $userProgress = $this->getUserProgress($currentUser['id']);

        $data = [
            'pageTitle' => 'Mi Panel - EcoCusco',
            'currentUser' => $currentUser,
            'userReports' => array_slice($userReports, 0, 10), // Últimos 10
            'personalStats' => $personalStats,
            'nearbyReports' => $nearbyReports,
            'userProgress' => $userProgress,
            'dashboardType' => 'user'
        ];

        $this->render('dashboard/user', $data);
    }

    /**
     * Perfil del usuario
     * 
     * @return void
     */
    public function profile(): void
    {
        $currentUser = $this->getCurrentUser();
        
        if ($this->isMethod('POST')) {
            $this->updateProfile();
            return;
        }

        $data = [
            'pageTitle' => 'Mi Perfil - EcoCusco',
            'currentUser' => $currentUser,
            'errors' => $this->getValidationErrors(),
            'csrfToken' => $this->generateCsrfToken()
        ];

        $this->render('dashboard/profile', $data);
    }

    /**
     * Actualiza el perfil del usuario
     * 
     * @return void
     */
    private function updateProfile(): void
    {
        if (!$this->verifyCsrfToken()) {
            $this->setFlash('error', 'Token de seguridad inválido.');
            $this->redirect('/dashboard/perfil');
            return;
        }

        $validation = $this->validate($this->request->all(), [
            'nombre' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'telefono' => 'string|max:20',
            'direccion' => 'string|max:200'
        ]);

        if (!$validation['valid']) {
            $this->setValidationErrors($validation['errors']);
            $this->redirect('/dashboard/perfil');
            return;
        }

        try {
            $currentUser = $this->getCurrentUser();
            
            $updateData = $this->request->only([
                'nombre', 'apellidos', 'telefono', 'direccion'
            ]);

            $updated = $this->userModel->update($currentUser['id'], $updateData);

            if ($updated) {
                $this->setFlash('success', 'Perfil actualizado correctamente.');
            } else {
                $this->setFlash('info', 'No se realizaron cambios en tu perfil.');
            }

        } catch (\Exception $e) {
            error_log("Error al actualizar perfil: " . $e->getMessage());
            $this->setFlash('error', 'Error al actualizar el perfil.');
        }

        $this->redirect('/dashboard/perfil');
    }

    /**
     * Cambiar contraseña
     * 
     * @return void
     */
    public function changePassword(): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('/dashboard/perfil');
            return;
        }

        if (!$this->verifyCsrfToken()) {
            $this->setFlash('error', 'Token de seguridad inválido.');
            $this->redirect('/dashboard/perfil');
            return;
        }

        $validation = $this->validate($this->request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
            'new_password_confirmation' => 'required|string'
        ]);

        if (!$validation['valid']) {
            $this->setValidationErrors($validation['errors']);
            $this->redirect('/dashboard/perfil');
            return;
        }

        $currentPassword = $this->request->get('current_password');
        $newPassword = $this->request->get('new_password');
        $confirmPassword = $this->request->get('new_password_confirmation');

        // Verificar que las nuevas contraseñas coincidan
        if ($newPassword !== $confirmPassword) {
            $this->setFlash('error', 'Las nuevas contraseñas no coinciden.');
            $this->redirect('/dashboard/perfil');
            return;
        }

        try {
            $currentUser = $this->getCurrentUser();
            
            // Verificar contraseña actual
            $user = $this->userModel->findById($currentUser['id']);
            if (!password_verify($currentPassword, $user['password_hash'])) {
                $this->setFlash('error', 'La contraseña actual es incorrecta.');
                $this->redirect('/dashboard/perfil');
                return;
            }

            // Actualizar contraseña
            $updated = $this->userModel->updatePassword($currentUser['id'], $newPassword);

            if ($updated) {
                $this->setFlash('success', 'Contraseña actualizada correctamente.');
            } else {
                $this->setFlash('error', 'Error al actualizar la contraseña.');
            }

        } catch (\Exception $e) {
            error_log("Error al cambiar contraseña: " . $e->getMessage());
            $this->setFlash('error', 'Error al procesar el cambio de contraseña.');
        }

        $this->redirect('/dashboard/perfil');
    }

    /**
     * API: Datos del dashboard
     * 
     * @return void
     */
    public function apiData(): void
    {
        try {
            $currentUser = $this->getCurrentUser();
            $type = $this->request->get('type', 'general');

            $data = [];

            switch ($type) {
                case 'admin-stats':
                    if ($currentUser['tipo_usuario'] === 'admin') {
                        $data = $this->getAdminStats();
                    }
                    break;
                case 'user-stats':
                    $data = $this->getUserPersonalStats($currentUser['id']);
                    break;
                case 'recent-activity':
                    if ($currentUser['tipo_usuario'] === 'admin') {
                        $data = $this->getRecentActivity();
                    }
                    break;
                case 'monthly-metrics':
                    $data = $this->getMonthlyMetrics();
                    break;
                default:
                    $data = ['message' => 'Tipo de datos no válido'];
                    break;
            }

            $this->renderJson([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            $this->renderJson([
                'success' => false,
                'message' => 'Error al obtener datos del dashboard'
            ], 500);
        }
    }

    /**
     * Obtiene estadísticas para administradores
     * 
     * @return array
     */
    private function getAdminStats(): array
    {
        $reportStats = $this->reportModel->getStats();
        $userStats = $this->userModel->getStats();

        return [
            'total_reportes' => $reportStats['total'],
            'reportes_pendientes' => $reportStats['por_estado']['pendiente'] ?? 0,
            'reportes_resueltos' => $reportStats['por_estado']['resuelto'] ?? 0,
            'reportes_en_proceso' => $reportStats['por_estado']['en_proceso'] ?? 0,
            'usuarios_activos' => $userStats['activos'],
            'usuarios_totales' => $userStats['total'],
            'tiempo_promedio_resolucion' => $reportStats['tiempo_promedio_resolucion'],
            'reportes_este_mes' => $this->getReportsThisMonth(),
            'reportes_urgentes' => $this->getUrgentReportsCount()
        ];
    }

    /**
     * Obtiene estadísticas personales del usuario
     * 
     * @param int $userId
     * @return array
     */
    private function getUserPersonalStats(int $userId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_reportes,
                    SUM(CASE WHEN estado = 'resuelto' THEN 1 ELSE 0 END) as reportes_resueltos,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as reportes_pendientes,
                    AVG(urgencia) as urgencia_promedio,
                    MAX(created_at) as ultimo_reporte
                FROM reportes 
                WHERE usuario_id = ?";

        $result = $this->reportModel->query($sql, [$userId]);
        $stats = $result[0] ?? [];

        // Calcular porcentaje de resolución
        $total = (int) ($stats['total_reportes'] ?? 0);
        $resueltos = (int) ($stats['reportes_resueltos'] ?? 0);
        $stats['porcentaje_resolucion'] = $total > 0 ? round(($resueltos / $total) * 100, 1) : 0;

        return $stats;
    }

    /**
     * Obtiene actividad reciente del sistema
     * 
     * @return array
     */
    private function getRecentActivity(): array
    {
        $sql = "SELECT 
                    'reporte' as tipo,
                    r.id,
                    r.ubicacion as descripcion,
                    r.created_at as fecha,
                    CONCAT(u.nombre, ' ', u.apellidos) as usuario,
                    r.estado
                FROM reportes r
                LEFT JOIN usuarios u ON r.usuario_id = u.id
                ORDER BY r.created_at DESC
                LIMIT 10";

        return $this->reportModel->query($sql);
    }

    /**
     * Obtiene métricas del mes actual
     * 
     * @return array
     */
    private function getMonthlyMetrics(): array
    {
        $sql = "SELECT 
                    COUNT(*) as reportes_mes,
                    SUM(CASE WHEN estado = 'resuelto' THEN 1 ELSE 0 END) as resueltos_mes,
                    AVG(urgencia) as urgencia_promedio_mes
                FROM reportes 
                WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                AND YEAR(created_at) = YEAR(CURRENT_DATE())";

        $result = $this->reportModel->query($sql);
        return $result[0] ?? [];
    }

    /**
     * Obtiene reportes por estado
     * 
     * @return array
     */
    private function getReportsByStatus(): array
    {
        $sql = "SELECT estado, COUNT(*) as cantidad FROM reportes GROUP BY estado";
        return $this->reportModel->query($sql);
    }

    /**
     * Obtiene top usuarios reportadores
     * 
     * @return array
     */
    private function getTopReporters(): array
    {
        $sql = "SELECT 
                    CONCAT(u.nombre, ' ', u.apellidos) as nombre,
                    COUNT(r.id) as total_reportes,
                    SUM(CASE WHEN r.estado = 'resuelto' THEN 1 ELSE 0 END) as resueltos
                FROM usuarios u
                INNER JOIN reportes r ON u.id = r.usuario_id
                GROUP BY u.id
                ORDER BY total_reportes DESC
                LIMIT 5";

        return $this->reportModel->query($sql);
    }

    /**
     * Obtiene reportes cercanos al usuario
     * 
     * @param array $user
     * @return array
     */
    private function getNearbyReports(array $user): array
    {
        // Por simplicidad, obtener reportes recientes
        // En un caso real, se usaría la ubicación del usuario
        return $this->reportModel->getRecentReports(5);
    }

    /**
     * Obtiene el progreso del usuario
     * 
     * @param int $userId
     * @return array
     */
    private function getUserProgress(int $userId): array
    {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as mes,
                    COUNT(*) as reportes
                FROM reportes 
                WHERE usuario_id = ? 
                AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY mes ASC";

        $monthlyReports = $this->reportModel->query($sql, [$userId]);

        return [
            'reportes_mensuales' => $monthlyReports,
            'total_impacto' => array_sum(array_column($monthlyReports, 'reportes'))
        ];
    }

    /**
     * Obtiene el número de reportes del mes actual
     * 
     * @return int
     */
    private function getReportsThisMonth(): int
    {
        $sql = "SELECT COUNT(*) as count FROM reportes 
                WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                AND YEAR(created_at) = YEAR(CURRENT_DATE())";

        $result = $this->reportModel->query($sql);
        return (int) $result[0]['count'];
    }

    /**
     * Obtiene el número de reportes urgentes
     * 
     * @return int
     */
    private function getUrgentReportsCount(): int
    {
        $sql = "SELECT COUNT(*) as count FROM reportes 
                WHERE urgencia >= 3 AND estado = 'pendiente'";

        $result = $this->reportModel->query($sql);
        return (int) $result[0]['count'];
    }
}
