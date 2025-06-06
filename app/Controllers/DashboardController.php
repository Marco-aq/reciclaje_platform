<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Report;
use App\Models\Stats;
use Exception;

/**
 * Controlador Dashboard - Panel principal del usuario
 */
class DashboardController extends Controller
{
    private $userModel;
    private $reportModel;
    private $statsModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->reportModel = new Report();
        $this->statsModel = new Stats();
    }

    /**
     * Dashboard principal
     */
    public function index()
    {
        // Verificar autenticación
        $this->requireAuth();

        $currentUser = $this->getCurrentUser();
        
        try {
            // Obtener estadísticas del usuario
            $userStats = $this->getUserStats($currentUser['id']);
            
            // Obtener reportes recientes del usuario
            $recentReports = $this->reportModel->getReportsByUser($currentUser['id'], 5);
            
            // Obtener estadísticas generales para comparación
            $generalStats = $this->statsModel->getDashboardStats();
            
            // Obtener ranking del usuario
            $userRanking = $this->getUserRanking($currentUser['id']);
            
            // Obtener datos para gráficos personalizados
            $chartData = $this->getUserChartData($currentUser['id']);

            $data = [
                'title' => 'Dashboard - ' . $currentUser['nombre'],
                'user' => $currentUser,
                'user_stats' => $userStats,
                'recent_reports' => $recentReports,
                'general_stats' => $generalStats,
                'user_ranking' => $userRanking,
                'chart_data' => $chartData,
                'notifications' => $this->getNotifications($currentUser['id'])
            ];

            return $this->viewWithLayout('dashboard.index', $data);

        } catch (Exception $e) {
            error_log("Error en Dashboard::index: " . $e->getMessage());
            
            $this->setFlash('error', 'Error al cargar el dashboard');
            
            // Dashboard mínimo en caso de error
            $data = [
                'title' => 'Dashboard - ' . $currentUser['nombre'],
                'user' => $currentUser,
                'user_stats' => $this->getDefaultUserStats(),
                'recent_reports' => [],
                'general_stats' => [],
                'user_ranking' => null,
                'chart_data' => [],
                'notifications' => []
            ];

            return $this->viewWithLayout('dashboard.index', $data);
        }
    }

    /**
     * Panel de estadísticas del usuario
     */
    public function stats()
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();

        try {
            $userStats = $this->getUserStats($currentUser['id']);
            $detailedChartData = $this->getDetailedUserChartData($currentUser['id']);
            $comparison = $this->getUserComparison($currentUser['id']);

            $data = [
                'title' => 'Mis Estadísticas',
                'user' => $currentUser,
                'stats' => $userStats,
                'chart_data' => $detailedChartData,
                'comparison' => $comparison
            ];

            return $this->viewWithLayout('dashboard.stats', $data);

        } catch (Exception $e) {
            error_log("Error en Dashboard::stats: " . $e->getMessage());
            $this->setFlash('error', 'Error al cargar las estadísticas');
            return $this->redirect('/dashboard');
        }
    }

    /**
     * Perfil del usuario
     */
    public function profile()
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();

        $data = [
            'title' => 'Mi Perfil',
            'user' => $currentUser,
            'csrf_token' => $this->generateCsrfToken()
        ];

        return $this->viewWithLayout('dashboard.profile', $data);
    }

    /**
     * Actualizar perfil
     */
    public function updateProfile()
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();
        $requestData = $this->getRequestData();

        // Verificar CSRF
        if (!isset($requestData['_token']) || !$this->verifyCsrfToken($requestData['_token'])) {
            $this->setFlash('error', 'Token de seguridad inválido');
            return $this->redirect('/dashboard/profile');
        }

        // Validar datos
        $errors = $this->validate($requestData, [
            'nombre' => 'required|min:2|max:100',
            'email' => 'required|email'
        ]);

        // Validar contraseña si se proporciona
        if (!empty($requestData['password'])) {
            if (strlen($requestData['password']) < 6) {
                $errors['password'][] = 'La contraseña debe tener al menos 6 caracteres';
            }
            if ($requestData['password'] !== $requestData['password_confirmation']) {
                $errors['password'][] = 'Las contraseñas no coinciden';
            }
        }

        if (!empty($errors)) {
            $this->setFlash('error', 'Por favor corrige los errores en el formulario');
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $requestData;
            return $this->redirect('/dashboard/profile');
        }

        try {
            // Preparar datos para actualizar
            $updateData = [
                'nombre' => $requestData['nombre'],
                'email' => $requestData['email']
            ];

            // Incluir contraseña si se proporciona
            if (!empty($requestData['password'])) {
                $updateData['password'] = $requestData['password'];
            }

            // Actualizar usuario
            $updatedUser = $this->userModel->updateUser($currentUser['id'], $updateData);
            
            if ($updatedUser) {
                // Actualizar datos en sesión
                $_SESSION['user_data'] = $updatedUser;
                $this->setFlash('success', 'Perfil actualizado correctamente');
            } else {
                throw new Exception("Error al actualizar el perfil");
            }

        } catch (Exception $e) {
            error_log("Error actualizando perfil: " . $e->getMessage());
            
            if (strpos($e->getMessage(), 'email ya está registrado') !== false) {
                $this->setFlash('error', 'El email ya está siendo usado por otro usuario');
            } else {
                $this->setFlash('error', 'Error al actualizar el perfil');
            }
            
            $_SESSION['old'] = $requestData;
        }

        return $this->redirect('/dashboard/profile');
    }

    /**
     * API: Estadísticas del dashboard
     */
    public function apiDashboardData()
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();

        try {
            $data = [
                'user_stats' => $this->getUserStats($currentUser['id']),
                'recent_reports' => $this->reportModel->getReportsByUser($currentUser['id'], 5),
                'chart_data' => $this->getUserChartData($currentUser['id'])
            ];

            return $this->jsonSuccess($data);

        } catch (Exception $e) {
            error_log("Error en API dashboard data: " . $e->getMessage());
            return $this->jsonError('Error obteniendo datos del dashboard');
        }
    }

    /**
     * API: Actualizar perfil
     */
    public function apiUpdateProfile()
    {
        $this->requireAuth();
        $currentUser = $this->getCurrentUser();
        $requestData = $this->getRequestData();

        // Validar datos
        $errors = $this->validate($requestData, [
            'nombre' => 'required|min:2|max:100',
            'email' => 'required|email'
        ]);

        if (!empty($errors)) {
            return $this->jsonError('Datos inválidos', 400, $errors);
        }

        try {
            $updatedUser = $this->userModel->updateUser($currentUser['id'], $requestData);
            
            if ($updatedUser) {
                $_SESSION['user_data'] = $updatedUser;
                return $this->jsonSuccess([
                    'user' => $updatedUser,
                    'message' => 'Perfil actualizado correctamente'
                ]);
            } else {
                throw new Exception("Error al actualizar el perfil");
            }

        } catch (Exception $e) {
            error_log("Error en API update profile: " . $e->getMessage());
            return $this->jsonError('Error al actualizar el perfil', 500);
        }
    }

    /**
     * Obtiene estadísticas detalladas del usuario
     */
    private function getUserStats($userId)
    {
        try {
            return [
                'total_reportes' => $this->reportModel->count(['usuario_id' => $userId]),
                'total_materiales' => $this->getTotalUserMaterials($userId),
                'reportes_mes' => $this->getMonthReports($userId),
                'reportes_semana' => $this->getWeekReports($userId),
                'tipos_materiales' => $this->reportModel->getMaterialTypesByUser($userId),
                'puntos' => $this->userModel->calculateUserPoints($userId),
                'impacto_ambiental' => $this->getUserEnvironmentalImpact($userId),
                'streak_dias' => $this->getUserStreak($userId)
            ];
        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas de usuario: " . $e->getMessage());
            return $this->getDefaultUserStats();
        }
    }

    /**
     * Obtiene datos para gráficos del usuario
     */
    private function getUserChartData($userId)
    {
        try {
            return [
                'reportes_por_mes' => $this->reportModel->query("
                    SELECT 
                        DATE_FORMAT(fecha_reporte, '%Y-%m') as mes,
                        COUNT(*) as cantidad_reportes,
                        SUM(cantidad) as cantidad_materiales
                    FROM reportes
                    WHERE usuario_id = ?
                    AND fecha_reporte >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                    GROUP BY DATE_FORMAT(fecha_reporte, '%Y-%m')
                    ORDER BY mes ASC
                ", [$userId]),
                
                'materiales_por_tipo' => $this->reportModel->getMaterialTypesByUser($userId),
                
                'actividad_semanal' => $this->reportModel->query("
                    SELECT 
                        DAYNAME(fecha_reporte) as dia,
                        COUNT(*) as reportes
                    FROM reportes
                    WHERE usuario_id = ?
                    AND fecha_reporte >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DAYOFWEEK(fecha_reporte), DAYNAME(fecha_reporte)
                    ORDER BY DAYOFWEEK(fecha_reporte)
                ", [$userId])
            ];
        } catch (Exception $e) {
            error_log("Error obteniendo datos de gráficos: " . $e->getMessage());
            return [
                'reportes_por_mes' => [],
                'materiales_por_tipo' => [],
                'actividad_semanal' => []
            ];
        }
    }

    /**
     * Obtiene datos detallados para gráficos
     */
    private function getDetailedUserChartData($userId)
    {
        try {
            $basicData = $this->getUserChartData($userId);
            
            // Agregar más datos detallados
            $basicData['tendencia_crecimiento'] = $this->getUserGrowthTrend($userId);
            $basicData['comparacion_promedio'] = $this->getUserVsAverage($userId);
            
            return $basicData;
        } catch (Exception $e) {
            error_log("Error obteniendo datos detallados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el ranking del usuario
     */
    private function getUserRanking($userId)
    {
        try {
            $allRankings = $this->userModel->getUserRanking(1000); // Obtener un número grande
            
            foreach ($allRankings as $index => $user) {
                if ($user['id'] == $userId) {
                    return [
                        'position' => $index + 1,
                        'total_users' => count($allRankings),
                        'percentile' => round((1 - ($index / count($allRankings))) * 100, 1)
                    ];
                }
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error obteniendo ranking: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene notificaciones del usuario
     */
    private function getNotifications($userId)
    {
        $notifications = [];
        
        try {
            // Verificar si es un nuevo usuario
            $userReports = $this->reportModel->count(['usuario_id' => $userId]);
            if ($userReports == 0) {
                $notifications[] = [
                    'type' => 'info',
                    'message' => '¡Bienvenido! Crea tu primer reporte de reciclaje.',
                    'action_url' => '/reportes/crear'
                ];
            }
            
            // Verificar racha de días
            $streak = $this->getUserStreak($userId);
            if ($streak >= 7) {
                $notifications[] = [
                    'type' => 'success',
                    'message' => "¡Increíble! Llevas {$streak} días consecutivos reportando."
                ];
            }
            
            // Verificar logros
            $achievements = $this->checkUserAchievements($userId);
            foreach ($achievements as $achievement) {
                $notifications[] = [
                    'type' => 'achievement',
                    'message' => "¡Logro desbloqueado: {$achievement}!"
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error obteniendo notificaciones: " . $e->getMessage());
        }
        
        return $notifications;
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
     * Reportes del mes actual
     */
    private function getMonthReports($userId)
    {
        try {
            return $this->reportModel->count([
                'usuario_id' => $userId,
                'fecha_reporte' => 'MONTH(fecha_reporte) = MONTH(NOW()) AND YEAR(fecha_reporte) = YEAR(NOW())'
            ]);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Reportes de la semana actual
     */
    private function getWeekReports($userId)
    {
        try {
            $result = $this->reportModel->queryOne("
                SELECT COUNT(*) as count 
                FROM reportes 
                WHERE usuario_id = ? 
                AND YEARWEEK(fecha_reporte, 1) = YEARWEEK(CURDATE(), 1)
            ", [$userId]);
            
            return $result ? (int)$result['count'] : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Calcula racha de días consecutivos
     */
    private function getUserStreak($userId)
    {
        try {
            // Obtener fechas únicas de reportes ordenadas
            $dates = $this->reportModel->query("
                SELECT DISTINCT DATE(fecha_reporte) as fecha
                FROM reportes
                WHERE usuario_id = ?
                ORDER BY fecha DESC
            ", [$userId]);

            if (empty($dates)) {
                return 0;
            }

            $streak = 0;
            $currentDate = date('Y-m-d');
            
            foreach ($dates as $dateRow) {
                $reportDate = $dateRow['fecha'];
                
                if ($reportDate == $currentDate) {
                    $streak++;
                    $currentDate = date('Y-m-d', strtotime($currentDate . ' -1 day'));
                } else {
                    break;
                }
            }
            
            return $streak;
        } catch (Exception $e) {
            error_log("Error calculando racha: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcula impacto ambiental del usuario
     */
    private function getUserEnvironmentalImpact($userId)
    {
        try {
            $impactFactors = [
                'plastico' => 2.0,
                'papel' => 3.3,
                'vidrio' => 0.5,
                'metal' => 6.0,
                'electronico' => 4.0,
                'organico' => 0.3,
                'textil' => 2.5,
                'otros' => 1.0
            ];

            $materials = $this->reportModel->getMaterialTypesByUser($userId);
            $totalCO2 = 0;

            foreach ($materials as $material) {
                $factor = $impactFactors[$material['tipo_material']] ?? 1.0;
                $totalCO2 += $material['cantidad_total'] * $factor;
            }

            return [
                'co2_evitado' => round($totalCO2, 2),
                'arboles_equivalente' => round($totalCO2 / 22),
                'autos_equivalente' => round($totalCO2 / 4600, 2)
            ];
        } catch (Exception $e) {
            error_log("Error calculando impacto ambiental: " . $e->getMessage());
            return ['co2_evitado' => 0, 'arboles_equivalente' => 0, 'autos_equivalente' => 0];
        }
    }

    /**
     * Verifica logros del usuario
     */
    private function checkUserAchievements($userId)
    {
        $achievements = [];
        
        try {
            $stats = $this->getUserStats($userId);
            
            // Logros por número de reportes
            if ($stats['total_reportes'] >= 50 && !$this->hasAchievement($userId, 'reporter_50')) {
                $achievements[] = 'Reporter Dedicado (50 reportes)';
                $this->grantAchievement($userId, 'reporter_50');
            }
            
            // Logros por variedad de materiales
            if (count($stats['tipos_materiales']) >= 5 && !$this->hasAchievement($userId, 'diversity_5')) {
                $achievements[] = 'Reciclador Diverso (5 tipos de materiales)';
                $this->grantAchievement($userId, 'diversity_5');
            }
            
        } catch (Exception $e) {
            error_log("Error verificando logros: " . $e->getMessage());
        }
        
        return $achievements;
    }

    /**
     * Verifica si el usuario tiene un logro
     */
    private function hasAchievement($userId, $achievement)
    {
        // Implementación simple usando sesión (en producción usar base de datos)
        return isset($_SESSION['achievements'][$userId][$achievement]);
    }

    /**
     * Otorga un logro al usuario
     */
    private function grantAchievement($userId, $achievement)
    {
        if (!isset($_SESSION['achievements'])) {
            $_SESSION['achievements'] = [];
        }
        if (!isset($_SESSION['achievements'][$userId])) {
            $_SESSION['achievements'][$userId] = [];
        }
        $_SESSION['achievements'][$userId][$achievement] = true;
    }

    /**
     * Estadísticas por defecto
     */
    private function getDefaultUserStats()
    {
        return [
            'total_reportes' => 0,
            'total_materiales' => 0,
            'reportes_mes' => 0,
            'reportes_semana' => 0,
            'tipos_materiales' => [],
            'puntos' => 0,
            'impacto_ambiental' => ['co2_evitado' => 0, 'arboles_equivalente' => 0, 'autos_equivalente' => 0],
            'streak_dias' => 0
        ];
    }

    /**
     * Obtiene tendencia de crecimiento del usuario
     */
    private function getUserGrowthTrend($userId)
    {
        try {
            return $this->reportModel->query("
                SELECT 
                    DATE_FORMAT(fecha_reporte, '%Y-%m') as mes,
                    COUNT(*) as reportes_mes,
                    LAG(COUNT(*)) OVER (ORDER BY DATE_FORMAT(fecha_reporte, '%Y-%m')) as reportes_mes_anterior
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
     * Compara usuario con promedio general
     */
    private function getUserVsAverage($userId)
    {
        try {
            $userAvg = $this->reportModel->queryOne("
                SELECT AVG(cantidad) as promedio_usuario
                FROM reportes
                WHERE usuario_id = ?
            ", [$userId]);

            $generalAvg = $this->reportModel->queryOne("
                SELECT AVG(cantidad) as promedio_general
                FROM reportes
            ");

            return [
                'usuario' => $userAvg ? (float)$userAvg['promedio_usuario'] : 0,
                'general' => $generalAvg ? (float)$generalAvg['promedio_general'] : 0
            ];
        } catch (Exception $e) {
            return ['usuario' => 0, 'general' => 0];
        }
    }

    /**
     * Obtiene comparación con otros usuarios
     */
    private function getUserComparison($userId)
    {
        try {
            $userStats = $this->getUserStats($userId);
            $allUsers = $this->userModel->getUserRanking(100);
            
            $userPosition = null;
            foreach ($allUsers as $index => $user) {
                if ($user['id'] == $userId) {
                    $userPosition = $index + 1;
                    break;
                }
            }

            return [
                'ranking_position' => $userPosition,
                'total_users' => count($allUsers),
                'top_10_percent' => $userPosition && $userPosition <= (count($allUsers) * 0.1),
                'above_average' => $userStats['total_reportes'] > ($allUsers[0]['total_reportes'] ?? 0) / 2
            ];
        } catch (Exception $e) {
            return [
                'ranking_position' => null,
                'total_users' => 0,
                'top_10_percent' => false,
                'above_average' => false
            ];
        }
    }
}
