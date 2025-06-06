# Plataforma de Reciclaje MVC

Una aplicación web completa desarrollada con arquitectura MVC para la gestión y seguimiento de actividades de reciclaje. Permite a los usuarios reportar sus actividades de reciclaje, visualizar estadísticas y realizar un seguimiento de su impacto ambiental.

## 🌟 Características

- **Arquitectura MVC**: Separación clara de responsabilidades
- **Sistema de Autenticación**: Registro, login y recuperación de contraseña
- **Gestión de Reportes**: CRUD completo para reportes de reciclaje
- **Dashboard Interactivo**: Estadísticas personalizadas y generales
- **Análisis de Datos**: Gráficos y visualizaciones avanzadas
- **Sistema de Puntos**: Gamificación para motivar el reciclaje
- **Responsive Design**: Compatible con dispositivos móviles
- **API REST**: Endpoints para integración externa
- **Seguridad**: Protección CSRF, validación de datos, sanitización

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL 8.0+
- **Frontend**: HTML5, CSS3, JavaScript ES6+
- **Frameworks CSS**: Bootstrap 5
- **Gráficos**: Chart.js
- **Iconos**: Font Awesome
- **Arquitectura**: MVC (Model-View-Controller)

## 📋 Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 8.0 o superior
- Servidor web (Apache/Nginx)
- Extensiones PHP requeridas:
  - PDO
  - MySQL
  - mbstring
  - fileinfo
  - GD (opcional, para manipulación de imágenes)

## 🚀 Instalación

### 1. Clonar el repositorio

```bash
git clone <URL_DEL_REPOSITORIO>
cd reciclaje_platform_mvc_working
```

### 2. Configurar el entorno

1. Copiar el archivo de configuración:
```bash
cp .env.example .env
```

2. Editar `.env` con tus configuraciones:
```env
# Configuración de Base de Datos
DB_HOST=localhost
DB_PORT=3306
DB_NAME=reciclaje_db
DB_USER=tu_usuario
DB_PASSWORD=tu_contraseña

# Configuración de la Aplicación
APP_NAME="Plataforma de Reciclaje"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://tu-dominio.com

# Configuración de Seguridad
APP_KEY=tu_clave_secreta_aqui_cambiar_en_produccion
SALT=tu_salt_personalizado
```

### 3. Configurar la base de datos

1. Crear la base de datos:
```sql
CREATE DATABASE reciclaje_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Importar el esquema:
```bash
mysql -u tu_usuario -p reciclaje_db < database_schema.sql
```

### 4. Configurar permisos

```bash
# Permisos para directorios de escritura
chmod 755 storage/logs/
chmod 755 public/uploads/

# Asegurar que el servidor web puede escribir
chown -R www-data:www-data storage/
chown -R www-data:www-data public/uploads/
```

### 5. Configurar servidor web

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}
```

## 🔧 Configuración

### Variables de Entorno

| Variable | Descripción | Valor por defecto |
|----------|-------------|-------------------|
| `DB_HOST` | Host de la base de datos | localhost |
| `DB_PORT` | Puerto de la base de datos | 3306 |
| `DB_NAME` | Nombre de la base de datos | reciclaje_db |
| `DB_USER` | Usuario de la base de datos | root |
| `DB_PASSWORD` | Contraseña de la base de datos | (vacío) |
| `APP_NAME` | Nombre de la aplicación | Plataforma de Reciclaje |
| `APP_ENV` | Entorno de la aplicación | production |
| `APP_DEBUG` | Modo debug | false |
| `APP_URL` | URL base de la aplicación | http://localhost |
| `APP_KEY` | Clave de encriptación | (generar una segura) |
| `SALT` | Salt para contraseñas | (generar uno seguro) |

### Configuración de Uploads

- Tamaño máximo: 5MB (configurable en `config/app.php`)
- Tipos permitidos: jpg, jpeg, png, gif, pdf
- Directorio: `public/uploads/`

## 📖 Uso

### Acceso Inicial

