<?php

namespace App\Core;

use PDO;
use PDOException;
use Exception;

/**
 * Clase Database - Implementa patrón Singleton para manejo de conexiones a BD
 * Incluye reconexión automática y manejo de errores robusto
 */
class Database
{
    private static $instance = null;
    private $connection;
    private $config;
    private $isConnected = false;
    private $connectionAttempts = 0;
    private $maxRetries;

    /**
     * Constructor privado para implementar Singleton
     */
    private function __construct()
    {
        $this->config = require CONFIG_PATH . '/database.php';
        $this->maxRetries = $this->config['pool']['retry_attempts'] ?? 3;
        $this->connect();
    }

    /**
     * Obtiene la instancia única de la clase
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtiene la conexión PDO
     */
    public function getConnection()
    {
        if (!$this->isConnected) {
            $this->reconnect();
        }
        return $this->connection;
    }

    /**
     * Establece conexión a la base de datos
     */
    private function connect()
    {
        try {
            $connectionConfig = $this->config['connections'][$this->config['default']];
            
            $dsn = sprintf(
                "%s:host=%s;port=%d;dbname=%s;charset=%s",
                $connectionConfig['driver'],
                $connectionConfig['host'],
                $connectionConfig['port'],
                $connectionConfig['database'],
                $connectionConfig['charset']
            );

            $this->connection = new PDO(
                $dsn,
                $connectionConfig['username'],
                $connectionConfig['password'],
                $connectionConfig['options']
            );

            $this->isConnected = true;
            $this->connectionAttempts = 0;
            
            // Log exitoso si es necesario
            $this->log("Conexión a base de datos establecida correctamente");
            
        } catch (PDOException $e) {
            $this->isConnected = false;
            $this->handleConnectionError($e);
        }
    }

    /**
     * Reconecta automáticamente
     */
    private function reconnect()
    {
        if ($this->connectionAttempts >= $this->maxRetries) {
            throw new Exception("No se pudo establecer conexión a la base de datos después de {$this->maxRetries} intentos");
        }

        $this->connectionAttempts++;
        $this->log("Intento de reconexión #{$this->connectionAttempts}");
        
        // Esperar antes de reconectar
        if (isset($this->config['pool']['retry_delay'])) {
            usleep($this->config['pool']['retry_delay'] * 1000);
        }
        
        $this->connect();
    }

    /**
     * Maneja errores de conexión
     */
    private function handleConnectionError(PDOException $e)
    {
        $errorMessage = "Error de conexión a BD: " . $e->getMessage();
        $this->log($errorMessage, 'error');
        
        // En desarrollo, mostrar error detallado
        if (env('APP_DEBUG', false)) {
            throw new Exception($errorMessage);
        } else {
            throw new Exception("Error de conexión a la base de datos. Por favor intenta más tarde.");
        }
    }

    /**
     * Ejecuta una consulta preparada
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->log("Error en consulta SQL: " . $e->getMessage(), 'error');
            throw new Exception("Error al ejecutar consulta: " . $e->getMessage());
        }
    }

    /**
     * Obtiene un registro
     */
    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Obtiene múltiples registros
     */
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Ejecuta una consulta y retorna el número de filas afectadas
     */
    public function execute($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Obtiene el último ID insertado
     */
    public function lastInsertId()
    {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Inicia una transacción
     */
    public function beginTransaction()
    {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Confirma una transacción
     */
    public function commit()
    {
        return $this->getConnection()->commit();
    }

    /**
     * Revierte una transacción
     */
    public function rollback()
    {
        return $this->getConnection()->rollback();
    }

    /**
     * Verifica si está en una transacción
     */
    public function inTransaction()
    {
        return $this->getConnection()->inTransaction();
    }

    /**
     * Escapa valores para prevenir inyección SQL
     */
    public function quote($value)
    {
        return $this->getConnection()->quote($value);
    }

    /**
     * Cierra la conexión
     */
    public function close()
    {
        $this->connection = null;
        $this->isConnected = false;
    }

    /**
     * Registra logs
     */
    private function log($message, $level = 'info')
    {
        $logFile = STORAGE_PATH . '/logs/database.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        // Crear directorio si no existe
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }

    /**
     * Previene clonación
     */
    private function __clone() {}

    /**
     * Previene deserialización
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
