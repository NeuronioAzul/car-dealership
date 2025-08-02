<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\Exceptions\UserCreationFailedException;
use App\Application\Validation\RequestValidator;
use App\Domain\Entities\User;
use App\Domain\Exceptions\UserAlreadyExistsException;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Messaging\EventPublisher;
use DateTime;

class RegisterUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly EventPublisher $eventPublisher,
        private readonly RequestValidator $validator
    ) {
    }

    public function execute(array $userData): array
    {
        // Validar dados usando Symfony Validator
        $this->validator->validate($userData, $this->validator->getRegisterUserConstraints());

        // Validar se email já existe
        if ($this->userRepository->existsByEmail($userData['email'])) {
            throw new UserAlreadyExistsException($userData['email']);
        }

        // Criar usuário
        $user = new User(
            $userData['name'],
            $userData['email'],
            $userData['password'],
            $userData['phone'],
            new DateTime($userData['birth_date']),
            $userData['role'] ?? 'customer',
            $userData['accept_terms'] ?? false,
            $userData['accept_privacy'] ?? false,
            $userData['accept_communications'] ?? false
        );

        // Salvar usuário
        if (!$this->userRepository->save($user)) {
            throw new UserCreationFailedException('Failed to save user to repository');
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
}
