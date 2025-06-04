<?php
// index.php
include('data/estadisticas.php');
$datos = include('data/estadisticas.php');
extract($datos); // Extraemos las variables para usarlas directamente
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas de Reciclaje - EcoCusco</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Estilos -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
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
            <a href="./index.php">Inicio</a>
            <a href="./reportes.php">Reportar</a>
            <a href="./estadisticas.php">Estadísticas</a>
        </div>
        <div class="menu">
            <a href="./login.php" class="ingresar">Ingresar</a>
            <a href="./register.php" class="registrarse">Registrarse</a>
        </div>
    </div>
<br>

<div class="stats-grid">
    <?php foreach($estadisticas as $tipo => $d):
        $cls = ($d['pct'] !== null ? ($d['pct'] >= 0 ? 'positive' : 'negative') : '');
        $txt = ($d['pct'] !== null ? (($d['pct'] >= 0 ? '+' : '') . $d['pct'] . '%') : '—');
    ?>
    <div class="stat-card">
        <h3><?= $tipo === 'Total' ? 'Total Reciclado' : $tipo ?></h3>
        <div class="stat-value"><?= number_format($d['actual'], 2) ?> kg</div>
        <div class="stat-trend <?= $cls ?>"><?= $txt ?> vs <?= $mesAnteriorObj->format('M Y') ?></div>
    </div>
    <?php endforeach; ?>
</div>

<div class="charts-container">
    <div class="chart-card">
        <h2>Progreso Mensual (<?= $anioActual ?>)</h2>
        <canvas id="chartMensual"></canvas>
    </div>
    <div class="chart-card">
        <h2>Distribución por Material</h2>
        <canvas id="chartDistribucion"></canvas>
    </div>
</div>

<div class="activity-table" id="actividad">
    <div class="header">
        <h2>Actividad Reciente</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Material</th>
                <th>Cantidad</th>
                <th>Ubicación</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody id="tablaActividades">
            <?php foreach($reportes as $f): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($f['fecha_reporte'])) ?></td>
                <td><?= $f['tipo_residuo'] ?></td>
                <td><?= number_format($f['cantidad'], 2) ?> kg</td>
                <td><?= $f['ubicacion_nombre'] ?></td>
                <td><span class="status <?= str_replace(' ', '-', $f['estado']) ?>"><?= $f['estado'] ?></span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Botón de ver más debajo de la tabla -->
    <div class="ver-mas-container">
        <button id="verMas" onclick="cargarMas()">Ver más</button>
    </div>
</div>

<!-- Pasar variables PHP a JS -->
<script>
    const meses = <?php echo json_encode($meses); ?>;
    const valMensual = <?php echo json_encode($valMensual); ?>;
    const mat = <?php echo json_encode($mat); ?>;
    const valDist = <?php echo json_encode($valDist); ?>;
</script>

<script src="js/charts.js"></script>

</body>
</html>
