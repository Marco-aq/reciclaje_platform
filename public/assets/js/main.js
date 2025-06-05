/**
 * EcoCusco - Main JavaScript File
 * 
 * This file contains all the main JavaScript functionality for the EcoCusco platform.
 * It includes utilities, event handlers, and common functions used throughout the application.
 */

(function() {
    'use strict';

    // ================================================
    // Global Application Object
    // ================================================
    window.EcoCusco = {
        config: {
            baseUrl: window.location.origin,
            apiUrl: window.location.origin + '/api',
            mapCenter: { lat: -13.5319, lng: -71.9675 }, // Cusco coordinates
            mapZoom: 13
        },
        utils: {},
        components: {},
        map: null,
        charts: {}
    };

    // ================================================
    // Utility Functions
    // ================================================
    EcoCusco.utils = {
        /**
         * Make AJAX request
         * @param {string} url 
         * @param {object} options 
         * @returns {Promise}
         */
        ajax: function(url, options = {}) {
            const defaults = {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            const config = Object.assign({}, defaults, options);

            return fetch(url, config)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .catch(error => {
                    console.error('AJAX Error:', error);
                    throw error;
                });
        },

        /**
         * Show notification
         * @param {string} message 
         * @param {string} type 
         * @param {number} duration 
         */
        notify: function(message, type = 'info', duration = 5000) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = `
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 400px;
                animation: slideInRight 0.3s ease-out;
            `;
            
            alertDiv.innerHTML = `
                <i class="fas fa-${this.getIconForType(type)} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(alertDiv);

            // Auto remove after duration
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, duration);
        },

        /**
         * Get icon for notification type
         * @param {string} type 
         * @returns {string}
         */
        getIconForType: function(type) {
            const icons = {
                'success': 'check-circle',
                'danger': 'exclamation-circle',
                'warning': 'exclamation-triangle',
                'info': 'info-circle'
            };
            return icons[type] || 'info-circle';
        },

        /**
         * Format date to Spanish locale
         * @param {string|Date} date 
         * @param {object} options 
         * @returns {string}
         */
        formatDate: function(date, options = {}) {
            const defaultOptions = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            
            const formatOptions = Object.assign({}, defaultOptions, options);
            
            return new Date(date).toLocaleDateString('es-PE', formatOptions);
        },

        /**
         * Debounce function
         * @param {Function} func 
         * @param {number} wait 
         * @returns {Function}
         */
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

        /**
         * Throttle function
         * @param {Function} func 
         * @param {number} limit 
         * @returns {Function}
         */
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
        },

        /**
         * Get user's current location
         * @returns {Promise}
         */
        getCurrentLocation: function() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Geolocation is not supported by this browser.'));
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    position => {
                        resolve({
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        });
                    },
                    error => {
                        reject(error);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 300000 // 5 minutes
                    }
                );
            });
        },

        /**
         * Validate form fields
         * @param {HTMLFormElement} form 
         * @returns {object}
         */
        validateForm: function(form) {
            const errors = {};
            const formData = new FormData(form);
            
            // Get all required fields
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                const value = formData.get(field.name);
                if (!value || value.trim() === '') {
                    errors[field.name] = 'Este campo es requerido';
                }
            });

            // Email validation
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                const value = formData.get(field.name);
                if (value && !this.isValidEmail(value)) {
                    errors[field.name] = 'Ingresa un email válido';
                }
            });

            return {
                isValid: Object.keys(errors).length === 0,
                errors: errors
            };
        },

        /**
         * Validate email format
         * @param {string} email 
         * @returns {boolean}
         */
        isValidEmail: function(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        /**
         * Show loading state on button
         * @param {HTMLButtonElement} button 
         * @param {string} loadingText 
         */
        showButtonLoading: function(button, loadingText = 'Cargando...') {
            if (button.dataset.originalText === undefined) {
                button.dataset.originalText = button.innerHTML;
            }
            button.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>${loadingText}`;
            button.disabled = true;
        },

        /**
         * Hide loading state on button
         * @param {HTMLButtonElement} button 
         */
        hideButtonLoading: function(button) {
            if (button.dataset.originalText !== undefined) {
                button.innerHTML = button.dataset.originalText;
                button.disabled = false;
            }
        }
    };

    // ================================================
    // Component Initializers
    // ================================================
    EcoCusco.components = {
        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        },

        /**
         * Initialize popovers
         */
        initPopovers: function() {
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        },

        /**
         * Initialize form validation
         */
        initFormValidation: function() {
            const forms = document.querySelectorAll('.needs-validation');
            
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    const validation = EcoCusco.utils.validateForm(form);
                    
                    if (!validation.isValid) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        // Show errors
                        Object.keys(validation.errors).forEach(fieldName => {
                            const field = form.querySelector(`[name="${fieldName}"]`);
                            if (field) {
                                field.classList.add('is-invalid');
                                const feedback = field.parentNode.querySelector('.invalid-feedback');
                                if (feedback) {
                                    feedback.textContent = validation.errors[fieldName];
                                }
                            }
                        });
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
        },

        /**
         * Initialize search functionality
         */
        initSearch: function() {
            const searchForm = document.querySelector('#searchForm');
            const searchInput = document.querySelector('#searchInput');
            
            if (searchForm && searchInput) {
                const debouncedSearch = EcoCusco.utils.debounce(function(query) {
                    if (query.length >= 3) {
                        EcoCusco.components.performSearch(query);
                    }
                }, 300);
                
                searchInput.addEventListener('input', function() {
                    debouncedSearch(this.value.trim());
                });
            }
        },

        /**
         * Perform search
         * @param {string} query 
         */
        performSearch: function(query) {
            EcoCusco.utils.ajax(`${EcoCusco.config.baseUrl}/search?q=${encodeURIComponent(query)}`)
                .then(data => {
                    if (data.success) {
                        this.displaySearchResults(data.results);
                    }
                })
                .catch(error => {
                    console.error('Search error:', error);
                });
        },

        /**
         * Initialize location picker
         */
        initLocationPicker: function() {
            const locationBtn = document.querySelector('#getLocationBtn');
            const latInput = document.querySelector('#latitud');
            const lngInput = document.querySelector('#longitud');
            
            if (locationBtn && latInput && lngInput) {
                locationBtn.addEventListener('click', function() {
                    EcoCusco.utils.showButtonLoading(this, 'Obteniendo ubicación...');
                    
                    EcoCusco.utils.getCurrentLocation()
                        .then(location => {
                            latInput.value = location.lat.toFixed(6);
                            lngInput.value = location.lng.toFixed(6);
                            EcoCusco.utils.notify('Ubicación obtenida exitosamente', 'success');
                        })
                        .catch(error => {
                            console.error('Location error:', error);
                            EcoCusco.utils.notify('No se pudo obtener la ubicación', 'danger');
                        })
                        .finally(() => {
                            EcoCusco.utils.hideButtonLoading(this);
                        });
                });
            }
        },

        /**
         * Initialize file upload
         */
        initFileUpload: function() {
            const fileInputs = document.querySelectorAll('input[type="file"]');
            
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        // Validate file size (5MB max)
                        if (file.size > 5 * 1024 * 1024) {
                            EcoCusco.utils.notify('El archivo es demasiado grande (máximo 5MB)', 'danger');
                            this.value = '';
                            return;
                        }
                        
                        // Validate file type
                        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                        if (!allowedTypes.includes(file.type)) {
                            EcoCusco.utils.notify('Tipo de archivo no válido', 'danger');
                            this.value = '';
                            return;
                        }
                        
                        // Show preview if it's an image
                        if (file.type.startsWith('image/')) {
                            this.showImagePreview(file);
                        }
                    }
                });
            });
        },

        /**
         * Show image preview
         * @param {File} file 
         */
        showImagePreview: function(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let preview = document.querySelector('#imagePreview');
                if (!preview) {
                    preview = document.createElement('div');
                    preview.id = 'imagePreview';
                    preview.className = 'mt-3';
                    file.parentNode.appendChild(preview);
                }
                
                preview.innerHTML = `
                    <img src="${e.target.result}" 
                         class="img-thumbnail" 
                         style="max-width: 200px; max-height: 200px;" 
                         alt="Vista previa">
                `;
            };
            reader.readAsDataURL(file);
        },

        /**
         * Initialize dashboard widgets
         */
        initDashboard: function() {
            // Auto-refresh dashboard data every 5 minutes
            if (document.querySelector('.dashboard-container')) {
                setInterval(() => {
                    this.refreshDashboardData();
                }, 5 * 60 * 1000);
            }
        },

        /**
         * Refresh dashboard data
         */
        refreshDashboardData: function() {
            EcoCusco.utils.ajax(`${EcoCusco.config.apiUrl}/dashboard/data`)
                .then(data => {
                    if (data.success) {
                        this.updateDashboardWidgets(data.data);
                    }
                })
                .catch(error => {
                    console.error('Dashboard refresh error:', error);
                });
        },

        /**
         * Update dashboard widgets
         * @param {object} data 
         */
        updateDashboardWidgets: function(data) {
            // Update stat cards
            Object.keys(data).forEach(key => {
                const element = document.querySelector(`[data-stat="${key}"]`);
                if (element) {
                    element.textContent = data[key];
                }
            });
        }
    };

    // ================================================
    // Event Listeners
    // ================================================
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize components
        EcoCusco.components.initTooltips();
        EcoCusco.components.initPopovers();
        EcoCusco.components.initFormValidation();
        EcoCusco.components.initSearch();
        EcoCusco.components.initLocationPicker();
        EcoCusco.components.initFileUpload();
        EcoCusco.components.initDashboard();

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                }
            }, 5000);
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Back to top button
        const backToTopBtn = document.querySelector('#backToTop');
        if (backToTopBtn) {
            window.addEventListener('scroll', EcoCusco.utils.throttle(function() {
                if (window.pageYOffset > 300) {
                    backToTopBtn.style.display = 'block';
                } else {
                    backToTopBtn.style.display = 'none';
                }
            }, 100));

            backToTopBtn.addEventListener('click', function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }

        // Confirm delete actions
        document.addEventListener('click', function(e) {
            if (e.target.matches('[data-confirm-delete]')) {
                e.preventDefault();
                const message = e.target.dataset.confirmDelete || '¿Estás seguro de que quieres eliminar este elemento?';
                
                if (confirm(message)) {
                    // If it's a form, submit it
                    const form = e.target.closest('form');
                    if (form) {
                        form.submit();
                    } else if (e.target.href) {
                        window.location.href = e.target.href;
                    }
                }
            }
        });
    });

    // ================================================
    // CSS Animations
    // ================================================
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeInUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
        #backToTop {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: var(--primary-color, #2E7D32);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            z-index: 1000;
            display: none;
        }
        
        #backToTop:hover {
            background-color: var(--primary-dark, #1B5E20);
            transform: translateY(-2px);
        }
    `;
    document.head.appendChild(style);

    // ================================================
    // Add back to top button
    // ================================================
    const backToTopBtn = document.createElement('button');
    backToTopBtn.id = 'backToTop';
    backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopBtn.title = 'Volver arriba';
    document.body.appendChild(backToTopBtn);

})();
