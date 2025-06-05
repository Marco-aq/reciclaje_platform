# EcoCusco - Plataforma de Gestión de Residuos Sólidos Urbanos

![EcoCusco Logo](public/assets/img/logo-eco.png)

## 📋 Descripción

EcoCusco es una plataforma web colaborativa diseñada para la gestión eficiente de residuos sólidos urbanos en la ciudad de Cusco. Permite a los ciudadanos reportar puntos de acumulación de residuos mediante geolocalización, seguir el estado de sus reportes y colaborar con las autoridades locales para mantener una ciudad más limpia y sostenible.

## ✨ Características Principales

### 🎯 Funcionalidades Core
- **Reportes Geolocalizados**: Permite reportar puntos de acumulación de residuos con ubicación GPS exacta
- **Sistema de Autenticación**: Registro y login seguro de usuarios
- **Dashboard Personalizado**: Panel de control adaptado según el tipo de usuario
- **Estadísticas en Tiempo Real**: Métricas y gráficos sobre la gestión de residuos
- **Gestión de Estados**: Seguimiento completo del ciclo de vida de los reportes

### 🏗️ Arquitectura y Patrones
- **Arquitectura MVC**: Separación clara de responsabilidades
- **Patrón Singleton**: Para conexión a base de datos
- **Patrón Factory**: Para creación de instancias de controladores
- **Patrón Repository**: Para acceso a datos consistente
- **PSR-4 Autoloading**: Carga automática de clases

### 🔒 Seguridad
- **Prepared Statements**: Prevención de inyección SQL
- **Validación de Entrada**: Sanitización y validación de todos los datos
- **Tokens CSRF**: Protección contra ataques de falsificación
- **Hash de Contraseñas**: Almacenamiento seguro usando password_hash()

## 🚀 Instalación

### Prerequisitos
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Composer (opcional, para dependencias futuras)

### 1. Clonar o Descargar el Proyecto
```bash
# Si tienes git instalado
git clone [repository-url] ecocusco
cd ecocusco

# O descargar y extraer el archivo ZIP
```

### 2. Configurar el Servidor Web

#### Apache
Configurar el DocumentRoot hacia la carpeta `public/`:
```apache
<VirtualHost *:80>
    DocumentRoot /path/to/ecocusco/public
    ServerName ecocusco.local
    
    <Directory /path/to/ecocusco/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name ecocusco.local;
    root /path/to/ecocusco/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 3. Configurar la Base de Datos

#### Crear la Base de Datos
```sql
CREATE DATABASE reciclaje_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Importar la Estructura
```sql
-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(50) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(200),
    fecha_nacimiento DATE,
    tipo_usuario ENUM('ciudadano', 'admin') DEFAULT 'ciudadano',
    estado BOOLEAN DEFAULT TRUE,
    ultima_actividad DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de reportes
CREATE TABLE reportes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    ubicacion VARCHAR(200) NOT NULL,
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8),
    tipo_residuo VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    urgencia TINYINT DEFAULT 2,
    estado ENUM('pendiente', 'en_proceso', 'resuelto', 'rechazado') DEFAULT 'pendiente',
    imagen_url VARCHAR(255),
    direccion_exacta VARCHAR(300),
    fecha_reporte DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_resolucion DATETIME NULL,
    comentario_resolucion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_estado (estado),
    INDEX idx_urgencia (urgencia),
    INDEX idx_fecha_reporte (fecha_reporte)
);
```

### 4. Configurar Variables de Entorno

Copiar el archivo de ejemplo y configurar:
```bash
cp .env.example .env
```

Editar el archivo `.env`:
```env
# Configuración de la aplicación
APP_NAME="EcoCusco - Gestión de Residuos"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost

# Configuración de base de datos
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=reciclaje_platform
DB_USERNAME=root
DB_PASSWORD=tu_password

# Configuración de sesiones
SESSION_LIFETIME=120
SESSION_DRIVER=file
```

### 5. Configurar Permisos

```bash
# Dar permisos de escritura a las carpetas necesarias
chmod -R 755 storage/
chmod -R 755 public/uploads/
chown -R www-data:www-data storage/
chown -R www-data:www-data public/uploads/
```

### 6. Probar la Instalación

Visitar `http://ecocusco.local` (o tu dominio configurado) y verificar que la aplicación carga correctamente.

## 📂 Estructura del Proyecto

```
reciclaje_platform_mvc/
├── app/
│   ├── Controllers/          # Controladores MVC
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── HomeController.php
│   │   ├── ReportsController.php
│   │   └── StatisticsController.php
│   ├── Core/                 # Clases del framework
│   │   ├── Config.php
│   │   ├── Controller.php
│   │   ├── Database.php
│   │   ├── Factory.php
│   │   ├── Model.php
│   │   ├── Request.php
│   │   ├── Router.php
│   │   ├── Validator.php
│   │   └── View.php
│   ├── Models/               # Modelos de datos
│   │   ├── Report.php
│   │   └── User.php
│   └── Views/                # Vistas de la aplicación
│       ├── auth/
│       ├── dashboard/
│       ├── home/
│       ├── layouts/
│       └── reports/
├── config/                   # Archivos de configuración
│   ├── app.php
│   └── database.php
├── public/                   # Punto de entrada web
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   ├── uploads/
│   └── index.php
├── storage/                  # Almacenamiento de archivos
│   ├── cache/
│   └── logs/
├── .env.example
├── .gitignore
├── composer.json
└── README.md
```

