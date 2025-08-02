<?php

declare(strict_types=1);

namespace App\Infrastructure\DI;

/**
 * Service Provider para configurar todas as dependências
 * 
 * Esta classe demonstra um padrão alternativo de DI, mas o Container atual
 * já tem suas próprias configurações no método registerServices()
 */
class ServiceProvider
{
    /**
     * Configura um container (dummy implementation para compatibilidade)
     * 
     * O Container atual já registra todos os serviços internamente,
     * então este método serve apenas como demonstração de API alternativa.
     */
    public static function configure(Container $container): void
    {
        // O Container atual já registra todos os serviços automaticamente
        // Este método existe apenas para demonstração da API alternativa
        
        // Verificar se o container tem os serviços principais registrados
        $requiredServices = [
            'database',
            \App\Domain\Repositories\UserRepositoryInterface::class,
            \App\Application\Services\JWTService::class,
            \App\Application\UseCases\LoginUseCase::class,
            \App\Application\UseCases\RegisterUseCase::class,
            \App\Application\UseCases\LogoutUseCase::class,
        ];
        
        // Validar se todos os serviços essenciais estão disponíveis
        foreach ($requiredServices as $service) {
            if (!$container->has($service)) {
                throw new \RuntimeException("Required service '{$service}' is not registered in container");
            }
        }
    }
    
    /**
     * Lista dos serviços que devem estar disponíveis no container
     */
    public static function getRequiredServices(): array
    {
        return [
            'database',
            \App\Domain\Repositories\UserRepositoryInterface::class,
            \App\Application\Services\JWTService::class,
            \App\Application\Services\TokenBlacklistService::class,
            \App\Application\UseCases\LoginUseCase::class,
            \App\Application\UseCases\RegisterUseCase::class,
            \App\Application\UseCases\LogoutUseCase::class,
            \App\Application\Validation\RequestValidator::class,
            \App\Infrastructure\Messaging\EventPublisher::class,
            \App\Infrastructure\Config\JWTConfig::class,
        ];
    }
}
