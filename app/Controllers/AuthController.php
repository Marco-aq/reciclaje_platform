<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use Exception;

/**
 * Controlador de Autenticación
 */
class AuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Muestra formulario de login
     */
    public function showLogin()
    {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->isAuthenticated()) {
            return $this->redirect('/dashboard');
        }

        $data = [
            'title' => 'Iniciar Sesión',
            'csrf_token' => $this->generateCsrfToken()
        ];

        return $this->viewWithLayout('auth.login', $data);
    }

    /**
     * Procesa el login
     */
    public function login()
    {
        // Verificar CSRF
        $requestData = $this->getRequestData();
        
        if (!isset($requestData['_token']) || !$this->verifyCsrfToken($requestData['_token'])) {
            $this->setFlash('error', 'Token de seguridad inválido');
            return $this->redirect('/login');
        }

        // Validar datos
        $errors = $this->validate($requestData, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!empty($errors)) {
            $this->setFlash('error', 'Por favor corrige los errores en el formulario');
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $requestData;
            return $this->redirect('/login');
        }

        try {
            // Intentar autenticar
            $user = $this->userModel->authenticate($requestData['email'], $requestData['password']);
            
            if (!$user) {
                $this->setFlash('error', 'Credenciales incorrectas');
                $_SESSION['old'] = ['email' => $requestData['email']];
                return $this->redirect('/login');
            }

            // Login exitoso
            $this->startUserSession($user);
            
            $this->setFlash('success', '¡Bienvenido de vuelta, ' . $user['nombre'] . '!');
            
            // Redirigir a la página solicitada o al dashboard
            $redirectTo = $_SESSION['intended_url'] ?? '/dashboard';
            unset($_SESSION['intended_url']);
            
            return $this->redirect($redirectTo);

        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            $this->setFlash('error', 'Error al iniciar sesión. Por favor intenta más tarde.');
            return $this->redirect('/login');
        }
    }

    /**
     * Muestra formulario de registro
     */
    public function showRegister()
    {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->isAuthenticated()) {
            return $this->redirect('/dashboard');
        }

        $data = [
            'title' => 'Crear Cuenta',
            'csrf_token' => $this->generateCsrfToken()
        ];

        return $this->viewWithLayout('auth.register', $data);
    }

    /**
     * Procesa el registro
     */
    public function register()
    {
        // Verificar CSRF
        $requestData = $this->getRequestData();
        
        if (!isset($requestData['_token']) || !$this->verifyCsrfToken($requestData['_token'])) {
            $this->setFlash('error', 'Token de seguridad inválido');
            return $this->redirect('/register');
        }

        // Validar datos
        $errors = $this->validate($requestData, [
            'nombre' => 'required|min:2|max:100',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'password_confirmation' => 'required'
        ]);

        // Verificar que las contraseñas coincidan
        if ($requestData['password'] !== $requestData['password_confirmation']) {
            $errors['password'][] = 'Las contraseñas no coinciden';
        }

        if (!empty($errors)) {
            $this->setFlash('error', 'Por favor corrige los errores en el formulario');
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = $requestData;
            return $this->redirect('/register');
        }

        try {
            // Verificar si el email ya existe
            if ($this->userModel->findByEmail($requestData['email'])) {
                $this->setFlash('error', 'El email ya está registrado');
                $_SESSION['old'] = $requestData;
                return $this->redirect('/register');
            }

            // Crear usuario
            $userData = [
                'nombre' => $requestData['nombre'],
                'email' => $requestData['email'],
                'password' => $requestData['password']
            ];

            $user = $this->userModel->createUser($userData);
            
            if (!$user) {
                throw new Exception("Error al crear el usuario");
            }

            // Iniciar sesión automáticamente
            $this->startUserSession($user);
            
            $this->setFlash('success', '¡Cuenta creada exitosamente! Bienvenido, ' . $user['nombre'] . '!');
            return $this->redirect('/dashboard');

        } catch (Exception $e) {
            error_log("Error en registro: " . $e->getMessage());
            
            if (strpos($e->getMessage(), 'email ya está registrado') !== false) {
                $this->setFlash('error', 'El email ya está registrado');
            } else {
                $this->setFlash('error', 'Error al crear la cuenta. Por favor intenta más tarde.');
            }
            
            $_SESSION['old'] = $requestData;
            return $this->redirect('/register');
        }
    }

    /**
     * Cierra sesión
     */
    public function logout()
    {
        $userName = $_SESSION['user_data']['nombre'] ?? 'Usuario';
        
        // Limpiar sesión
        $this->destroyUserSession();
        
        $this->setFlash('success', '¡Hasta luego, ' . $userName . '!');
        return $this->redirect('/');
    }

    /**
     * Muestra formulario para recuperar contraseña
     */
    public function showForgotPassword()
    {
        $data = [
            'title' => 'Recuperar Contraseña',
            'csrf_token' => $this->generateCsrfToken()
        ];

        return $this->viewWithLayout('auth.forgot_password', $data);
    }

    /**
     * Procesa solicitud de recuperación de contraseña
     */
    public function forgotPassword()
    {
        $requestData = $this->getRequestData();
        
        // Verificar CSRF
        if (!isset($requestData['_token']) || !$this->verifyCsrfToken($requestData['_token'])) {
            $this->setFlash('error', 'Token de seguridad inválido');
            return $this->redirect('/forgot-password');
        }

        // Validar email
        $errors = $this->validate($requestData, [
            'email' => 'required|email'
        ]);

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            return $this->redirect('/forgot-password');
        }

        try {
            // Verificar si el usuario existe
            $user = $this->userModel->findByEmail($requestData['email']);
            
            if (!$user) {
                // Por seguridad, no revelar si el email existe o no
                $this->setFlash('success', 'Si el email existe en nuestro sistema, recibirás un enlace de recuperación.');
                return $this->redirect('/login');
            }

            // Generar token de recuperación
            $token = $this->userModel->generatePasswordResetToken($requestData['email']);
            
            // Aquí normalmente enviarías un email
            // Por ahora, solo log el token (en producción usar servicio de email)
            $this->logPasswordResetToken($requestData['email'], $token);
            
            $this->setFlash('success', 'Si el email existe en nuestro sistema, recibirás un enlace de recuperación.');
            return $this->redirect('/login');

        } catch (Exception $e) {
            error_log("Error en recuperación de contraseña: " . $e->getMessage());
            $this->setFlash('error', 'Error al procesar la solicitud. Por favor intenta más tarde.');
            return $this->redirect('/forgot-password');
        }
    }

    /**
     * Muestra formulario para restablecer contraseña
     */
    public function showResetPassword($token)
    {
        try {
            // Verificar token
            $resetData = $this->userModel->verifyPasswordResetToken($token);
            
            if (!$resetData) {
                $this->setFlash('error', 'Token de recuperación inválido o expirado');
                return $this->redirect('/login');
            }

            $data = [
                'title' => 'Restablecer Contraseña',
                'token' => $token,
                'csrf_token' => $this->generateCsrfToken()
            ];

            return $this->viewWithLayout('auth.reset_password', $data);

        } catch (Exception $e) {
            error_log("Error verificando token de reset: " . $e->getMessage());
            $this->setFlash('error', 'Error al verificar el token');
            return $this->redirect('/login');
        }
    }

    /**
     * Procesa restablecimiento de contraseña
     */
    public function resetPassword()
    {
        $requestData = $this->getRequestData();
        
        // Verificar CSRF
        if (!isset($requestData['_token']) || !$this->verifyCsrfToken($requestData['_token'])) {
            $this->setFlash('error', 'Token de seguridad inválido');
            return $this->redirect('/login');
        }

        // Validar datos
        $errors = $this->validate($requestData, [
            'token' => 'required',
            'password' => 'required|min:6',
            'password_confirmation' => 'required'
        ]);

        if ($requestData['password'] !== $requestData['password_confirmation']) {
            $errors['password'][] = 'Las contraseñas no coinciden';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            return $this->redirect('/reset-password/' . $requestData['token']);
        }

        try {
            // Restablecer contraseña
            $this->userModel->resetPassword($requestData['token'], $requestData['password']);
            
            $this->setFlash('success', 'Contraseña restablecida exitosamente. Ya puedes iniciar sesión.');
            return $this->redirect('/login');

        } catch (Exception $e) {
            error_log("Error restableciendo contraseña: " . $e->getMessage());
            $this->setFlash('error', 'Error al restablecer la contraseña. El token puede haber expirado.');
            return $this->redirect('/login');
        }
    }

    /**
     * API: Login
     */
    public function apiLogin()
    {
        $requestData = $this->getRequestData();

        // Validar datos
        $errors = $this->validate($requestData, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!empty($errors)) {
            return $this->jsonError('Datos inválidos', 400, $errors);
        }

        try {
            $user = $this->userModel->authenticate($requestData['email'], $requestData['password']);
            
            if (!$user) {
                return $this->jsonError('Credenciales incorrectas', 401);
            }

            $this->startUserSession($user);
            
            return $this->jsonSuccess([
                'user' => $user,
                'message' => 'Login exitoso'
            ]);

        } catch (Exception $e) {
            error_log("Error en API login: " . $e->getMessage());
            return $this->jsonError('Error interno del servidor', 500);
        }
    }

    /**
     * API: Registro
     */
    public function apiRegister()
    {
        $requestData = $this->getRequestData();

        // Validar datos
        $errors = $this->validate($requestData, [
            'nombre' => 'required|min:2|max:100',
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if (!empty($errors)) {
            return $this->jsonError('Datos inválidos', 400, $errors);
        }

        try {
            // Verificar si el email ya existe
            if ($this->userModel->findByEmail($requestData['email'])) {
                return $this->jsonError('El email ya está registrado', 409);
            }

            $user = $this->userModel->createUser($requestData);
            $this->startUserSession($user);
            
            return $this->jsonSuccess([
                'user' => $user,
                'message' => 'Usuario creado exitosamente'
            ], 201);

        } catch (Exception $e) {
            error_log("Error en API register: " . $e->getMessage());
            return $this->jsonError('Error interno del servidor', 500);
        }
    }

    /**
     * API: Logout
     */
    public function apiLogout()
    {
        $this->destroyUserSession();
        return $this->jsonSuccess(['message' => 'Logout exitoso']);
    }

    /**
     * Inicia sesión de usuario
     */
    private function startUserSession($user)
    {
        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);
        
        // Guardar datos del usuario en sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_data'] = $user;
        $_SESSION['login_time'] = time();
        
        // Limpiar datos temporales
        unset($_SESSION['errors']);
        unset($_SESSION['old']);
    }

    /**
     * Destruye sesión de usuario
     */
    private function destroyUserSession()
    {
        // Limpiar variables de sesión relacionadas con el usuario
        unset($_SESSION['user_id']);
        unset($_SESSION['user_data']);
        unset($_SESSION['login_time']);
        unset($_SESSION['errors']);
        unset($_SESSION['old']);
        unset($_SESSION['csrf_token']);
        
        // Regenerar ID de sesión
        session_regenerate_id(true);
    }

    /**
     * Registra token de recuperación en log (temporal)
     */
    private function logPasswordResetToken($email, $token)
    {
        $logFile = STORAGE_PATH . '/logs/password_reset.log';
        $timestamp = date('Y-m-d H:i:s');
        $resetUrl = env('APP_URL') . "/reset-password/{$token}";
        $message = "[{$timestamp}] Password reset - Email: {$email}, URL: {$resetUrl}" . PHP_EOL;
        
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $message, FILE_APPEND | LOCK_EX);
    }

    /**
     * Verifica estado de autenticación (API)
     */
    public function apiCheckAuth()
    {
        if ($this->isAuthenticated()) {
            return $this->jsonSuccess([
                'authenticated' => true,
                'user' => $this->getCurrentUser()
            ]);
        } else {
            return $this->jsonSuccess([
                'authenticated' => false,
                'user' => null
            ]);
        }
    }
}
