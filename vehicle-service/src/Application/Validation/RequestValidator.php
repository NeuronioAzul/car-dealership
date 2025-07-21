<?php

namespace App\Application\Validation;

class RequestValidator
{
    private array $rules;
    private array $errors = [];

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public function validate(array $data): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $rules = explode('|', $fieldRules);

            foreach ($rules as $rule) {
                
                if (str_contains($field, '.')) {
                    // Handle nested fields
                    $nestedFields = explode('.', $field);
                    $nestedData = $data;
                    foreach ($nestedFields as $nestedField) {
                        if (isset($nestedData[$nestedField])) {
                            $nestedData = $nestedData[$nestedField];
                        } else {
                            $nestedData = null;
                            break;
                        }
                    }
                    $data[$field] = $nestedData;
                }

                // Verifica se o campo é um campo aninhado, como 'address.street'
                if ($rule === 'required' && (!isset($data[$field]) || $data[$field] === '')) {
                    $this->errors[$field][] = 'O campo ' . $field . ' é obrigatório.';
                }
                if ($rule === 'email' && isset($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = 'Formato de email inválido.';
                }
                if (str_starts_with($rule, 'min:')) {
                    $min = (int)explode(':', $rule)[1];
                    if (isset($data[$field]) && strlen($data[$field]) < $min) {
                        $this->errors[$field][] = "Mínimo de $min caracteres.";
                    }
                }
                if (str_starts_with($rule, 'max:')) {
                    $max = (int)explode(':', $rule)[1];
                    if (isset($data[$field]) && strlen($data[$field]) > $max) {
                        $this->errors[$field][] = "Máximo de $max caracteres.";
                    }
                }
                // Adicione mais regras conforme necessário

                if ($rule === 'boolean' && isset($data[$field]) && !is_bool($data[$field])) {
                    $this->errors[$field][] = 'O campo deve ser verdadeiro ou falso.';
                }
                if ($rule === 'date' && isset($data[$field]) && !strtotime($data[$field])) {
                    $this->errors[$field][] = 'Formato de data inválido. Use o formato YYYY-MM-DD.';
                }
                if ($rule === 'array' && isset($data[$field]) && !is_array($data[$field])) {
                    $this->errors[$field][] = 'O campo deve ser um array.';
                }
                if ($rule === 'numeric' && isset($data[$field]) && !is_numeric($data[$field])) {
                    $this->errors[$field][] = 'O campo deve ser numérico.';
                }
                if ($rule === 'integer' && isset($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_INT)) {
                    $this->errors[$field][] = 'O campo deve ser um número inteiro.';
                }
                if ($rule === 'string' && isset($data[$field]) && !is_string($data[$field])) {
                    $this->errors[$field][] = 'O campo deve ser texto.';
                }
                if ($rule === 'uuid' && isset($data[$field]) && !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $data[$field])) {
                    $this->errors[$field][] = 'Formato de UUID inválido.';
                }
                
                // Validações específicas para veículos
                if (str_starts_with($rule, 'year_range:')) {
                    $range = explode(',', substr($rule, 11));
                    $min = (int)$range[0];
                    $max = (int)($range[1] ?? date('Y'));
                    if (isset($data[$field]) && ((int)$data[$field] < $min || (int)$data[$field] > $max)) {
                        $this->errors[$field][] = "O ano deve estar entre $min e $max.";
                    }
                }
                
                if ($rule === 'positive' && isset($data[$field]) && (float)$data[$field] <= 0) {
                    $this->errors[$field][] = 'O valor deve ser positivo.';
                }
                
                if ($rule === 'vehicle_status' && isset($data[$field]) && !in_array($data[$field], ['available', 'reserved', 'sold'])) {
                    $this->errors[$field][] = 'Status deve ser: available, reserved ou sold.';
                }
                
                if ($rule === 'fuel_type' && isset($data[$field]) && !in_array($data[$field], ['Gasolina', 'Etanol', 'Flex', 'Diesel', 'Hibrido', 'Eletrico'])) {
                    $this->errors[$field][] = 'Tipo de combustível inválido.';
                }
                
                if ($rule === 'transmission' && isset($data[$field]) && !in_array($data[$field], ['Manual', 'Automatico', 'CVT'])) {
                    $this->errors[$field][] = 'Tipo de transmissão inválido.';
                }

                # if has in: options
                if (str_starts_with($rule, 'in:')) {
                    $options = explode(',', substr($rule, 3));
                    if (isset($data[$field]) && !in_array($data[$field], $options)) {
                        $this->errors[$field][] = 'O campo deve ser um dos seguintes valores: ' . implode(', ', $options) . '.';
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
