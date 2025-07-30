<?php

declare(strict_types=1);

namespace App\Infrastructure\DI;

use App\Application\Services\JWTService;
use App\Application\Services\TokenBlacklistService;
use App\Application\UseCases\LoginUseCase;
use App\Application\UseCases\RegisterUseCase;
use App\Application\UseCases\LogoutUseCase;
use App\Application\Validation\RequestValidator;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Database\TokenBlacklistRepository;
use App\Infrastructure\Database\UserRepository;
use App\Infrastructure\Messaging\EventPublisher;
use PDO;

/**
 * Service Provider para configurar todas as dependências
 * 
 * Resolve o problema do Service Locator anti-pattern no AuthController
 */
class ServiceProvider
{
    public static function configure(Container $container): void
    {
        // Database Connection
        $container->singleton(PDO::class, function () {
            $host = $_ENV['DB_HOST'];
            $port = $_ENV['DB_PORT'];
            $database = $_ENV['DB_DATABASE'];
            $username = $_ENV['DB_USERNAME'];
            $password = $_ENV['DB_PASSWORD'];

            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

            return new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        });

        // Repositories
        $container->bind(UserRepositoryInterface::class, function (Container $c) {
            return new UserRepository($c->get(PDO::class));
        });

        $container->bind(TokenBlacklistRepository::class, function (Container $c) {
            return new TokenBlacklistRepository($c->get(PDO::class));
        });

        // Services
        $container->bind(TokenBlacklistService::class, function (Container $c) {
            return new TokenBlacklistService($c->get(TokenBlacklistRepository::class));
        });

        $container->bind(JWTService::class, function (Container $c) {
            return new JWTService(
                $c->get(TokenBlacklistService::class),
                $c->get(UserRepositoryInterface::class)
            );
        });

        $container->bind(EventPublisher::class, function () {
            return new EventPublisher();
        });

        $container->bind(RequestValidator::class, function () {
            return new RequestValidator();
        });

        // Use Cases
        $container->bind(LoginUseCase::class, function (Container $c) {
            return new LoginUseCase(
                $c->get(UserRepositoryInterface::class),
                $c->get(JWTService::class),
                $c->get(EventPublisher::class)
            );
        });

        $container->bind(RegisterUseCase::class, function (Container $c) {
            return new RegisterUseCase(
                $c->get(UserRepositoryInterface::class),
                $c->get(EventPublisher::class),
                $c->get(RequestValidator::class)
            );
        });

        $container->bind(LogoutUseCase::class, function (Container $c) {
            return new LogoutUseCase($c->get(JWTService::class));
        });
    }
}
