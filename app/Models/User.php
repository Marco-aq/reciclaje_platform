<?php

namespace App\Models;

use App\Core\Model;

/**
 * Modelo User - Gestión de usuarios
 * 
 * Maneja las operaciones relacionadas con los usuarios
 * del sistema incluyendo autenticación y perfil.
 */
class User extends Model
{
    protected string $table = 'usuarios';
    
    protected array $fillable = [
        'nombre',
        'apellidos', 
        'email',
        'password_hash',
        'telefono',
        'direccion',
        'fecha_nacimiento',
        'tipo_usuario',
        'estado'
    ];
    
    protected array $hidden = [
        'password_hash'
    ];
    
    protected array $casts = [
        'id' => 'integer',
        'fecha_nacimiento' => 'date',
        'estado' => 'boolean'
    ];

    /**
     * Crea un nuevo usuario
     * 
     * @param array $data
     * @return int|bool
     */
    public function createUser(array $data): int|bool
    {
        // Hash de la contraseña
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        
        // Valores por defecto
        $data['tipo_usuario'] = $data['tipo_usuario'] ?? 'ciudadano';
        $data['estado'] = $data['estado'] ?? 1;
        
        return $this->create($data);
    }

    /**
     * Actualiza la contraseña de un usuario
     * 
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        return $this->update($userId, [
            'password_hash' => $hashedPassword
        ]);
    }

    /**
     * Verifica las credenciales de un usuario
     * 
     * @param string $email
     * @param string $password
     * @return array|null
     */
    public function verifyCredentials(string $email, string $password): ?array
    {
        $user = $this->findBy('email', $email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Verificar que el usuario esté activo
            if ($user['estado']) {
                return $user;
            }
        }
        
        return null;
    }

    /**
     * Busca un usuario por email
     * 
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    /**
     * Verifica si un email ya está registrado
     * 
     * @param string $email
     * @param int|null $excludeUserId
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeUserId = null): bool
    {
        $conditions = ['email' => $email];
        
        if ($excludeUserId) {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ? AND id != ?";
            $stmt = $this->db->query($sql, [$email, $excludeUserId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
            $stmt = $this->db->query($sql, [$email]);
        }
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Obtiene usuarios por tipo
     * 
     * @param string $type
     * @return array
     */
    public function getUsersByType(string $type): array
    {
        return $this->where(['tipo_usuario' => $type, 'estado' => 1]);
    }

    /**
     * Obtiene estadísticas de usuarios
     * 
     * @return array
     */
    public function getStats(): array
    {
        $stats = [];
        
        // Total de usuarios
        $stats['total'] = $this->count();
        
        // Usuarios activos
        $stats['activos'] = $this->count(['estado' => 1]);
        
        // Usuarios por tipo
        $sql = "SELECT tipo_usuario, COUNT(*) as count FROM {$this->table} WHERE estado = 1 GROUP BY tipo_usuario";
        $result = $this->query($sql);
        
        $stats['por_tipo'] = [];
        foreach ($result as $row) {
            $stats['por_tipo'][$row['tipo_usuario']] = $row['count'];
        }
        
        // Registros por mes (últimos 6 meses)
        $sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as mes, COUNT(*) as count 
                FROM {$this->table} 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY mes DESC";
        
        $result = $this->query($sql);
        $stats['registros_mensuales'] = $result;
        
        return $stats;
    }

    /**
     * Busca usuarios por nombre o email
     * 
     * @param string $searchTerm
     * @return array
     */
    public function searchUsers(string $searchTerm): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE (nombre LIKE ? OR apellidos LIKE ? OR email LIKE ?) 
                AND estado = 1 
                ORDER BY nombre, apellidos";
        
        $searchPattern = "%{$searchTerm}%";
        $stmt = $this->db->query($sql, [$searchPattern, $searchPattern, $searchPattern]);
        
        $results = $stmt->fetchAll();
        return array_map([$this, 'castAttributes'], $results);
    }

    /**
     * Actualiza la última actividad del usuario
     * 
     * @param int $userId
     * @return bool
     */
    public function updateLastActivity(int $userId): bool
    {
        $sql = "UPDATE {$this->table} SET ultima_actividad = NOW() WHERE id = ?";
        
        try {
            $stmt = $this->db->query($sql, [$userId]);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error al actualizar última actividad: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el perfil completo del usuario
     * 
     * @param int $userId
     * @return array|null
     */
    public function getProfile(int $userId): ?array
    {
        $user = $this->findById($userId);
        
        if ($user) {
            // Agregar estadísticas del usuario
            $user['reportes_realizados'] = $this->getUserReportsCount($userId);
            $user['fecha_registro'] = $user['created_at'];
        }
        
        return $user;
    }

    /**
     * Obtiene la cantidad de reportes realizados por un usuario
     * 
     * @param int $userId
     * @return int
     */
    private function getUserReportsCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) as count FROM reportes WHERE usuario_id = ?";
        $stmt = $this->db->query($sql, [$userId]);
        $result = $stmt->fetch();
        
        return (int) $result['count'];
    }

    /**
     * Desactiva un usuario
     * 
     * @param int $userId
     * @return bool
     */
    public function deactivateUser(int $userId): bool
    {
        return $this->update($userId, ['estado' => 0]);
    }

    /**
     * Activa un usuario
     * 
     * @param int $userId
     * @return bool
     */
    public function activateUser(int $userId): bool
    {
        return $this->update($userId, ['estado' => 1]);
    }
}
