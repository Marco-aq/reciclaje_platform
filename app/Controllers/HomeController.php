<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Report;
use App\Models\Stats;
use App\Models\User;

/**
 * Controlador Home - Página principal
 */
class HomeController extends Controller
{
    private $reportModel;
    private $statsModel;
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->reportModel = new Report();
        $this->statsModel = new Stats();
        $this->userModel = new User();
    }

    /**
     * Página principal
     */
    public function index()
    {
        try {
            // Obtener estadísticas generales para mostrar en la página principal
            $stats = $this->getPublicStats();
            
            // Obtener reportes recientes (solo algunos para mostrar actividad)
            $recentReports = $this->getRecentReports();
            
            // Obtener datos para gráficos básicos
            $chartData = $this->getBasicChartData();
            
            // Datos para la vista
            $data = [
                'title' => 'Plataforma de Reciclaje',
                'stats' => $stats,
                'recent_reports' => $recentReports,
                'chart_data' => $chartData,
                'is_authenticated' => $this->isAuthenticated(),
                'current_user' => $this->getCurrentUser()
            ];

            return $this->viewWithLayout('home.index', $data);

        } catch (\Exception $e) {
            error_log("Error en HomeController::index: " . $e->getMessage());
            
            // En caso de error, mostrar página básica
            $data = [
                'title' => 'Plataforma de Reciclaje',
                'error' => env('APP_DEBUG', false) ? $e->getMessage() : 'Error al cargar la página',
                'stats' => $this->getDefaultStats(),
                'is_authenticated' => $this->isAuthenticated(),
                'current_user' => $this->getCurrentUser()
            ];

            return $this->viewWithLayout('home.index', $data);
        }
    }

    /**
     * Obtiene estadísticas públicas
     */
    private function getPublicStats()
    {
        try {
            return [
                'total_reportes' => $this->reportModel->count(),
                'total_usuarios' => $this->userModel->count(),
                'total_materiales' => $this->getTotalMaterials(),
                'impacto_ambiental' => $this->reportModel->getEnvironmentalImpact(),
                'tipos_materiales' => $this->reportModel->query("
                    SELECT 
                        tipo_material,
                        COUNT(*) as cantidad_reportes,
                        SUM(cantidad) as cantidad_total
                    FROM reportes
                    GROUP BY tipo_material
                    ORDER BY cantidad_reportes DESC
                    LIMIT 5
                ")
            ];
        } catch (\Exception $e) {
            error_log("Error obteniendo estadísticas públicas: " . $e->getMessage());
            return $this->getDefaultStats();
        }
    }

    /**
     * Obtiene reportes recientes para mostrar actividad
     */
    private function getRecentReports()
    {
        try {
            return $this->reportModel->query("
                SELECT 
                    r.tipo_material,
                    r.cantidad,
                    r.ubicacion,
                    r.fecha_reporte,
                    u.nombre as usuario_nombre
                FROM reportes r
                LEFT JOIN usuarios u ON r.usuario_id = u.id
                ORDER BY r.fecha_reporte DESC
                LIMIT 10
            ");
        } catch (\Exception $e) {
            error_log("Error obteniendo reportes recientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene datos básicos para gráficos
     */
    private function getBasicChartData()
    {
        try {
            return [
                'materiales_por_tipo' => $this->statsModel->getMaterialsByType(),
                'reportes_ultimos_meses' => $this->statsModel->getReportsByMonth(6),
                'top_ubicaciones' => $this->statsModel->getTopLocations(5)
            ];
        } catch (\Exception $e) {
            error_log("Error obteniendo datos de gráficos: " . $e->getMessage());
            return [
                'materiales_por_tipo' => [],
                'reportes_ultimos_meses' => [],
                'top_ubicaciones' => []
            ];
        }
    }

    /**
     * Obtiene el total de materiales reciclados
     */
    private function getTotalMaterials()
    {
        try {
            $result = $this->reportModel->queryOne("SELECT SUM(cantidad) as total FROM reportes");
            return $result ? (float)$result['total'] : 0;
        } catch (\Exception $e) {
            error_log("Error obteniendo total de materiales: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Estadísticas por defecto en caso de error
     */
    private function getDefaultStats()
    {
        return [
            'total_reportes' => 0,
            'total_usuarios' => 0,
            'total_materiales' => 0,
            'impacto_ambiental' => [
                'total_co2_evitado' => 0,
                'impacto_por_material' => [],
                'equivalente_arboles' => 0,
                'equivalente_autos' => 0
            ],
            'tipos_materiales' => []
        ];
    }

    /**
     * Página "Acerca de"
     */
    public function about()
    {
        $data = [
            'title' => 'Acerca de - Plataforma de Reciclaje',
            'is_authenticated' => $this->isAuthenticated(),
            'current_user' => $this->getCurrentUser()
        ];

        return $this->viewWithLayout('home.about', $data);
    }

    /**
     * Página de contacto
     */
    public function contact()
    {
        $data = [
            'title' => 'Contacto - Plataforma de Reciclaje',
            'is_authenticated' => $this->isAuthenticated(),
            'current_user' => $this->getCurrentUser()
        ];

        return $this->viewWithLayout('home.contact', $data);
    }

    /**
     * Procesa formulario de contacto
     */
    public function submitContact()
    {
        $requestData = $this->getRequestData();
        
        // Validar datos
        $errors = $this->validate($requestData, [
            'nombre' => 'required|min:2|max:100',
            'email' => 'required|email',
            'mensaje' => 'required|min:10|max:1000'
        ]);

        if (!empty($errors)) {
            $this->setFlash('error', 'Por favor corrige los errores en el formulario');
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $requestData;
            return $this->redirect('/contact');
        }

        try {
            // Aquí podrías guardar el mensaje en base de datos o enviarlo por email
            $this->logContactMessage($requestData);
            
            $this->setFlash('success', 'Tu mensaje ha sido enviado correctamente. Te responderemos pronto.');
            return $this->redirect('/contact');

        } catch (\Exception $e) {
            error_log("Error procesando contacto: " . $e->getMessage());
            $this->setFlash('error', 'Error al enviar el mensaje. Por favor intenta más tarde.');
            return $this->redirect('/contact');
        }
    }

    /**
     * API: Obtiene estadísticas públicas
     */
    public function apiStats()
    {
        try {
            $stats = $this->getPublicStats();
            return $this->jsonSuccess($stats);
        } catch (\Exception $e) {
            error_log("Error en API stats: " . $e->getMessage());
            return $this->jsonError('Error obteniendo estadísticas');
        }
    }

    /**
     * API: Obtiene datos de gráficos públicos
     */
    public function apiChartData()
    {
        try {
            $chartData = $this->getBasicChartData();
            return $this->jsonSuccess($chartData);
        } catch (\Exception $e) {
            error_log("Error en API chart data: " . $e->getMessage());
            return $this->jsonError('Error obteniendo datos de gráficos');
        }
    }

    /**
     * Registra mensaje de contacto en log
     */
    private function logContactMessage($data)
    {
        $logFile = STORAGE_PATH . '/logs/contact.log';
        $timestamp = date('Y-m-d H:i:s');
        $message = "[{$timestamp}] Contacto - Nombre: {$data['nombre']}, Email: {$data['email']}, Mensaje: {$data['mensaje']}" . PHP_EOL;
        
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);
    }

    /**
     * Búsqueda general
     */
    public function search()
    {
        $query = $_GET['q'] ?? '';
        
        if (empty($query)) {
            return $this->redirect('/');
        }

        try {
            // Buscar en reportes
            $reports = $this->reportModel->query("
                SELECT 
                    r.*,
                    u.nombre as usuario_nombre
                FROM reportes r
                LEFT JOIN usuarios u ON r.usuario_id = u.id
                WHERE r.tipo_material LIKE ? 
                   OR r.ubicacion LIKE ? 
                   OR r.descripcion LIKE ?
                   OR u.nombre LIKE ?
                ORDER BY r.fecha_reporte DESC
                LIMIT 20
            ", ["%{$query}%", "%{$query}%", "%{$query}%", "%{$query}%"]);

            // Buscar ubicaciones
            $locations = $this->reportModel->query("
                SELECT 
                    ubicacion,
                    COUNT(*) as cantidad_reportes
                FROM reportes
                WHERE ubicacion LIKE ?
                GROUP BY ubicacion
                ORDER BY cantidad_reportes DESC
                LIMIT 10
            ", ["%{$query}%"]);

            $data = [
                'title' => "Resultados de búsqueda: {$query}",
                'query' => $query,
                'reports' => $reports,
                'locations' => $locations,
                'is_authenticated' => $this->isAuthenticated(),
                'current_user' => $this->getCurrentUser()
            ];

            return $this->viewWithLayout('home.search', $data);

        } catch (\Exception $e) {
            error_log("Error en búsqueda: " . $e->getMessage());
            $this->setFlash('error', 'Error al realizar la búsqueda');
            return $this->redirect('/');
        }
    }

    /**
     * Página de términos y condiciones
     */
    public function terms()
    {
        $data = [
            'title' => 'Términos y Condiciones',
            'is_authenticated' => $this->isAuthenticated(),
            'current_user' => $this->getCurrentUser()
        ];

        return $this->viewWithLayout('home.terms', $data);
    }

    /**
     * Página de política de privacidad
     */
    public function privacy()
    {
        $data = [
            'title' => 'Política de Privacidad',
            'is_authenticated' => $this->isAuthenticated(),
            'current_user' => $this->getCurrentUser()
        ];

        return $this->viewWithLayout('home.privacy', $data);
    }
}
