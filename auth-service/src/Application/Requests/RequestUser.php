<?php

declare(strict_types=1);

namespace App\Application\Requests;

use App\Application\Validation\RequestValidator;

class RequestUser
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
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'phone' => 'required',
            'birth_date' => 'required|date_format:Y-m-d',
            'address.street' => 'required|max:255',
            'address.number' => 'required|max:10',
            'address.neighborhood' => 'required|max:100',
            'address.city' => 'required|max:100',
            'address.state' => 'required|uf',
            'address.zip_code' => 'required|zip_code|max:10',
            'role' => 'nullable|in:customer,admin',
            'accept_terms' => 'required|boolean',
            'accept_privacy' => 'required|boolean',
            'accept_communications' => 'required|boolean',
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
