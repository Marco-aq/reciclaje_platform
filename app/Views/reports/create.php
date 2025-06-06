<?php
$additional_css = '
<style>
    .create-header {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
    
    .form-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
    
    .form-step {
        display: none;
    }
    
    .form-step.active {
        display: block;
    }
    
    .step-indicator {
        display: flex;
        justify-content: center;
        margin-bottom: 2rem;
    }
    
    .step {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 1rem;
        position: relative;
        color: #6c757d;
        font-weight: bold;
    }
    
    .step.active {
        background: var(--primary-color);
        color: white;
    }
    
    .step.completed {
        background: var(--success-color);
        color: white;
    }
    
    .step::after {
        content: "";
        position: absolute;
        right: -2rem;
        width: 2rem;
        height: 2px;
        background: #e9ecef;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .step:last-child::after {
        display: none;
    }
    
    .step.completed::after {
        background: var(--success-color);
    }
    
    .material-selector {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .material-option {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }
    
    .material-option:hover {
        border-color: var(--primary-color);
        background: rgba(40, 167, 69, 0.1);
    }
    
    .material-option.selected {
        border-color: var(--primary-color);
        background: var(--primary-color);
        color: white;
    }
    
    .material-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .photo-upload {
        border: 2px dashed #e9ecef;
        border-radius: 10px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    
    .photo-upload:hover {
        border-color: var(--primary-color);
        background: rgba(40, 167, 69, 0.1);
    }
    
    .photo-upload.dragover {
        border-color: var(--primary-color);
        background: rgba(40, 167, 69, 0.2);
    }
    
    .photo-preview {
        max-width: 200px;
        max-height: 200px;
        border-radius: 10px;
        margin-top: 1rem;
    }
    
    .summary-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        border-left: 4px solid var(--primary-color);
    }
</style>
';

$inline_js = '
let currentStep = 1;
const totalSteps = 3;

// Navegación entre pasos
function nextStep() {
    if (validateCurrentStep()) {
        if (currentStep < totalSteps) {
            currentStep++;
            updateStepDisplay();
        }
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateStepDisplay();
    }
}

function updateStepDisplay() {
    // Ocultar todos los pasos
    document.querySelectorAll(".form-step").forEach(step => {
        step.classList.remove("active");
    });
    
    // Mostrar paso actual
    document.getElementById("step" + currentStep).classList.add("active");
    
    // Actualizar indicadores
    document.querySelectorAll(".step").forEach((step, index) => {
        step.classList.remove("active", "completed");
        if (index + 1 === currentStep) {
            step.classList.add("active");
        } else if (index + 1 < currentStep) {
            step.classList.add("completed");
        }
    });
    
    // Actualizar botones
    document.getElementById("prevBtn").style.display = currentStep === 1 ? "none" : "inline-block";
    document.getElementById("nextBtn").style.display = currentStep === totalSteps ? "none" : "inline-block";
    document.getElementById("submitBtn").style.display = currentStep === totalSteps ? "inline-block" : "none";
    
    // Actualizar resumen en el paso final
    if (currentStep === 3) {
        updateSummary();
    }
}

function validateCurrentStep() {
    switch(currentStep) {
        case 1:
            const materialSelected = document.querySelector("input[name=\"tipo_material\"]:checked");
            if (!materialSelected) {
                showAlert("Por favor selecciona un tipo de material", "warning");
                return false;
            }
            break;
            
        case 2:
            const cantidad = document.getElementById("cantidad").value;
            const ubicacion = document.getElementById("ubicacion").value;
            
            if (!cantidad || cantidad <= 0) {
                showAlert("Por favor ingresa una cantidad válida", "warning");
                return false;
            }
            
            if (!ubicacion || ubicacion.length < 5) {
                showAlert("Por favor ingresa una ubicación válida (mínimo 5 caracteres)", "warning");
                return false;
            }
            break;
    }
    return true;
}

function selectMaterial(type, element) {
    // Remover selección anterior
    document.querySelectorAll(".material-option").forEach(opt => {
        opt.classList.remove("selected");
    });
    
    // Seleccionar el actual
    element.classList.add("selected");
    
    // Marcar el radio button
    document.getElementById("material_" + type).checked = true;
}

function updateSummary() {
    const materialType = document.querySelector("input[name=\"tipo_material\"]:checked");
    const cantidad = document.getElementById("cantidad").value;
    const ubicacion = document.getElementById("ubicacion").value;
    const descripcion = document.getElementById("descripcion").value;
    const foto = document.getElementById("foto").files[0];
    
    document.getElementById("summary-material").textContent = materialType ? materialType.nextElementSibling.textContent : "-";
    document.getElementById("summary-cantidad").textContent = cantidad ? cantidad + " kg" : "-";
    document.getElementById("summary-ubicacion").textContent = ubicacion || "-";
    document.getElementById("summary-descripcion").textContent = descripcion || "Sin descripción";
    document.getElementById("summary-foto").textContent = foto ? foto.name : "Sin foto";
}

// Manejo de upload de foto
document.addEventListener("DOMContentLoaded", function() {
    const photoUpload = document.getElementById("photo-upload");
    const fotoInput = document.getElementById("foto");
    
    // Click para seleccionar archivo
    photoUpload.addEventListener("click", () => fotoInput.click());
    
    // Drag and drop
    photoUpload.addEventListener("dragover", (e) => {
        e.preventDefault();
        photoUpload.classList.add("dragover");
    });
    
    photoUpload.addEventListener("dragleave", () => {
        photoUpload.classList.remove("dragover");
    });
    
    photoUpload.addEventListener("drop", (e) => {
        e.preventDefault();
        photoUpload.classList.remove("dragover");
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fotoInput.files = files;
            previewPhoto(files[0]);
        }
    });
    
    // Preview cuando se selecciona archivo
    fotoInput.addEventListener("change", (e) => {
        if (e.target.files.length > 0) {
            previewPhoto(e.target.files[0]);
        }
    });
    
    function previewPhoto(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const preview = document.getElementById("photo-preview");
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = "block";
            } else {
                const img = document.createElement("img");
                img.id = "photo-preview";
                img.src = e.target.result;
                img.className = "photo-preview";
                photoUpload.appendChild(img);
            }
        };
        reader.readAsDataURL(file);
    }
    
    // Inicializar display
    updateStepDisplay();
});
';
?>

