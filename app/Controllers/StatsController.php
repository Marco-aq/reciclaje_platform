<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Stats;
use App\Models\Report;
use App\Models\User;
use Exception;

/**
 * Controlador de Estadísticas y Análisis
 */
class StatsController extends Controller
{
    private $statsModel;
    private $reportModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->statsModel = new Stats();
        $this->reportModel = new Report();
        $this->userModel = new User();
    }

    /**
     * Página principal de estadísticas
     */
    public function index()
    {
        $this->requireAuth();

        try {
            $currentUser = $this->getCurrentUser();

            // Obtener estadísticas generales
            $dashboardStats = $this->statsModel->getDashboardStats();
            
            // Obtener datos para gráficos
            $chartData = $this->statsModel->getChartData();
            
            // Obtener estadísticas del usuario actual
            $userStats = $this->getUserPersonalStats($currentUser['id']);
            
            // Obtener ranking y comparaciones
            $rankings = $this->getRankings();
            
            // Obtener impacto ambiental
            $environmentalImpact = $this->reportModel->getEnvironmentalImpact();

            $data = [
                'title' => 'Estadísticas y Análisis',
                'user' => $currentUser,
                'dashboard_stats' => $dashboardStats,
                'chart_data' => $chartData,
                'user_stats' => $userStats,
                'rankings' => $rankings,
                'environmental_impact' => $environmentalImpact,
                'comparison_periods' => $this->getComparisonPeriods()
            ];

            return $this->viewWithLayout('stats.index', $data);

        } catch (Exception $e) {
            error_log("Error en StatsController::index: " . $e->getMessage());
            $this->setFlash('error', 'Error al cargar las estadísticas');
            return $this->redirect('/dashboard');
        }
    }

    /**
     * Página de análisis avanzado
     */
    public function advanced()
    {
        $this->requireAuth();

        try {
            $currentUser = $this->getCurrentUser();

            // Obtener análisis de tendencias
            $trends = $this->statsModel->getRecyclingTrends(12);
            
            // Obtener estadísticas por ubicación
            $locationStats = $this->statsModel->getTopLocations(20);
            
            // Obtener análisis temporal
            $weekdayStats = $this->statsModel->getWeekdayStats();
            $hourlyStats = $this->statsModel->getHourlyStats();
            
            // Obtener proyecciones
            $projections = $this->statsModel->getProjections(6);

            $data = [
                'title' => 'Análisis Avanzado',
                'user' => $currentUser,
                'trends' => $trends,
                'location_stats' => $locationStats,
                'weekday_stats' => $weekdayStats,
                'hourly_stats' => $hourlyStats,
                'projections' => $projections
            ];

            return $this->viewWithLayout('stats.advanced', $data);

        } catch (Exception $e) {
            error_log("Error en StatsController::advanced: " . $e->getMessage());
            $this->setFlash('error', 'Error al cargar el análisis avanzado');
            return $this->redirect('/estadisticas');
        }
    }

    /**
     * Comparativas de períodos
     */
    public function comparison()
    {
        $this->requireAuth();

        $period1Start = $_GET['period1_start'] ?? date('Y-m-01');
        $period1End = $_GET['period1_end'] ?? date('Y-m-t');
        $period2Start = $_GET['period2_start'] ?? date('Y-m-01', strtotime('-1 month'));
        $period2End = $_GET['period2_end'] ?? date('Y-m-t', strtotime('-1 month'));

        try {
            $comparison = $this->statsModel->getPeriodComparison(
                $period1Start, $period1End,
                $period2Start, $period2End
            );

            $data = [
                'title' => 'Comparación de Períodos',
                'user' => $this->getCurrentUser(),
                'comparison' => $comparison,
                'period1_start' => $period1Start,
                'period1_end' => $period1End,
                'period2_start' => $period2Start,
                'period2_end' => $period2End
            ];

            return $this->viewWithLayout('stats.comparison', $data);

        } catch (Exception $e) {
            error_log("Error en StatsController::comparison: " . $e->getMessage());
            $this->setFlash('error', 'Error al generar la comparación');
            return $this->redirect('/estadisticas');
        }
    }

    /**
     * API: Datos generales del dashboard
     */
    public function getData()
    {
        try {
            $dashboardStats = $this->statsModel->getDashboardStats();
            return $this->jsonSuccess($dashboardStats);

        } catch (Exception $e) {
            error_log("Error en API getData: " . $e->getMessage());
            return $this->jsonError('Error obteniendo datos de estadísticas');
        }
    }

    /**
     * API: Datos para gráficos
     */
    public function getChartData()
    {
        try {
            $type = $_GET['type'] ?? 'all';
            $period = $_GET['period'] ?? '6'; // meses por defecto

            switch ($type) {
                case 'materials':
                    $data = $this->statsModel->getMaterialsByType();
                    break;
                case 'monthly':
                    $data = $this->statsModel->getReportsByMonth((int)$period);
                    break;
                case 'users':
                    $data = $this->statsModel->getActiveUsersChart((int)$period);
                    break;
                case 'trends':
                    $data = $this->statsModel->getRecyclingTrends((int)$period);
                    break;
                case 'environmental':
                    $data = $this->statsModel->getEnvironmentalImpactChart();
                    break;
                case 'locations':
                    $limit = (int)($_GET['limit'] ?? 10);
                    $data = $this->statsModel->getTopLocations($limit);
                    break;
                case 'weekday':
                    $data = $this->statsModel->getWeekdayStats();
                    break;
                case 'hourly':
                    $data = $this->statsModel->getHourlyStats();
                    break;
                default:
                    $data = $this->statsModel->getChartData();
            }

            return $this->jsonSuccess($data);

        } catch (Exception $e) {
            error_log("Error en API getChartData: " . $e->getMessage());
            return $this->jsonError('Error obteniendo datos de gráficos');
        }
    }

    /**
     * API: Estadísticas del usuario actual
     */
    public function getUserStats()
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();

        try {
            $userStats = $this->getUserPersonalStats($currentUser['id']);
            return $this->jsonSuccess($userStats);

        } catch (Exception $e) {
            error_log("Error en API getUserStats: " . $e->getMessage());
            return $this->jsonError('Error obteniendo estadísticas del usuario');
        }
    }

    /**
     * API: Rankings
     */
    public function getRankingsData()
    {
        try {
            $type = $_GET['type'] ?? 'users';
            $limit = min((int)($_GET['limit'] ?? 10), 50);

            switch ($type) {
                case 'users':
                    $data = $this->userModel->getUserRanking($limit);
                    break;
                case 'locations':
                    $data = $this->statsModel->getTopLocations($limit);
                    break;
                default:
                    $data = [];
            }

            return $this->jsonSuccess($data);

        } catch (Exception $e) {
            error_log("Error en API getRankingsData: " . $e->getMessage());
            return $this->jsonError('Error obteniendo rankings');
        }
    }

    /**
     * API: Comparación de períodos
     */
    public function getComparisonData()
    {
        try {
            $period1Start = $_GET['period1_start'] ?? date('Y-m-01');
            $period1End = $_GET['period1_end'] ?? date('Y-m-t');
            $period2Start = $_GET['period2_start'] ?? date('Y-m-01', strtotime('-1 month'));
            $period2End = $_GET['period2_end'] ?? date('Y-m-t', strtotime('-1 month'));

            $comparison = $this->statsModel->getPeriodComparison(
                $period1Start, $period1End,
                $period2Start, $period2End
            );

            return $this->jsonSuccess($comparison);

        } catch (Exception $e) {
            error_log("Error en API getComparisonData: " . $e->getMessage());
            return $this->jsonError('Error obteniendo comparación');
        }
    }

    /**
     * API: Proyecciones
     */
    public function getProjections()
    {
        try {
            $months = min((int)($_GET['months'] ?? 3), 12);
            $projections = $this->statsModel->getProjections($months);

            if ($projections) {
                return $this->jsonSuccess($projections);
            } else {
                return $this->jsonError('No hay suficientes datos para generar proyecciones', 400);
            }

        } catch (Exception $e) {
            error_log("Error en API getProjections: " . $e->getMessage());
            return $this->jsonError('Error obteniendo proyecciones');
        }
    }

    /**
     * API: Impacto ambiental
     */
    public function getEnvironmentalImpact()
    {
        try {
            $impact = $this->reportModel->getEnvironmentalImpact();
            $chartData = $this->statsModel->getEnvironmentalImpactChart();

            return $this->jsonSuccess([
                'impact' => $impact,
                'chart_data' => $chartData
            ]);

        } catch (Exception $e) {
            error_log("Error en API getEnvironmentalImpact: " . $e->getMessage());
            return $this->jsonError('Error obteniendo impacto ambiental');
        }
    }

    /**
     * Exportar estadísticas
     */
    public function export()
    {
        $this->requireAuth();

        try {
            $format = $_GET['format'] ?? 'csv';
            $type = $_GET['type'] ?? 'general';

            switch ($type) {
                case 'general':
                    $data = $this->statsModel->getDashboardStats();
                    $filename = 'estadisticas_generales_' . date('Y-m-d');
                    break;
                case 'materials':
                    $data = $this->statsModel->getMaterialsByType();
                    $filename = 'materiales_por_tipo_' . date('Y-m-d');
                    break;
                case 'monthly':
                    $data = $this->statsModel->getReportsByMonth(12);
                    $filename = 'reportes_mensuales_' . date('Y-m-d');
                    break;
                case 'locations':
                    $data = $this->statsModel->getTopLocations(50);
                    $filename = 'ubicaciones_top_' . date('Y-m-d');
                    break;
                default:
                    throw new Exception("Tipo de exportación no válido");
            }

            if ($format === 'csv') {
                $this->exportToCsv($data, $filename);
            } elseif ($format === 'json') {
                $this->exportToJson($data, $filename);
            } else {
                throw new Exception("Formato de exportación no válido");
            }

        } catch (Exception $e) {
            error_log("Error exportando estadísticas: " . $e->getMessage());
            $this->setFlash('error', 'Error al exportar las estadísticas');
            return $this->redirect('/estadisticas');
        }
    }

    /**
     * Obtiene estadísticas personales del usuario
     */
    private function getUserPersonalStats($userId)
    {
        try {
            return [
                'total_reportes' => $this->reportModel->count(['usuario_id' => $userId]),
                'total_materiales' => $this->getTotalUserMaterials($userId),
                'tipos_materiales' => $this->reportModel->getMaterialTypesByUser($userId),
                'reportes_por_mes' => $this->getUserMonthlyReports($userId),
                'puntos' => $this->userModel->calculateUserPoints($userId),
                'ranking_position' => $this->getUserRankingPosition($userId),
                'actividad_reciente' => $this->getUserRecentActivity($userId)
            ];
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas personales: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene rankings varios
     */
    private function getRankings()
    {
        try {
            return [
                'top_usuarios' => $this->userModel->getUserRanking(10),
                'top_ubicaciones' => $this->statsModel->getTopLocations(10),
                'usuarios_activos' => $this->userModel->getActiveUsers(30)
            ];
        } catch (Exception $e) {
            error_log("Error obteniendo rankings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene períodos de comparación predefinidos
     */
    private function getComparisonPeriods()
    {
        return [
            'mes_actual_vs_anterior' => [
                'period1' => ['start' => date('Y-m-01'), 'end' => date('Y-m-t')],
                'period2' => ['start' => date('Y-m-01', strtotime('-1 month')), 'end' => date('Y-m-t', strtotime('-1 month'))],
                'label' => 'Mes actual vs. anterior'
            ],
            'trimestre_actual_vs_anterior' => [
                'period1' => ['start' => date('Y-m-01', strtotime('-2 months')), 'end' => date('Y-m-t')],
                'period2' => ['start' => date('Y-m-01', strtotime('-5 months')), 'end' => date('Y-m-t', strtotime('-3 months'))],
                'label' => 'Trimestre actual vs. anterior'
            ]
        ];
    }

    /**
     * Obtiene total de materiales del usuario
     */
    private function getTotalUserMaterials($userId)
    {
        try {
            $result = $this->reportModel->queryOne(
                "SELECT SUM(cantidad) as total FROM reportes WHERE usuario_id = ?",
                [$userId]
            );
            return $result ? (float)$result['total'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Obtiene reportes mensuales del usuario
     */
    private function getUserMonthlyReports($userId)
    {
        try {
            return $this->reportModel->query("
                SELECT 
                    DATE_FORMAT(fecha_reporte, '%Y-%m') as mes,
                    COUNT(*) as cantidad_reportes,
                    SUM(cantidad) as cantidad_materiales
                FROM reportes
                WHERE usuario_id = ?
                AND fecha_reporte >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(fecha_reporte, '%Y-%m')
                ORDER BY mes ASC
            ", [$userId]);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene posición en ranking del usuario
     */
    private function getUserRankingPosition($userId)
    {
        try {
            $allRankings = $this->userModel->getUserRanking(1000);
            
            foreach ($allRankings as $index => $user) {
                if ($user['id'] == $userId) {
                    return $index + 1;
                }
            }
            
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Obtiene actividad reciente del usuario
     */
    private function getUserRecentActivity($userId)
    {
        try {
            return $this->reportModel->getReportsByUser($userId, 5);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Exporta datos a CSV
     */
    private function exportToCsv($data, $filename)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data) && is_array($data)) {
            // Si es un array asociativo, usar las claves como encabezados
            if (isset($data[0]) && is_array($data[0])) {
                fputcsv($output, array_keys($data[0]));
                foreach ($data as $row) {
                    fputcsv($output, $row);
                }
            } else {
                // Si es un array simple, exportar como clave-valor
                fputcsv($output, ['Clave', 'Valor']);
                foreach ($data as $key => $value) {
                    fputcsv($output, [$key, is_array($value) ? json_encode($value) : $value]);
                }
            }
        }
        
        fclose($output);
        exit;
    }

    /**
     * Exporta datos a JSON
     */
    private function exportToJson($data, $filename)
    {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '.json"');
        
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Dashboard personalizado para usuarios específicos
     */
    public function personalDashboard()
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();

        try {
            // Crear dashboard personalizado basado en los datos del usuario
            $personalStats = $this->createPersonalizedDashboard($currentUser['id']);

            $data = [
                'title' => 'Mi Dashboard Personalizado',
                'user' => $currentUser,
                'personal_stats' => $personalStats
            ];

            return $this->viewWithLayout('stats.personal_dashboard', $data);

        } catch (Exception $e) {
            error_log("Error en dashboard personalizado: " . $e->getMessage());
            $this->setFlash('error', 'Error al crear dashboard personalizado');
            return $this->redirect('/estadisticas');
        }
    }

    /**
     * Crea un dashboard personalizado basado en el comportamiento del usuario
     */
    private function createPersonalizedDashboard($userId)
    {
        try {
            $userStats = $this->getUserPersonalStats($userId);
            
            // Determinar qué mostrar basado en la actividad del usuario
            $recommendations = [];
            
            if ($userStats['total_reportes'] < 5) {
                $recommendations[] = 'Intenta hacer más reportes para obtener mejor análisis';
            }
            
            if (count($userStats['tipos_materiales']) < 3) {
                $recommendations[] = 'Diversifica los tipos de materiales que reciclas';
            }
            
            return [
                'stats' => $userStats,
                'recommendations' => $recommendations,
                'insights' => $this->generateUserInsights($userId, $userStats)
            ];
        } catch (Exception $e) {
            error_log("Error creando dashboard personalizado: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Genera insights personalizados para el usuario
     */
    private function generateUserInsights($userId, $userStats)
    {
        $insights = [];
        
        try {
            // Insight sobre frecuencia
            if ($userStats['total_reportes'] > 0) {
                $daysSinceFirst = $this->getDaysSinceFirstReport($userId);
                if ($daysSinceFirst > 0) {
                    $frequency = round($userStats['total_reportes'] / $daysSinceFirst, 2);
                    $insights[] = "Reportas en promedio {$frequency} veces por día";
                }
            }
            
            // Insight sobre material favorito
            if (!empty($userStats['tipos_materiales'])) {
                $topMaterial = $userStats['tipos_materiales'][0];
                $insights[] = "Tu material más reciclado es " . Report::getTipoMaterialNombre($topMaterial['tipo_material']);
            }
            
            // Insight sobre ranking
            if ($userStats['ranking_position']) {
                $insights[] = "Estás en la posición #{$userStats['ranking_position']} del ranking";
            }
            
        } catch (Exception $e) {
            error_log("Error generando insights: " . $e->getMessage());
        }
        
        return $insights;
    }

    /**
     * Obtiene días desde el primer reporte del usuario
     */
    private function getDaysSinceFirstReport($userId)
    {
        try {
            $result = $this->reportModel->queryOne(
                "SELECT MIN(fecha_reporte) as first_report FROM reportes WHERE usuario_id = ?",
                [$userId]
            );
            
            if ($result && $result['first_report']) {
                $firstDate = new \DateTime($result['first_report']);
                $now = new \DateTime();
                return $now->diff($firstDate)->days;
            }
            
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}
