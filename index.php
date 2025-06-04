<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EcoCusco - Gestión de Residuos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    .hero-section {
      background-color: #f8f9fa;
      padding: 60px 0;
    }
    .features-section {
      background-color: #ffffff;
      padding: 40px 0;
    }
    .stats-section {
      background-color: #009c5c;
      color: white;
      padding: 40px 0;
      text-align: center;
    }
    .footer {
      background-color: #212529;
      color: white;
      padding: 40px 0;
    }
    .footer a {
      color: #ccc;
      text-decoration: none;
    }
    .footer a:hover {
      color: white;
    }
    .map-container {
      padding: 40px 0;
    }
    .nav-link.active {
      color: #009c5c !important;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container">
      <a class="navbar-brand" href="#"><i class="fas fa-recycle me-2"></i>EcoCusco</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item"><a class="nav-link active" href="./home-page.php">Inicio</a></li>
          <li class="nav-item"><a class="nav-link" href="./reportes.php">Reportar</a></li>
          <li class="nav-item"><a class="nav-link" href="./estadisticas.php">Estadísticas</a></li>
        </ul>
        <div>
          <a href="#" class="btn btn-outline-success me-2">Ingresar</a>
          <a href="#" class="btn btn-success">Registrarse</a>
        </div>
      </div>
    </div>
  </nav>

  <section class="hero-section">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h1>Sistema de Gestión de Residuos Sólidos Urbanos en Cusco</h1>
          <p>Plataforma colaborativa para reportar y gestionar puntos de acumulación de residuos mediante geolocalización y mapas interactivos.</p>
          <a href="#" class="btn btn-success me-2">Reportar Residuos</a>
          <a href="#" class="btn btn-outline-secondary">Ver Mapa</a>
        </div>
        <div class="col-md-6">
          <img src="./pagina principal.png" alt="Gestión de Residuos" class="img-fluid rounded">
        </div>
      </div>
    </div>
  </section>

  <section class="features-section py-5">
    <div class="container">
        <h2 class="text-center mb-5">Características Principales</h2>
        <div class="row">
            <!-- Reportes Geolocalizados -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 bg-light border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="fas fa-map-marker-alt fa-2x text-success"></i>
                        </div>
                        <h4 class="card-title">Reportes Geolocalizados</h4>
                        <p class="card-text">Reporta puntos de acumulación de residuos con ubicación exacta y evidencia fotográfica.</p>
                    </div>
                </div>
            </div>
            
            <!-- Directorio de Empresas -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 bg-light border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="fas fa-building fa-2x text-success"></i>
                        </div>
                        <h4 class="card-title">Directorio de Empresas</h4>
                        <p class="card-text">Accede a una base de datos de empresas de reciclaje y limpieza certificadas.</p>
                    </div>
                </div>
            </div>
            
            <!-- Integración API -->
            <div class="col-md-4 mb-4">
                <div class="card h-100 bg-light border-0 shadow-sm">
                    <div class="card-body text-center p-4">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="fas fa-plug fa-2x text-success"></i>
                        </div>
                        <h4 class="card-title">Integración API</h4>
                        <p class="card-text">Comunicación optimizada entre ciudadanos y empresas de recolección.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="map-container bg-light py-5">
    <div class="container">
        <h2 class="text-center mb-4">Mapa Interactivo de Residuos</h2>
        
        <div class="d-flex justify-content-end mb-4">
            <button class="btn btn-outline-secondary me-2">
                <i class="fas fa-filter me-1"></i> Filtrar
            </button>
            <button class="btn btn-success">
                <i class="fas fa-plus me-1"></i> Nuevo Reporte
            </button>
        </div>
        
        <!-- Mapa de Google Maps -->
        <div class="ratio ratio-16x9 mb-4">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d124140.274301623!2d-71.9924992!3d-13.53195!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x916dd5cdcdfd5f3b%3A0x9e5e3a84d6f4b0a4!2sCusco%2C%20Peru!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy"
                class="rounded shadow-sm">
            </iframe>
        </div>
        

    </div>
</section>

  <section class="stats-section">
    <div class="container">
      <div class="row">
        <div class="col-md-3"><h4>2,500+</h4><p>Reportes Realizados</p></div>
        <div class="col-md-3"><h4>50+</h4><p>Empresas Registradas</p></div>
        <div class="col-md-3"><h4>1,800+</h4><p>Problemas Resueltos</p></div>
        <div class="col-md-3"><h4>15,000+</h4><p>Usuarios Activos</p></div>
      </div>
    </div>
  </section>

  <footer class="footer">
    <div class="container">
      <div class="row">
        <div class="col-md-4">
          <h5>EcoCusco</h5>
          <p>Trabajando juntos por una ciudad más limpia y sostenible.</p>
        </div>
        <div class="col-md-2">
          <h6>Enlaces Rápidos</h6>
          <ul class="list-unstyled">
            <li><a href="#">Inicio</a></li>
            <li><a href="#">Reportar</a></li>
            <li><a href="#">Empresas</a></li>
            <li><a href="#">Estadísticas</a></li>
          </ul>
        </div>
        <div class="col-md-3">
          <h6>Contacto</h6>
          <p><i class="fas fa-envelope me-2"></i>contacto@ecocusco.pe</p>
          <p><i class="fas fa-phone me-2"></i>+51 984 123 456</p>
          <p><i class="fas fa-map-marker-alt me-2"></i>Cusco, Perú</p>
        </div>
        <div class="col-md-3">
          <h6>Síguenos</h6>
          <a href="#" class="me-3"><i class="fab fa-facebook fa-lg"></i></a>
          <a href="#" class="me-3"><i class="fab fa-twitter fa-lg"></i></a>
          <a href="#"><i class="fab fa-instagram fa-lg"></i></a>
        </div>
      </div>
      <div class="text-center mt-4">
        <small>&copy; 2025 EcoCusco. Todos los derechos reservados.</small>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>