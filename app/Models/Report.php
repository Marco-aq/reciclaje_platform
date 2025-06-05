<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo Report - Gestión de reportes de residuos
 * 
 * Maneja las operaciones relacionadas con los reportes
 * de puntos de acumulación de residuos.
 */
class Report extends Model
{
    protected string $table = 'reportes';
    
    protected array $fillable = [
        'usuario_id',
        'ubicacion',
        'latitud',
        'longitud',
        'tipo_residuo',
        'descripcion',
        'urgencia',
        'estado',
        'imagen_url',
        'direccion_exacta',
        'fecha_reporte'
    ];
    
    protected array $casts = [
        'id' => 'integer',
        'usuario_id' => 'integer',
        'latitud' => 'float',
        'longitud' => 'float',
        'urgencia' => 'integer'
    ];

    // Estados posibles del reporte
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EN_PROCESO = 'en_proceso';
    const ESTADO_RESUELTO = 'resuelto';
    const ESTADO_RECHAZADO = 'rechazado';

    // Niveles de urgencia
    const URGENCIA_BAJA = 1;
    const URGENCIA_MEDIA = 2;
    const URGENCIA_ALTA = 3;
    const URGENCIA_CRITICA = 4;

    /**
     * Crea un nuevo reporte
     * 
     * @param array $data
     * @return int|bool
     */
    public function createReport(array $data): int|bool
    {
        // Valores por defecto
        $data['estado'] = $data['estado'] ?? self::ESTADO_PENDIENTE;
        $data['urgencia'] = $data['urgencia'] ?? self::URGENCIA_MEDIA;
        $data['fecha_reporte'] = $data['fecha_reporte'] ?? date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    /**
     * Obtiene reportes con información del usuario
     * 
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getReportsWithUser(int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT r.*, u.nombre, u.apellidos, u.email 
                FROM {$this->table} r 
                LEFT JOIN usuarios u ON r.usuario_id = u.id 
                ORDER BY r.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->query($sql, [$limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene reportes por usuario
     * 
     * @param int $userId
     * @return array
     */
    public function getReportsByUser(int $userId): array
    {
        return $this->where(['usuario_id' => $userId]);
    }

    /**
     * Obtiene reportes por estado
     * 
     * @param string $estado
     * @return array
     */
    public function getReportsByStatus(string $estado): array
    {
        return $this->where(['estado' => $estado]);
    }

    /**
     * Obtiene reportes por tipo de residuo
     * 
     * @param string $tipoResiduo
     * @return array
     */
    public function getReportsByWasteType(string $tipoResiduo): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE tipo_residuo LIKE ? ORDER BY created_at DESC";
        $stmt = $this->db->query($sql, ["%{$tipoResiduo}%"]);
        
        $results = $stmt->fetchAll();
        return array_map([$this, 'castAttributes'], $results);
    }

    /**
     * Obtiene reportes por nivel de urgencia
     * 
     * @param int $urgencia
     * @return array
     */
    public function getReportsByUrgency(int $urgencia): array
    {
        return $this->where(['urgencia' => $urgencia]);
    }

    /**
     * Busca reportes por ubicación (radio en kilómetros)
     * 
     * @param float $lat
     * @param float $lng
     * @param float $radius
     * @return array
     */
    public function getReportsByLocation(float $lat, float $lng, float $radius = 5): array
    {
        $sql = "SELECT *, 
                (6371 * acos(cos(radians(?)) * cos(radians(latitud)) * 
                cos(radians(longitud) - radians(?)) + sin(radians(?)) * 
                sin(radians(latitud)))) AS distancia 
                FROM {$this->table} 
                HAVING distancia < ? 
                ORDER BY distancia";
        
        $stmt = $this->db->query($sql, [$lat, $lng, $lat, $radius]);
        return $stmt->fetchAll();
    }

