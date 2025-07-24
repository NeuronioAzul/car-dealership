<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\ValueObjects\Address;
use App\Infrastructure\Messaging\EventPublisher;
use DateTime;

class RegisterUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly EventPublisher $eventPublisher
    ) {
    }

    public function execute(array $userData): array
    {
        // Validar se email já existe
        if ($this->userRepository->existsByEmail($userData['email'])) {
            throw new \Exception('Email já cadastrado', 409);
        }

        // Validar dados obrigatórios
        $this->validateRequiredFields($userData);

        // Criar endereço
        $address = new Address(
            $userData['address']['street'],
            $userData['address']['number'],
            $userData['address']['neighborhood'],
            $userData['address']['city'],
            $userData['address']['state'],
            $userData['address']['zip_code']
        );

        // Criar usuário
        $user = new User(
            $userData['name'],
            $userData['email'],
            $userData['password'],
            $userData['phone'],
            new DateTime($userData['birth_date']),
            $address,
            $userData['role'] ?? 'customer',
            $userData['accept_terms'] ?? false,
            $userData['accept_privacy'] ?? false,
            $userData['accept_communications'] ?? false
        );

        // Salvar usuário
        if (!$this->userRepository->save($user)) {
            throw new \Exception('Erro ao criar usuário', 500);
        }

        // Publicar evento de registro
        $this->eventPublisher->publish('auth.user_registered', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'name' => $user->getName(),
            'role' => $user->getRole(),
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        return [
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole(),
            ],
            'message' => 'Usuário criado com sucesso',
        ];
    }

    private function validateRequiredFields(array $userData): void
    {
        $required = [
            'name',
            'email',
            'password',
            'phone',
            'birth_date',
            'address',
            'accept_terms',
            'accept_privacy',
        ];

        foreach ($required as $field) {
            if (!isset($userData[$field]) || empty($userData[$field])) {
                throw new \Exception("Campo obrigatório: {$field}", 400);
            }
        }

        // Validar endereço
        $addressRequired = ['street', 'number', 'neighborhood', 'city', 'state', 'zip_code'];
        foreach ($addressRequired as $field) {
            if (!isset($userData['address'][$field]) || empty($userData['address'][$field])) {
                throw new \Exception("Campo obrigatório no endereço: {$field}", 400);
            }
        }

        // Validar aceitação de termos
        if (!$userData['accept_terms'] || !$userData['accept_privacy']) {
            throw new \Exception('É necessário aceitar os termos de uso e política de privacidade', 400);
        }

        // Validar formato do email
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Email inválido', 400);
        }

        // Validar senha
        if (strlen($userData['password']) < 8) {
            throw new \Exception('Senha deve ter pelo menos 8 caracteres', 400);
        }
    }
}
