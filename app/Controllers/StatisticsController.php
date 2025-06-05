<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Report;
use App\Models\User;

/**
 * StatisticsController - Controlador de estadísticas
 * 
 * Maneja la visualización de estadísticas y métricas
 * de la plataforma de gestión de residuos.
 */
class StatisticsController extends Controller
{
    private Report $reportModel;
    private User $userModel;

    protected function init(): void
    {
        $this->reportModel = new Report();
        $this->userModel = new User();
    }

    /**
     * Página principal de estadísticas
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            // Obtener estadísticas generales
            $reportStats = $this->reportModel->getStats();
            $userStats = $this->userModel->getStats();

            // Estadísticas específicas
            $monthlyData = $this->getMonthlyReportsData();
            $urgencyDistribution = $this->getUrgencyDistribution();
            $statusDistribution = $this->getStatusDistribution();
            $wasteTypeStats = $this->getWasteTypeStats();
            $topUsers = $this->getTopReporters();
            $resolutionTrends = $this->getResolutionTrends();

            $data = [
                'pageTitle' => 'Estadísticas - EcoCusco',
                'reportStats' => $reportStats,
                'userStats' => $userStats,
                'monthlyData' => $monthlyData,
                'urgencyDistribution' => $urgencyDistribution,
                'statusDistribution' => $statusDistribution,
                'wasteTypeStats' => $wasteTypeStats,
                'topUsers' => $topUsers,
                'resolutionTrends' => $resolutionTrends,
                'currentUser' => $this->getCurrentUser()
            ];

            $this->render('statistics/index', $data);

        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * API: Datos para gráficos de estadísticas
     * 
     * @return void
     */
    public function apiData(): void
    {
        try {
            $type = $this->request->get('type', 'general');

            switch ($type) {
                case 'monthly':
                    $data = $this->getMonthlyReportsData();
                    break;
                case 'urgency':
                    $data = $this->getUrgencyDistribution();
                    break;
                case 'status':
                    $data = $this->getStatusDistribution();
                    break;
                case 'waste-types':
                    $data = $this->getWasteTypeStats();
                    break;
                case 'resolution-time':
                    $data = $this->getResolutionTimeStats();
                    break;
                case 'location':
                    $data = $this->getLocationStats();
                    break;
                case 'general':
                default:
                    $data = [
                        'reports' => $this->reportModel->getStats(),
                        'users' => $this->userModel->getStats()
                    ];
                    break;
            }

            $this->renderJson([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            $this->renderJson([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    /**
     * Estadísticas por período personalizado
     * 
     * @return void
     */
    public function customPeriod(): void
    {
        try {
            $startDate = $this->request->get('start_date');
            $endDate = $this->request->get('end_date');

            if (!$startDate || !$endDate) {
                $this->renderJson([
                    'success' => false,
                    'message' => 'Fechas requeridas'
                ], 400);
                return;
            }

            $stats = $this->getStatsByPeriod($startDate, $endDate);

            $this->renderJson([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            $this->renderJson([
                'success' => false,
                'message' => 'Error al obtener estadísticas del período'
            ], 500);
        }
    }

    /**
     * Exportar estadísticas en formato CSV
     * 
     * @return void
     */
    public function exportCsv(): void
    {
        $this->requireAuth();

        try {
            $currentUser = $this->getCurrentUser();
            
            // Solo administradores pueden exportar
            if ($currentUser['tipo_usuario'] !== 'admin') {
                $this->setFlash('error', 'No tienes permisos para exportar estadísticas.');
                $this->redirect('/estadisticas');
                return;
            }

            $type = $this->request->get('type', 'reports');
            $filename = 'estadisticas_' . $type . '_' . date('Y-m-d') . '.csv';

            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fputs($output, "\xEF\xBB\xBF");

            switch ($type) {
                case 'reports':
                    $this->exportReportsData($output);
                    break;
                case 'users':
                    $this->exportUsersData($output);
                    break;
                case 'summary':
                default:
                    $this->exportSummaryData($output);
                    break;
            }

            fclose($output);
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar CSV: " . $e->getMessage());
            $this->setFlash('error', 'Error al exportar estadísticas.');
            $this->redirect('/estadisticas');
        }
    }

    /**
     * Obtiene datos de reportes mensuales
     * 
     * @return array
     */
    private function getMonthlyReportsData(): array
    {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as mes,
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'resuelto' THEN 1 ELSE 0 END) as resueltos,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN urgencia >= 3 THEN 1 ELSE 0 END) as urgentes
                FROM reportes 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY mes ASC";

        return $this->reportModel->query($sql);
    }

    /**
     * Obtiene distribución por urgencia
     * 
     * @return array
     */
    private function getUrgencyDistribution(): array
    {
        $sql = "SELECT 
                    urgencia,
                    COUNT(*) as cantidad,
                    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM reportes)), 2) as porcentaje
                FROM reportes 
                GROUP BY urgencia 
                ORDER BY urgencia";

        $results = $this->reportModel->query($sql);
        
        // Agregar nombres de urgencia
        foreach ($results as &$result) {
            $result['nombre'] = Report::getUrgencyName($result['urgencia']);
            $result['color'] = Report::getUrgencyColor($result['urgencia']);
        }

        return $results;
    }

    /**
     * Obtiene distribución por estado
     * 
     * @return array
     */
    private function getStatusDistribution(): array
    {
        $sql = "SELECT 
                    estado,
                    COUNT(*) as cantidad,
                    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM reportes)), 2) as porcentaje
                FROM reportes 
                GROUP BY estado 
                ORDER BY cantidad DESC";

        return $this->reportModel->query($sql);
    }

