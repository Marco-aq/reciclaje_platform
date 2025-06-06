<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo Stats - Maneja estadísticas y análisis de datos
 */
class Stats extends Model
{
    protected $table = 'reportes'; // Usamos la tabla de reportes como base

    /**
     * Obtiene estadísticas del dashboard
     */
    public function getDashboardStats()
    {
        $stats = [];

        // Estadísticas básicas
        $stats['total_reportes'] = $this->getTotalReports();
        $stats['total_usuarios'] = $this->getTotalUsers();
        $stats['total_materiales'] = $this->getTotalMaterials();
        $stats['reportes_hoy'] = $this->getReportsToday();
        $stats['reportes_semana'] = $this->getReportsThisWeek();
        $stats['reportes_mes'] = $this->getReportsThisMonth();

        // Crecimiento
        $stats['crecimiento_reportes'] = $this->getReportsGrowth();
        $stats['crecimiento_usuarios'] = $this->getUsersGrowth();

        return $stats;
    }

    /**
     * Obtiene datos para gráficos
     */
    public function getChartData()
    {
        return [
            'reportes_por_mes' => $this->getReportsByMonth(),
            'materiales_por_tipo' => $this->getMaterialsByType(),
            'usuarios_activos' => $this->getActiveUsersChart(),
            'tendencias_reciclaje' => $this->getRecyclingTrends(),
            'impacto_ambiental' => $this->getEnvironmentalImpactChart()
        ];
    }

