<?php

namespace App\Models;

use App\Core\Model;
use Exception;

/**
 * Modelo Report - Maneja operaciones de reportes de reciclaje
 */
class Report extends Model
{
    protected $table = 'reportes';
    protected $primaryKey = 'id';
    protected $fillable = ['usuario_id', 'tipo_material', 'cantidad', 'ubicacion', 'descripcion', 'foto', 'fecha_reporte'];
    protected $timestamps = true;

    // Tipos de materiales permitidos
    const TIPOS_MATERIALES = [
        'plastico' => 'Plástico',
        'papel' => 'Papel',
        'vidrio' => 'Vidrio',
        'metal' => 'Metal',
        'electronico' => 'Electrónico',
        'organico' => 'Orgánico',
        'textil' => 'Textil',
        'otros' => 'Otros'
    ];

    /**
     * Crea un nuevo reporte
     */
    public function createReport($data)
    {
        // Validar datos
        $errors = $this->validateReportData($data);
        if (!empty($errors)) {
            throw new Exception("Datos de reporte inválidos: " . implode(', ', array_merge(...$errors)));
        }

        // Asegurar que fecha_reporte esté presente
        if (empty($data['fecha_reporte'])) {
            $data['fecha_reporte'] = date('Y-m-d H:i:s');
        }

        return $this->create($data);
    }

    /**
     * Obtiene reportes del usuario
     */
    public function getReportsByUser($userId, $limit = null)
    {
        $sql = "
            SELECT r.*, u.nombre as usuario_nombre
            FROM {$this->table} r
            LEFT JOIN usuarios u ON r.usuario_id = u.id
            WHERE r.usuario_id = ?
            ORDER BY r.fecha_reporte DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->query($sql, [$userId]);
    }

    /**
     * Obtiene reportes con información del usuario
     */
    public function getReportsWithUser($limit = null, $offset = 0)
    {
        $sql = "
            SELECT r.*, u.nombre as usuario_nombre, u.email as usuario_email
            FROM {$this->table} r
            LEFT JOIN usuarios u ON r.usuario_id = u.id
            ORDER BY r.fecha_reporte DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
            if ($offset) {
                $sql .= " OFFSET {$offset}";
            }
        }

