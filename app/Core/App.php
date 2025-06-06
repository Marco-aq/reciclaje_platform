<?php

namespace App\Core;

use Exception;

/**
 * Clase App - Núcleo de la aplicación MVC
 * Maneja la inicialización, configuración y enrutamiento
 */
class App
{
    private $router;
    private $config;
    private $view;

    public function __construct()
    {
        $this->config = require CONFIG_PATH . '/app.php';
        $this->router = new Router();
        $this->view = new View();
        
        $this->initialize();
        $this->registerRoutes();
    }

    /**
     * Inicializa la aplicación
     */
    private function initialize()
    {
        // Configurar zona horaria
        date_default_timezone_set('America/Mexico_City');
        
        // Configurar sesiones
        $this->configureSession();
        
        // Configurar manejo de errores
        $this->configureErrorHandling();
        
        // Compartir datos globales con las vistas
        $this->shareGlobalViewData();
    }

    /**
     * Configura las sesiones
     */
    private function configureSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configurar parámetros de sesión
            ini_set('session.cookie_lifetime', $this->config['session']['lifetime']);
            ini_set('session.cookie_secure', $this->config['session']['secure']);
            ini_set('session.cookie_httponly', $this->config['session']['http_only']);
            ini_set('session.use_strict_mode', 1);
            
            session_start();
            
