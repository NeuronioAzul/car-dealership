<?php

declare(strict_types=1);

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
                    $this->errors[$field][] = 'O campo é obrigatório.';
                }

                if ($rule === 'email' && isset($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = 'Formato de email inválido.';
                }

                if (str_starts_with($rule, 'min:')) {
                    $min = (int) explode(':', $rule)[1];

                    if (isset($data[$field]) && strlen($data[$field]) < $min) {
                        $this->errors[$field][] = "Mínimo de $min caracteres.";
                    }
                }

                if (str_starts_with($rule, 'max:')) {
                    $max = (int) explode(':', $rule)[1];

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

                if ($rule === 'uuid' && isset($data[$field]) && !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $data[$field])) {
                    $this->errors[$field][] = 'Formato de UUID inválido.';
                }

                if ($rule === 'phone' && isset($data[$field]) && !preg_match('/^\+?[0-9\s\-\(\)]+$/', $data[$field])) {
                    $this->errors[$field][] = 'Formato de telefone inválido.';
                }

                if ($rule === 'zip_code' && isset($data[$field]) && !preg_match('/^\d{5}-\d{3}$/', $data[$field])) {
                    $this->errors[$field][] = 'Formato de CEP inválido. Precisa ser no formato XXXXX-XXX.';
                }

                if ($rule === 'boolean' && isset($data[$field]) && !is_bool($data[$field])) {
                    $this->errors[$field][] = 'O campo deve ser verdadeiro ou falso.';
                }

                if ($rule === 'accept_terms' && (!isset($data[$field]) || !$data[$field])) {
                    $this->errors[$field][] = 'É necessário aceitar os termos de uso.';
                }

                if ($rule === 'accept_privacy' && (!isset($data[$field]) || !$data[$field])) {
                    $this->errors[$field][] = 'É necessário aceitar a política de privacidade.';
                }

                if ($rule === 'accept_communications' && (!isset($data[$field]) || !$data[$field])) {
                    $this->errors[$field][] = 'É necessário aceitar as comunicações.';
                }

                if ($rule === 'role' && isset($data[$field]) && !in_array($data[$field], ['admin', 'customer'])) {
                    $this->errors[$field][] = 'O papel deve ser "admin" ou "customer".';
                }

                if ($rule === 'password' && isset($data[$field])) {
                    if (strlen($data[$field]) < 8) {
                        $this->errors[$field][] = 'A senha deve ter pelo menos 8 caracteres.';
                    }

                    if (!preg_match('/[A-Z]/', $data[$field])) {
                        $this->errors[$field][] = 'A senha deve conter pelo menos uma letra maiúscula.';
                    }

                    if (!preg_match('/[a-z]/', $data[$field])) {
                        $this->errors[$field][] = 'A senha deve conter pelo menos uma letra minúscula.';
                    }

                    if (!preg_match('/[0-9]/', $data[$field])) {
                        $this->errors[$field][] = 'A senha deve conter pelo menos um número.';
                    }
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