## 🎮 Uso de la Aplicación

### Para Ciudadanos

1. **Registro**: Crear una cuenta en `/register`
2. **Login**: Iniciar sesión en `/login`
3. **Reportar**: Crear nuevo reporte en `/reportes/crear`
4. **Seguimiento**: Ver estado de reportes en `/dashboard`

### Para Administradores

1. **Dashboard**: Panel completo en `/dashboard`
2. **Gestión de Reportes**: Cambiar estados y asignar resoluciones
3. **Estadísticas**: Ver métricas completas en `/estadisticas`
4. **Exportación**: Descargar datos en formato CSV

## 🔧 Configuración Avanzada

### Personalizar Estilos
Editar `/public/assets/css/main.css` para modificar la apariencia:
```css
:root {
    --primary-color: #2E7D32;    /* Color principal */
    --primary-light: #4CAF50;    /* Color claro */
    --primary-dark: #1B5E20;     /* Color oscuro */
}
```

### Agregar Nuevas Rutas
En `/app/Core/Router.php`, método `defineRoutes()`:
```php
$this->get('/nueva-ruta', 'NuevoController@metodo');
$this->post('/procesar', 'NuevoController@procesar');
```

### Crear Nuevo Controlador
```php
<?php
namespace App\Controllers;

use App\Core\Controller;

class NuevoController extends Controller
{
    public function index()
    {
        $this->render('nueva-vista/index', [
            'pageTitle' => 'Nueva Página'
        ]);
    }
}
```

## 📊 API Endpoints

### Estadísticas
- `GET /api/estadisticas` - Obtener estadísticas generales
- `GET /api/estadisticas?type=monthly` - Estadísticas mensuales

### Reportes
- `GET /api/reportes/mapa` - Datos para mapa interactivo
- `POST /api/reportes/{id}/estado` - Actualizar estado de reporte

### Dashboard
- `GET /api/dashboard/data` - Datos del dashboard

## 🛡️ Seguridad

### Validación de Entrada
```php
$validation = $this->validate($this->request->all(), [
    'email' => 'required|email|max:150',
    'password' => 'required|string|min:8',
    'nombre' => 'required|string|max:50'
]);
```

### Protección CSRF
```php
// En las vistas
<?= $this->csrfField() ?>

// En los controladores
if (!$this->verifyCsrfToken()) {
    // Manejar token inválido
}
```

## 🚀 Optimización y Performance

### Cache de Configuración
Las configuraciones se cargan una sola vez por request y se mantienen en memoria.

### Conexión Singleton
La conexión a base de datos usa el patrón Singleton para evitar múltiples conexiones.

### Consultas Optimizadas
Todos los modelos usan prepared statements y consultas optimizadas con índices apropiados.

## 🔍 Debugging

### Habilitar Debug
En `.env`:
```env
APP_DEBUG=true
```

### Logs de Error
Los errores se registran en `/storage/logs/` cuando debug está deshabilitado.

### Validación de Datos
```php
// Ejemplo de validación personalizada
$validation = $this->validate($data, [
    'urgencia' => 'required|integer|min:1|max:4',
    'tipo_residuo' => 'required|string|in:Orgánicos,Plástico,Papel'
]);
```

## 🤝 Contribución

### Agregar Nueva Funcionalidad

1. **Crear Modelo**: Si necesitas nueva tabla
2. **Crear Controlador**: Para manejar la lógica
3. **Crear Vistas**: Para la interfaz de usuario
4. **Agregar Rutas**: En el router
5. **Probar**: Verificar funcionalidad completa

### Estándares de Código

- Seguir PSR-4 para autoloading
- Usar camelCase para métodos
- Usar PascalCase para clases
- Documentar funciones públicas
- Validar siempre los datos de entrada

## 📱 Responsive Design

La aplicación está optimizada para:
- **Desktop**: Experiencia completa
- **Tablet**: Interfaz adaptada
- **Mobile**: Diseño mobile-first

## 🌐 Internacionalización

El sistema está preparado para múltiples idiomas:
- Español (por defecto)
- Estructura preparada para agregar más idiomas

## 📈 Métricas y Analytics

### Estadísticas Incluidas
- Total de reportes por período
- Distribución por tipo de residuo
- Tiempo promedio de resolución
- Usuarios más activos
- Zonas con más reportes

### Exportación de Datos
- CSV para reportes
- CSV para usuarios
- Resumen ejecutivo

## 🆘 Soporte y Troubleshooting

### Problemas Comunes

**Error de conexión a base de datos:**
- Verificar credenciales en `.env`
- Confirmar que MySQL esté ejecutándose
- Revisar permisos de usuario de BD

**Error 500:**
- Habilitar `APP_DEBUG=true`
- Revisar logs en `/storage/logs/`
- Verificar permisos de carpetas

**Rutas no funcionan:**
- Verificar configuración de servidor web
- Confirmar que mod_rewrite esté habilitado (Apache)
- Revisar configuración de try_files (Nginx)

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver archivo `LICENSE` para más detalles.

## 👥 Equipo

Desarrollado por el equipo EcoCusco para contribuir a una ciudad más sostenible.

---

**EcoCusco** - Trabajando juntos por un Cusco más verde 🌱
