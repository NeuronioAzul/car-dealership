<?php

namespace App\Application\UseCases;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Application\Services\JWTService;
use App\Infrastructure\Messaging\EventPublisher;

class LoginUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly JWTService $jwtService,
        private readonly EventPublisher $eventPublisher
    ) {}

    public function execute(string $email, string $password): array
    {
        // Buscar usuário por email
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new \Exception('Usuário não encontrado', 404);
        }

        if ($user->isDeleted()) {
            throw new \Exception('Usuário inativo', 403);
        }

        // Verificar senha
        if (!$user->verifyPassword($password)) {
            throw new \Exception('Credenciais inválidas', 401);
        }

        // Gerar token JWT
        $token = $this->jwtService->generateToken($user);
        $refreshToken = $this->jwtService->generateRefreshToken($user);

        // Publicar evento de login
        $this->eventPublisher->publish('auth.user_logged_in', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        return [
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'role' => $user->getRole()
            ],
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $_ENV['JWT_EXPIRATION']
        ];
    }
}
