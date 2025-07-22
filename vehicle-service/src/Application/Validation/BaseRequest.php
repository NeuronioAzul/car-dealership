<?php

namespace App\Application\Validation;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class BaseRequest
{
    protected array $data;
    protected array $errors = [];
    protected ValidatorInterface $validator;
    
    public function __construct(array $data)
    {
        $this->data = $data;
        $this->validator = Validation::createValidator();
    }
    
    abstract protected function constraints(): array;
    
    public function validate(): bool
    {
        $constraints = new Assert\Collection($this->constraints());
        $violations = $this->validator->validate($this->data, $constraints);
        
        if (count($violations) > 0) {
            $this->errors = $this->formatViolations($violations);
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
    
    private function formatViolations(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        
        foreach ($violations as $violation) {
            $field = trim($violation->getPropertyPath(), '[]');
            $errors[$field][] = $violation->getMessage();
        }
        
        return $errors;
    }
    
    private function extractValidatedData(): array
    {
        $constraints = $this->constraints();
        $validated = [];
        
        foreach ($constraints as $field => $constraint) {
            if (isset($this->data[$field])) {
                $validated[$field] = $this->data[$field];
            }
        }
        
        return $validated;
    }
}
