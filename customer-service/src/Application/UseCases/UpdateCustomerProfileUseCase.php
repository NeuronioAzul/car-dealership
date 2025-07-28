<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\CustomerDTO;
use App\Domain\Repositories\CustomerRepositoryInterface;
use App\Domain\ValueObjects\CustomerAddress;
use App\Infrastructure\Messaging\EventPublisher;
use DateTime;

class UpdateCustomerProfileUseCase
{
    /**
     * Use Case para atualizar o perfil do cliente.
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param EventPublisher $eventPublisher
     */
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly EventPublisher $eventPublisher
    ) {
    }

    public function execute(string $customerId, CustomerDTO $customerData): array
    {
        $customer = $this->customerRepository->findById($customerId);

        if (!$customer) {
            throw new \Exception('Cliente não encontrado', 404);
        }

        if ($customer->isDeleted()) {
            throw new \Exception('Cliente inativo', 403);
        }

        if (isset($customerData->email)) {
            // Verificar se email já existe para outro cliente
            $existingCustomer = $this->customerRepository->findByEmail($customerData->email);

            if ($existingCustomer && $existingCustomer->id !== $customerId) {
                throw new \Exception('Email já está em uso por outro cliente', 409);
            }
        }

        // Salvar alterações
        if (!$this->customerRepository->update($customer)) {
            throw new \Exception('Erro ao atualizar perfil do cliente', 500);
        }

        // Publicar evento de atualização
        $this->eventPublisher->publish('customer.profile_updated', [
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'name' => $customer->fullName,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        return $customer->toArray();
    }
}
