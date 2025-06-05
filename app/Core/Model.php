<?php

namespace App\Core;

use PDO;

/**
 * Clase Model base - Implementa el patrón Repository
 * 
 * Proporciona funcionalidades base para todos los modelos
 * incluyendo operaciones CRUD básicas y consultas avanzadas.
 */
abstract class Model
{
    protected Database $db;
    protected PDO $connection;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $casts = [];
    protected array $dates = ['created_at', 'updated_at'];
    protected bool $timestamps = true;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->connection = $this->db->getConnection();
    }

    /**
     * Encuentra un registro por su ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
        
        $result = $stmt->fetch();
        return $result ? $this->castAttributes($result) : null;
    }

    /**
     * Encuentra un registro por una condición específica
     * 
     * @param string $column
     * @param mixed $value
     * @return array|null
     */
    public function findBy(string $column, $value): ?array
    {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table} WHERE {$column} = ?",
            [$value]
        );
        
        $result = $stmt->fetch();
        return $result ? $this->castAttributes($result) : null;
    }

    /**
     * Obtiene todos los registros
     * 
     * @return array
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        $results = $stmt->fetchAll();
        
        return array_map([$this, 'castAttributes'], $results);
    }

    /**
     * Obtiene registros con paginación
     * 
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Obtener total de registros
        $countStmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $total = $countStmt->fetch()['total'];
        
        // Obtener registros paginados
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table} LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        $data = array_map([$this, 'castAttributes'], $stmt->fetchAll());
        
        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'has_more' => $page < ceil($total / $perPage)
        ];
    }

    /**
     * Crea un nuevo registro
     * 
     * @param array $data
     * @return int|bool ID del registro creado o false si falla
     */
    public function create(array $data): int|bool
    {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $columns = array_keys($data);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        
        $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES ({$placeholders})";
        
        try {
            $stmt = $this->db->query($sql, array_values($data));
            return $this->db->lastInsertId();
        } catch (\Exception $e) {
            error_log("Error al crear registro: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un registro por ID
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $setClause = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?";
        
        $values = array_values($data);
        $values[] = $id;
        
        try {
            $stmt = $this->db->query($sql, $values);
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error al actualizar registro: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un registro por ID
     * 
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->query(
                "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?",
                [$id]
            );
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Error al eliminar registro: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca registros con condiciones WHERE
     * 
     * @param array $conditions
     * @return array
     */
    public function where(array $conditions): array
    {
        $whereClause = implode(' = ? AND ', array_keys($conditions)) . ' = ?';
        $sql = "SELECT * FROM {$this->table} WHERE {$whereClause}";
        
        $stmt = $this->db->query($sql, array_values($conditions));
        $results = $stmt->fetchAll();
        
        return array_map([$this, 'castAttributes'], $results);
    }

    /**
     * Cuenta registros con condiciones
     * 
     * @param array $conditions
     * @return int
     */
    public function count(array $conditions = []): int
    {
        if (empty($conditions)) {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        } else {
            $whereClause = implode(' = ? AND ', array_keys($conditions)) . ' = ?';
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
            $stmt = $this->db->query($sql, array_values($conditions));
        }
        
        return (int) $stmt->fetch()['total'];
    }

    /**
     * Ejecuta una consulta SQL personalizada
     * 
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Filtra solo los campos permitidos (fillable)
     * 
     * @param array $data
     * @return array
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Oculta campos sensibles antes de devolver datos
     * 
     * @param array $data
     * @return array
     */
    protected function hideAttributes(array $data): array
    {
        if (empty($this->hidden)) {
            return $data;
        }
        
        return array_diff_key($data, array_flip($this->hidden));
    }

    /**
     * Convierte tipos de datos según configuración
     * 
     * @param array $data
     * @return array
     */
    protected function castAttributes(array $data): array
    {
        foreach ($this->casts as $key => $type) {
            if (array_key_exists($key, $data)) {
                switch ($type) {
                    case 'int':
                    case 'integer':
                        $data[$key] = (int) $data[$key];
                        break;
                    case 'float':
                    case 'double':
                        $data[$key] = (float) $data[$key];
                        break;
                    case 'bool':
                    case 'boolean':
                        $data[$key] = (bool) $data[$key];
                        break;
                    case 'array':
                    case 'json':
                        $data[$key] = json_decode($data[$key], true);
                        break;
                    case 'date':
                        if ($data[$key]) {
                            $data[$key] = date('Y-m-d', strtotime($data[$key]));
                        }
                        break;
                    case 'datetime':
                        if ($data[$key]) {
                            $data[$key] = date('Y-m-d H:i:s', strtotime($data[$key]));
                        }
                        break;
                }
            }
        }
        
        return $this->hideAttributes($data);
    }

    /**
     * Busca registros con LIKE
     * 
     * @param string $column
     * @param string $value
     * @return array
     */
    public function search(string $column, string $value): array
    {
        $stmt = $this->db->query(
            "SELECT * FROM {$this->table} WHERE {$column} LIKE ?",
            ["%{$value}%"]
        );
        
        $results = $stmt->fetchAll();
        return array_map([$this, 'castAttributes'], $results);
    }

    /**
     * Inicia una transacción
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Confirma una transacción
     * 
     * @return bool
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Revierte una transacción
     * 
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->db->rollback();
    }
}
