<?php
/**
 * EcoCusco - Test de Conexión a Base de Datos
 * 
 * Este archivo ayuda a verificar que la configuración de la base de datos
 * esté funcionando correctamente antes de usar la aplicación completa.
 * 
 * IMPORTANTE: Eliminar este archivo en producción por seguridad.
 */

// Solo permitir en modo desarrollo
if (!isset($_GET['allow_test']) || $_GET['allow_test'] !== 'yes') {
    die('Acceso no autorizado. Para usar este test, agregue ?allow_test=yes a la URL.');
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión - EcoCusco</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-result { margin: 10px 0; padding: 15px; border-radius: 5px; }
        .test-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .test-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .test-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .test-info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-database me-2"></i>
                            Test de Conexión a Base de Datos - EcoCusco
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php
                        $tests = [];
                        $allPassed = true;

                        // Test 1: Verificar que existe el archivo .env
                        if (file_exists('.env')) {
                            $tests[] = ['type' => 'success', 'message' => '✓ Archivo .env encontrado'];
                        } else {
                            $tests[] = ['type' => 'error', 'message' => '✗ Archivo .env no encontrado. Crea el archivo .env basado en las instrucciones del README.'];
                            $allPassed = false;
                        }

                        // Test 2: Cargar configuración
                        try {
                            require_once 'includes/config.php';
                            $tests[] = ['type' => 'success', 'message' => '✓ Archivo de configuración cargado correctamente'];
                        } catch (Exception $e) {
                            $tests[] = ['type' => 'error', 'message' => '✗ Error cargando configuración: ' . $e->getMessage()];
                            $allPassed = false;
                        }

                        // Test 3: Verificar extensiones PHP
                        $required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl'];
                        foreach ($required_extensions as $ext) {
                            if (extension_loaded($ext)) {
                                $tests[] = ['type' => 'success', 'message' => "✓ Extensión PHP '{$ext}' disponible"];
                            } else {
                                $tests[] = ['type' => 'error', 'message' => "✗ Extensión PHP '{$ext}' no encontrada"];
                                $allPassed = false;
                            }
                        }

                        // Test 4: Verificar configuración de base de datos
                        if (defined('DB_HOST') && defined('DB_DATABASE') && defined('DB_USERNAME')) {
                            $tests[] = ['type' => 'success', 'message' => '✓ Variables de configuración de BD definidas'];
                            
                            // Test 5: Intentar conexión a base de datos
                            try {
                                require_once 'includes/database.php';
                                $db = Database::getInstance();
                                $connection = $db->getConnection();
                                
                                $tests[] = ['type' => 'success', 'message' => '✓ Conexión a base de datos exitosa'];
                                
                                // Test 6: Verificar que existan las tablas principales
                                $tablas_requeridas = ['usuarios', 'tipos_residuos', 'reportes', 'puntos_usuarios'];
                                foreach ($tablas_requeridas as $tabla) {
                                    if ($db->tableExists($tabla)) {
                                        $tests[] = ['type' => 'success', 'message' => "✓ Tabla '{$tabla}' existe"];
                                    } else {
                                        $tests[] = ['type' => 'warning', 'message' => "⚠ Tabla '{$tabla}' no encontrada. Ejecuta el archivo database_schema.sql"];
                                    }
                                }
                                
                                // Test 7: Verificar usuario administrador
                                $admin = $db->fetchOne("SELECT id, email FROM usuarios WHERE rol = 'administrador' LIMIT 1");
                                if ($admin) {
                                    $tests[] = ['type' => 'success', 'message' => "✓ Usuario administrador encontrado: {$admin['email']}"];
                                } else {
                                    $tests[] = ['type' => 'warning', 'message' => "⚠ No se encontró usuario administrador. Ejecuta el archivo database_schema.sql"];
                                }
                                
                            } catch (Exception $e) {
                                $tests[] = ['type' => 'error', 'message' => '✗ Error de conexión a BD: ' . $e->getMessage()];
                                $allPassed = false;
                            }
                        } else {
                            $tests[] = ['type' => 'error', 'message' => '✗ Variables de configuración de BD no están definidas correctamente'];
                            $allPassed = false;
                        }

                        // Test 8: Verificar permisos de escritura
                        if (is_writable('logs/')) {
                            $tests[] = ['type' => 'success', 'message' => '✓ Directorio logs/ tiene permisos de escritura'];
                        } else {
                            $tests[] = ['type' => 'warning', 'message' => "⚠ Directorio logs/ no tiene permisos de escritura. Ejecuta: chmod 755 logs/"];
                        }

                        // Test 9: Verificar configuración de sesiones
                        if (session_status() === PHP_SESSION_ACTIVE || session_start()) {
                            $tests[] = ['type' => 'success', 'message' => '✓ Sesiones PHP funcionando correctamente'];
                        } else {
                            $tests[] = ['type' => 'error', 'message' => '✗ Error iniciando sesiones PHP'];
                            $allPassed = false;
                        }

                        // Mostrar resultados
                        foreach ($tests as $test) {
                            echo "<div class='test-result test-{$test['type']}'>{$test['message']}</div>";
                        }

                        // Resultado final
                        if ($allPassed) {
                            echo "<div class='test-result test-success'>";
                            echo "<h5>🎉 ¡Todas las pruebas pasaron exitosamente!</h5>";
                            echo "<p>Tu instalación de EcoCusco está lista para usar. Puedes acceder a:</p>";
                            echo "<ul>";
                            echo "<li><a href='public/index.php' class='btn btn-primary btn-sm'>Página Principal</a></li>";
                            echo "<li><a href='pages/login.php' class='btn btn-success btn-sm'>Iniciar Sesión</a></li>";
                            echo "<li><a href='pages/register.php' class='btn btn-info btn-sm'>Registrarse</a></li>";
                            echo "</ul>";
                            echo "<p class='mt-3'><strong>IMPORTANTE:</strong> Elimina este archivo (test_connection.php) antes de pasar a producción.</p>";
                            echo "</div>";
                        } else {
                            echo "<div class='test-result test-error'>";
                            echo "<h5>❌ Algunas pruebas fallaron</h5>";
                            echo "<p>Revisa los errores anteriores y consulta el archivo README.md para obtener instrucciones de configuración.</p>";
                            echo "</div>";
                        }
                        ?>

                        <div class="test-result test-info">
                            <h6>Información del Sistema:</h6>
                            <ul class="mb-0">
                                <li><strong>PHP Version:</strong> <?= phpversion() ?></li>
                                <li><strong>Sistema Operativo:</strong> <?= php_uname('s') . ' ' . php_uname('r') ?></li>
                                <li><strong>Servidor Web:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido' ?></li>
                                <li><strong>Directorio del Proyecto:</strong> <?= __DIR__ ?></li>
                                <li><strong>Fecha/Hora del Test:</strong> <?= date('Y-m-d H:i:s') ?></li>
                            </ul>
                        </div>

                        <div class="mt-4">
                            <a href="?allow_test=yes" class="btn btn-primary">
                                <i class="fas fa-refresh me-2"></i>Ejecutar Test Nuevamente
                            </a>
                            <a href="README.md" class="btn btn-outline-info">
                                <i class="fas fa-book me-2"></i>Ver Documentación
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