        return $this->query($sql);
    }

    /**
     * Obtiene reportes del mes actual para un usuario
     */
    public function getReportesThisMonth($userId)
    {
        return $this->count([
            'usuario_id' => $userId,
            'fecha_reporte' => 'MONTH(fecha_reporte) = MONTH(NOW()) AND YEAR(fecha_reporte) = YEAR(NOW())'
        ]);
    }

    /**
     * Obtiene tipos de materiales reportados por usuario
     */
    public function getMaterialTypesByUser($userId)
    {
        return $this->query("
            SELECT 
                tipo_material,
                COUNT(*) as cantidad_reportes,
                SUM(cantidad) as cantidad_total
            FROM {$this->table}
            WHERE usuario_id = ?
            GROUP BY tipo_material
            ORDER BY cantidad_reportes DESC
        ", [$userId]);
    }

    /**
     * Obtiene estadísticas generales
     */
    public function getGeneralStats()
    {
        $stats = [];

        // Total de reportes
        $stats['total_reportes'] = $this->count();

        // Reportes por tipo de material
        $stats['reportes_por_tipo'] = $this->query("
            SELECT 
                tipo_material,
                COUNT(*) as cantidad_reportes,
                SUM(cantidad) as cantidad_total
            FROM {$this->table}
            GROUP BY tipo_material
            ORDER BY cantidad_reportes DESC
        ");

        // Reportes por mes (últimos 12 meses)
        $stats['reportes_por_mes'] = $this->query("
            SELECT 
                DATE_FORMAT(fecha_reporte, '%Y-%m') as mes,
                COUNT(*) as cantidad_reportes,
                SUM(cantidad) as cantidad_total
            FROM {$this->table}
            WHERE fecha_reporte >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(fecha_reporte, '%Y-%m')
            ORDER BY mes DESC
        ");

        // Top usuarios
        $stats['top_usuarios'] = $this->query("
            SELECT 
                u.nombre,
                COUNT(r.id) as cantidad_reportes,
                SUM(r.cantidad) as cantidad_total
            FROM usuarios u
            INNER JOIN {$this->table} r ON u.id = r.usuario_id
            GROUP BY u.id, u.nombre
            ORDER BY cantidad_reportes DESC
            LIMIT 10
        ");

        // Reportes recientes (última semana)
        $stats['reportes_semana'] = $this->count([
            'fecha_reporte' => 'fecha_reporte >= DATE_SUB(NOW(), INTERVAL 7 DAY)'
        ]);

        return $stats;
    }

    /**
     * Obtiene reportes por ubicación
     */
    public function getReportsByLocation()
    {
        return $this->query("
            SELECT 
                ubicacion,
                COUNT(*) as cantidad_reportes,
                SUM(cantidad) as cantidad_total
            FROM {$this->table}
            WHERE ubicacion IS NOT NULL AND ubicacion != ''
            GROUP BY ubicacion
            ORDER BY cantidad_reportes DESC
        ");
    }

    /**
     * Obtiene tendencias de reciclaje
     */
    public function getRecyclingTrends($months = 6)
    {
        return $this->query("
            SELECT 
                DATE_FORMAT(fecha_reporte, '%Y-%m') as mes,
                tipo_material,
                COUNT(*) as cantidad_reportes,
                SUM(cantidad) as cantidad_total
            FROM {$this->table}
            WHERE fecha_reporte >= DATE_SUB(NOW(), INTERVAL {$months} MONTH)
            GROUP BY DATE_FORMAT(fecha_reporte, '%Y-%m'), tipo_material
            ORDER BY mes DESC, cantidad_reportes DESC
        ");
    }

    /**
     * Busca reportes con filtros
     */
    public function searchReports($filters = [])
    {
        $conditions = [];
        $params = [];

        // Filtro por tipo de material
        if (!empty($filters['tipo_material'])) {
            $conditions[] = "tipo_material = ?";
            $params[] = $filters['tipo_material'];
        }

        // Filtro por fecha desde
        if (!empty($filters['fecha_desde'])) {
            $conditions[] = "fecha_reporte >= ?";
            $params[] = $filters['fecha_desde'];
        }

        // Filtro por fecha hasta
        if (!empty($filters['fecha_hasta'])) {
            $conditions[] = "fecha_reporte <= ?";
            $params[] = $filters['fecha_hasta'] . ' 23:59:59';
        }

        // Filtro por ubicación
        if (!empty($filters['ubicacion'])) {
            $conditions[] = "ubicacion LIKE ?";
            $params[] = "%{$filters['ubicacion']}%";
        }

        // Filtro por usuario
        if (!empty($filters['usuario_id'])) {
            $conditions[] = "usuario_id = ?";
            $params[] = $filters['usuario_id'];
        }

        // Filtro por cantidad mínima
        if (!empty($filters['cantidad_min'])) {
            $conditions[] = "cantidad >= ?";
            $params[] = $filters['cantidad_min'];
        }

        $sql = "
            SELECT r.*, u.nombre as usuario_nombre
            FROM {$this->table} r
            LEFT JOIN usuarios u ON r.usuario_id = u.id
        ";

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY r.fecha_reporte DESC";

        return $this->query($sql, $params);
    }

    /**
     * Obtiene reportes con paginación y filtros
     */
    public function getPaginatedReports($page = 1, $perPage = 10, $filters = [])
    {
        $offset = ($page - 1) * $perPage;
        $conditions = [];
        $params = [];

        // Aplicar filtros
        if (!empty($filters['tipo_material'])) {
            $conditions[] = "r.tipo_material = ?";
            $params[] = $filters['tipo_material'];
        }

        if (!empty($filters['fecha_desde'])) {
            $conditions[] = "r.fecha_reporte >= ?";
            $params[] = $filters['fecha_desde'];
        }

        if (!empty($filters['fecha_hasta'])) {
            $conditions[] = "r.fecha_reporte <= ?";
            $params[] = $filters['fecha_hasta'] . ' 23:59:59';
        }

        if (!empty($filters['ubicacion'])) {
            $conditions[] = "r.ubicacion LIKE ?";
            $params[] = "%{$filters['ubicacion']}%";
        }

        if (!empty($filters['usuario_id'])) {
            $conditions[] = "r.usuario_id = ?";
            $params[] = $filters['usuario_id'];
        }

        $whereClause = !empty($conditions) ? " WHERE " . implode(' AND ', $conditions) : "";

        // Contar total de registros
        $countSql = "
            SELECT COUNT(*) as count 
            FROM {$this->table} r
            LEFT JOIN usuarios u ON r.usuario_id = u.id
            {$whereClause}
        ";
        
        $totalResult = $this->queryOne($countSql, $params);
        $total = $totalResult ? (int)$totalResult['count'] : 0;

        // Obtener datos de la página
        $dataSql = "
            SELECT r.*, u.nombre as usuario_nombre, u.email as usuario_email
            FROM {$this->table} r
            LEFT JOIN usuarios u ON r.usuario_id = u.id
            {$whereClause}
            ORDER BY r.fecha_reporte DESC
            LIMIT {$perPage} OFFSET {$offset}
        ";
        
        $data = $this->query($dataSql, $params);

        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => ceil($total / $perPage),
            'has_next' => $page < ceil($total / $perPage),
            'has_prev' => $page > 1,
            'filters' => $filters
        ];
    }

    /**
     * Valida datos de reporte
     */
    public function validateReportData($data)
    {
        $errors = [];

        // Validar usuario_id
        if (empty($data['usuario_id'])) {
            $errors['usuario_id'][] = 'El ID de usuario es requerido';
        } elseif (!is_numeric($data['usuario_id'])) {
            $errors['usuario_id'][] = 'El ID de usuario debe ser numérico';
        }

        // Validar tipo_material
        if (empty($data['tipo_material'])) {
            $errors['tipo_material'][] = 'El tipo de material es requerido';
        } elseif (!array_key_exists($data['tipo_material'], self::TIPOS_MATERIALES)) {
            $errors['tipo_material'][] = 'Tipo de material inválido';
        }

        // Validar cantidad
        if (empty($data['cantidad'])) {
            $errors['cantidad'][] = 'La cantidad es requerida';
        } elseif (!is_numeric($data['cantidad']) || $data['cantidad'] <= 0) {
            $errors['cantidad'][] = 'La cantidad debe ser un número mayor a 0';
        } elseif ($data['cantidad'] > 999999) {
            $errors['cantidad'][] = 'La cantidad no puede ser mayor a 999,999';
        }

        // Validar ubicación
        if (empty($data['ubicacion'])) {
            $errors['ubicacion'][] = 'La ubicación es requerida';
        } elseif (strlen($data['ubicacion']) > 255) {
            $errors['ubicacion'][] = 'La ubicación no puede tener más de 255 caracteres';
        }

        // Validar descripción (opcional)
        if (!empty($data['descripcion']) && strlen($data['descripcion']) > 1000) {
            $errors['descripcion'][] = 'La descripción no puede tener más de 1000 caracteres';
        }

        // Validar fecha_reporte (opcional, se asigna automáticamente si no se proporciona)
        if (!empty($data['fecha_reporte'])) {
            $timestamp = strtotime($data['fecha_reporte']);
            if (!$timestamp) {
                $errors['fecha_reporte'][] = 'Fecha de reporte inválida';
            } elseif ($timestamp > time()) {
                $errors['fecha_reporte'][] = 'La fecha de reporte no puede ser futura';
            }
        }

        return $errors;
    }

    /**
     * Obtiene los tipos de materiales disponibles
     */
    public static function getTiposMateriales()
    {
        return self::TIPOS_MATERIALES;
    }

    /**
     * Obtiene el nombre legible del tipo de material
     */
    public static function getTipoMaterialNombre($tipo)
    {
        return self::TIPOS_MATERIALES[$tipo] ?? $tipo;
    }

    /**
     * Elimina reporte con validaciones
     */
    public function deleteReport($id, $userId = null)
    {
        $report = $this->find($id);
        if (!$report) {
            throw new Exception("Reporte no encontrado");
        }

        // Si se especifica userId, verificar que el reporte pertenezca al usuario
        if ($userId && $report['usuario_id'] != $userId) {
            throw new Exception("No tienes permisos para eliminar este reporte");
        }

        // Eliminar foto si existe
        if (!empty($report['foto'])) {
            $photoPath = PUBLIC_PATH . '/uploads/' . $report['foto'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }

        return $this->delete($id);
    }

    /**
     * Obtiene resumen de impacto ambiental
     */
    public function getEnvironmentalImpact()
    {
        // Factores de conversión aproximados (kg CO2 evitado por kg de material reciclado)
        $impactFactors = [
            'plastico' => 2.0,
            'papel' => 3.3,
            'vidrio' => 0.5,
            'metal' => 6.0,
            'electronico' => 4.0,
            'organico' => 0.3,
            'textil' => 2.5,
            'otros' => 1.0
        ];

        $materialsData = $this->query("
            SELECT 
                tipo_material,
                SUM(cantidad) as cantidad_total
            FROM {$this->table}
            GROUP BY tipo_material
        ");

        $totalCO2Saved = 0;
        $materialImpacts = [];

        foreach ($materialsData as $material) {
            $factor = $impactFactors[$material['tipo_material']] ?? 1.0;
            $co2Saved = $material['cantidad_total'] * $factor;
            $totalCO2Saved += $co2Saved;
            
            $materialImpacts[] = [
                'tipo_material' => $material['tipo_material'],
                'cantidad_total' => $material['cantidad_total'],
                'co2_evitado' => $co2Saved,
                'nombre_material' => self::getTipoMaterialNombre($material['tipo_material'])
            ];
        }

        return [
            'total_co2_evitado' => $totalCO2Saved,
            'impacto_por_material' => $materialImpacts,
            'equivalente_arboles' => round($totalCO2Saved / 22), // Un árbol absorbe aprox. 22kg CO2/año
            'equivalente_autos' => round($totalCO2Saved / 4600) // Un auto promedio emite 4.6 toneladas CO2/año
        ];
    }
}
