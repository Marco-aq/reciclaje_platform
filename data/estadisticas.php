<?php
// estadisticas.php
include('conexion.php');

// Fechas mes actual y anterior
$mesActual = date('Y-m');
$mesAnteriorObj = (new DateTime($mesActual . '-01'))->modify('-1 month');
$mesAnterior = $mesAnteriorObj->format('Y-m');

// Tipos a mostrar
$tipos = [
    'Total'    => '',
    'Plástico' => 'Plástico',
    'Papel'    => 'Papel',
    'Vidrio'   => 'Vidrio',
];

// Cálculo estadísticas
$estadisticas = [];
foreach ($tipos as $clave => $valor) {
    $condAct = $valor === ''
        ? "DATE_FORMAT(fecha_reporte, '%Y-%m') = '$mesActual'"
        : "tipo_residuo = '$valor' AND DATE_FORMAT(fecha_reporte, '%Y-%m') = '$mesActual'";
    $resAct = $conexion->query("SELECT SUM(cantidad) AS total FROM reportes WHERE $condAct");
    $totAct = (float)($resAct->fetch_assoc()['total'] ?? 0);

    $condAnt = $valor === ''
        ? "DATE_FORMAT(fecha_reporte, '%Y-%m') = '$mesAnterior'"
        : "tipo_residuo = '$valor' AND DATE_FORMAT(fecha_reporte, '%Y-%m') = '$mesAnterior'";
    $resAnt = $conexion->query("SELECT SUM(cantidad) AS total FROM reportes WHERE $condAnt");
    $totAnt = (float)($resAnt->fetch_assoc()['total'] ?? 0);

    $pct = null;
    if ($totAnt > 0) {
        $pct = round((($totAct - $totAnt) / $totAnt) * 100, 1);
    }

    $estadisticas[$clave] = ['actual' => $totAct, 'pct' => $pct];
}

// Progreso mensual
$anioActual = date('Y');
$resMensual = $conexion->query("SELECT DATE_FORMAT(fecha_reporte, '%Y-%m') AS mes, SUM(cantidad) AS total FROM reportes WHERE YEAR(fecha_reporte) = $anioActual GROUP BY mes ORDER BY mes");
$meses = []; $valMensual = [];
while ($r = $resMensual->fetch_assoc()) {
    $meses[] = $r['mes'];
    $valMensual[] = (float)$r['total'];
}

// Distribución por material
$resDist = $conexion->query("SELECT tipo_residuo, SUM(cantidad) AS total FROM reportes GROUP BY tipo_residuo");
$mat = []; $valDist = [];
while ($r = $resDist->fetch_assoc()) {
    $mat[] = $r['tipo_residuo'];
    $valDist[] = (float)$r['total'];
}

// Reportes iniciales (5)
$limitInicial = 5;
$resAct = $conexion->query("SELECT fecha_reporte, tipo_residuo, cantidad, ubicacion_nombre, estado FROM reportes ORDER BY fecha_reporte DESC LIMIT $limitInicial");
$reportes = [];
while ($f = $resAct->fetch_assoc()) {
    $reportes[] = $f;
}

// Total de reportes
$totalReportes = $conexion->query("SELECT COUNT(*) AS total FROM reportes")->fetch_assoc()['total'];

// Devuelve las estadísticas y los reportes
return compact('estadisticas', 'meses', 'valMensual', 'mat', 'valDist', 'reportes', 'totalReportes');
?>
<?php
// estadisticas.php

// Obtén el límite desde la solicitud
$limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 5;
$offset = $limite - 5;  // Asumimos que la primera página comienza con 5 reportes

// Query para obtener los reportes con el límite
$resAct = $conexion->query("SELECT fecha_reporte, tipo_residuo, cantidad, ubicacion_nombre, estado FROM reportes ORDER BY fecha_reporte DESC LIMIT $offset, 5");
$reportes = [];
while ($f = $resAct->fetch_assoc()) {
    $reportes[] = $f;
}

// Total de reportes
$totalReportes = $conexion->query("SELECT COUNT(*) AS total FROM reportes")->fetch_assoc()['total'];

// Respuesta en JSON
echo json_encode([
    'reportes' => $reportes,
    'totalReportes' => $totalReportes
]);
?>