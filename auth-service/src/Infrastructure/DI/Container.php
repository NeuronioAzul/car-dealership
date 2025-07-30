<?php

declare(strict_types=1);

namespace App\Infrastructure\DI;

use App\Application\Services\JWTService;
use App\Application\Services\TokenBlacklistService;
use App\Application\UseCases\LoginUseCase;
use App\Application\UseCases\LogoutUseCase;
use App\Application\UseCases\RegisterUseCase;
use App\Application\Validation\RequestValidator;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Config\JWTConfig;
use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\Database\TokenBlacklistRepository;
use App\Infrastructure\Database\UserRepository;
use App\Infrastructure\Messaging\EventPublisher;
use PDO;

/**
 * Simple Dependency Injection Container
 */
class Container
{
    private array $services = [];
    private array $instances = [];

    public function __construct()
    {
        $this->registerServices();
    }

    /**
     * Get a service from the container
     */
    public function get(string $serviceId): object
    {
        if (isset($this->instances[$serviceId])) {
            return $this->instances[$serviceId];
        }

        if (!isset($this->services[$serviceId])) {
            throw new \InvalidArgumentException("Service '{$serviceId}' not found in container");
        }

        $factory = $this->services[$serviceId];
        $service = $factory($this);

        // Store singletons
        if ($this->isSingleton($serviceId)) {
            $this->instances[$serviceId] = $service;
        }

        return $service;
    }

    /**
     * Check if a service exists in the container
     */
    public function has(string $serviceId): bool
    {
        return isset($this->services[$serviceId]);
    }

    /**
     * Register all services
     */
    private function registerServices(): void
    {
        // Database connection (singleton)
        $this->services['database'] = function (): PDO {
            return DatabaseConfig::getConnection();
        };

        // Repositories
        $this->services[UserRepositoryInterface::class] = function (Container $container): UserRepository {
            return new UserRepository($container->get('database'));
        };

        $this->services['token_blacklist_repository'] = function (Container $container): TokenBlacklistRepository {
            return new TokenBlacklistRepository($container->get('database'));
        };

        // Services
        $this->services[JWTConfig::class] = function (): JWTConfig {
            return new JWTConfig();
        };

        $this->services[EventPublisher::class] = function (): EventPublisher {
            return new EventPublisher();
        };

        $this->services[RequestValidator::class] = function (): RequestValidator {
            return new RequestValidator();
        };

        $this->services[TokenBlacklistService::class] = function (Container $container): TokenBlacklistService {
            return new TokenBlacklistService($container->get('token_blacklist_repository'));
        };

        $this->services[JWTService::class] = function (Container $container): JWTService {
            return new JWTService(
                $container->get(JWTConfig::class),
                $container->get(TokenBlacklistService::class),
                $container->get(UserRepositoryInterface::class)
            );
        };

        // Use Cases
        $this->services[LoginUseCase::class] = function (Container $container): LoginUseCase {
            return new LoginUseCase(
                $container->get(UserRepositoryInterface::class),
                $container->get(JWTService::class),
                $container->get(EventPublisher::class)
            );
        };

        $this->services[RegisterUseCase::class] = function (Container $container): RegisterUseCase {
            return new RegisterUseCase(
                $container->get(UserRepositoryInterface::class),
                $container->get(EventPublisher::class),
                $container->get(RequestValidator::class)
            );
        };

        $this->services[LogoutUseCase::class] = function (Container $container): LogoutUseCase {
            return new LogoutUseCase($container->get(JWTService::class));
        };
    }

    /**
     * Define which services should be singletons
     */
    private function isSingleton(string $serviceId): bool
    {
        $singletons = [
            'database',
            UserRepositoryInterface::class,
            'token_blacklist_repository',
            JWTConfig::class,
            EventPublisher::class,
            RequestValidator::class,
            TokenBlacklistService::class,
            JWTService::class,
        ];

        return in_array($serviceId, $singletons, true);
    }
}
