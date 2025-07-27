<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Repositories\UserRepositoryInterface;

class DeleteUserUseCase
{
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    public function execute(string $userId): bool
    {
        // Validar o ID do usuário
        if (empty($userId) || !\Ramsey\Uuid\Uuid::isValid($userId)) {
            throw new \InvalidArgumentException('ID do usuário inválido');
        }

        // Marca o usuário como deletado
        return $this->userRepository->delete($userId);
    }
}
