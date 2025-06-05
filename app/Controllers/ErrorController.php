<?php

namespace App\Controllers;

use App\Core\Controller;

/**
 * ErrorController - Manejo de errores de la aplicación
 * 
 * Controla las páginas de error personalizadas
 * y el manejo de excepciones.
 */
class ErrorController extends Controller
{
    /**
     * Página de error 404 - No encontrado
     * 
     * @return void
     */
    public function notFound(): void
    {
        http_response_code(404);
        
        $data = [
            'pageTitle' => 'Página No Encontrada - EcoCusco',
            'errorCode' => 404,
            'errorMessage' => 'La página que buscas no existe',
            'errorDescription' => 'Es posible que la página haya sido movida, eliminada o que hayas escrito mal la dirección.'
        ];

        $this->render('errors/404', $data, 'layouts/error');
    }

    /**
     * Página de error 403 - Prohibido
     * 
     * @return void
     */
    public function forbidden(): void
    {
        http_response_code(403);
        
        $data = [
            'pageTitle' => 'Acceso Prohibido - EcoCusco',
            'errorCode' => 403,
            'errorMessage' => 'No tienes permisos para acceder',
            'errorDescription' => 'No tienes los permisos necesarios para ver esta página. Contacta al administrador si crees que esto es un error.'
        ];

        $this->render('errors/403', $data, 'layouts/error');
    }

    /**
     * Página de error 500 - Error interno del servidor
     * 
     * @return void
     */
    public function serverError(): void
    {
        http_response_code(500);
        
        $data = [
            'pageTitle' => 'Error del Servidor - EcoCusco',
            'errorCode' => 500,
            'errorMessage' => 'Error interno del servidor',
            'errorDescription' => 'Ha ocurrido un error inesperado. Nuestro equipo ha sido notificado y trabajamos para solucionarlo.'
        ];

        $this->render('errors/500', $data, 'layouts/error');
    }

    /**
     * Página de error 503 - Servicio no disponible
     * 
     * @return void
     */
    public function serviceUnavailable(): void
    {
        http_response_code(503);
        
        $data = [
            'pageTitle' => 'Servicio No Disponible - EcoCusco',
            'errorCode' => 503,
            'errorMessage' => 'Servicio temporalmente no disponible',
            'errorDescription' => 'Estamos realizando mantenimiento. El servicio estará disponible pronto.'
        ];

        $this->render('errors/503', $data, 'layouts/error');
    }

    /**
     * Página de error de debug para desarrolladores
     * 
     * @return void
     */
    public function debug(): void
    {
        $exception = $this->request->get('exception');
        $statusCode = $this->request->get('statusCode', 500);
        
        http_response_code($statusCode);
        
        $data = [
            'pageTitle' => 'Error de Desarrollo - EcoCusco',
            'exception' => $exception,
            'statusCode' => $statusCode
        ];

        $this->render('errors/debug', $data, 'layouts/error');
    }

    /**
     * Maneja errores AJAX
     * 
     * @return void
     */
    public function ajaxError(): void
    {
        $errorCode = $this->request->get('code', 500);
        $errorMessage = $this->request->get('message', 'Error interno del servidor');
        
        $this->renderJson([
            'success' => false,
            'error' => [
                'code' => $errorCode,
                'message' => $errorMessage
            ]
        ], $errorCode);
    }
}
