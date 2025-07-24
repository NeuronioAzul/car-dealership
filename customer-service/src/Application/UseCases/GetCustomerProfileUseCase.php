<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Repositories\CustomerRepositoryInterface;

class GetCustomerProfileUseCase
{
    private CustomerRepositoryInterface $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function execute(string $userId): array
    {
        $customer = $this->customerRepository->findByUserId($userId);

        if (!$customer) {
            throw new \Exception('Cliente não encontrado', 404);
        }

        if ($customer->isDeleted()) {
            throw new \Exception('Cliente inativo', 403);
        }

        return $customer->toArray();
    }
}