    /**
     * Obtiene estadísticas de tipos de residuos
     * 
     * @return array
     */
    private function getWasteTypeStats(): array
    {
        $sql = "SELECT 
                    tipo_residuo,
                    COUNT(*) as cantidad,
                    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM reportes)), 2) as porcentaje,
                    AVG(urgencia) as urgencia_promedio,
                    SUM(CASE WHEN estado = 'resuelto' THEN 1 ELSE 0 END) as resueltos
                FROM reportes 
                GROUP BY tipo_residuo 
                ORDER BY cantidad DESC 
                LIMIT 15";

        return $this->reportModel->query($sql);
    }

    /**
     * Obtiene los usuarios que más reportan
     * 
     * @return array
     */
    private function getTopReporters(): array
    {
        $sql = "SELECT 
                    u.nombre,
                    u.apellidos,
                    u.email,
                    COUNT(r.id) as total_reportes,
                    SUM(CASE WHEN r.estado = 'resuelto' THEN 1 ELSE 0 END) as reportes_resueltos,
                    AVG(r.urgencia) as urgencia_promedio
                FROM usuarios u
                INNER JOIN reportes r ON u.id = r.usuario_id
                GROUP BY u.id
                ORDER BY total_reportes DESC
                LIMIT 10";

        return $this->reportModel->query($sql);
    }

    /**
     * Obtiene tendencias de resolución
     * 
     * @return array
     */
    private function getResolutionTrends(): array
    {
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as mes,
                    AVG(CASE 
                        WHEN estado = 'resuelto' AND fecha_resolucion IS NOT NULL 
                        THEN DATEDIFF(fecha_resolucion, created_at) 
                        ELSE NULL 
                    END) as tiempo_promedio_resolucion,
                    COUNT(*) as total_reportes,
                    SUM(CASE WHEN estado = 'resuelto' THEN 1 ELSE 0 END) as resueltos
                FROM reportes 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY mes ASC";

        return $this->reportModel->query($sql);
    }

    /**
     * Obtiene estadísticas de tiempo de resolución
     * 
     * @return array
     */
    private function getResolutionTimeStats(): array
    {
        $sql = "SELECT 
                    CASE 
                        WHEN DATEDIFF(fecha_resolucion, created_at) <= 1 THEN '0-1 días'
                        WHEN DATEDIFF(fecha_resolucion, created_at) <= 3 THEN '2-3 días'
                        WHEN DATEDIFF(fecha_resolucion, created_at) <= 7 THEN '4-7 días'
                        WHEN DATEDIFF(fecha_resolucion, created_at) <= 14 THEN '8-14 días'
                        WHEN DATEDIFF(fecha_resolucion, created_at) <= 30 THEN '15-30 días'
                        ELSE 'Más de 30 días'
                    END as rango_tiempo,
                    COUNT(*) as cantidad
                FROM reportes 
                WHERE estado = 'resuelto' AND fecha_resolucion IS NOT NULL
                GROUP BY rango_tiempo
                ORDER BY 
                    CASE 
                        WHEN DATEDIFF(fecha_resolucion, created_at) <= 1 THEN 1
                        WHEN DATEDIFF(fecha_resolucion, created_at) <= 3 THEN 2
                        WHEN DATEDIFF(fecha_resolucion, created_at) <= 7 THEN 3
                        WHEN DATEDIFF(fecha_resolucion, created_at) <= 14 THEN 4
                        WHEN DATEDIFF(fecha_resolucion, created_at) <= 30 THEN 5
                        ELSE 6
                    END";

        return $this->reportModel->query($sql);
    }

    /**
     * Obtiene estadísticas por ubicación
     * 
     * @return array
     */
    private function getLocationStats(): array
    {
        $sql = "SELECT 
                    SUBSTRING_INDEX(ubicacion, ',', 1) as zona,
                    COUNT(*) as cantidad,
                    AVG(urgencia) as urgencia_promedio,
                    SUM(CASE WHEN estado = 'resuelto' THEN 1 ELSE 0 END) as resueltos
                FROM reportes 
                GROUP BY zona
                HAVING cantidad > 1
                ORDER BY cantidad DESC
                LIMIT 20";

        return $this->reportModel->query($sql);
    }

    /**
     * Obtiene estadísticas por período personalizado
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getStatsByPeriod(string $startDate, string $endDate): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_reportes,
                    SUM(CASE WHEN estado = 'resuelto' THEN 1 ELSE 0 END) as resueltos,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
                    AVG(urgencia) as urgencia_promedio,
                    AVG(CASE 
                        WHEN estado = 'resuelto' AND fecha_resolucion IS NOT NULL 
                        THEN DATEDIFF(fecha_resolucion, created_at) 
                        ELSE NULL 
                    END) as tiempo_promedio_resolucion
                FROM reportes 
                WHERE DATE(created_at) BETWEEN ? AND ?";

        $result = $this->reportModel->query($sql, [$startDate, $endDate]);
        
        return $result[0] ?? [];
    }

    /**
     * Exporta datos de reportes a CSV
     * 
     * @param resource $output
     * @return void
     */
    private function exportReportsData($output): void
    {
        // Encabezados
        fputcsv($output, [
            'ID', 'Fecha Creación', 'Usuario', 'Ubicación', 'Tipo Residuo',
            'Urgencia', 'Estado', 'Fecha Resolución', 'Tiempo Resolución (días)'
        ]);

        // Datos
        $sql = "SELECT 
                    r.id,
                    r.created_at,
                    CONCAT(u.nombre, ' ', u.apellidos) as usuario,
                    r.ubicacion,
                    r.tipo_residuo,
                    r.urgencia,
                    r.estado,
                    r.fecha_resolucion,
                    CASE 
                        WHEN r.fecha_resolucion IS NOT NULL 
                        THEN DATEDIFF(r.fecha_resolucion, r.created_at)
                        ELSE NULL
                    END as tiempo_resolucion
                FROM reportes r
                LEFT JOIN usuarios u ON r.usuario_id = u.id
                ORDER BY r.created_at DESC";

        $reports = $this->reportModel->query($sql);

        foreach ($reports as $report) {
            fputcsv($output, [
                $report['id'],
                $report['created_at'],
                $report['usuario'],
                $report['ubicacion'],
                $report['tipo_residuo'],
                Report::getUrgencyName($report['urgencia']),
                ucfirst($report['estado']),
                $report['fecha_resolucion'] ?? 'N/A',
                $report['tiempo_resolucion'] ?? 'N/A'
            ]);
        }
    }

    /**
     * Exporta datos de usuarios a CSV
     * 
     * @param resource $output
     * @return void
     */
    private function exportUsersData($output): void
    {
        // Encabezados
        fputcsv($output, [
            'ID', 'Nombre', 'Email', 'Tipo Usuario', 'Fecha Registro',
            'Total Reportes', 'Reportes Resueltos', 'Estado'
        ]);

        // Datos
        $sql = "SELECT 
                    u.id,
                    CONCAT(u.nombre, ' ', u.apellidos) as nombre_completo,
                    u.email,
                    u.tipo_usuario,
                    u.created_at,
                    COUNT(r.id) as total_reportes,
                    SUM(CASE WHEN r.estado = 'resuelto' THEN 1 ELSE 0 END) as reportes_resueltos,
                    u.estado
                FROM usuarios u
                LEFT JOIN reportes r ON u.id = r.usuario_id
                GROUP BY u.id
                ORDER BY u.created_at DESC";

        $users = $this->userModel->query($sql);

        foreach ($users as $user) {
            fputcsv($output, [
                $user['id'],
                $user['nombre_completo'],
                $user['email'],
                ucfirst($user['tipo_usuario']),
                $user['created_at'],
                $user['total_reportes'],
                $user['reportes_resueltos'],
                $user['estado'] ? 'Activo' : 'Inactivo'
            ]);
        }
    }

    /**
     * Exporta resumen de estadísticas a CSV
     * 
     * @param resource $output
     * @return void
     */
    private function exportSummaryData($output): void
    {
        $reportStats = $this->reportModel->getStats();
        $userStats = $this->userModel->getStats();

        // Resumen general
        fputcsv($output, ['RESUMEN GENERAL DE ESTADÍSTICAS']);
        fputcsv($output, ['Fecha de exportación', date('Y-m-d H:i:s')]);
        fputcsv($output, []);

        // Estadísticas de reportes
        fputcsv($output, ['REPORTES']);
        fputcsv($output, ['Total de reportes', $reportStats['total']]);
        fputcsv($output, ['Reportes resueltos', $reportStats['por_estado']['resuelto'] ?? 0]);
        fputcsv($output, ['Reportes pendientes', $reportStats['por_estado']['pendiente'] ?? 0]);
        fputcsv($output, ['Tiempo promedio resolución (días)', $reportStats['tiempo_promedio_resolucion']]);
        fputcsv($output, []);

        // Estadísticas de usuarios
        fputcsv($output, ['USUARIOS']);
        fputcsv($output, ['Total de usuarios', $userStats['total']]);
        fputcsv($output, ['Usuarios activos', $userStats['activos']]);
        fputcsv($output, []);

        // Top tipos de residuos
        fputcsv($output, ['TOP TIPOS DE RESIDUOS']);
        fputcsv($output, ['Tipo', 'Cantidad']);
        foreach ($reportStats['tipos_residuo_top'] as $tipo) {
            fputcsv($output, [$tipo['tipo_residuo'], $tipo['count']]);
        }
    }
}
