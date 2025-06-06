<?php

namespace App\Core;

use Exception;

/**
 * Clase base Model
 * Proporciona funcionalidades ORM básicas para todos los modelos
 */
abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];
    protected $timestamps = true;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Busca todos los registros
     */
    public function all($columns = '*')
    {
        $sql = "SELECT {$columns} FROM {$this->table}";
        return $this->db->fetchAll($sql);
    }

    /**
     * Busca registros con condiciones
     */
    public function where($column, $operator, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} ?";
        return $this->db->fetchAll($sql, [$value]);
    }

    /**
     * Busca un registro por ID
     */
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Busca el primer registro que coincida
     */
    public function first($conditions = [])
    {
        if (empty($conditions)) {
            $sql = "SELECT * FROM {$this->table} LIMIT 1";
            return $this->db->fetchOne($sql);
        }

        $whereClause = [];
        $params = [];

        foreach ($conditions as $column => $value) {
            $whereClause[] = "{$column} = ?";
            $params[] = $value;
        }

        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClause) . " LIMIT 1";
        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Crea un nuevo registro
     */
    public function create($data)
    {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->db->execute($sql, array_values($data));
        
        $id = $this->db->lastInsertId();
        return $this->find($id);
    }

    /**
     * Actualiza un registro
     */
    public function update($id, $data)
    {
        $data = $this->filterFillable($data);
        
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $setClause = [];
        $params = [];

        foreach ($data as $column => $value) {
            $setClause[] = "{$column} = ?";
            $params[] = $value;
        }

        $params[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE {$this->primaryKey} = ?";
        
        $rowsAffected = $this->db->execute($sql, $params);
        
        if ($rowsAffected > 0) {
            return $this->find($id);
        }
        
        return false;
    }

    /**
     * Elimina un registro
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->execute($sql, [$id]) > 0;
    }

    /**
     * Cuenta registros
     */
    public function count($conditions = [])
    {
        if (empty($conditions)) {
            $sql = "SELECT COUNT(*) as count FROM {$this->table}";
            $result = $this->db->fetchOne($sql);
        } else {
            $whereClause = [];
            $params = [];

            foreach ($conditions as $column => $value) {
                $whereClause[] = "{$column} = ?";
                $params[] = $value;
            }

            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE " . implode(' AND ', $whereClause);
            $result = $this->db->fetchOne($sql, $params);
        }

        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Paginación
     */
    public function paginate($page = 1, $perPage = 10, $conditions = [])
    {
        $offset = ($page - 1) * $perPage;
        
        // Construir consulta base
        $baseQuery = "FROM {$this->table}";
        $params = [];
        
        if (!empty($conditions)) {
            $whereClause = [];
            foreach ($conditions as $column => $value) {
                $whereClause[] = "{$column} = ?";
                $params[] = $value;
            }
            $baseQuery .= " WHERE " . implode(' AND ', $whereClause);
        }

        // Obtener total de registros
        $countSql = "SELECT COUNT(*) as count " . $baseQuery;
        $totalResult = $this->db->fetchOne($countSql, $params);
        $total = $totalResult ? (int)$totalResult['count'] : 0;

        // Obtener registros de la página actual
        $dataSql = "SELECT * " . $baseQuery . " LIMIT {$perPage} OFFSET {$offset}";
        $data = $this->db->fetchAll($dataSql, $params);

        return [
            'data' => $data,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => ceil($total / $perPage),
            'has_next' => $page < ceil($total / $perPage),
            'has_prev' => $page > 1
        ];
    }

    /**
     * Ejecuta consulta SQL personalizada
     */
    public function query($sql, $params = [])
    {
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Ejecuta consulta SQL personalizada para un registro
     */
    public function queryOne($sql, $params = [])
    {
        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Busca o crea un registro
     */
    public function firstOrCreate($conditions, $data = [])
    {
        $existing = $this->first($conditions);
        
        if ($existing) {
            return $existing;
        }

        return $this->create(array_merge($conditions, $data));
    }

    /**
     * Actualiza o crea un registro
     */
    public function updateOrCreate($conditions, $data = [])
    {
        $existing = $this->first($conditions);
        
        if ($existing) {
            return $this->update($existing[$this->primaryKey], $data);
        }

        return $this->create(array_merge($conditions, $data));
    }

    /**
     * Filtra campos permitidos
     */
    protected function filterFillable($data)
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Oculta campos sensibles
     */
    protected function hideFields($data)
    {
        if (empty($this->hidden)) {
            return $data;
        }

        if (is_array($data) && isset($data[0])) {
            // Array de registros
            return array_map(function($item) {
                return array_diff_key($item, array_flip($this->hidden));
            }, $data);
        } else {
            // Registro único
            return array_diff_key($data, array_flip($this->hidden));
        }
    }

    /**
     * Inicia transacción
     */
    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }

    /**
     * Confirma transacción
     */
    public function commit()
    {
        return $this->db->commit();
    }

    /**
     * Revierte transacción
     */
    public function rollback()
    {
        return $this->db->rollback();
    }

    /**
     * Búsqueda con LIKE
     */
    public function search($column, $term, $limit = null)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} LIKE ?";
        $params = ["%{$term}%"];
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Ordenar resultados
     */
    public function orderBy($column, $direction = 'ASC', $limit = null)
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $sql = "SELECT * FROM {$this->table} ORDER BY {$column} {$direction}";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Validar datos antes de guardar
     */
    protected function validate($data, $rules = [])
    {
        // Esta función puede ser sobrescrita en cada modelo
        return [];
    }
}
