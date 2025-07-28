<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\DeleteUserUseCase;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\Database\UserRepository;
use App\Presentation\Middleware\AuthMiddleware;

class UserController
{
    private UserRepositoryInterface $userRepository;
    private DeleteUserUseCase $deleteUserUseCase;
    private AuthMiddleware $authMiddleware;

    public function __construct()
    {
        $database = DatabaseConfig::getConnection();
        $this->userRepository = new UserRepository($database);
        $this->deleteUserUseCase = new DeleteUserUseCase($this->userRepository);
        $this->authMiddleware = new AuthMiddleware();
    }

    // Implementação do UserController
    // Este controller pode incluir métodos para gerenciar usuários, como criar, atualizar, deletar e listar usuários.

    // Exemplo de método para listar usuários
    public function delete(string $id): void
    {
        $user = $this->authMiddleware->authenticate();

        try {
            // Somente Admin ou o próprio usuário podem deletar um usuário
            if ($user['role'] !== 'admin' && $user['id'] !== $id) {
                http_response_code(403); // Forbidden
                echo json_encode(['error' => 'Acesso negado']);

                return;
            }

            // Chamar o caso de uso para deletar o usuário
            if ($this->deleteUserUseCase->execute($id)) {
                http_response_code(204); // No Content
            } else {
                http_response_code(404); // Not Found
                echo json_encode(['error' => 'Usuário não encontrado']);
            }
        } catch (\Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
