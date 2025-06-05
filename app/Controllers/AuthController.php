<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Config;
use App\Models\User;

/**
 * AuthController - Controlador de autenticación
 * 
 * Maneja el registro, login, logout y recuperación
 * de contraseñas de los usuarios.
 */
class AuthController extends Controller
{
    private User $userModel;

    protected function init(): void
    {
        $this->userModel = new User();
    }

    /**
     * Muestra el formulario de login
     * 
     * @return void
     */
    public function showLogin(): void
    {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }

        $data = [
            'pageTitle' => 'Iniciar Sesión - EcoCusco',
            'errors' => $this->getValidationErrors(),
            'csrfToken' => $this->generateCsrfToken()
        ];

        $this->render('auth/login', $data);
    }

    /**
     * Procesa el login
     * 
     * @return void
     */
    public function login(): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('/login');
            return;
        }

        // Verificar token CSRF
        if (!$this->verifyCsrfToken()) {
            $this->setFlash('error', 'Token de seguridad inválido. Intenta nuevamente.');
            $this->redirect('/login');
            return;
        }

        // Validar datos de entrada
        $validation = $this->validate($this->request->all(), [
            'email' => 'required|email|max:150',
            'password' => 'required|string|min:6'
        ]);

        if (!$validation['valid']) {
            $this->setValidationErrors($validation['errors']);
            $this->request->saveOldInput();
            $this->redirect('/login');
            return;
        }

        $email = $this->request->get('email');
        $password = $this->request->get('password');
        $remember = $this->request->get('remember', false);

        try {
            // Verificar credenciales
            $user = $this->userModel->verifyCredentials($email, $password);

            if ($user) {
                // Iniciar sesión
                $this->startUserSession($user);
                
                // Actualizar última actividad
                $this->userModel->updateLastActivity($user['id']);

                // Manejar "recordarme"
                if ($remember) {
                    $this->setRememberMeCookie($user['id']);
                }

                $this->setFlash('success', 'Bienvenido de nuevo, ' . $user['nombre'] . '!');
                
                // Redirigir a la página solicitada o al dashboard
                $redirectTo = $this->request->get('redirect', '/dashboard');
                $this->redirect($redirectTo);
            } else {
                $this->setFlash('error', 'Credenciales incorrectas. Verifica tu email y contraseña.');
                $this->request->saveOldInput();
                $this->redirect('/login');
            }

        } catch (\Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            $this->setFlash('error', 'Error al procesar el login. Intenta nuevamente.');
            $this->redirect('/login');
        }
    }

    /**
     * Muestra el formulario de registro
     * 
     * @return void
     */
    public function showRegister(): void
    {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
            return;
        }

        $data = [
            'pageTitle' => 'Registrarse - EcoCusco',
            'errors' => $this->getValidationErrors(),
            'csrfToken' => $this->generateCsrfToken()
        ];

        $this->render('auth/register', $data);
    }

    /**
     * Procesa el registro
     * 
     * @return void
     */
    public function register(): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('/register');
            return;
        }

        // Verificar token CSRF
        if (!$this->verifyCsrfToken()) {
            $this->setFlash('error', 'Token de seguridad inválido. Intenta nuevamente.');
            $this->redirect('/register');
            return;
        }

        // Validar datos de entrada
        $validation = $this->validate($this->request->all(), [
            'nombre' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'email' => 'required|email|max:150',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string',
            'telefono' => 'string|max:20',
            'direccion' => 'string|max:200',
            'fecha_nacimiento' => 'string',
            'terminos' => 'required'
        ]);

        if (!$validation['valid']) {
            $this->setValidationErrors($validation['errors']);
            $this->request->saveOldInput();
            $this->redirect('/register');
            return;
        }

        // Verificar que las contraseñas coincidan
        if ($this->request->get('password') !== $this->request->get('password_confirmation')) {
            $this->setFlash('error', 'Las contraseñas no coinciden.');
            $this->request->saveOldInput();
            $this->redirect('/register');
            return;
        }

        // Verificar que el email no esté en uso
        if ($this->userModel->emailExists($this->request->get('email'))) {
            $this->setFlash('error', 'El email ya está registrado. Usa otro email o inicia sesión.');
            $this->request->saveOldInput();
            $this->redirect('/register');
            return;
        }

        try {
            // Crear usuario
            $userData = $this->request->only([
                'nombre', 'apellidos', 'email', 'telefono', 
                'direccion', 'fecha_nacimiento'
            ]);
            
            $userData['password'] = $this->request->get('password');
            $userData['tipo_usuario'] = 'ciudadano';
            
            $userId = $this->userModel->createUser($userData);

            if ($userId) {
                // Obtener el usuario creado
                $user = $this->userModel->findById($userId);
                
                // Iniciar sesión automáticamente
                $this->startUserSession($user);

                $this->setFlash('success', '¡Registro exitoso! Bienvenido a EcoCusco.');
                $this->redirect('/dashboard');
            } else {
                $this->setFlash('error', 'Error al crear la cuenta. Intenta nuevamente.');
                $this->redirect('/register');
            }

        } catch (\Exception $e) {
            error_log("Error en registro: " . $e->getMessage());
            $this->setFlash('error', 'Error al procesar el registro. Intenta nuevamente.');
            $this->redirect('/register');
        }
    }

    /**
     * Cierra sesión
     * 
     * @return void
     */
    public function logout(): void
    {
        // Limpiar datos de sesión
        if (isset($_SESSION['user_id'])) {
            unset($_SESSION['user_id']);
        }

        // Limpiar cookie de "recordarme"
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }

        // Destruir sesión
        session_destroy();

        $this->setFlash('success', 'Has cerrado sesión correctamente.');
        $this->redirect('/');
    }

    /**
     * Muestra formulario de recuperación de contraseña
     * 
     * @return void
     */
    public function showForgotPassword(): void
    {
        $data = [
            'pageTitle' => 'Recuperar Contraseña - EcoCusco',
            'csrfToken' => $this->generateCsrfToken()
        ];

        $this->render('auth/forgot-password', $data);
    }

    /**
     * Procesa la solicitud de recuperación de contraseña
     * 
     * @return void
     */
    public function forgotPassword(): void
    {
        if (!$this->isMethod('POST')) {
            $this->redirect('/forgot-password');
            return;
        }

        $validation = $this->validate($this->request->all(), [
            'email' => 'required|email'
        ]);

        if (!$validation['valid']) {
            $this->setValidationErrors($validation['errors']);
            $this->redirect('/forgot-password');
            return;
        }

        $email = $this->request->get('email');
        $user = $this->userModel->findByEmail($email);

        if ($user) {
            // Aquí se implementaría el envío de email de recuperación
            // Por ahora solo mostrar mensaje
            $this->setFlash('success', 'Si el email existe, recibirás instrucciones para recuperar tu contraseña.');
        } else {
            $this->setFlash('success', 'Si el email existe, recibirás instrucciones para recuperar tu contraseña.');
        }

        $this->redirect('/login');
    }

    /**
     * Verifica el estado de autenticación (API)
     * 
     * @return void
     */
    public function checkAuth(): void
    {
        $isAuthenticated = $this->isAuthenticated();
        $user = $isAuthenticated ? $this->getCurrentUser() : null;

        $this->renderJson([
            'authenticated' => $isAuthenticated,
            'user' => $user ? [
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'apellidos' => $user['apellidos'],
                'email' => $user['email'],
                'tipo_usuario' => $user['tipo_usuario']
            ] : null
        ]);
    }

    /**
     * Inicia la sesión del usuario
     * 
     * @param array $user
     * @return void
     */
    private function startUserSession(array $user): void
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_type'] = $user['tipo_usuario'];
        $_SESSION['login_time'] = time();

        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);
    }

    /**
     * Establece cookie de "recordarme"
     * 
     * @param int $userId
     * @return void
     */
    private function setRememberMeCookie(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 días

        // Aquí se debería guardar el token en la base de datos
        // Por simplicidad, solo establecer la cookie
        setcookie('remember_token', $token, $expiry, '/', '', false, true);
    }

    /**
     * Middleware para verificar autenticación en rutas protegidas
     * 
     * @return void
     */
    public function requireAuthentication(): void
    {
        if (!$this->isAuthenticated()) {
            $this->setFlash('warning', 'Debes iniciar sesión para acceder a esta página.');
            $this->redirect('/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        }
    }

    /**
     * Middleware para verificar que NO esté autenticado (para login/register)
     * 
     * @return void
     */
    public function requireGuest(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }
    }
}