1. Visita tu dominio en el navegador
2. Crea una cuenta o usa las credenciales de demo:
   - Email: `demo@ejemplo.com`
   - Contraseña: `demo123`

### Funcionalidades Principales

#### 📊 Dashboard
- Estadísticas personales de reciclaje
- Gráficos interactivos
- Ranking de usuarios
- Impacto ambiental calculado

#### 📝 Gestión de Reportes
- Crear nuevos reportes de reciclaje
- Editar reportes existentes
- Subir fotos de evidencia
- Filtrar y buscar reportes

#### 📈 Estadísticas
- Análisis detallado de datos
- Comparativas por períodos
- Proyecciones de tendencias
- Exportación de datos (CSV/JSON)

#### 👤 Perfil de Usuario
- Actualizar información personal
- Cambiar contraseña
- Ver historial de actividad

## 🔌 API

### Endpoints Disponibles

#### Autenticación
```
POST /api/login          - Iniciar sesión
POST /api/register       - Registrar usuario
POST /api/logout         - Cerrar sesión
GET  /api/check-auth     - Verificar autenticación
```

#### Reportes
```
GET    /api/reportes           - Listar reportes
POST   /api/reportes           - Crear reporte
GET    /api/reportes/{id}      - Obtener reporte
PUT    /api/reportes/{id}      - Actualizar reporte
DELETE /api/reportes/{id}      - Eliminar reporte
```

#### Estadísticas
```
GET /api/estadisticas/datos    - Datos generales
GET /api/estadisticas/graficos - Datos para gráficos
```

### Ejemplo de Uso

```javascript
// Crear un nuevo reporte
fetch('/api/reportes', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({
        tipo_material: 'plastico',
        cantidad: 2.5,
        ubicacion: 'Centro de la Ciudad',
        descripcion: 'Botellas de agua'
    })
})
.then(response => response.json())
.then(data => console.log(data));
```

## 🏗️ Arquitectura

### Estructura de Directorios

```
reciclaje_platform_mvc_working/
├── app/                          # Código de la aplicación
│   ├── Core/                     # Núcleo del framework
│   │   ├── App.php              # Clase principal
│   │   ├── Controller.php       # Controlador base
│   │   ├── Database.php         # Manejo de BD
│   │   ├── Model.php            # Modelo base
│   │   ├── Router.php           # Enrutamiento
│   │   └── View.php             # Sistema de vistas
│   ├── Controllers/             # Controladores
│   ├── Models/                  # Modelos
│   └── Views/                   # Vistas
├── config/                      # Configuración
├── public/                      # Archivos públicos
│   ├── assets/                  # CSS, JS, imágenes
│   └── uploads/                 # Archivos subidos
├── storage/                     # Almacenamiento
│   └── logs/                    # Logs del sistema
├── .env                         # Variables de entorno
├── .env.example                 # Ejemplo de configuración
├── database_schema.sql          # Esquema de BD
└── index.php                    # Punto de entrada
```

### Patrón MVC

#### Modelos (Models)
- `User.php`: Gestión de usuarios
- `Report.php`: Gestión de reportes
- `Stats.php`: Estadísticas y análisis

#### Vistas (Views)
- `layouts/main.php`: Layout principal
- `home/`: Páginas públicas
- `auth/`: Autenticación
- `dashboard/`: Panel de usuario
- `reports/`: Gestión de reportes

#### Controladores (Controllers)
- `HomeController.php`: Página principal
- `AuthController.php`: Autenticación
- `DashboardController.php`: Panel de usuario
- `ReportController.php`: Gestión de reportes
- `StatsController.php`: Estadísticas

## 🔒 Seguridad

### Medidas Implementadas

1. **Protección CSRF**: Tokens en formularios
2. **Validación de Datos**: Sanitización de entradas
3. **Prepared Statements**: Prevención de SQL injection
4. **Hash de Contraseñas**: bcrypt con salt personalizado
5. **Validación de Archivos**: Tipos y tamaños permitidos
6. **Escape de Salida**: Prevención de XSS

