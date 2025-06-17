<?php

namespace App\Application\UseCases;

use App\Domain\Repositories\CustomerRepositoryInterface;

class GetCustomerProfileUseCase
{
    private CustomerRepositoryInterface $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function execute(string $customerId): array
    {
        $customer = $this->customerRepository->findById($customerId);

        if (!$customer) {
            throw new \Exception('Cliente não encontrado', 404);
        }

        if ($customer->isDeleted()) {
            throw new \Exception('Cliente inativo', 403);
        }

        return $customer->toArray();
    }
}

