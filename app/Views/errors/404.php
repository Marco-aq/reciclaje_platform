<div class="error-container">
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="text-center">
            <div class="error-illustration mb-4">
                <i class="fas fa-search fa-5x text-muted mb-3"></i>
                <div class="error-code">
                    <span class="display-1 fw-bold text-success"><?= $errorCode ?></span>
                </div>
            </div>
            
            <div class="error-content">
                <h1 class="h2 mb-3"><?= $this->escape($errorMessage) ?></h1>
                <p class="lead text-muted mb-4">
                    <?= $this->escape($errorDescription) ?>
                </p>
                
                <div class="error-actions">
                    <a href="<?= $this->url('/') ?>" class="btn btn-success btn-lg me-3">
                        <i class="fas fa-home me-2"></i>Ir al Inicio
                    </a>
                    <button onclick="history.back()" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Volver Atrás
                    </button>
                </div>
                
                <div class="mt-5">
                    <h5 class="mb-3">¿Necesitas ayuda?</h5>
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <a href="<?= $this->url('/reportes') ?>" class="text-decoration-none">
                                        <div class="card h-100 border-0 bg-light">
                                            <div class="card-body text-center">
                                                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                                <h6>Ver Reportes</h6>
                                                <small class="text-muted">Explora reportes existentes</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="<?= $this->url('/estadisticas') ?>" class="text-decoration-none">
                                        <div class="card h-100 border-0 bg-light">
                                            <div class="card-body text-center">
                                                <i class="fas fa-chart-bar fa-2x text-info mb-2"></i>
                                                <h6>Estadísticas</h6>
                                                <small class="text-muted">Ver métricas del sistema</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="<?= $this->url('/contacto') ?>" class="text-decoration-none">
                                        <div class="card h-100 border-0 bg-light">
                                            <div class="card-body text-center">
                                                <i class="fas fa-envelope fa-2x text-success mb-2"></i>
                                                <h6>Contacto</h6>
                                                <small class="text-muted">Envíanos un mensaje</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.error-container {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    min-height: 100vh;
}

.error-illustration {
    position: relative;
}

.error-code {
    position: relative;
    display: inline-block;
}

.error-code::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 200px;
    height: 200px;
    background: rgba(46, 125, 50, 0.1);
    border-radius: 50%;
    z-index: -1;
}

.error-actions .btn {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.error-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .error-code span {
        font-size: 4rem;
    }
    
    .error-actions .btn {
        display: block;
        width: 100%;
        margin-bottom: 1rem;
    }
    
    .error-actions .btn:last-child {
        margin-bottom: 0;
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

.error-illustration i {
    animation: float 3s ease-in-out infinite;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add some interactivity to the error page
    const errorCode = document.querySelector('.error-code span');
    
    if (errorCode) {
        // Add a subtle animation to the error code
        errorCode.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        errorCode.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    }
    
    // Track error for analytics (if needed)
    if (typeof gtag !== 'undefined') {
        gtag('event', 'page_not_found', {
            'page_location': window.location.href,
            'page_title': document.title
        });
    }
});
</script>
