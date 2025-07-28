<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\Exceptions\UserCreationFailedException;
use App\Application\Exceptions\ValidationException;
use App\Domain\Entities\User;
use App\Domain\Exceptions\UserAlreadyExistsException;
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
            throw new UserAlreadyExistsException($userData['email']);
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

    private function validateRequiredFields(array $userData): void
    {
        $errors = [];
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
                $errors[] = "Campo obrigatório: {$field}";
            }
        }

        // Validar endereço
        if (isset($userData['address']) && is_array($userData['address'])) {
            $addressRequired = ['street', 'number', 'neighborhood', 'city', 'state', 'zip_code'];
            foreach ($addressRequired as $field) {
                if (!isset($userData['address'][$field]) || empty($userData['address'][$field])) {
                    $errors[] = "Campo obrigatório no endereço: {$field}";
                }
            }
        }

        // Validar aceitação de termos
        if (isset($userData['accept_terms']) && !$userData['accept_terms']) {
            $errors[] = 'É necessário aceitar os termos de uso';
        }

        if (isset($userData['accept_privacy']) && !$userData['accept_privacy']) {
            $errors[] = 'É necessário aceitar a política de privacidade';
        }

        // Validar formato do email
        if (isset($userData['email']) && !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email inválido';
        }

        // Validar senha
        if (isset($userData['password']) && strlen($userData['password']) < 8) {
            $errors[] = 'Senha deve ter pelo menos 8 caracteres';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}