<!-- Header -->
<div class="create-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="mb-2">
                <i class="fas fa-plus-circle me-3"></i>
                Crear Nuevo Reporte
            </h1>
            <p class="mb-0 opacity-75">
                Registra tu actividad de reciclaje y contribuye al cuidado del medio ambiente
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="<?= $this->url('/reportes') ?>" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i>
                Volver a Reportes
            </a>
        </div>
    </div>
</div>

<div class="form-card card">
    <div class="card-body p-4">
        <!-- Indicador de pasos -->
        <div class="step-indicator">
            <div class="step active">1</div>
            <div class="step">2</div>
            <div class="step">3</div>
        </div>
        
        <!-- Mostrar errores de validación -->
        <?php if ($this->hasErrors()): ?>
            <div class="alert alert-danger">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Por favor corrige los siguientes errores:</strong>
                </div>
                <?php foreach ($this->errors() as $field => $fieldErrors): ?>
                    <ul class="mb-0">
                        <?php foreach ($fieldErrors as $error): ?>
                            <li><?= $this->escape($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form id="reportForm" method="POST" action="<?= $this->url('/reportes') ?>" enctype="multipart/form-data">
            <?= $this->csrfField() ?>
            
            <!-- Paso 1: Tipo de Material -->
            <div id="step1" class="form-step active">
                <h4 class="mb-4">
                    <i class="fas fa-recycle me-2 text-primary"></i>
                    ¿Qué tipo de material reciclaste?
                </h4>
                
                <div class="material-selector">
                    <?php
                    $materialIcons = [
                        'plastico' => 'fa-bottle-water',
                        'papel' => 'fa-newspaper',
                        'vidrio' => 'fa-wine-bottle',
                        'metal' => 'fa-cog',
                        'electronico' => 'fa-mobile-alt',
                        'organico' => 'fa-leaf',
                        'textil' => 'fa-tshirt',
                        'otros' => 'fa-box'
                    ];
                    ?>
                    
                    <?php foreach ($tipos_materiales as $value => $label): ?>
                        <div class="material-option" onclick="selectMaterial('<?= $value ?>', this)">
                            <i class="fas <?= $materialIcons[$value] ?? 'fa-recycle' ?> material-icon"></i>
                            <div><?= $this->escape($label) ?></div>
                            <input type="radio" 
                                   id="material_<?= $value ?>" 
                                   name="tipo_material" 
                                   value="<?= $value ?>"
                                   style="display: none;"
                                   <?= $this->old('tipo_material') === $value ? 'checked' : '' ?>>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Paso 2: Detalles -->
            <div id="step2" class="form-step">
                <h4 class="mb-4">
                    <i class="fas fa-info-circle me-2 text-primary"></i>
                    Detalles del Reciclaje
                </h4>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cantidad" class="form-label">
                            <i class="fas fa-weight-hanging me-1"></i>
                            Cantidad (kg) *
                        </label>
                        <input type="number" 
                               class="form-control" 
                               id="cantidad" 
                               name="cantidad" 
                               step="0.1" 
                               min="0.1"
                               placeholder="Ej: 2.5"
                               value="<?= $this->escape($this->old('cantidad')) ?>"
                               required>
                        <div class="form-text">Ingresa la cantidad en kilogramos</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="ubicacion" class="form-label">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            Ubicación *
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="ubicacion" 
                               name="ubicacion" 
                               placeholder="Ej: Centro Comercial Plaza Norte"
                               value="<?= $this->escape($this->old('ubicacion')) ?>"
                               required>
                        <div class="form-text">¿Dónde realizaste el reciclaje?</div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="descripcion" class="form-label">
                        <i class="fas fa-edit me-1"></i>
                        Descripción (opcional)
                    </label>
                    <textarea class="form-control" 
                              id="descripcion" 
                              name="descripcion" 
                              rows="3"
                              placeholder="Describe brevemente qué tipo de materiales específicos reciclaste..."><?= $this->escape($this->old('descripcion')) ?></textarea>
                    <div class="form-text">Máximo 1000 caracteres</div>
                </div>
                
                <!-- Upload de foto -->
                <div class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-camera me-1"></i>
                        Foto del reciclaje (opcional)
                    </label>
                    <div id="photo-upload" class="photo-upload">
                        <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-muted"></i>
                        <div>Haz clic aquí o arrastra una imagen</div>
                        <small class="text-muted">JPG, PNG, GIF - Max 5MB</small>
                    </div>
                    <input type="file" 
                           id="foto" 
                           name="foto" 
                           accept=".jpg,.jpeg,.png,.gif"
                           style="display: none;">
                </div>
            </div>
            
            <!-- Paso 3: Confirmación -->
            <div id="step3" class="form-step">
                <h4 class="mb-4">
                    <i class="fas fa-check-circle me-2 text-primary"></i>
                    Confirmar Reporte
                </h4>
                
                <p class="text-muted mb-4">
                    Revisa los detalles de tu reporte antes de enviarlo:
                </p>
                
                <div class="summary-item">
                    <strong>Tipo de Material:</strong>
                    <span id="summary-material">-</span>
                </div>
                
                <div class="summary-item">
                    <strong>Cantidad:</strong>
                    <span id="summary-cantidad">-</span>
                </div>
                
                <div class="summary-item">
                    <strong>Ubicación:</strong>
                    <span id="summary-ubicacion">-</span>
                </div>
                
                <div class="summary-item">
                    <strong>Descripción:</strong>
                    <span id="summary-descripcion">-</span>
                </div>
                
                <div class="summary-item">
                    <strong>Foto:</strong>
                    <span id="summary-foto">-</span>
                </div>
                
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>¡Excelente trabajo!</strong> 
                    Este reporte ayudará a calcular tu impacto ambiental positivo.
                </div>
            </div>
            
            <!-- Botones de navegación -->
            <div class="d-flex justify-content-between mt-4">
                <button type="button" id="prevBtn" class="btn btn-outline-secondary" onclick="prevStep()" style="display: none;">
                    <i class="fas fa-chevron-left me-1"></i>
                    Anterior
                </button>
                
                <div class="ms-auto">
                    <button type="button" id="nextBtn" class="btn btn-primary" onclick="nextStep()">
                        Siguiente
                        <i class="fas fa-chevron-right ms-1"></i>
                    </button>
                    
                    <button type="submit" id="submitBtn" class="btn btn-success" style="display: none;">
                        <i class="fas fa-check me-1"></i>
                        Crear Reporte
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
