<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Clase Database - Implementa el patrón Singleton
 * 
 * Maneja la conexión a la base de datos de forma centralizada
 * garantizando una sola instancia de conexión en toda la aplicación.
 */
class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private array $config;

    /**
     * Constructor privado para implementar Singleton
     */
    private function __construct()
    {
        $this->config = Config::get('database');
        $this->connect();
    }

    /**
     * Previene la clonación del objeto
     */
    private function __clone() {}

    /**
     * Previente la deserialización del objeto
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    /**
     * Obtiene la instancia única de la clase Database
     * 
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Establece la conexión con la base de datos
     * 
     * @throws PDOException
     */
    private function connect(): void
    {
        try {
            $connectionName = $this->config['default'];
            $dbConfig = $this->config['connections'][$connectionName];

            if ($dbConfig['driver'] === 'mysql') {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    $dbConfig['host'],
                    $dbConfig['port'],
                    $dbConfig['database'],
                    $dbConfig['charset']
                );
            } elseif ($dbConfig['driver'] === 'sqlite') {
                $dsn = 'sqlite:' . $dbConfig['database'];
            } else {
                throw new \Exception("Driver de base de datos no soportado: {$dbConfig['driver']}");
            }

            $this->connection = new PDO(
                $dsn,
                $dbConfig['username'] ?? null,
                $dbConfig['password'] ?? null,
                $dbConfig['options'] ?? []
            );

            // Configurar zona horaria para MySQL
            if ($dbConfig['driver'] === 'mysql') {
                $this->connection->exec("SET time_zone = '+00:00'");
            }

        } catch (PDOException $e) {
            error_log("Error de conexión a la base de datos: " . $e->getMessage());
            throw new \Exception("No se pudo conectar a la base de datos.");
        }
    }

    /**
     * Obtiene la conexión PDO
     * 
     * @return PDO
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }

        return $this->connection;
    }

    /**
     * Ejecuta una consulta preparada
     * 
     * @param string $query
     * @param array $params
     * @return \PDOStatement
     */
    public function query(string $query, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error en consulta SQL: " . $e->getMessage());
            throw new \Exception("Error al ejecutar la consulta.");
        }
    }

    /**
     * Obtiene un registro por ID
     * 
     * @param string $table
     * @param int $id
     * @return array|null
     */
    public function findById(string $table, int $id): ?array
    {
        $stmt = $this->query("SELECT * FROM {$table} WHERE id = ?", [$id]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }

    /**
     * Obtiene el último ID insertado
     * 
     * @return int
     */
    public function lastInsertId(): int
    {
        return (int) $this->connection->lastInsertId();
    }

    /**
     * Inicia una transacción
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Confirma una transacción
     * 
     * @return bool
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Revierte una transacción
     * 
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->connection->rollback();
    }

    /**
     * Verifica si existe una tabla
     * 
     * @param string $table
     * @return bool
     */
    public function tableExists(string $table): bool
    {
        try {
            $stmt = $this->query("SELECT 1 FROM {$table} LIMIT 1");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
