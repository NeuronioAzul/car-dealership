<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\CustomerDTO;
use App\Domain\Repositories\CustomerRepositoryInterface;

class CreateCustomerProfileUseCase
{
    public function __construct(private readonly CustomerRepositoryInterface $customerRepository)
    {
    }

    public function execute(CustomerDTO $customer): bool
    {
        // Verifica se o cliente já existe pelo email
        if ($this->customerRepository->existsByEmail($customer->email)) {
            throw new \Exception('Cliente já cadastrado', 409);
        }

        // Salva o novo cliente no repositório
        return $this->customerRepository->save($customer);
    }
}
