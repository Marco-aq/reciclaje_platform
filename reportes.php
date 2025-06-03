<?php
// Conexión a la base de datos
$hostDB = '127.0.0.1';
$nombreDB = 'reciclaje_platform';
$usuarioDB = 'root';
$password = 'Miperritoeszeuz1';

$hostPDO = "mysql:host=$hostDB;dbname=$nombreDB;charset=utf8";
$conn = new PDO($hostPDO, $usuarioDB, $password);

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar y sanitizar entradas
    $ubicacion = htmlspecialchars($_POST['ubicacion']);
    $tipo_residuo = isset($_POST['tipo_residuo']) ? implode(', ', $_POST['tipo_residuo']) : ''; // Combina los valores seleccionados
    $descripcion = htmlspecialchars($_POST['descripcion']);
    
    // Insertar en base de datos
    $stmt = $conn->prepare("INSERT INTO reportes (ubicacion, tipo_residuo, descripcion) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $ubicacion, $tipo_residuo, $descripcion);
    $stmt->execute();
    $stmt->close();
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
            height: 200px;
            background: url('https://vagabondbuddha.com/wp-content/uploads/2018/07/null-40.jpeg') no-repeat center center;
            background-size: cover;
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
    </style>
</head>
<body>
    <div class="cabecera">
        <div class="titulo">EcoCusco</div>
        <div class="menu">
            <a href="#">Inicio</a>
            <a href="#">Reportar</a>
            <a href="#">Empresas</a>
            <a href="./index.php">Estadísticas</a>
        </div>
        <div class="menu">
            <a href="./login.php" class="ingresar">Ingresar</a>
            <a href="./register.php" class="registrarse">Registrarse</a>
        </div>
    </div>

    <div class="contenedor">
        <h1>Reportar Residuos</h1>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <!-- Sección Ubicación -->
            <div class="seccion">
                <h2>Ubicación</h2>
                <div class="mapa"></div>
                <div class="boton-ubicacion">
                    <button type="button" class="boton">
                        <span class="icono">📍</span> Usar ubicación actual
                    </button>
                    <button type="button" class="boton">
                        <span class="icono">📌</span> Seleccionar en mapa
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

            <!-- Sección Fotos -->
            <div class="seccion">
                <h2>Fotografías</h2>
                <label class="foto-label">
                    <span class="icono">📷</span>
                    <span>Agregar foto</span>
                    <input type="file" name="fotos[]" accept="image/*" multiple style="display: none;">
                </label>
            </div>

            <!-- Sección Descripción -->
            <div class="seccion">
                <h2>Descripción</h2>
                <textarea name="descripcion" rows="4" placeholder="Describe los detalles del residuo y la situación..."></textarea>
            </div>

            <div class="boton-enviar">
                <button type="button" class="boton">
                    <span class="icono">📤</span> Enviar Reporte
                </button>
            </div>
        </form>
    </div>

    <footer>
        © 2025 EcoCusco. Todos los derechos reservados.
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.tipo-residuo');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function () {
                    const selected = document.querySelectorAll('.tipo-residuo:checked');
                    if (selected.length > 3) {
                        this.checked = false; // Desmarca la casilla si ya hay 3 seleccionadas
                        alert('Solo puedes seleccionar hasta 3 tipos de residuos.');
                    }
                });
            });
        });
    </script>
</body>
</html>
