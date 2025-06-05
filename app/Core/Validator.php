<?php

namespace App\Core;

/**
 * Clase Validator - Validación de datos
 * 
 * Proporciona un sistema completo de validación de datos
 * con reglas personalizables y mensajes de error.
 */
class Validator
{
    private array $errors = [];
    private array $data = [];

    /**
     * Valida datos según las reglas especificadas
     * 
     * @param array $data
     * @param array $rules
     * @return array Array con 'valid' (bool) y 'errors' (array)
     */
    public function validate(array $data, array $rules): array
    {
        $this->data = $data;
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $this->validateField($field, $fieldRules);
        }

        return [
            'valid' => empty($this->errors),
            'errors' => $this->errors
        ];
    }

    /**
     * Valida un campo específico
     * 
     * @param string $field
     * @param string|array $rules
     * @return void
     */
    private function validateField(string $field, $rules): void
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        $value = $this->data[$field] ?? null;

        foreach ($rules as $rule) {
            $this->applyRule($field, $value, $rule);
        }
    }

    /**
     * Aplica una regla de validación
     * 
     * @param string $field
     * @param mixed $value
     * @param string $rule
     * @return void
     */
    private function applyRule(string $field, $value, string $rule): void
    {
        $ruleParts = explode(':', $rule, 2);
        $ruleName = $ruleParts[0];
        $ruleValue = $ruleParts[1] ?? null;

        switch ($ruleName) {
            case 'required':
                $this->validateRequired($field, $value);
                break;
            case 'email':
                $this->validateEmail($field, $value);
                break;
            case 'min':
                $this->validateMin($field, $value, (int) $ruleValue);
                break;
            case 'max':
                $this->validateMax($field, $value, (int) $ruleValue);
                break;
            case 'string':
                $this->validateString($field, $value);
                break;
            case 'numeric':
                $this->validateNumeric($field, $value);
                break;
            case 'integer':
                $this->validateInteger($field, $value);
                break;
            case 'in':
                $options = explode(',', $ruleValue);
                $this->validateIn($field, $value, $options);
                break;
            case 'unique':
                $this->validateUnique($field, $value, $ruleValue);
                break;
            case 'confirmed':
                $this->validateConfirmed($field, $value);
                break;
            case 'file':
                $this->validateFile($field, $value);
                break;
            case 'image':
                $this->validateImage($field, $value);
                break;
            case 'regex':
                $this->validateRegex($field, $value, $ruleValue);
                break;
        }
    }

    /**
     * Valida que un campo sea requerido
     */
    private function validateRequired(string $field, $value): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, "El campo {$field} es requerido.");
        }
    }

    /**
     * Valida formato de email
     */
    private function validateEmail(string $field, $value): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "El campo {$field} debe ser un email válido.");
        }
    }

    /**
     * Valida longitud mínima
     */
    private function validateMin(string $field, $value, int $min): void
    {
        if ($value !== null && $value !== '') {
            if (is_string($value) && mb_strlen($value) < $min) {
                $this->addError($field, "El campo {$field} debe tener al menos {$min} caracteres.");
            } elseif (is_numeric($value) && $value < $min) {
                $this->addError($field, "El campo {$field} debe ser mayor o igual a {$min}.");
            }
        }
    }

    /**
     * Valida longitud máxima
     */
    private function validateMax(string $field, $value, int $max): void
    {
        if ($value !== null && $value !== '') {
            if (is_string($value) && mb_strlen($value) > $max) {
                $this->addError($field, "El campo {$field} no puede tener más de {$max} caracteres.");
            } elseif (is_numeric($value) && $value > $max) {
                $this->addError($field, "El campo {$field} debe ser menor o igual a {$max}.");
            }
        }
    }

    /**
     * Valida que sea string
     */
    private function validateString(string $field, $value): void
    {
        if ($value !== null && !is_string($value)) {
            $this->addError($field, "El campo {$field} debe ser texto.");
        }
    }

    /**
     * Valida que sea numérico
     */
    private function validateNumeric(string $field, $value): void
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->addError($field, "El campo {$field} debe ser numérico.");
        }
    }

    /**
     * Valida que sea entero
     */
    private function validateInteger(string $field, $value): void
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, "El campo {$field} debe ser un número entero.");
        }
    }

    /**
     * Valida que esté en una lista de valores permitidos
     */
    private function validateIn(string $field, $value, array $options): void
    {
        if ($value !== null && $value !== '' && !in_array($value, $options)) {
            $optionsList = implode(', ', $options);
            $this->addError($field, "El campo {$field} debe ser uno de: {$optionsList}.");
        }
    }

    /**
     * Valida que sea único en la base de datos
     */
    private function validateUnique(string $field, $value, string $table): void
    {
        if ($value !== null && $value !== '') {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT COUNT(*) as count FROM {$table} WHERE {$field} = ?", [$value]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                $this->addError($field, "El {$field} ya está en uso.");
            }
        }
    }

    /**
     * Valida confirmación de campo (ej: password_confirmation)
     */
    private function validateConfirmed(string $field, $value): void
    {
        $confirmationField = $field . '_confirmation';
        $confirmationValue = $this->data[$confirmationField] ?? null;
        
        if ($value !== $confirmationValue) {
            $this->addError($field, "La confirmación del campo {$field} no coincide.");
        }
    }

    /**
     * Valida archivo subido
     */
    private function validateFile(string $field, $value): void
    {
        $file = $_FILES[$field] ?? null;
        
        if ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $this->addError($field, "Error al subir el archivo {$field}.");
            }
        }
    }

    /**
     * Valida que sea una imagen
     */
    private function validateImage(string $field, $value): void
    {
        $file = $_FILES[$field] ?? null;
        
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($file['type'], $allowedTypes)) {
                $this->addError($field, "El archivo {$field} debe ser una imagen válida (JPEG, PNG, GIF, WebP).");
            }
        }
    }

    /**
     * Valida expresión regular
     */
    private function validateRegex(string $field, $value, string $pattern): void
    {
        if ($value !== null && $value !== '' && !preg_match($pattern, $value)) {
            $this->addError($field, "El campo {$field} no tiene el formato correcto.");
        }
    }

    /**
     * Agrega un error de validación
     * 
     * @param string $field
     * @param string $message
     * @return void
     */
    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }

    /**
     * Obtiene todos los errores
     * 
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtiene errores de un campo específico
     * 
     * @param string $field
     * @return array
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * Verifica si hay errores
     * 
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Verifica si un campo tiene errores
     * 
     * @param string $field
     * @return bool
     */
    public function hasFieldError(string $field): bool
    {
        return isset($this->errors[$field]) && !empty($this->errors[$field]);
    }
}
