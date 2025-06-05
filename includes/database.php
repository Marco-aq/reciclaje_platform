<?php
/**
 * EcoCusco - Clase Database Simplificada
 * 
 * Implementa el patrón Singleton para manejar la conexión a la base de datos
 * de forma simple y eficiente.
 */

class Database {
    private static $instance = null;
    private $connection = null;
    private $host;
    private $database;
    private $username;
    private $password;
    private $port;

    /**
     * Constructor privado para implementar Singleton
     */
    private function __construct() {
        $this->host = DB_HOST;
        $this->database = DB_DATABASE;
        $this->username = DB_USERNAME;
        $this->password = DB_PASSWORD;
        $this->port = DB_PORT;
        
        $this->connect();
    }

    /**
     * Prevenir clonación
     */
    private function __clone() {}

    /**
     * Prevenir deserialización
     */
    public function __wakeup() {
        throw new Exception("No se puede deserializar un singleton.");
    }

    /**
     * Obtener la instancia única de Database
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establecer la conexión a la base de datos
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
            // Configurar zona horaria para MySQL
            $this->connection->exec("SET time_zone = '-05:00'"); // Hora de Perú
            
        } catch (PDOException $e) {
            // Log del error
            error_log("Error de conexión a BD: " . $e->getMessage());
            
            // Mensaje amigable según el entorno
            if (APP_DEBUG) {
                throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
            } else {
                throw new Exception("Error de conexión a la base de datos. Verifica la configuración.");
            }
        }
    }

    /**
     * Obtener la conexión PDO
     * 
     * @return PDO
     */
    public function getConnection() {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Ejecutar una consulta preparada
     * 
     * @param string $query
     * @param array $params
     * @return PDOStatement
     */
    public function query($query, $params = []) {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en consulta SQL: " . $e->getMessage() . " | Query: " . $query);
            
            if (APP_DEBUG) {
                throw new Exception("Error en consulta SQL: " . $e->getMessage());
            } else {
                throw new Exception("Error al ejecutar la consulta.");
            }
        }
    }

    /**
     * Obtener un registro por ID
     * 
     * @param string $table
     * @param int $id
     * @return array|null
     */
    public function findById($table, $id) {
        $stmt = $this->query("SELECT * FROM `{$table}` WHERE id = ?", [$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Obtener múltiples registros
     * 
     * @param string $query
     * @param array $params
     * @return array
     */
    public function fetchAll($query, $params = []) {
        $stmt = $this->query($query, $params);
        return $stmt->fetchAll();
    }

    /**
     * Obtener un solo registro
     * 
     * @param string $query
     * @param array $params
     * @return array|null
     */
    public function fetchOne($query, $params = []) {
        $stmt = $this->query($query, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Insertar un registro
     * 
     * @param string $table
     * @param array $data
     * @return int ID del registro insertado
     */
    public function insert($table, $data) {
        $keys = array_keys($data);
        $fields = '`' . implode('`, `', $keys) . '`';
        $placeholders = ':' . implode(', :', $keys);
        
        $query = "INSERT INTO `{$table}` ({$fields}) VALUES ({$placeholders})";
        $this->query($query, $data);
        
        return $this->connection->lastInsertId();
    }

    /**
     * Actualizar registros
     * 
     * @param string $table
     * @param array $data
     * @param array $where
     * @return int Número de filas afectadas
     */
    public function update($table, $data, $where) {
        $setClause = [];
        foreach ($data as $key => $value) {
            $setClause[] = "`{$key}` = :{$key}";
        }
        
        $whereClause = [];
        foreach ($where as $key => $value) {
            $whereClause[] = "`{$key}` = :where_{$key}";
            $data["where_{$key}"] = $value;
        }
        
        $query = "UPDATE `{$table}` SET " . implode(', ', $setClause) . 
                 " WHERE " . implode(' AND ', $whereClause);
        
        $stmt = $this->query($query, $data);
        return $stmt->rowCount();
    }

    /**
     * Eliminar registros
     * 
     * @param string $table
     * @param array $where
     * @return int Número de filas afectadas
     */
    public function delete($table, $where) {
        $whereClause = [];
        foreach ($where as $key => $value) {
            $whereClause[] = "`{$key}` = :{$key}";
        }
        
        $query = "DELETE FROM `{$table}` WHERE " . implode(' AND ', $whereClause);
        $stmt = $this->query($query, $where);
        return $stmt->rowCount();
    }

    /**
     * Verificar si una tabla existe
     * 
     * @param string $table
     * @return bool
     */
    public function tableExists($table) {
        try {
            $stmt = $this->query("SELECT 1 FROM `{$table}` LIMIT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Iniciar transacción
     * 
     * @return bool
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Confirmar transacción
     * 
     * @return bool
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Revertir transacción
     * 
     * @return bool
     */
    public function rollback() {
        return $this->connection->rollback();
    }

    /**
     * Obtener el último ID insertado
     * 
     * @return string
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}
?>
