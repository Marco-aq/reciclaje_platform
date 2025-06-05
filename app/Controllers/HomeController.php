<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Report;
use App\Models\User;

/**
 * HomeController - Controlador de la página principal
 * 
 * Maneja la página de inicio y estadísticas generales
 * de la plataforma.
 */
class HomeController extends Controller
{
    private Report $reportModel;
    private User $userModel;

    protected function init(): void
    {
        $this->reportModel = new Report();
        $this->userModel = new User();
    }

    /**
     * Página principal
     * 
     * @return void
     */
    public function index(): void
    {
        try {
            // Obtener estadísticas para la página principal
            $stats = $this->getHomeStats();
            
            // Obtener reportes recientes para mostrar actividad
            $recentReports = $this->reportModel->getRecentReports(5);
            
            // Datos para la vista
            $data = [
                'pageTitle' => 'Inicio - EcoCusco',
                'stats' => $stats,
                'recentReports' => $recentReports,
                'currentUser' => $this->getCurrentUser()
            ];
            
            $this->render('home/index', $data);
            
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Página "Acerca de"
     * 
     * @return void
     */
    public function about(): void
    {
        $data = [
            'pageTitle' => 'Acerca de - EcoCusco'
        ];
        
        $this->render('home/about', $data);
    }

    /**
     * Página de contacto
     * 
     * @return void
     */
    public function contact(): void
    {
        $data = [
            'pageTitle' => 'Contacto - EcoCusco'
        ];
        
        if ($this->isMethod('POST')) {
            $this->handleContactForm();
            return;
        }
        
        $this->render('home/contact', $data);
    }

    /**
     * Procesa el formulario de contacto
     * 
     * @return void
     */
    private function handleContactForm(): void
    {
        $validation = $this->validate($this->request->all(), [
            'nombre' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'asunto' => 'required|string|max:200',
            'mensaje' => 'required|string|max:1000'
        ]);
        
        if (!$validation['valid']) {
            $this->setValidationErrors($validation['errors']);
            $this->request->saveOldInput();
            $this->redirectBack();
            return;
        }
        
        // Aquí se podría procesar el envío del email
        // Por ahora solo mostrar mensaje de éxito
        $this->setFlash('success', 'Tu mensaje ha sido enviado correctamente. Te contactaremos pronto.');
        $this->redirect('/contact');
    }

    /**
     * Página de términos y condiciones
     * 
     * @return void
     */
    public function terms(): void
    {
        $data = [
            'pageTitle' => 'Términos y Condiciones - EcoCusco'
        ];
        
        $this->render('home/terms', $data);
    }

    /**
     * Página de política de privacidad
     * 
     * @return void
     */
    public function privacy(): void
    {
        $data = [
            'pageTitle' => 'Política de Privacidad - EcoCusco'
        ];
        
        $this->render('home/privacy', $data);
    }

    /**
     * API endpoint para obtener estadísticas básicas
     * 
     * @return void
     */
    public function apiStats(): void
    {
        try {
            $stats = $this->getHomeStats();
            
            $this->renderJson([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            $this->renderJson([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    /**
     * API endpoint para obtener reportes recientes
     * 
     * @return void
     */
    public function apiRecentReports(): void
    {
        try {
            $limit = (int) $this->request->get('limit', 10);
            $limit = min($limit, 50); // Máximo 50 reportes
            
            $reports = $this->reportModel->getRecentReports($limit);
            
            $this->renderJson([
                'success' => true,
                'data' => $reports
            ]);
            
        } catch (\Exception $e) {
            $this->renderJson([
                'success' => false,
                'message' => 'Error al obtener reportes'
            ], 500);
        }
    }

    /**
     * Búsqueda general en la plataforma
     * 
     * @return void
     */
    public function search(): void
    {
        $query = trim($this->request->get('q', ''));
        
        if (empty($query)) {
            $this->redirect('/');
            return;
        }
        
        try {
            // Buscar en reportes
            $reports = $this->reportModel->searchReports($query);
            
            // Buscar en usuarios (solo si el usuario actual es admin)
            $users = [];
            if ($this->isAuthenticated()) {
                $currentUser = $this->getCurrentUser();
                if ($currentUser && $currentUser['tipo_usuario'] === 'admin') {
                    $users = $this->userModel->searchUsers($query);
                }
            }
            
            $data = [
                'pageTitle' => "Búsqueda: {$query} - EcoCusco",
                'query' => $query,
                'reports' => $reports,
                'users' => $users,
                'totalResults' => count($reports) + count($users)
            ];
            
            $this->render('home/search', $data);
            
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Obtiene estadísticas para la página principal
     * 
     * @return array
     */
    private function getHomeStats(): array
    {
        $reportStats = $this->reportModel->getStats();
        $userStats = $this->userModel->getStats();
        
        return [
            'reportes_totales' => $reportStats['total'],
            'reportes_resueltos' => $reportStats['por_estado']['resuelto'] ?? 0,
            'reportes_pendientes' => $reportStats['por_estado']['pendiente'] ?? 0,
            'usuarios_activos' => $userStats['activos'],
            'usuarios_totales' => $userStats['total'],
            'tiempo_promedio_resolucion' => $reportStats['tiempo_promedio_resolucion'],
            'tipos_residuo_top' => array_slice($reportStats['tipos_residuo_top'], 0, 5),
            'reportes_este_mes' => $this->getReportsThisMonth(),
            'porcentaje_resolucion' => $this->calculateResolutionPercentage($reportStats)
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
     * Calcula el porcentaje de resolución de reportes
     * 
     * @param array $reportStats
     * @return float
     */
    private function calculateResolutionPercentage(array $reportStats): float
    {
        if ($reportStats['total'] == 0) {
            return 0;
        }
        
        $resueltos = $reportStats['por_estado']['resuelto'] ?? 0;
        return round(($resueltos / $reportStats['total']) * 100, 1);
    }

    /**
     * Manifiesto de la aplicación web progresiva
     * 
     * @return void
     */
    public function manifest(): void
    {
        header('Content-Type: application/json');
        
        $manifest = [
            'name' => 'EcoCusco - Gestión de Residuos',
            'short_name' => 'EcoCusco',
            'description' => 'Plataforma colaborativa para reportar y gestionar residuos sólidos urbanos en Cusco',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#2E7D32',
            'icons' => [
                [
                    'src' => '/assets/img/icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png'
                ],
                [
                    'src' => '/assets/img/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png'
                ]
            ]
        ];
        
        echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