### Configuración de Seguridad

```php
// Ejemplo de configuración en config/app.php
'security' => [
    'password_min_length' => 6,
    'session_regenerate' => true,
    'csrf_protection' => true,
],
```

## 🧪 Testing

### Testing Manual

1. Registro de usuario
2. Login/logout
3. Creación de reportes
4. Edición de reportes
5. Visualización de estadísticas
6. Upload de archivos

### Casos de Prueba

- ✅ Validación de formularios
- ✅ Manejo de errores de BD
- ✅ Protección CSRF
- ✅ Sanitización de datos
- ✅ Responsive design

## 📊 Base de Datos

### Tablas Principales

#### usuarios
- Información de usuarios registrados
- Autenticación y perfiles

#### reportes
- Reportes de actividades de reciclaje
- Relación con usuarios

#### password_resets
- Tokens para recuperación de contraseña

#### configuracion
- Configuración del sistema

### Vistas y Funciones

- `vista_estadisticas_generales`: Estadísticas rápidas
- `vista_ranking_usuarios`: Ranking de usuarios
- `calcular_co2_evitado()`: Cálculo de impacto ambiental

## 🔄 Mantenimiento

### Logs

Los logs se almacenan en `storage/logs/`:
- `app.log`: Log general de la aplicación
- `database.log`: Log de base de datos
- `contact.log`: Mensajes de contacto

### Limpieza de Datos

Ejecutar periódicamente:
```sql
CALL limpiar_datos_antiguos();
```

### Backup

Recomendado hacer backup diario:
```bash
mysqldump -u usuario -p reciclaje_db > backup_$(date +%Y%m%d).sql
```

## 🚨 Solución de Problemas

### Errores Comunes

#### Error de conexión a BD
1. Verificar credenciales en `.env`
2. Comprobar que el servicio MySQL esté activo
3. Verificar permisos del usuario de BD

#### Errores de permisos
```bash
# Corregir permisos
chmod 755 storage/logs/
chmod 755 public/uploads/
chown -R www-data:www-data storage/
```

#### Error 404 en rutas
1. Verificar configuración de servidor web
2. Comprobar archivo `.htaccess` (Apache)
3. Verificar configuración Nginx

### Debug

Para activar el modo debug:
```env
APP_DEBUG=true
```

## 🤝 Contribución

### Pautas de Desarrollo

1. Seguir PSR-4 para autoloading
2. Documentar código con PHPDoc
3. Usar nombres descriptivos
4. Validar todas las entradas
5. Manejar errores apropiadamente

### Estructura de Commits

```
tipo(scope): descripción

feat(auth): agregar recuperación de contraseña
fix(reports): corregir validación de fechas
docs(readme): actualizar instrucciones de instalación
```

## 📝 Changelog

### v1.0.0 (2025-01-07)
- ✨ Implementación inicial del sistema MVC
- 🔐 Sistema de autenticación completo
- 📊 Dashboard con estadísticas
- 📝 Gestión de reportes CRUD
- 📈 Sistema de análisis y gráficos
- 🎨 Diseño responsive con Bootstrap 5
- 🔌 API REST funcional
- 🛡️ Medidas de seguridad implementadas

## 📄 Licencia

Este proyecto está licenciado bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👥 Autores

- **Desarrollador Principal**: [Tu Nombre]
- **Contribuidores**: Ver `CONTRIBUTORS.md`

## 📞 Soporte

Para soporte técnico:
- 📧 Email: soporte@plataforma-reciclaje.com
- 📋 Issues: [GitHub Issues]
- 📖 Wiki: [Documentación Wiki]

## 🙏 Agradecimientos

- Bootstrap por el framework CSS
- Chart.js por las visualizaciones
- Font Awesome por los iconos
- Comunidad PHP por las mejores prácticas

---

**¡Gracias por usar la Plataforma de Reciclaje MVC! Juntos construimos un futuro más sostenible. 🌱**
