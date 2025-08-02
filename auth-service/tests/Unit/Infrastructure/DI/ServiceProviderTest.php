<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\DI;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\DI\ServiceProvider;
use App\Infrastructure\DI\Container;
use App\Infrastructure\Config\JWTConfig;
use App\Application\Services\JWTService;
use App\Application\Services\TokenBlacklistService;
use App\Application\Validation\RequestValidator;
use App\Infrastructure\Messaging\EventPublisher;
use App\Application\UseCases\LoginUseCase;
use App\Application\UseCases\RegisterUseCase;
use App\Application\UseCases\LogoutUseCase;
use PDO;

class ServiceProviderTest extends TestCase
{
    private Container $container;
    private ServiceProvider $serviceProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->serviceProvider = new ServiceProvider();
    }

    public function test_configure_method_exists(): void
    {
        $this->assertTrue(method_exists(ServiceProvider::class, 'configure'));
        
        $reflection = new \ReflectionClass(ServiceProvider::class);
        $method = $reflection->getMethod('configure');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    public function test_configure_method_parameters(): void
    {
        $reflection = new \ReflectionClass(ServiceProvider::class);
        $method = $reflection->getMethod('configure');
        $parameters = $method->getParameters();
        
        $this->assertCount(1, $parameters);
        $this->assertEquals('container', $parameters[0]->getName());
    }

    public function test_configure_creates_service_bindings(): void
    {
        $container = new Container();
        
        // O container já deve ter os serviços registrados automaticamente
        $this->assertTrue($container->has('database'));
        $this->assertTrue($container->has(\App\Domain\Repositories\UserRepositoryInterface::class));
        $this->assertTrue($container->has(\App\Application\Services\JWTService::class));
        
        // O configure deve validar se os serviços estão presentes
        ServiceProvider::configure($container);
        
        // Verificar que ainda estão disponíveis após a configuração
        $this->assertTrue($container->has('database'));
        $this->assertTrue($container->has(\App\Domain\Repositories\UserRepositoryInterface::class));
        $this->assertTrue($container->has(\App\Application\Services\JWTService::class));
    }

    public function test_service_provider_class_structure(): void
    {
        $reflection = new \ReflectionClass(ServiceProvider::class);
        
        // Test class properties
        $this->assertTrue($reflection->isInstantiable());
        $this->assertFalse($reflection->isAbstract());
        $this->assertFalse($reflection->isFinal());
        
        // Test that it has expected methods
        $this->assertTrue($reflection->hasMethod('configure'));
        
        // Test method count (should have at least the configure method)
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        $this->assertGreaterThanOrEqual(1, count($methods));
    }

    public function test_service_provider_registration_flow(): void
    {
        $container = new Container();
        
        // Verificar que os serviços já estão registrados pelo Container
        $requiredServices = ServiceProvider::getRequiredServices();
        
        foreach ($requiredServices as $service) {
            $this->assertTrue($container->has($service), "Service '{$service}' should be registered");
        }
        
        // Configurar com ServiceProvider deve validar sem erros
        ServiceProvider::configure($container);
        
        // Todos os serviços ainda devem estar disponíveis
        foreach ($requiredServices as $service) {
            $this->assertTrue($container->has($service), "Service '{$service}' should still be available after configure");
        }
    }

    public function test_service_provider_can_be_instantiated(): void
    {
        $provider = new ServiceProvider();
        $this->assertInstanceOf(ServiceProvider::class, $provider);
    }

    public function test_service_provider_namespace(): void
    {
        $reflection = new \ReflectionClass(ServiceProvider::class);
        $this->assertEquals('App\Infrastructure\DI\ServiceProvider', $reflection->getName());
        $this->assertEquals('App\Infrastructure\DI', $reflection->getNamespaceName());
        $this->assertEquals('ServiceProvider', $reflection->getShortName());
    }

    public function test_service_provider_dependencies_structure(): void
    {
        // Test that ServiceProvider doesn't have unexpected dependencies
        $reflection = new \ReflectionClass(ServiceProvider::class);
        $constructor = $reflection->getConstructor();
        
        // Constructor should not require parameters (if it exists)
        if ($constructor) {
            $parameters = $constructor->getParameters();
            $this->assertEmpty($parameters);
        }
    }

    public function test_configure_handles_missing_environment(): void
    {
        $container = new Container();
        
        // O ServiceProvider atual não depende de variáveis de ambiente para validação
        // Ele apenas verifica se os serviços estão registrados
        ServiceProvider::configure($container);
        
        // Verificar se pelo menos alguns serviços básicos estão disponíveis
        $this->assertTrue($container->has(\App\Domain\Repositories\UserRepositoryInterface::class));
        $this->assertTrue($container->has(\App\Application\Services\JWTService::class));
    }
    
    public function test_get_required_services_returns_array(): void
    {
        $services = ServiceProvider::getRequiredServices();
        
        $this->assertIsArray($services);
        $this->assertNotEmpty($services);
        
        // Verificar se contém alguns serviços essenciais
        $this->assertContains('database', $services);
        $this->assertContains(\App\Domain\Repositories\UserRepositoryInterface::class, $services);
        $this->assertContains(\App\Application\Services\JWTService::class, $services);
        $this->assertContains(\App\Application\UseCases\LoginUseCase::class, $services);
    }

    public function test_configure_with_valid_container(): void
    {
        $container = new Container();
        
        // Deve executar sem exceção
        ServiceProvider::configure($container);
        
        // Verificar que os serviços estão disponíveis
        $services = ServiceProvider::getRequiredServices();
        foreach ($services as $service) {
            $this->assertTrue($container->has($service), "Service '{$service}' should be available");
        }
    }
}
