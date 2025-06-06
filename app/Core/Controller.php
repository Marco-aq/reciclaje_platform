<?php

namespace App\Core;

use Exception;

/**
 * Clase base Controller
 * Proporciona funcionalidades comunes para todos los controladores
 */
class Controller
{
    protected $view;
    protected $data = [];

    public function __construct()
    {
        $this->view = new View();
    }

    /**
     * Renderiza una vista
     */
    protected function view($viewName, $data = [])
    {
        return $this->view->render($viewName, array_merge($this->data, $data));
    }

    /**
     * Renderiza una vista con layout
     */
    protected function viewWithLayout($viewName, $data = [], $layout = 'main')
    {
        return $this->view->renderWithLayout($viewName, array_merge($this->data, $data), $layout);
    }

    /**
     * Retorna respuesta JSON
     */
    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Retorna respuesta JSON de éxito
     */
    protected function jsonSuccess($data = [], $message = 'Operación exitosa')
    {
        return $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Retorna respuesta JSON de error
     */
    protected function jsonError($message = 'Error en la operación', $status = 400, $errors = [])
    {
        return $this->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }

    /**
     * Redirecciona a una URL
     */
    protected function redirect($url, $message = null, $type = 'info')
    {
        if ($message) {
            $this->setFlash($type, $message);
        }
        
        header("Location: {$url}");
        exit;
    }

    /**
     * Redirecciona hacia atrás
     */
    protected function redirectBack($message = null, $type = 'info')
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer, $message, $type);
    }

    /**
     * Establece un mensaje flash
     */
    protected function setFlash($type, $message)
    {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Obtiene mensajes flash
     */
    protected function getFlash($type = null)
    {
        if (!isset($_SESSION['flash'])) {
            return $type ? null : [];
        }

        if ($type) {
            $message = $_SESSION['flash'][$type] ?? null;
            unset($_SESSION['flash'][$type]);
            return $message;
        }

        $messages = $_SESSION['flash'];
        $_SESSION['flash'] = [];
        return $messages;
    }

    /**
     * Valida datos de entrada
     */
    protected function validate($data, $rules)
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $ruleArray = explode('|', $rule);

            foreach ($ruleArray as $singleRule) {
                $ruleParts = explode(':', $singleRule);
                $ruleName = $ruleParts[0];
                $ruleParam = $ruleParts[1] ?? null;

                switch ($ruleName) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = "El campo {$field} es requerido";
                        }
                        break;

                    case 'email':
                        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "El campo {$field} debe ser un email válido";
                        }
                        break;

                    case 'min':
                        if ($value && strlen($value) < $ruleParam) {
                            $errors[$field][] = "El campo {$field} debe tener al menos {$ruleParam} caracteres";
                        }
                        break;

                    case 'max':
                        if ($value && strlen($value) > $ruleParam) {
                            $errors[$field][] = "El campo {$field} no puede tener más de {$ruleParam} caracteres";
                        }
                        break;

                    case 'numeric':
                        if ($value && !is_numeric($value)) {
                            $errors[$field][] = "El campo {$field} debe ser numérico";
                        }
                        break;

                    case 'confirmed':
                        $confirmField = $field . '_confirmation';
                        if ($value !== ($data[$confirmField] ?? null)) {
                            $errors[$field][] = "El campo {$field} no coincide con su confirmación";
                        }
                        break;
                }
            }
        }

        return $errors;
    }

    /**
     * Sanitiza datos de entrada
     */
    protected function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Obtiene datos de la petición
     */
    protected function getRequestData()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET':
                return $this->sanitize($_GET);
            case 'POST':
                return $this->sanitize($_POST);
            case 'PUT':
            case 'DELETE':
                parse_str(file_get_contents('php://input'), $data);
                return $this->sanitize($data);
            default:
                return [];
        }
    }

    /**
     * Verifica si el usuario está autenticado
     */
    protected function requireAuth()
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login', 'Debes iniciar sesión para acceder a esta página', 'warning');
        }
    }

    /**
     * Verifica si el usuario está autenticado
     */
    protected function isAuthenticated()
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Obtiene el usuario actual
     */
    protected function getCurrentUser()
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        // Si ya está en cache de sesión
        if (isset($_SESSION['user_data'])) {
            return $_SESSION['user_data'];
        }

        // Cargar desde base de datos
        try {
            $db = Database::getInstance();
            $user = $db->fetchOne(
                "SELECT id, nombre, email, created_at FROM usuarios WHERE id = ?",
                [$_SESSION['user_id']]
            );

            if ($user) {
                $_SESSION['user_data'] = $user;
                return $user;
            }
        } catch (Exception $e) {
            error_log("Error obteniendo usuario actual: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Genera token CSRF
     */
    protected function generateCsrfToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verifica token CSRF
     */
    protected function verifyCsrfToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Manejo de uploads de archivos
     */
    protected function handleFileUpload($fileInput, $allowedTypes = null, $maxSize = null)
    {
        if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error en la subida del archivo");
        }

        $file = $_FILES[$fileInput];
        $config = require CONFIG_PATH . '/app.php';
        
        $allowedTypes = $allowedTypes ?? $config['upload']['allowed_types'];
        $maxSize = $maxSize ?? $config['upload']['max_size'];

        // Verificar tamaño
        if ($file['size'] > $maxSize) {
            throw new Exception("El archivo es demasiado grande");
        }

        // Verificar tipo
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $allowedTypes)) {
            throw new Exception("Tipo de archivo no permitido");
        }

        // Generar nombre único
        $fileName = uniqid() . '.' . $fileExt;
        $uploadPath = $config['upload']['path'] . $fileName;

        // Crear directorio si no existe
        $uploadDir = dirname($uploadPath);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception("Error al guardar el archivo");
        }

        return $fileName;
    }
}