    /**
     * Total de reportes
     */
    private function getTotalReports()
    {
        $result = $this->queryOne("SELECT COUNT(*) as count FROM reportes");
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Total de usuarios
     */
    private function getTotalUsers()
    {
        $result = $this->queryOne("SELECT COUNT(*) as count FROM usuarios");
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Total de materiales reciclados (suma de cantidades)
     */
    private function getTotalMaterials()
    {
        $result = $this->queryOne("SELECT SUM(cantidad) as total FROM reportes");
        return $result ? (float)$result['total'] : 0;
    }

    /**
     * Reportes de hoy
     */
    private function getReportsToday()
    {
        $result = $this->queryOne("
            SELECT COUNT(*) as count 
            FROM reportes 
            WHERE DATE(fecha_reporte) = CURDATE()
        ");
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Reportes de esta semana
     */
    private function getReportsThisWeek()
    {
        $result = $this->queryOne("
            SELECT COUNT(*) as count 
            FROM reportes 
            WHERE YEARWEEK(fecha_reporte, 1) = YEARWEEK(CURDATE(), 1)
        ");
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Reportes de este mes
     */
    private function getReportsThisMonth()
    {
        $result = $this->queryOne("
            SELECT COUNT(*) as count 
            FROM reportes 
            WHERE MONTH(fecha_reporte) = MONTH(CURDATE()) 
            AND YEAR(fecha_reporte) = YEAR(CURDATE())
        ");
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Crecimiento de reportes (comparado con el mes anterior)
     */
    private function getReportsGrowth()
    {
        $currentMonth = $this->queryOne("
            SELECT COUNT(*) as count 
            FROM reportes 
            WHERE MONTH(fecha_reporte) = MONTH(CURDATE()) 
            AND YEAR(fecha_reporte) = YEAR(CURDATE())
        ");

        $previousMonth = $this->queryOne("
            SELECT COUNT(*) as count 
            FROM reportes 
            WHERE MONTH(fecha_reporte) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
            AND YEAR(fecha_reporte) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
        ");

        $current = $currentMonth ? (int)$currentMonth['count'] : 0;
        $previous = $previousMonth ? (int)$previousMonth['count'] : 0;

        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Crecimiento de usuarios (comparado con el mes anterior)
     */
    private function getUsersGrowth()
    {
        $currentMonth = $this->queryOne("
            SELECT COUNT(*) as count 
            FROM usuarios 
            WHERE MONTH(created_at) = MONTH(CURDATE()) 
            AND YEAR(created_at) = YEAR(CURDATE())
        ");

        $previousMonth = $this->queryOne("
            SELECT COUNT(*) as count 
            FROM usuarios 
            WHERE MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
            AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
        ");

        $current = $currentMonth ? (int)$currentMonth['count'] : 0;
        $previous = $previousMonth ? (int)$previousMonth['count'] : 0;

        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Reportes por mes (últimos 12 meses)
     */
    public function getReportsByMonth($months = 12)
    {
        return $this->query("
            SELECT 
                DATE_FORMAT(fecha_reporte, '%Y-%m') as mes,
                DATE_FORMAT(fecha_reporte, '%M %Y') as mes_nombre,
                COUNT(*) as cantidad_reportes,
                SUM(cantidad) as cantidad_materiales
            FROM reportes
            WHERE fecha_reporte >= DATE_SUB(CURDATE(), INTERVAL {$months} MONTH)
            GROUP BY DATE_FORMAT(fecha_reporte, '%Y-%m')
            ORDER BY mes ASC
        ");
    }

    /**
     * Materiales por tipo
     */
    public function getMaterialsByType()
    {
        return $this->query("
            SELECT 
                tipo_material,
                COUNT(*) as cantidad_reportes,
                SUM(cantidad) as cantidad_total,
                ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM reportes)), 2) as porcentaje
            FROM reportes
            GROUP BY tipo_material
            ORDER BY cantidad_reportes DESC
        ");
    }

    /**
     * Usuarios activos por mes
     */
    public function getActiveUsersChart($months = 6)
    {
        return $this->query("
            SELECT 
                DATE_FORMAT(r.fecha_reporte, '%Y-%m') as mes,
                DATE_FORMAT(r.fecha_reporte, '%M %Y') as mes_nombre,
                COUNT(DISTINCT r.usuario_id) as usuarios_activos,
                COUNT(r.id) as total_reportes
            FROM reportes r
            WHERE r.fecha_reporte >= DATE_SUB(CURDATE(), INTERVAL {$months} MONTH)
            GROUP BY DATE_FORMAT(r.fecha_reporte, '%Y-%m')
            ORDER BY mes ASC
        ");
    }

    /**
     * Tendencias de reciclaje
     */
    public function getRecyclingTrends($months = 6)
    {
        return $this->query("
            SELECT 
                DATE_FORMAT(fecha_reporte, '%Y-%m') as mes,
                tipo_material,
                SUM(cantidad) as cantidad_total,
                COUNT(*) as numero_reportes
            FROM reportes
            WHERE fecha_reporte >= DATE_SUB(CURDATE(), INTERVAL {$months} MONTH)
            GROUP BY DATE_FORMAT(fecha_reporte, '%Y-%m'), tipo_material
            ORDER BY mes ASC, cantidad_total DESC
        ");
    }

    /**
     * Datos de impacto ambiental para gráficos
     */
    public function getEnvironmentalImpactChart()
    {
        // Factores de conversión
        $impactFactors = [
            'plastico' => ['co2' => 2.0, 'energia' => 2000, 'agua' => 500],
            'papel' => ['co2' => 3.3, 'energia' => 1500, 'agua' => 300],
            'vidrio' => ['co2' => 0.5, 'energia' => 800, 'agua' => 100],
            'metal' => ['co2' => 6.0, 'energia' => 3000, 'agua' => 800],
            'electronico' => ['co2' => 4.0, 'energia' => 2500, 'agua' => 600],
            'organico' => ['co2' => 0.3, 'energia' => 200, 'agua' => 50],
            'textil' => ['co2' => 2.5, 'energia' => 1800, 'agua' => 400],
            'otros' => ['co2' => 1.0, 'energia' => 1000, 'agua' => 200]
        ];

        $materialsData = $this->query("
            SELECT 
                tipo_material,
                SUM(cantidad) as cantidad_total
            FROM reportes
            GROUP BY tipo_material
        ");

        $impactData = [];
        $totalImpact = ['co2' => 0, 'energia' => 0, 'agua' => 0];

        foreach ($materialsData as $material) {
            $tipo = $material['tipo_material'];
            $cantidad = (float)$material['cantidad_total'];
            $factors = $impactFactors[$tipo] ?? $impactFactors['otros'];

            $impact = [
                'tipo_material' => $tipo,
                'cantidad' => $cantidad,
                'co2_evitado' => $cantidad * $factors['co2'],
                'energia_ahorrada' => $cantidad * $factors['energia'],
                'agua_ahorrada' => $cantidad * $factors['agua']
            ];

            $impactData[] = $impact;
            $totalImpact['co2'] += $impact['co2_evitado'];
            $totalImpact['energia'] += $impact['energia_ahorrada'];
            $totalImpact['agua'] += $impact['agua_ahorrada'];
        }

        return [
            'impacto_por_material' => $impactData,
            'impacto_total' => $totalImpact,
            'equivalencias' => [
                'arboles_plantados' => round($totalImpact['co2'] / 22),
                'autos_retirados' => round($totalImpact['co2'] / 4600),
                'hogares_energia' => round($totalImpact['energia'] / 10950), // kWh promedio anual por hogar
                'piscinas_agua' => round($totalImpact['agua'] / 2500000) // litros de una piscina olímpica
            ]
        ];
    }

    /**
     * Top ubicaciones de reciclaje
     */
    public function getTopLocations($limit = 10)
    {
        return $this->query("
            SELECT 
                ubicacion,
                COUNT(*) as cantidad_reportes,
                SUM(cantidad) as cantidad_materiales,
                COUNT(DISTINCT usuario_id) as usuarios_unicos
            FROM reportes
            WHERE ubicacion IS NOT NULL AND ubicacion != ''
            GROUP BY ubicacion
            ORDER BY cantidad_reportes DESC
            LIMIT {$limit}
        ");
    }

    /**
     * Ranking de usuarios
     */
    public function getUserRanking($limit = 10)
    {
        return $this->query("
            SELECT 
                u.id,
                u.nombre,
                COUNT(r.id) as total_reportes,
                SUM(r.cantidad) as total_materiales,
                SUM(r.cantidad * 10) as puntos_estimados,
                MAX(r.fecha_reporte) as ultimo_reporte
            FROM usuarios u
            INNER JOIN reportes r ON u.id = r.usuario_id
            GROUP BY u.id, u.nombre
            ORDER BY total_reportes DESC, total_materiales DESC
            LIMIT {$limit}
        ");
    }

    /**
     * Estadísticas por día de la semana
     */
    public function getWeekdayStats()
    {
        return $this->query("
            SELECT 
                DAYNAME(fecha_reporte) as dia_semana,
                DAYOFWEEK(fecha_reporte) as dia_numero,
                COUNT(*) as cantidad_reportes,
                SUM(cantidad) as cantidad_materiales,
                AVG(cantidad) as promedio_cantidad
            FROM reportes
            GROUP BY DAYOFWEEK(fecha_reporte), DAYNAME(fecha_reporte)
            ORDER BY dia_numero
        ");
    }

    /**
     * Estadísticas por hora del día
     */
    public function getHourlyStats()
    {
        return $this->query("
            SELECT 
                HOUR(fecha_reporte) as hora,
                COUNT(*) as cantidad_reportes,
                SUM(cantidad) as cantidad_materiales
            FROM reportes
            GROUP BY HOUR(fecha_reporte)
            ORDER BY hora
        ");
    }

    /**
     * Comparativa de períodos
     */
    public function getPeriodComparison($currentStart, $currentEnd, $previousStart, $previousEnd)
    {
        $currentData = $this->queryOne("
            SELECT 
                COUNT(*) as reportes,
                SUM(cantidad) as materiales,
                COUNT(DISTINCT usuario_id) as usuarios
            FROM reportes
            WHERE fecha_reporte BETWEEN ? AND ?
        ", [$currentStart, $currentEnd]);

        $previousData = $this->queryOne("
            SELECT 
                COUNT(*) as reportes,
                SUM(cantidad) as materiales,
                COUNT(DISTINCT usuario_id) as usuarios
            FROM reportes
            WHERE fecha_reporte BETWEEN ? AND ?
        ", [$previousStart, $previousEnd]);

        $comparison = [];
        
        foreach (['reportes', 'materiales', 'usuarios'] as $metric) {
            $current = (int)($currentData[$metric] ?? 0);
            $previous = (int)($previousData[$metric] ?? 0);
            
            $change = 0;
            if ($previous > 0) {
                $change = round((($current - $previous) / $previous) * 100, 2);
            } elseif ($current > 0) {
                $change = 100;
            }
            
            $comparison[$metric] = [
                'actual' => $current,
                'anterior' => $previous,
                'cambio_porcentaje' => $change,
                'cambio_absoluto' => $current - $previous,
                'tendencia' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'stable')
            ];
        }

        return $comparison;
    }

    /**
     * Proyecciones basadas en tendencias
     */
    public function getProjections($months = 3)
    {
        // Obtener datos de los últimos 6 meses para calcular tendencia
        $historicalData = $this->query("
            SELECT 
                DATE_FORMAT(fecha_reporte, '%Y-%m') as mes,
                COUNT(*) as reportes,
                SUM(cantidad) as materiales
            FROM reportes
            WHERE fecha_reporte >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(fecha_reporte, '%Y-%m')
            ORDER BY mes ASC
        ");

        if (count($historicalData) < 2) {
            return null; // No hay suficientes datos para proyecciones
        }

        // Calcular tendencia simple (promedio de crecimiento)
        $reportesTrend = 0;
        $materialesTrend = 0;
        $dataPoints = count($historicalData);

        for ($i = 1; $i < $dataPoints; $i++) {
            $currentReportes = (int)$historicalData[$i]['reportes'];
            $previousReportes = (int)$historicalData[$i-1]['reportes'];
            
            $currentMateriales = (float)$historicalData[$i]['materiales'];
            $previousMateriales = (float)$historicalData[$i-1]['materiales'];

            if ($previousReportes > 0) {
                $reportesTrend += ($currentReportes - $previousReportes) / $previousReportes;
            }
            
            if ($previousMateriales > 0) {
                $materialesTrend += ($currentMateriales - $previousMateriales) / $previousMateriales;
            }
        }

        $reportesTrend = $reportesTrend / ($dataPoints - 1);
        $materialesTrend = $materialesTrend / ($dataPoints - 1);

        // Generar proyecciones
        $lastData = end($historicalData);
        $projections = [];

        for ($i = 1; $i <= $months; $i++) {
            $projectedReportes = round((int)$lastData['reportes'] * pow(1 + $reportesTrend, $i));
            $projectedMateriales = round((float)$lastData['materiales'] * pow(1 + $materialesTrend, $i), 2);
            
            $projections[] = [
                'mes' => date('Y-m', strtotime("+{$i} month")),
                'reportes_proyectados' => max(0, $projectedReportes),
                'materiales_proyectados' => max(0, $projectedMateriales),
                'confianza' => max(10, 90 - ($i * 15)) // Confianza decrece con el tiempo
            ];
        }

        return [
            'tendencia_reportes' => round($reportesTrend * 100, 2),
            'tendencia_materiales' => round($materialesTrend * 100, 2),
            'proyecciones' => $projections
        ];
    }
}
