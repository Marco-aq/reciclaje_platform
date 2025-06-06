<?php
/**
 * Script de Prueba de Conexión - Plataforma de Reciclaje MVC
 * 
 * Este archivo permite probar la conexión a la base de datos
 * y verificar que la configuración esté funcionando correctamente
 */

// Incluir el bootstrap principal
require_once __DIR__ . '/index.php';

echo "<h1>Prueba de Conexión - Plataforma de Reciclaje MVC</h1>";

try {
    // Probar conexión a base de datos
    echo "<h2>🔌 Probando Conexión a Base de Datos</h2>";
    
    $db = App\Core\Database::getInstance();
    $connection = $db->getConnection();
    
    echo "<p style='color: green;'>✅ Conexión exitosa a la base de datos</p>";
    
    // Verificar tablas
    echo "<h2>📋 Verificando Tablas</h2>";
    
    $tables = ['usuarios', 'reportes', 'password_resets', 'configuracion'];
    foreach ($tables as $table) {
        try {
            $result = $db->query("SELECT COUNT(*) as count FROM {$table}");
            $count = $result->fetch()['count'];
            echo "<p>✅ Tabla '{$table}': {$count} registros</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Error en tabla '{$table}': " . $e->getMessage() . "</p>";
        }
    }
    
    // Probar configuración
    echo "<h2>⚙️ Verificando Configuración</h2>";
    
    $config = require CONFIG_PATH . '/app.php';
    echo "<p>✅ Nombre de la aplicación: " . $config['name'] . "</p>";
    echo "<p>✅ Entorno: " . $config['env'] . "</p>";
    echo "<p>✅ Debug: " . ($config['debug'] ? 'Activado' : 'Desactivado') . "</p>";
    
    // Probar variables de entorno
    echo "<h2>🌍 Variables de Entorno</h2>";
    
    $envVars = ['DB_HOST', 'DB_NAME', 'DB_USER', 'APP_NAME', 'APP_URL'];
    foreach ($envVars as $var) {
        $value = env($var, 'No definida');
        echo "<p>✅ {$var}: " . ($var === 'DB_PASSWORD' ? '***' : $value) . "</p>";
    }
    
    // Probar permisos de escritura
    echo "<h2>📁 Verificando Permisos</h2>";
    
    $writableDirs = [
        STORAGE_PATH . '/logs',
        PUBLIC_PATH . '/uploads'
    ];
    
    foreach ($writableDirs as $dir) {
        if (is_writable($dir)) {
            echo "<p>✅ Directorio escribible: {$dir}</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Directorio sin permisos de escritura: {$dir}</p>";
        }
    }
    
    // Probar creación de usuario demo
    echo "<h2>👤 Probando Funcionalidades</h2>";
    
    $userModel = new App\Models\User();
    $demoUser = $userModel->findByEmail('demo@ejemplo.com');
    
    if ($demoUser) {
        echo "<p>✅ Usuario demo encontrado: " . $demoUser['nombre'] . "</p>";
        
        // Verificar reportes del usuario demo
        $reportModel = new App\Models\Report();
        $reportCount = $reportModel->count(['usuario_id' => $demoUser['id']]);
        echo "<p>✅ Reportes del usuario demo: {$reportCount}</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Usuario demo no encontrado</p>";
    }
    
    // Probar estadísticas
    $statsModel = new App\Models\Stats();
    $dashboardStats = $statsModel->getDashboardStats();
    
    echo "<p>✅ Total de reportes en sistema: " . ($dashboardStats['total_reportes'] ?? 0) . "</p>";
    echo "<p>✅ Total de usuarios: " . ($dashboardStats['total_usuarios'] ?? 0) . "</p>";
    
    echo "<h2>🎉 Conclusión</h2>";
    echo "<p style='color: green; font-weight: bold;'>✅ Sistema funcionando correctamente</p>";
    echo "<p>La aplicación está lista para usarse.</p>";
    echo "<p><a href='/'>← Ir a la aplicación</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Error en la Prueba</h2>";
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Línea:</strong> " . $e->getLine() . "</p>";
    
    echo "<h3>💡 Posibles Soluciones:</h3>";
    echo "<ul>";
    echo "<li>Verificar que la base de datos esté creada</li>";
    echo "<li>Comprobar las credenciales en el archivo .env</li>";
    echo "<li>Ejecutar el archivo database_schema.sql</li>";
    echo "<li>Verificar que el servicio MySQL esté ejecutándose</li>";
    echo "<li>Comprobar permisos de directorios</li>";
    echo "</ul>";
    
    if (env('APP_DEBUG', false)) {
        echo "<h3>🔍 Stack Trace:</h3>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
}

echo "<hr>";
echo "<p><small>Plataforma de Reciclaje MVC - Prueba de Conexión</small></p>";
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background: #f8f9fa;
    }
    
    h1 {
        color: #28a745;
        border-bottom: 2px solid #28a745;
        padding-bottom: 10px;
    }
    
    h2 {
        color: #6c757d;
        margin-top: 30px;
    }
    
    p {
        margin: 8px 0;
        padding: 5px;
        background: white;
        border-radius: 4px;
        border-left: 4px solid #dee2e6;
    }
    
    pre {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        overflow-x: auto;
    }
    
    ul {
        background: white;
        padding: 15px;
        border-radius: 4px;
        border-left: 4px solid #ffc107;
    }
</style>
