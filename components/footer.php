<?php
/**
 * Footer Component
 */
?>

<!-- Footer -->
<footer class="bg-dark text-light py-5 mt-auto">
    <div class="container">
        <div class="row g-4">
            <!-- About Section -->
            <div class="col-lg-4 col-md-6">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-recycle me-2 text-success"></i>
                    EcoCusco
                </h5>
                <p class="text-muted">
                    Plataforma digital para la gestión inteligente de residuos sólidos urbanos en la ciudad del Cusco, 
                    contribuyendo al desarrollo sostenible y la preservación del patrimonio cultural.
                </p>
                <div class="d-flex gap-3">
                    <a href="#" class="text-muted hover-text-success">
                        <i class="fab fa-facebook-f fa-lg"></i>
                    </a>
                    <a href="#" class="text-muted hover-text-success">
                        <i class="fab fa-twitter fa-lg"></i>
                    </a>
                    <a href="#" class="text-muted hover-text-success">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                    <a href="#" class="text-muted hover-text-success">
                        <i class="fab fa-linkedin-in fa-lg"></i>
                    </a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h6 class="fw-bold mb-3">Enlaces Rápidos</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="<?= url() ?>" class="text-muted text-decoration-none hover-text-success">
                            <i class="fas fa-home me-2"></i>Inicio
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="mb-2">
                            <a href="<?= url('../pages/dashboard.php') ?>" class="text-muted text-decoration-none hover-text-success">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?= url('../pages/reportes.php') ?>" class="text-muted text-decoration-none hover-text-success">
                                <i class="fas fa-chart-bar me-2"></i>Reportes
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?= url('../pages/estadisticas.php') ?>" class="text-muted text-decoration-none hover-text-success">
                                <i class="fas fa-chart-pie me-2"></i>Estadísticas
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="mb-2">
                            <a href="<?= url('../pages/register.php') ?>" class="text-muted text-decoration-none hover-text-success">
                                <i class="fas fa-user-plus me-2"></i>Registrarse
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?= url('../pages/login.php') ?>" class="text-muted text-decoration-none hover-text-success">
                                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Support -->
            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold mb-3">Soporte</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <a href="<?= url('../pages/ayuda.php') ?>" class="text-muted text-decoration-none hover-text-success">
                            <i class="fas fa-question-circle me-2"></i>Centro de Ayuda
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= url('../pages/contacto.php') ?>" class="text-muted text-decoration-none hover-text-success">
                            <i class="fas fa-envelope me-2"></i>Contacto
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= url('../pages/documentacion.php') ?>" class="text-muted text-decoration-none hover-text-success">
                            <i class="fas fa-book me-2"></i>Documentación
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="<?= url('../pages/politicas.php') ?>" class="text-muted text-decoration-none hover-text-success">
                            <i class="fas fa-shield-alt me-2"></i>Políticas
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold mb-3">Contacto</h6>
                <ul class="list-unstyled">
                    <li class="mb-2 text-muted">
                        <i class="fas fa-map-marker-alt me-2 text-success"></i>
                        Cusco, Perú
                    </li>
                    <li class="mb-2 text-muted">
                        <i class="fas fa-phone me-2 text-success"></i>
                        +51 984 123 456
                    </li>
                    <li class="mb-2 text-muted">
                        <i class="fas fa-envelope me-2 text-success"></i>
                        info@ecocusco.pe
                    </li>
                    <li class="mb-2 text-muted">
                        <i class="fas fa-clock me-2 text-success"></i>
                        Lun - Vie: 8:00 AM - 6:00 PM
                    </li>
                </ul>
            </div>
        </div>

        <hr class="my-4 border-secondary">

        <!-- Bottom Footer -->
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="text-muted mb-0">
                    &copy; <?= date('Y') ?> EcoCusco. Todos los derechos reservados.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex justify-content-md-end justify-content-center gap-3 mt-3 mt-md-0">
                    <a href="<?= url('../pages/terminos.php') ?>" class="text-muted text-decoration-none hover-text-success small">
                        Términos de Uso
                    </a>
                    <a href="<?= url('../pages/privacidad.php') ?>" class="text-muted text-decoration-none hover-text-success small">
                        Política de Privacidad
                    </a>
                    <a href="<?= url('../pages/cookies.php') ?>" class="text-muted text-decoration-none hover-text-success small">
                        Cookies
                    </a>
                </div>
            </div>
        </div>

        <!-- Development Info (solo en modo debug) -->
        <?php if (APP_DEBUG): ?>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-info alert-sm">
                        <small>
                            <i class="fas fa-code me-2"></i>
                            <strong>Modo Desarrollo:</strong> 
                            Versión <?= '1.0.0' ?> | 
                            PHP <?= phpversion() ?> | 
                            Tiempo de carga: <?= number_format((microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))) * 1000, 2) ?>ms
                        </small>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</footer>

<!-- Back to Top Button -->
<button class="btn btn-success btn-floating position-fixed bottom-0 end-0 m-3 d-none" id="backToTop" style="z-index: 1000;">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Custom Footer Styles -->
<style>
.hover-text-success:hover {
    color: #198754 !important;
    transition: color 0.3s ease;
}

.btn-floating {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

#backToTop {
    transition: all 0.3s ease;
}

#backToTop:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.4);
}
</style>

<!-- Footer JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Back to top button functionality
    const backToTopBtn = document.getElementById('backToTop');
    
    if (backToTopBtn) {
        // Show/hide button based on scroll position
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.remove('d-none');
            } else {
                backToTopBtn.classList.add('d-none');
            }
        });
        
        // Smooth scroll to top
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});
</script>
