<?php

namespace App\Application\UseCases;
use App\Domain\Entities\Customer;
use App\Domain\Repositories\CustomerRepositoryInterface;
class CreateCustomerProfileUseCase
{
    private CustomerRepositoryInterface $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function execute(Customer $customer): bool
    {
        // Verifica se o cliente já existe pelo email
        if ($this->customerRepository->existsByEmail($customer->getEmail())) {
            throw new \Exception('Cliente já cadastrado', 409);
        }

        // Salva o novo cliente no repositório
        return $this->customerRepository->save($customer);
    }
}