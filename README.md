# EcoCusco - Plataforma de Gestión de Residuos Sólidos Urbanos

## Descripción

EcoCusco es una plataforma web simplificada y funcional para la gestión de residuos sólidos urbanos en Cusco, Perú. Esta versión ha sido optimizada para resolver problemas de conectividad a la base de datos y proporcionar una experiencia de usuario fluida.

## Características Principales

- ✅ **Conexión de Base de Datos Estable**: Sistema simplificado que funciona sin errores
- ✅ **Autenticación Segura**: Registro e inicio de sesión con validación robusta
- ✅ **Dashboard Interactivo**: Panel de control con estadísticas en tiempo real
- ✅ **Gestión de Reportes**: Sistema completo para reportar y gestionar residuos
- ✅ **Estadísticas Visuales**: Gráficos y análisis de datos de reciclaje
- ✅ **Sistema de Puntos**: Gamificación para incentivar el reciclaje
- ✅ **Diseño Responsivo**: Compatible con dispositivos móviles y desktop
- ✅ **Configuración Simple**: Archivo .env para configuración fácil

## Arquitectura Simplificada

```
reciclaje_platform_fixed/
├── public/                     # Punto de entrada público
│   ├── index.php              # Página principal
│   └── assets/                # Recursos estáticos (CSS, JS, img)
├── includes/                  # Lógica de backend
│   ├── config.php            # Configuración principal
│   ├── database.php          # Clase Database (Singleton)
│   └── functions.php         # Funciones utilitarias
├── pages/                     # Páginas de la aplicación
│   ├── login.php             # Inicio de sesión
│   ├── register.php          # Registro de usuarios
│   ├── dashboard.php         # Panel principal
│   ├── reportes.php          # Gestión de reportes
│   ├── estadisticas.php      # Estadísticas y gráficos
│   └── logout.php            # Cerrar sesión
├── components/                # Componentes reutilizables
│   ├── header.php            # Navegación principal
│   └── footer.php            # Pie de página
├── .env                       # Variables de entorno
├── database_schema.sql        # Esquema de base de datos
└── README.md                  # Documentación
```

## Instalación y Configuración

### Requisitos Previos

- PHP 8.0 o superior
- MySQL 5.7 o superior / MariaDB 10.3 o superior
- Servidor web (Apache/Nginx)
- Extensiones PHP: PDO, PDO_MySQL, mbstring, openssl

### Paso 1: Clonar o Copiar los Archivos

```bash
# Copiar todos los archivos al directorio de tu servidor web
cp -r reciclaje_platform_fixed/ /var/www/html/ecocusco/
```

### Paso 2: Configurar la Base de Datos

1. **Crear la base de datos:**
```sql
CREATE DATABASE reciclaje_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. **Importar el esquema:**
```bash
mysql -u tu_usuario -p reciclaje_platform < database_schema.sql
```

### Paso 3: Configurar Variables de Entorno

Edita el archivo `.env` con tus configuraciones:

```env
# Configuración de la aplicación
APP_NAME="EcoCusco - Gestión de Residuos"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://tu-dominio.com

# Configuración de base de datos
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=reciclaje_platform
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

# Configuración de sesiones
SESSION_LIFETIME=120
SESSION_NAME=ecocusco_session

# Configuración de archivos
UPLOAD_MAX_SIZE=10485760
ALLOWED_FILE_TYPES=jpg,jpeg,png,pdf

# Configuración de seguridad
BCRYPT_ROUNDS=10
```

### Paso 4: Configurar Permisos

```bash
# Dar permisos de escritura a directorios necesarios
chmod 755 /ruta/a/ecocusco/
chmod 644 /ruta/a/ecocusco/.env
mkdir -p /ruta/a/ecocusco/logs/
chmod 755 /ruta/a/ecocusco/logs/
```

### Paso 5: Configurar Servidor Web

#### Apache (.htaccess)

Crea un archivo `.htaccess` en la carpeta `public/`:

```apache
RewriteEngine On

# Redirigir a HTTPS (opcional)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Seguridad
<FilesMatch "\.(env|log|sql)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Cache de archivos estáticos
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /var/www/html/ecocusco/public;
    index index.php;

    # Seguridad - denegar acceso a archivos sensibles
    location ~ /\.(env|log|sql) {
        deny all;
        return 404;
    }

    # Archivos estáticos
    location ~* \.(css|js|png|jpg|jpeg|gif|svg|ico)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # PHP
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Fallback para rutas
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

