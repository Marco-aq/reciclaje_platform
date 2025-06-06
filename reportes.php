<?php
// Conexión a la base de datos
$hostDB = '127.0.0.1';
$nombreDB = 'reciclaje_platform';
$usuarioDB = 'root';
$password = 'Miperritoeszeuz1';

try {
    $hostPDO = "mysql:host=$hostDB;dbname=$nombreDB;charset=utf8";
    $conn = new PDO($hostPDO, $usuarioDB, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y sanitizar entradas
    $descripcion = htmlspecialchars($_POST['descripcion'] ?? '');
    
    // Incorporar ubicación en la descripción
    $ubicacion = htmlspecialchars($_POST['ubicacion'] ?? '');
    $descripcion_completa = "Ubicación: $ubicacion\n\n$descripcion";
    
    $tipo_residuo = isset($_POST['tipo_residuo']) ? implode(', ', $_POST['tipo_residuo']) : '';

    // Insertar en base de datos usando solo las columnas existentes
    try {
        $sql = "INSERT INTO reportes (tipo_residuo, descripcion) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$tipo_residuo, $descripcion_completa]);
        
        // Redireccionar para evitar reenvío del formulario
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        die("Error al guardar el reporte: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Residuos - EcoCusco</title>
    <style>
        :root {
            --verde-eco: #2E7D32;
            --verde-claro: #C8E6C9;
            --gris-fondo: #F5F5F5;
            --gris-texto: #666;
            --blanco: #FFFFFF;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--gris-fondo);
            margin: 0;
            padding: 0;
        }

        .cabecera {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: var(--blanco);
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .cabecera .titulo {
            font-weight: bold;
            font-size: 24px;
            color: var(--verde-eco);
        }

        .cabecera .menu {
            display: flex;
            gap: 20px;
        }

        .cabecera .menu a {
            color: var(--gris-texto);
            text-decoration: none;
            font-size: 16px;
        }

        .cabecera .menu a:hover {
            color: var(--verde-eco);
        }

        .cabecera .menu a.registrarse {
            background-color: var(--verde-eco);
            color: var(--blanco);
            padding: 5px 10px;
            border-radius: 5px;
        }

        .cabecera .menu a.registrarse:hover {
            background-color: #1B5E20;
        }

        .cabecera .menu a.ingresar::before {
            content: "👤";
            margin-right: 5px;
        }

        .contenedor {
            max-width: 800px;
            margin: 20px auto;
            background: var(--blanco);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: var(--verde-eco);
            text-align: left;
            margin-bottom: 20px;
            font-size: 28px;
        }

        .seccion {
            margin-bottom: 25px;
        }

        .grupo-campos {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: var(--gris-texto);
            font-weight: bold;
        }

        select, input, textarea, button {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--verde-claro);
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            background-color: var(--verde-eco);
            color: var(--blanco);
            cursor: pointer;
        }

        button:hover {
            background-color: #1B5E20;
        }

        .mapa {
            height: 400px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .mapa-iframe {
            width: 100%;
            height: 100%;
            border: 0;
            border-radius: 5px;
        }

        .icono {
            margin-right: 10px;
        }

        .boton {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background-color: var(--verde-eco);
            color: var(--blanco);
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .boton:hover {
            background-color: #1B5E20;
        }

        .boton-ubicacion {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .boton-ubicacion button {
            flex: 1;
            margin: 0 5px;
        }

        .foto-label {
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed var(--gris-texto);
            border-radius: 5px;
            padding: 20px;
            cursor: pointer;
            color: var(--gris-texto);
        }

        .foto-label:hover {
            background-color: var(--gris-fondo);
        }

        .foto-label span {
            margin-left: 10px;
        }

        footer {
            text-align: center;
            margin-top: 30px;
            color: var(--gris-texto);
            font-size: 14px;
        }

        .boton-enviar {
            display: flex;
            justify-content: flex-end;
        }

        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .preview-container img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .success-message {
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            color: #3c763d;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- HEADER INCLUIDO DESDE components/header.php -->
    <?php include 'components/header.php'; ?>

<div class="contenedor">
    <h1>Reportar Residuos</h1>
    
    <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
        <div class="success-message">
            ¡Reporte enviado exitosamente! Gracias por contribuir a un Cusco más limpio.
        </div>
    <?php endif; ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <!-- Sección Ubicación -->
        <div class="seccion">
            <h2>Ubicación</h2>
            <input type="text" id="direccion" name="ubicacion" 
                   placeholder="Ingrese dirección o seleccione en el mapa" 
                   required 
                   style="width: 100%; padding: 10px; margin-bottom: 15px;">
            
            <div class="mapa">
                <iframe 
                    id="mapa-iframe"
                    class="mapa-iframe"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d124140.274301623!2d-71.9924992!3d-13.53195!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x916dd5cdcdfd5f3b%3A0x9e5e3a84d6f4b0a4!2sCusco%2C%20Peru!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus" 
                    allowfullscreen 
                    loading="lazy">
                </iframe>
            </div>
            
            <div class="boton-ubicacion">
                <button type="button" id="btn-confirmar" class="boton">
                    <span class="icono">📍</span> Confirmar ubicación seleccionada
                </button>
            </div>
        </div>

        <!-- Sección Tipo de Residuo -->
        <div class="seccion">
            <h2>Tipo de Residuo</h2>
            <div class="grupo-campos">
                <div>
                    <label>
                        <input type="checkbox" name="tipo_residuo[]" value="Plásticos" class="tipo-residuo">
                        <span class="icono">🍼</span> Plásticos
                    </label>
                </div>
                <div>
                    <label>
                        <input type="checkbox" name="tipo_residuo[]" value="Papel/Cartón" class="tipo-residuo">
                        <span class="icono">📄</span> Papel/Cartón
                    </label>
                </div>
                <div>
                    <label>
                        <input type="checkbox" name="tipo_residuo[]" value="Vidrio" class="tipo-residuo">
                        <span class="icono">🍾</span> Vidrio
                    </label>
                </div>
                <div>
                    <label>
                        <input type="checkbox" name="tipo_residuo[]" value="Electrónicos" class="tipo-residuo">
                        <span class="icono">💻</span> Electrónicos
                    </label>
                </div>
                <div>
                    <label>
                        <input type="checkbox" name="tipo_residuo[]" value="Orgánicos" class="tipo-residuo">
                        <span class="icono">🍂</span> Orgánicos
                    </label>
                </div>
                <div>
                    <label>
                        <input type="checkbox" name="tipo_residuo[]" value="Otros" class="tipo-residuo">
                        <span class="icono">❓</span> Otros
                    </label>
                </div>
            </div>
        </div>

        <!-- Sección Descripción -->
        <div class="seccion">
            <h2>Descripción</h2>
            <textarea name="descripcion" rows="4" placeholder="Describe los detalles del residuo y la situación..." required></textarea>
        </div>

        <div class="boton-enviar">
            <button type="submit" class="boton">
                <span class="icono">📤</span> Enviar Reporte
            </button>
        </div>
    </form>
</div>

<footer>
    © 2025 EcoCusco. Todos los derechos reservados.
</footer>

<script>
    // Limitar selección de residuos (máximo 3)
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.tipo-residuo');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const selected = document.querySelectorAll('.tipo-residuo:checked');
                if (selected.length > 3) {
                    this.checked = false;
                    alert('Solo puedes seleccionar hasta 3 tipos de residuos.');
                }
            });
        });
        
        // Botón de confirmación de ubicación
        document.getElementById('btn-confirmar').addEventListener('click', function() {
            const direccion = document.getElementById('direccion').value;
            if (!direccion) {
                alert('Por favor ingrese una dirección');
                return;
            }
            
            // Actualizar el mapa con la nueva ubicación
            const encodedDireccion = encodeURIComponent(direccion);
            const mapa = document.getElementById('mapa-iframe');
            mapa.src = `https://www.google.com/maps/embed/v1/place?key=AIzaSyBFw0Qbyq9zTFTd-tUY6dZWTgaQzuU17R8&q=${encodedDireccion}`;
            
            alert('Ubicación confirmada: ' + direccion);
        });
    });
</script>
</body>
</html>