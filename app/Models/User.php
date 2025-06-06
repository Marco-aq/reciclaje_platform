<?php

namespace App\Models;

use App\Core\Model;
use Exception;

/**
 * Modelo User - Maneja operaciones de usuarios
 */
class User extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    protected $fillable = ['nombre', 'email', 'password'];
    protected $hidden = ['password'];
    protected $timestamps = true;

    /**
     * Busca usuario por email
     */
    public function findByEmail($email)
    {
        return $this->first(['email' => $email]);
    }

    /**
     * Crea un nuevo usuario
     */
    public function createUser($data)
    {
        // Validar datos
        $errors = $this->validateUserData($data);
        if (!empty($errors)) {
            throw new Exception("Datos de usuario inválidos: " . implode(', ', array_merge(...$errors)));
        }

        // Verificar si el email ya existe
        if ($this->findByEmail($data['email'])) {
            throw new Exception("El email ya está registrado");
        }

        // Hash de la contraseña
        $data['password'] = $this->hashPassword($data['password']);

        return $this->create($data);
    }

    /**
     * Actualiza un usuario
     */
    public function updateUser($id, $data)
    {
        $user = $this->find($id);
        if (!$user) {
            throw new Exception("Usuario no encontrado");
        }

        // Si se está cambiando el email, verificar que no exista
        if (isset($data['email']) && $data['email'] !== $user['email']) {
            if ($this->findByEmail($data['email'])) {
                throw new Exception("El email ya está registrado");
            }
        }

        // Si se está cambiando la contraseña, hashearla
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = $this->hashPassword($data['password']);
        } else {
            // Remover password si está vacío
            unset($data['password']);
        }

        return $this->update($id, $data);
    }

    /**
     * Autentica un usuario
     */
    public function authenticate($email, $password)
    {
        $user = $this->queryOne(
            "SELECT id, nombre, email, password, created_at FROM {$this->table} WHERE email = ?",
            [$email]
        );

        if (!$user) {
            return false;
        }

        if (!$this->verifyPassword($password, $user['password'])) {
            return false;
        }

        // Remover password del resultado
        unset($user['password']);
        return $user;
    }

    /**
     * Obtiene estadísticas del usuario
     */
    public function getUserStats($userId)
    {
        $reportesModel = new Report();
        
        $stats = [
            'total_reportes' => $reportesModel->count(['usuario_id' => $userId]),
            'reportes_mes' => $reportesModel->getReportesThisMonth($userId),
            'tipos_materiales' => $reportesModel->getMaterialTypesByUser($userId),
            'puntos_totales' => $this->calculateUserPoints($userId)
        ];

        return $stats;
    }

    /**
     * Calcula puntos del usuario basado en reportes
     */
    public function calculateUserPoints($userId)
    {
        $result = $this->queryOne(
            "SELECT SUM(cantidad * 10) as puntos FROM reportes WHERE usuario_id = ?",
            [$userId]
        );

        return $result ? (int)$result['puntos'] : 0;
    }

    /**
     * Obtiene el ranking de usuarios
     */
    public function getUserRanking($limit = 10)
    {
        return $this->query("
            SELECT 
                u.id,
                u.nombre,
                u.email,
                COUNT(r.id) as total_reportes,
                SUM(r.cantidad) as total_cantidad,
                SUM(r.cantidad * 10) as puntos
            FROM {$this->table} u
            LEFT JOIN reportes r ON u.id = r.usuario_id
            GROUP BY u.id, u.nombre, u.email
            ORDER BY puntos DESC, total_reportes DESC
            LIMIT {$limit}
        ");
    }

    /**
     * Obtiene usuarios activos (con reportes recientes)
     */
    public function getActiveUsers($days = 30)
    {
        return $this->query("
            SELECT DISTINCT 
                u.id,
                u.nombre,
                u.email,
                u.created_at,
                COUNT(r.id) as reportes_recientes
            FROM {$this->table} u
            INNER JOIN reportes r ON u.id = r.usuario_id
            WHERE r.fecha_reporte >= DATE_SUB(NOW(), INTERVAL {$days} DAY)
            GROUP BY u.id, u.nombre, u.email, u.created_at
            ORDER BY reportes_recientes DESC
        ");
    }

    /**
     * Valida datos de usuario
     */
    public function validateUserData($data, $isUpdate = false)
    {
        $errors = [];

        // Validar nombre
        if (empty($data['nombre'])) {
            $errors['nombre'][] = 'El nombre es requerido';
        } elseif (strlen($data['nombre']) < 2) {
            $errors['nombre'][] = 'El nombre debe tener al menos 2 caracteres';
        } elseif (strlen($data['nombre']) > 100) {
            $errors['nombre'][] = 'El nombre no puede tener más de 100 caracteres';
        }

        // Validar email
        if (empty($data['email'])) {
            $errors['email'][] = 'El email es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'El email debe ser válido';
        } elseif (strlen($data['email']) > 150) {
            $errors['email'][] = 'El email no puede tener más de 150 caracteres';
        }

        // Validar contraseña (solo en creación o si se proporciona en actualización)
        if (!$isUpdate || (!empty($data['password']))) {
            if (empty($data['password'])) {
                $errors['password'][] = 'La contraseña es requerida';
            } elseif (strlen($data['password']) < 6) {
                $errors['password'][] = 'La contraseña debe tener al menos 6 caracteres';
            } elseif (strlen($data['password']) > 255) {
                $errors['password'][] = 'La contraseña no puede tener más de 255 caracteres';
            }

            // Verificar confirmación de contraseña
            if (isset($data['password_confirmation'])) {
                if ($data['password'] !== $data['password_confirmation']) {
                    $errors['password'][] = 'Las contraseñas no coinciden';
                }
            }
        }

        return $errors;
    }

    /**
     * Hashea una contraseña
     */
    private function hashPassword($password)
    {
        $salt = env('SALT', 'default_salt');
        return password_hash($password . $salt, PASSWORD_DEFAULT);
    }

    /**
     * Verifica una contraseña
     */
    private function verifyPassword($password, $hash)
    {
        $salt = env('SALT', 'default_salt');
        return password_verify($password . $salt, $hash);
    }

    /**
     * Genera token de recuperación de contraseña
     */
    public function generatePasswordResetToken($email)
    {
        $user = $this->findByEmail($email);
        if (!$user) {
            throw new Exception("Usuario no encontrado");
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Guardar token en base de datos (se necesitaría una tabla password_resets)
        $this->db->execute(
            "INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE token = ?, expires_at = ?, created_at = NOW()",
            [$email, $token, $expires, $token, $expires]
        );

        return $token;
    }

    /**
     * Verifica token de recuperación de contraseña
     */
    public function verifyPasswordResetToken($token)
    {
        return $this->db->fetchOne(
            "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()",
            [$token]
        );
    }

    /**
     * Resetea la contraseña
     */
    public function resetPassword($token, $newPassword)
    {
        $resetData = $this->verifyPasswordResetToken($token);
        if (!$resetData) {
            throw new Exception("Token de recuperación inválido o expirado");
        }

        $user = $this->findByEmail($resetData['email']);
        if (!$user) {
            throw new Exception("Usuario no encontrado");
        }

        // Actualizar contraseña
        $hashedPassword = $this->hashPassword($newPassword);
        $this->update($user['id'], ['password' => $hashedPassword]);

        // Eliminar token usado
        $this->db->execute(
            "DELETE FROM password_resets WHERE token = ?",
            [$token]
        );

        return true;
    }

    /**
     * Limpia tokens de recuperación expirados
     */
    public function cleanExpiredTokens()
    {
        return $this->db->execute(
            "DELETE FROM password_resets WHERE expires_at <= NOW()"
        );
    }

    /**
     * Obtiene usuarios con paginación
     */
    public function getPaginatedUsers($page = 1, $perPage = 10, $search = '')
    {
        $conditions = [];
        if (!empty($search)) {
            $searchQuery = "
                SELECT * FROM {$this->table} 
                WHERE nombre LIKE ? OR email LIKE ?
                ORDER BY created_at DESC
                LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage);
            
            $data = $this->query($searchQuery, ["%{$search}%", "%{$search}%"]);
            
            $countQuery = "
                SELECT COUNT(*) as count FROM {$this->table} 
                WHERE nombre LIKE ? OR email LIKE ?";
            
            $totalResult = $this->queryOne($countQuery, ["%{$search}%", "%{$search}%"]);
            $total = $totalResult ? (int)$totalResult['count'] : 0;
            
            return [
                'data' => $this->hideFields($data),
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage),
                'has_next' => $page < ceil($total / $perPage),
                'has_prev' => $page > 1
            ];
        }

        $result = $this->paginate($page, $perPage);
        $result['data'] = $this->hideFields($result['data']);
        
        return $result;
    }
}