            // Regenerar ID de sesión para seguridad
            if ($this->config['security']['session_regenerate'] && !isset($_SESSION['initiated'])) {
                session_regenerate_id(true);
                $_SESSION['initiated'] = true;
            }
        }
    }

    /**
     * Configura el manejo de errores
     */
    private function configureErrorHandling()
    {
        if ($this->config['debug']) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
            
            // Registrar manejador de errores personalizado
            set_error_handler([$this, 'handleError']);
            set_exception_handler([$this, 'handleException']);
        }
    }

    /**
     * Comparte datos globales con las vistas
     */
    private function shareGlobalViewData()
    {
        $this->view->share([
            'app_name' => $this->config['name'],
            'app_url' => $this->config['url'],
            'is_debug' => $this->config['debug'],
        ]);
    }

    /**
     * Registra todas las rutas de la aplicación
     */
    private function registerRoutes()
    {
        // Rutas públicas
        $this->router->get('/', 'HomeController@index');
        $this->router->get('/home', 'HomeController@index');
        
        // Rutas de autenticación
        $this->router->get('/login', 'AuthController@showLogin');
        $this->router->post('/login', 'AuthController@login');
        $this->router->get('/register', 'AuthController@showRegister');
        $this->router->post('/register', 'AuthController@register');
        $this->router->get('/logout', 'AuthController@logout');
        $this->router->post('/logout', 'AuthController@logout');
        
        // Rutas protegidas (requieren autenticación)
        $this->router->get('/dashboard', 'DashboardController@index');
        
        // Rutas de reportes
        $this->router->get('/reportes', 'ReportController@index');
        $this->router->get('/reportes/crear', 'ReportController@create');
        $this->router->post('/reportes', 'ReportController@store');
        $this->router->get('/reportes/{id}', 'ReportController@show');
        $this->router->get('/reportes/{id}/editar', 'ReportController@edit');
        $this->router->post('/reportes/{id}', 'ReportController@update');
        $this->router->post('/reportes/{id}/eliminar', 'ReportController@delete');
        
        // Rutas de estadísticas
        $this->router->get('/estadisticas', 'StatsController@index');
        $this->router->get('/api/estadisticas/datos', 'StatsController@getData');
        $this->router->get('/api/estadisticas/graficos', 'StatsController@getChartData');
        
        // Rutas API
        $this->router->get('/api/reportes', 'ReportController@apiIndex');
        $this->router->post('/api/reportes', 'ReportController@apiStore');
        $this->router->get('/api/reportes/{id}', 'ReportController@apiShow');
        $this->router->put('/api/reportes/{id}', 'ReportController@apiUpdate');
        $this->router->delete('/api/reportes/{id}', 'ReportController@apiDelete');
        
        // Middleware de autenticación para rutas protegidas
        $authMiddleware = function() {
            if (!isset($_SESSION['user_id'])) {
                header('Location: /login');
                exit;
            }
        };
        
        // Aplicar middleware a rutas específicas
        $this->router->middleware('auth', new class {
            public function handle() {
                if (!isset($_SESSION['user_id'])) {
                    if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
                        http_response_code(401);
                        header('Content-Type: application/json');
                        echo json_encode(['error' => 'No autorizado']);
                        exit;
                    } else {
                        header('Location: /login');
                        exit;
                    }
                }
            }
        });
    }

    /**
     * Ejecuta la aplicación
     */
    public function run()
    {
        try {
            // Verificar conexión a base de datos
            $this->checkDatabase();
            
            // Resolver y ejecutar la ruta
            $this->router->resolve();
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Verifica la conexión a la base de datos
     */
    private function checkDatabase()
    {
        try {
            $db = Database::getInstance();
            $db->getConnection();
        } catch (Exception $e) {
            if ($this->config['debug']) {
                throw new Exception("Error de conexión a base de datos: " . $e->getMessage());
            } else {
                throw new Exception("Error de configuración del sistema. Por favor contacta al administrador.");
            }
        }
    }

    /**
     * Maneja errores de PHP
     */
    public function handleError($severity, $message, $file, $line)
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        $errorMessage = "Error PHP [{$severity}]: {$message} en {$file}:{$line}";
        
        // Log del error
        $this->logError($errorMessage);
        
        if ($this->config['debug']) {
            echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px;'>";
            echo "<strong>Error:</strong> " . htmlspecialchars($message) . "<br>";
            echo "<strong>Archivo:</strong> " . htmlspecialchars($file) . "<br>";
            echo "<strong>Línea:</strong> {$line}";
            echo "</div>";
        }

        return true;
    }

    /**
     * Maneja excepciones no capturadas
     */
    public function handleException($exception)
    {
        $errorMessage = "Excepción: " . $exception->getMessage() . " en " . $exception->getFile() . ":" . $exception->getLine();
        
        // Log de la excepción
        $this->logError($errorMessage);
        $this->logError($exception->getTraceAsString());
        
        // Respuesta según el tipo de petición
        if ($this->isApiRequest()) {
            $this->handleApiException($exception);
        } else {
            $this->handleWebException($exception);
        }
    }

    /**
     * Maneja excepciones para peticiones API
     */
    private function handleApiException($exception)
    {
        http_response_code(500);
        header('Content-Type: application/json');
        
        $response = [
            'success' => false,
            'message' => 'Error interno del servidor'
        ];
        
        if ($this->config['debug']) {
            $response['debug'] = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Maneja excepciones para peticiones web
     */
    private function handleWebException($exception)
    {
        http_response_code(500);
        
        if ($this->config['debug']) {
            echo "<h1>Error de Aplicación</h1>";
            echo "<p><strong>Mensaje:</strong> " . htmlspecialchars($exception->getMessage()) . "</p>";
            echo "<p><strong>Archivo:</strong> " . htmlspecialchars($exception->getFile()) . "</p>";
            echo "<p><strong>Línea:</strong> " . $exception->getLine() . "</p>";
            echo "<h3>Stack Trace:</h3>";
            echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
        } else {
            // Mostrar página de error amigable
            $errorView = APP_PATH . '/Views/errors/500.php';
            if (file_exists($errorView)) {
                include $errorView;
            } else {
                echo "<h1>Error del Servidor</h1>";
                echo "<p>Ha ocurrido un error inesperado. Por favor intenta más tarde.</p>";
                echo "<a href='/'>Volver al inicio</a>";
            }
        }
    }

    /**
     * Verifica si es una petición API
     */
    private function isApiRequest()
    {
        return strpos($_SERVER['REQUEST_URI'], '/api/') === 0 || 
               (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    /**
     * Registra errores en log
     */
    private function logError($message)
    {
        $logFile = STORAGE_PATH . '/logs/app.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        // Crear directorio si no existe
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Obtiene la configuración de la aplicación
     */
    public function getConfig($key = null)
    {
        if ($key === null) {
            return $this->config;
        }
        
        return $this->config[$key] ?? null;
    }

    /**
     * Obtiene el router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Obtiene la instancia de View
     */
    public function getView()
    {
        return $this->view;
    }
}
