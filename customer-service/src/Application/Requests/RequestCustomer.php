<?php

namespace App\Application\Requests;

use App\Application\Validation\RequestValidator;

class RequestCustomer
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->validate();
    }

    private function validate(): void
    {
        $validator = new RequestValidator([
            'user_id' => 'required|uuid',
            'full_name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|phone|max:20',
            'birth_date' => 'required|date_format:Y-m-d',
            'address.street' => 'required|max:255',
            'address.number' => 'required|max:20',
            'address.neighborhood' => 'required|max:100',
            'address.city' => 'required|max:100',
            'address.state' => 'required|uf',
            'address.zip_code' => 'required|zip_code|max:10',
            'cpf' => 'required|numeric|cpf|max:11',
            'rg' => 'nullable|max:20',
            'gender' => 'nullable|in:M,F,Other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed,common_law',
            'occupation' => 'nullable|max:255',
            'company' => 'nullable|max:255',
            'monthly_income' => 'nullable|numeric|min:0',
            'preferred_contact' => 'nullable|in:email,phone,whatsapp',
            'newsletter_subscription' => 'nullable|boolean',
            'sms_notifications' => 'nullable',
            'total_purchases' => 'nullable|numeric|min:0',
            'total_spent' => 'nullable|numeric|min:0',
            'last_purchase_date' => 'nullable|date_format:Y-m-d',
            'customer_score' => 'nullable|numeric|min:0|max:1000',
            'customer_tier' => 'nullable|in:bronze,silver,gold,platinum',
            'address.complement' => 'nullable|max:100',
            'accept_terms' => 'required|numeric|in:1,0',
            'accept_privacy' => 'required|numeric|in:1,0',
            'accept_communications' => 'required|numeric|in:1,0',
        ]);
        
        if (!$validator->validate($this->data)) {
            $this->errors = $validator->errors();
        }
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function all(): array
    {
        return $this->data;
    }
}