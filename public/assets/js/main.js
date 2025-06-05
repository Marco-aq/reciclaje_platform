/**
 * EcoCusco - JavaScript Principal
 * Funcionalidades generales de la aplicación
 */

// Variables globales
const EcoCusco = {
    version: '1.0.0',
    debug: true,
    apiUrl: '/api',
    
    // Configuración
    config: {
        alertTimeout: 5000,
        toastTimeout: 3000,
        animationDuration: 300
    },
    
    // Inicialización
    init: function() {
        this.setupEventListeners();
        this.initializeComponents();
        this.setupFormValidation();
        this.setupTooltips();
        this.log('EcoCusco inicializado correctamente');
    },
    
    // Configurar event listeners
    setupEventListeners: function() {
        document.addEventListener('DOMContentLoaded', () => {
            this.handleAlerts();
            this.handleBackToTop();
            this.handleFormSubmissions();
            this.handleTableActions();
        });
    },
    
    // Inicializar componentes
    initializeComponents: function() {
        // Inicializar tooltips de Bootstrap
        this.initTooltips();
        
        // Inicializar popovers de Bootstrap
        this.initPopovers();
        
        // Configurar dropdowns
        this.setupDropdowns();
        
        // Configurar modales
        this.setupModals();
    },
    
    // Manejo de alertas
    handleAlerts: function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-persistent)');
        
        alerts.forEach(alert => {
            // Auto-hide alerts después de un tiempo
            setTimeout(() => {
                if (alert && alert.parentNode) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, this.config.alertTimeout);
        });
    },
    
    // Botón "Volver arriba"
    handleBackToTop: function() {
        const backToTopBtn = document.getElementById('backToTop');
        
        if (backToTopBtn) {
            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    backToTopBtn.classList.remove('d-none');
                    backToTopBtn.classList.add('fade-in');
                } else {
                    backToTopBtn.classList.add('d-none');
                    backToTopBtn.classList.remove('fade-in');
                }
            });
            
            backToTopBtn.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    },
    
    // Manejo de envío de formularios
    handleFormSubmissions: function() {
        const forms = document.querySelectorAll('form[data-ajax="true"]');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitFormAjax(form);
            });
        });
    },
    
    // Envío de formulario via AJAX
    submitFormAjax: function(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.innerHTML : '';
        
        // Deshabilitar botón y mostrar loading
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
        }
        
        fetch(form.action || window.location.href, {
            method: form.method || 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showToast('Operación exitosa', data.message || 'Datos guardados correctamente', 'success');
                
                // Redirigir si se especifica
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                }
                
                // Limpiar formulario si se especifica
                if (data.reset_form) {
                    form.reset();
                }
            } else {
                this.showToast('Error', data.message || 'Ha ocurrido un error', 'error');
                
                // Mostrar errores de validación
                if (data.errors) {
                    this.showValidationErrors(form, data.errors);
                }
            }
        })
        .catch(error => {
            this.log('Error en AJAX:', error);
            this.showToast('Error', 'Error de conexión. Inténtalo nuevamente.', 'error');
        })
        .finally(() => {
            // Restaurar botón
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    },
    
    // Mostrar errores de validación
    showValidationErrors: function(form, errors) {
        // Limpiar errores previos
        form.querySelectorAll('.is-invalid').forEach(input => {
            input.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(feedback => {
            feedback.remove();
        });
        
        // Mostrar nuevos errores
        Object.keys(errors).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.classList.add('is-invalid');
                
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = errors[fieldName];
                field.parentNode.appendChild(feedback);
            }
        });
    },
    
    // Acciones de tabla
    handleTableActions: function() {
        document.addEventListener('click', (e) => {
            // Botón de eliminar
            if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
                e.preventDefault();
                const btn = e.target.classList.contains('btn-delete') ? e.target : e.target.closest('.btn-delete');
                this.confirmDelete(btn);
            }
            
            // Botón de editar
            if (e.target.classList.contains('btn-edit') || e.target.closest('.btn-edit')) {
                e.preventDefault();
                const btn = e.target.classList.contains('btn-edit') ? e.target : e.target.closest('.btn-edit');
                this.handleEdit(btn);
            }
        });
    },
    
    // Confirmar eliminación
    confirmDelete: function(btn) {
        const itemName = btn.dataset.itemName || 'este elemento';
        const deleteUrl = btn.href || btn.dataset.url;
        
        if (confirm(`¿Estás seguro de que quieres eliminar ${itemName}? Esta acción no se puede deshacer.`)) {
            this.performDelete(deleteUrl, btn);
        }
    },
    
    // Realizar eliminación
    performDelete: function(url, btn) {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showToast('Eliminado', data.message || 'Elemento eliminado correctamente', 'success');
                
                // Remover fila de la tabla
                const row = btn.closest('tr');
                if (row) {
                    row.style.transition = 'opacity 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
                
                // Actualizar contadores si existen
                this.updateCounters();
            } else {
                this.showToast('Error', data.message || 'No se pudo eliminar el elemento', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(error => {
            this.log('Error en eliminación:', error);
            this.showToast('Error', 'Error de conexión', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    },
    
    // Manejo de edición
    handleEdit: function(btn) {
        const editUrl = btn.href || btn.dataset.url;
        if (editUrl) {
            window.location.href = editUrl;
        }
    },
    
    // Actualizar contadores
    updateCounters: function() {
        const counters = document.querySelectorAll('[data-counter]');
        counters.forEach(counter => {
            const currentValue = parseInt(counter.textContent) || 0;
            if (currentValue > 0) {
                counter.textContent = currentValue - 1;
            }
        });
    },
    
    // Configuración de validación de formularios
    setupFormValidation: function() {
        const forms = document.querySelectorAll('.needs-validation');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Enfocar primer campo con error
                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.focus();
                    }
                }
                
                form.classList.add('was-validated');
            });
            
            // Validación en tiempo real
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.addEventListener('blur', () => {
                    if (input.checkValidity()) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else {
                        input.classList.remove('is-valid');
                        input.classList.add('is-invalid');
                    }
                });
            });
        });
    },
    
    // Configurar tooltips
    setupTooltips: function() {
        this.initTooltips();
    },
    
    // Inicializar tooltips
    initTooltips: function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    },
    
    // Inicializar popovers
    initPopovers: function() {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    },
    
    // Configurar dropdowns
    setupDropdowns: function() {
        const dropdowns = document.querySelectorAll('.dropdown-toggle');
        dropdowns.forEach(dropdown => {
            new bootstrap.Dropdown(dropdown);
        });
    },
    
    // Configurar modales
    setupModals: function() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('show.bs.modal', (e) => {
                this.log('Modal abierto:', modal.id);
            });
            
            modal.addEventListener('hidden.bs.modal', (e) => {
                this.log('Modal cerrado:', modal.id);
                
                // Limpiar formularios en modales
                const forms = modal.querySelectorAll('form');
                forms.forEach(form => {
                    form.reset();
                    form.classList.remove('was-validated');
                    form.querySelectorAll('.is-valid, .is-invalid').forEach(input => {
                        input.classList.remove('is-valid', 'is-invalid');
                    });
                });
            });
        });
    },
    
    // Mostrar toast
    showToast: function(title, message, type = 'info') {
        const toastContainer = this.getToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${this.getBootstrapColor(type)} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}</strong><br>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast, {
            delay: this.config.toastTimeout
        });
        bsToast.show();
        
        // Remover toast después de que se oculte
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    },
    
    // Obtener contenedor de toasts
    getToastContainer: function() {
        let container = document.getElementById('toast-container');
        
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        
        return container;
    },
    
    // Convertir tipo a color de Bootstrap
    getBootstrapColor: function(type) {
        const colorMap = {
            'success': 'success',
            'error': 'danger',
            'warning': 'warning',
            'info': 'info'
        };
        
        return colorMap[type] || 'info';
    },
    
    // Utilidades de API
    api: {
        get: function(url) {
            return fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => response.json());
        },
        
        post: function(url, data) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            }).then(response => response.json());
        },
        
        put: function(url, data) {
            return fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            }).then(response => response.json());
        },
        
        delete: function(url) {
            return fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => response.json());
        }
    },
    
    // Utilidades
    utils: {
        // Formatear números
        formatNumber: function(number, decimals = 2) {
            return new Intl.NumberFormat('es-PE', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(number);
        },
        
        // Formatear fechas
        formatDate: function(date, options = {}) {
            const defaultOptions = {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            
            return new Intl.DateTimeFormat('es-PE', { ...defaultOptions, ...options }).format(new Date(date));
        },
        
        // Debounce
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        // Throttle
        throttle: function(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
    },
    
    // Logging
    log: function(...args) {
        if (this.debug) {
            console.log('[EcoCusco]', ...args);
        }
    },
    
    error: function(...args) {
        console.error('[EcoCusco Error]', ...args);
    }
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    EcoCusco.init();
});

// Exportar para uso global
window.EcoCusco = EcoCusco;

// Funciones globales de conveniencia
window.showToast = (title, message, type) => EcoCusco.showToast(title, message, type);
window.formatNumber = (number, decimals) => EcoCusco.utils.formatNumber(number, decimals);
window.formatDate = (date, options) => EcoCusco.utils.formatDate(date, options);
