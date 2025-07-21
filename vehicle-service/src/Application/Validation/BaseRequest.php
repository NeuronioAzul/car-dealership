<?php

namespace App\Application\Validation;

abstract class BaseRequest
{
    protected array $data;
    protected array $errors = [];
    
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    abstract protected function rules(): array;
    
    abstract protected function messages(): array;
    
    public function validate(): bool
    {
        $validator = new RequestValidator($this->rules());
        $isValid = $validator->validate($this->data);
        
        if (!$isValid) {
            $this->errors = $validator->errors();
            return false;
        }
        
        return true;
    }
    
    public function validated(): array
    {
        if (!$this->validate()) {
            throw new \Exception('Validation failed: ' . json_encode($this->errors));
        }
        
        return $this->extractValidatedData();
    }
    
    public function errors(): array
    {
        return $this->errors;
    }
    
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }
    
    public function all(): array
    {
        return $this->data;
    }
    
    private function extractValidatedData(): array
    {
        $rules = $this->rules();
        $validated = [];
        
        foreach ($rules as $field => $rule) {
            if (isset($this->data[$field])) {
                $validated[$field] = $this->data[$field];
            }
        }
        
        return $validated;
    }
}