    /**
     * Actualiza el estado de un reporte
     * 
     * @param int $reportId
     * @param string $nuevoEstado
     * @return bool
     */
    public function updateStatus(int $reportId, string $nuevoEstado): bool
    {
        $estadosValidos = [
            self::ESTADO_PENDIENTE,
            self::ESTADO_EN_PROCESO,
            self::ESTADO_RESUELTO,
            self::ESTADO_RECHAZADO
        ];
        
        if (!in_array($nuevoEstado, $estadosValidos)) {
            return false;
        }
        
        $updateData = ['estado' => $nuevoEstado];
        
        // Si se marca como resuelto, agregar fecha de resolución
        if ($nuevoEstado === self::ESTADO_RESUELTO) {
            $updateData['fecha_resolucion'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($reportId, $updateData);
    }

    /**
     * Obtiene estadísticas de reportes
     * 
     * @return array
     */
    public function getStats(): array
    {
        $stats = [];
        
        // Total de reportes
        $stats['total'] = $this->count();
        
        // Reportes por estado
        $sql = "SELECT estado, COUNT(*) as count FROM {$this->table} GROUP BY estado";
        $result = $this->query($sql);
        
        $stats['por_estado'] = [];
        foreach ($result as $row) {
            $stats['por_estado'][$row['estado']] = $row['count'];
        }
        
        // Reportes por urgencia
        $sql = "SELECT urgencia, COUNT(*) as count FROM {$this->table} GROUP BY urgencia ORDER BY urgencia";
        $result = $this->query($sql);
        
        $stats['por_urgencia'] = [];
        foreach ($result as $row) {
            $stats['por_urgencia'][$row['urgencia']] = $row['count'];
        }
        
        // Tipos de residuo más reportados
        $sql = "SELECT tipo_residuo, COUNT(*) as count 
                FROM {$this->table} 
                GROUP BY tipo_residuo 
                ORDER BY count DESC 
                LIMIT 10";
        $result = $this->query($sql);
        $stats['tipos_residuo_top'] = $result;
        
        // Reportes por mes (últimos 12 meses)
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as mes, COUNT(*) as count 
                FROM {$this->table} 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY mes DESC";
        
        $result = $this->query($sql);
        $stats['reportes_mensuales'] = $result;
        
        // Tiempo promedio de resolución (en días)
        $sql = "SELECT AVG(DATEDIFF(fecha_resolucion, created_at)) as promedio_dias 
                FROM {$this->table} 
                WHERE estado = 'resuelto' AND fecha_resolucion IS NOT NULL";
        
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        $stats['tiempo_promedio_resolucion'] = round($result['promedio_dias'] ?? 0, 1);
        
        return $stats;
    }

    /**
     * Obtiene reportes recientes
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentReports(int $limit = 10): array
    {
        $sql = "SELECT r.*, u.nombre, u.apellidos 
                FROM {$this->table} r 
                LEFT JOIN usuarios u ON r.usuario_id = u.id 
                ORDER BY r.created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene reportes urgentes pendientes
     * 
     * @return array
     */
    public function getUrgentReports(): array
    {
        $sql = "SELECT r.*, u.nombre, u.apellidos 
                FROM {$this->table} r 
                LEFT JOIN usuarios u ON r.usuario_id = u.id 
                WHERE r.estado = ? AND r.urgencia >= ? 
                ORDER BY r.urgencia DESC, r.created_at ASC";
        
        $stmt = $this->db->query($sql, [self::ESTADO_PENDIENTE, self::URGENCIA_ALTA]);
        return $stmt->fetchAll();
    }

    /**
     * Busca reportes por texto
     * 
     * @param string $searchTerm
     * @return array
     */
    public function searchReports(string $searchTerm): array
    {
        $sql = "SELECT r.*, u.nombre, u.apellidos 
                FROM {$this->table} r 
                LEFT JOIN usuarios u ON r.usuario_id = u.id 
                WHERE r.ubicacion LIKE ? 
                   OR r.descripcion LIKE ? 
                   OR r.tipo_residuo LIKE ? 
                   OR r.direccion_exacta LIKE ?
                ORDER BY r.created_at DESC";
        
        $searchPattern = "%{$searchTerm}%";
        $params = [$searchPattern, $searchPattern, $searchPattern, $searchPattern];
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene reportes para el mapa (con coordenadas)
     * 
     * @return array
     */
    public function getReportsForMap(): array
    {
        $sql = "SELECT id, latitud, longitud, tipo_residuo, urgencia, estado, ubicacion 
                FROM {$this->table} 
                WHERE latitud IS NOT NULL AND longitud IS NOT NULL 
                ORDER BY created_at DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Marca un reporte como resuelto
     * 
     * @param int $reportId
     * @param string $comentarioResolucion
     * @return bool
     */
    public function markAsResolved(int $reportId, string $comentarioResolucion = ''): bool
    {
        $updateData = [
            'estado' => self::ESTADO_RESUELTO,
            'fecha_resolucion' => date('Y-m-d H:i:s')
        ];
        
        if (!empty($comentarioResolucion)) {
            $updateData['comentario_resolucion'] = $comentarioResolucion;
        }
        
        return $this->update($reportId, $updateData);
    }

    /**
     * Obtiene los nombres de urgencia
     * 
     * @param int $nivel
     * @return string
     */
    public static function getUrgencyName(int $nivel): string
    {
        $nombres = [
            self::URGENCIA_BAJA => 'Baja',
            self::URGENCIA_MEDIA => 'Media',
            self::URGENCIA_ALTA => 'Alta',
            self::URGENCIA_CRITICA => 'Crítica'
        ];
        
        return $nombres[$nivel] ?? 'Desconocida';
    }

    /**
     * Obtiene el color de urgencia para UI
     * 
     * @param int $nivel
     * @return string
     */
    public static function getUrgencyColor(int $nivel): string
    {
        $colores = [
            self::URGENCIA_BAJA => 'success',
            self::URGENCIA_MEDIA => 'warning',
            self::URGENCIA_ALTA => 'danger',
            self::URGENCIA_CRITICA => 'dark'
        ];
        
        return $colores[$nivel] ?? 'secondary';
    }
}
