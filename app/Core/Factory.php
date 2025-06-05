<?php

namespace App\Core;

/**
 * Clase Factory - Implementa el patrón Factory
 * 
 * Maneja la creación de instancias de controladores, modelos
 * y otros objetos de forma centralizada y flexible.
 */
class Factory
{
    private static array $instances = [];
    private static array $bindings = [];

    /**
     * Registra una clase en el factory
     * 
     * @param string $abstract
     * @param string|callable $concrete
     * @return void
     */
    public static function bind(string $abstract, $concrete): void
    {
        self::$bindings[$abstract] = $concrete;
    }

    /**
     * Registra una instancia singleton
     * 
     * @param string $abstract
     * @param string|callable $concrete
     * @return void
     */
    public static function singleton(string $abstract, $concrete): void
    {
        self::bind($abstract, $concrete);
        // Marcar como singleton añadiendo prefijo
        self::$bindings['_singleton_' . $abstract] = true;
    }

    /**
     * Crea o retorna una instancia
     * 
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     */
    public static function make(string $abstract, array $parameters = [])
    {
        // Si es singleton y ya existe, retornar instancia existente
        if (isset(self::$bindings['_singleton_' . $abstract]) && 
            isset(self::$instances[$abstract])) {
            return self::$instances[$abstract];
        }

        $instance = self::resolve($abstract, $parameters);

        // Si es singleton, guardar instancia
        if (isset(self::$bindings['_singleton_' . $abstract])) {
            self::$instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Resuelve una instancia
     * 
     * @param string $abstract
     * @param array $parameters
     * @return mixed
     */
    private static function resolve(string $abstract, array $parameters = [])
    {
        // Si hay un binding específico, usarlo
        if (isset(self::$bindings[$abstract])) {
            $concrete = self::$bindings[$abstract];
            
            if (is_callable($concrete)) {
                return call_user_func_array($concrete, $parameters);
            }
            
            if (is_string($concrete)) {
                return self::build($concrete, $parameters);
            }
        }

        // Intentar construir directamente
        return self::build($abstract, $parameters);
    }

    /**
     * Construye una instancia de clase
     * 
     * @param string $className
     * @param array $parameters
     * @return mixed
     */
    private static function build(string $className, array $parameters = [])
    {
        if (!class_exists($className)) {
            throw new \Exception("Clase no encontrada: {$className}");
        }

        $reflectionClass = new \ReflectionClass($className);

        if (!$reflectionClass->isInstantiable()) {
            throw new \Exception("Clase no instanciable: {$className}");
        }

        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            return new $className;
        }

        $dependencies = self::resolveDependencies($constructor, $parameters);
        
        return $reflectionClass->newInstanceArgs($dependencies);
    }

    /**
     * Resuelve dependencias del constructor
     * 
     * @param \ReflectionMethod $constructor
     * @param array $parameters
     * @return array
     */
    private static function resolveDependencies(\ReflectionMethod $constructor, array $parameters = []): array
    {
        $dependencies = [];
        
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            
            if ($type && !$type->isBuiltin()) {
                $className = $type->getName();
                $dependencies[] = self::make($className);
            } elseif (isset($parameters[$parameter->getName()])) {
                $dependencies[] = $parameters[$parameter->getName()];
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new \Exception("No se puede resolver la dependencia: {$parameter->getName()}");
            }
        }
        
        return $dependencies;
    }

    /**
     * Crea un controlador
     * 
     * @param string $controllerName
     * @return Controller
     */
    public static function createController(string $controllerName): Controller
    {
        $controllerClass = "App\\Controllers\\{$controllerName}";
        
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controlador no encontrado: {$controllerName}");
        }
        
        return self::make($controllerClass);
    }

    /**
     * Crea un modelo
     * 
     * @param string $modelName
     * @return Model
     */
    public static function createModel(string $modelName): Model
    {
        $modelClass = "App\\Models\\{$modelName}";
        
        if (!class_exists($modelClass)) {
            throw new \Exception("Modelo no encontrado: {$modelName}");
        }
        
        return self::make($modelClass);
    }

    /**
     * Crea una instancia de servicio
     * 
     * @param string $serviceName
     * @return mixed
     */
    public static function createService(string $serviceName)
    {
        $serviceClass = "App\\Services\\{$serviceName}";
        
        if (!class_exists($serviceClass)) {
            throw new \Exception("Servicio no encontrado: {$serviceName}");
        }
        
        return self::make($serviceClass);
    }

    /**
     * Verifica si existe un binding
     * 
     * @param string $abstract
     * @return bool
     */
    public static function bound(string $abstract): bool
    {
        return isset(self::$bindings[$abstract]);
    }

    /**
     * Limpia todas las instancias singleton
     * 
     * @return void
     */
    public static function flush(): void
    {
        self::$instances = [];
    }

    /**
     * Registra bindings por defecto del sistema
     * 
     * @return void
     */
    public static function registerDefaultBindings(): void
    {
        // Database como singleton
        self::singleton('Database', function() {
            return Database::getInstance();
        });

        // View como singleton
        self::singleton('View', function() {
            return new View();
        });

        // Validator
        self::bind('Validator', function() {
            return new Validator();
        });

        // Request
        self::bind('Request', function() {
            return new Request();
        });

        // Config como singleton
        self::singleton('Config', function() {
            Config::load();
            return new class {
                public function get($key, $default = null) {
                    return Config::get($key, $default);
                }
            };
        });
    }

    /**
     * Obtiene una instancia existente
     * 
     * @param string $abstract
     * @return mixed|null
     */
    public static function getInstance(string $abstract)
    {
        return self::$instances[$abstract] ?? null;
    }

    /**
     * Remueve una instancia
     * 
     * @param string $abstract
     * @return void
     */
    public static function forget(string $abstract): void
    {
        unset(self::$instances[$abstract]);
        unset(self::$bindings[$abstract]);
        unset(self::$bindings['_singleton_' . $abstract]);
    }

    /**
     * Obtiene todos los bindings registrados
     * 
     * @return array
     */
    public static function getBindings(): array
    {
        return self::$bindings;
    }

    /**
     * Obtiene todas las instancias activas
     * 
     * @return array
     */
    public static function getInstances(): array
    {
        return self::$instances;
    }
}
