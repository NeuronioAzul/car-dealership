<?php

namespace App\Application\UseCases;

use App\Domain\Entities\Customer;
use App\Domain\ValueObjects\Address;
use App\Domain\Repositories\CustomerRepositoryInterface;
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
    ) {}

    public function execute(string $customerId, array $updateData): array
    {
        $customer = $this->customerRepository->findById($customerId);

        if (!$customer) {
            throw new \Exception('Cliente não encontrado', 404);
        }

        if ($customer->isDeleted()) {
            throw new \Exception('Cliente inativo', 403);
        }

        // Atualizar dados do cliente
        if (isset($updateData['name'])) {
            $customer->setFullName($updateData['name']);
        }

        if (isset($updateData['email'])) {
            // Verificar se email já existe para outro cliente
            $existingCustomer = $this->customerRepository->findByEmail($updateData['email']);
            if ($existingCustomer && $existingCustomer->getId() !== $customerId) {
                throw new \Exception('Email já está em uso por outro cliente', 409);
            }
            $customer->setEmail($updateData['email']);
        }

        if (isset($updateData['phone'])) {
            $customer->setPhone($updateData['phone']);
        }

        if (isset($updateData['birth_date'])) {
            $customer->setBirthDate(new DateTime($updateData['birth_date']));
        }

        if (isset($updateData['address'])) {
            $address = new Address(
                $updateData['address']['street'],
                $updateData['address']['number'],
                $updateData['address']['neighborhood'],
                $updateData['address']['complement'] ?? null,
                $updateData['address']['city'],
                $updateData['address']['state'],
                $updateData['address']['zip_code']
            );
            $customer->setAddress($address);
        }

        // Salvar alterações
        if (!$this->customerRepository->update($customer)) {
            throw new \Exception('Erro ao atualizar perfil do cliente', 500);
        }

        // Publicar evento de atualização
        $this->eventPublisher->publish('customer.profile_updated', [
            'customer_id' => $customer->getId(),
            'email' => $customer->getEmail(),
            'name' => $customer->getFullName(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        return $customer->toArray();
    }
}