## Cuentas de Acceso

### Administrador por Defecto

- **Email:** `admin@ecocusco.pe`
- **Contraseña:** `password` 

⚠️ **IMPORTANTE:** Cambia esta contraseña inmediatamente después de la instalación.

### Crear Nuevos Usuarios

Los usuarios pueden registrarse libremente a través de la página de registro o el administrador puede crearlos desde el panel de administración.

## Funcionalidades Principales

### 1. **Sistema de Autenticación**
- Registro de usuarios con validación
- Inicio de sesión seguro
- Recuperación de contraseñas
- Sesiones persistentes opcionales

### 2. **Dashboard Interactivo**
- Estadísticas personales de reciclaje
- Gráficos de progreso
- Actividad reciente
- Ranking de usuarios

### 3. **Gestión de Reportes**
- Crear reportes de reciclaje
- Filtros avanzados de búsqueda
- Estados de procesamiento
- Historial completo

### 4. **Sistema de Estadísticas**
- Análisis temporal de datos
- Distribución por tipo de residuo
- Métricas de rendimiento
- Exportación de datos

### 5. **Sistema de Puntos y Gamificación**
- Puntos por cantidad reciclada
- Niveles de usuario (Bronce, Plata, Oro)
- Rankings y competencias
- Historial de puntos

## Seguridad

### Medidas Implementadas

- ✅ Protección CSRF en formularios
- ✅ Validación y sanitización de entradas
- ✅ Contraseñas hasheadas con bcrypt
- ✅ Sesiones seguras con cookies HttpOnly
- ✅ Validación de tipos de archivo
- ✅ Prevención de XSS
- ✅ Logging de eventos de seguridad

### Recomendaciones Adicionales

1. **Usar HTTPS en producción**
2. **Configurar firewall apropiado**
3. **Mantener PHP y MySQL actualizados**
4. **Realizar backups regulares**
5. **Monitorear logs de seguridad**

## Mantenimiento

### Logs del Sistema

Los logs se almacenan en `/logs/app.log` y incluyen:
- Eventos de autenticación
- Errores de aplicación
- Acciones administrativas
- Estadísticas de uso

### Backup de Base de Datos

```bash
# Crear backup
mysqldump -u usuario -p reciclaje_platform > backup_$(date +%Y%m%d_%H%M%S).sql

# Restaurar backup
mysql -u usuario -p reciclaje_platform < backup_archivo.sql
```

### Optimización

1. **Configurar caché de PHP OPcache**
2. **Optimizar consultas de base de datos**
3. **Comprimir archivos estáticos**
4. **Configurar CDN para recursos**

## Solución de Problemas

### Error de Conexión a Base de Datos

1. Verificar configuración en `.env`
2. Confirmar que el servicio MySQL esté activo
3. Validar permisos de usuario de base de datos
4. Revisar logs en `/logs/app.log`

### Errores de Permisos

```bash
# Ajustar permisos de archivos
find /ruta/a/ecocusco -type f -exec chmod 644 {} \;
find /ruta/a/ecocusco -type d -exec chmod 755 {} \;
chmod 755 /ruta/a/ecocusco/logs/
```

### Problemas de Sesión

1. Verificar configuración de sesiones en `config.php`
2. Confirmar permisos en directorio de sesiones
3. Revisar configuración de cookies

## Desarrollo y Personalización

### Agregar Nuevas Páginas

1. Crear archivo PHP en `/pages/`
2. Incluir archivos de configuración necesarios
3. Usar componentes de header y footer
4. Seguir patrones de validación y seguridad

### Modificar Estilos

1. Editar `/public/assets/css/style.css`
2. Usar variables CSS para consistencia
3. Mantener diseño responsivo

### Extensiones Recomendadas

- Sistema de notificaciones por email
- Integración con APIs de mapas
- Dashboard administrativo avanzado
- Módulo de reportes PDF automatizados
- API REST para aplicaciones móviles

## Soporte

Para soporte técnico o reportar problemas:

1. Revisar este README
2. Consultar logs de aplicación
3. Verificar configuración de entorno
4. Documentar pasos para reproducir errores

## Licencia

Este proyecto es software libre desarrollado para contribuir al desarrollo sostenible de Cusco, Perú.

---

**EcoCusco v1.0** - Plataforma Simple y Funcional para la Gestión de Residuos Sólidos Urbanos
