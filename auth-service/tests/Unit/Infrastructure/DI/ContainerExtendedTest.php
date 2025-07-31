<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\DI;

use App\Infrastructure\DI\Container;
use App\Application\Services\JWTService;
use App\Application\Services\TokenBlacklistService;
use App\Application\UseCases\LoginUseCase;
use App\Application\UseCases\RegisterUseCase;
use App\Application\UseCases\LogoutUseCase;
use App\Infrastructure\Database\UserRepository;
use App\Infrastructure\Database\TokenBlacklistRepository;
use App\Infrastructure\Config\JWTConfig;
use App\Application\Validation\RequestValidator;
use App\Infrastructure\Messaging\EventPublisher;
use App\Domain\Repositories\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ContainerExtendedTest extends TestCase
{
    private Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    public function test_container_initialization(): void
    {
        $this->assertInstanceOf(Container::class, $this->container);
    }

    public function test_container_has_method(): void
    {
        // Test that has method exists and works
        $this->assertTrue($this->container->has(JWTService::class));
        $this->assertTrue($this->container->has(TokenBlacklistService::class));
        $this->assertTrue($this->container->has(LoginUseCase::class));
        $this->assertFalse($this->container->has('NonExistentService'));
    }

    public function test_get_jwt_service(): void
    {
        $jwtService = $this->container->get(JWTService::class);
        
        $this->assertInstanceOf(JWTService::class, $jwtService);
    }

    public function test_get_token_blacklist_service(): void
    {
        $service = $this->container->get(TokenBlacklistService::class);
        
        $this->assertInstanceOf(TokenBlacklistService::class, $service);
    }

    public function test_get_login_use_case(): void
    {
        $useCase = $this->container->get(LoginUseCase::class);
        
        $this->assertInstanceOf(LoginUseCase::class, $useCase);
    }

    public function test_get_register_use_case(): void
    {
        $useCase = $this->container->get(RegisterUseCase::class);
        
        $this->assertInstanceOf(RegisterUseCase::class, $useCase);
    }

    public function test_get_logout_use_case(): void
    {
        $useCase = $this->container->get(LogoutUseCase::class);
        
        $this->assertInstanceOf(LogoutUseCase::class, $useCase);
    }

    public function test_get_jwt_config(): void
    {
        $config = $this->container->get(JWTConfig::class);
        
        $this->assertInstanceOf(JWTConfig::class, $config);
    }

    public function test_get_event_publisher(): void
    {
        $publisher = $this->container->get(EventPublisher::class);
        
        $this->assertInstanceOf(EventPublisher::class, $publisher);
    }

    public function test_get_request_validator(): void
    {
        $validator = $this->container->get(RequestValidator::class);
        
        $this->assertInstanceOf(RequestValidator::class, $validator);
    }

    public function test_get_user_repository(): void
    {
        $repository = $this->container->get(UserRepositoryInterface::class);
        
        $this->assertInstanceOf(UserRepository::class, $repository);
    }

    public function test_singleton_behavior(): void
    {
        // Test that singletons return the same instance
        $jwtService1 = $this->container->get(JWTService::class);
        $jwtService2 = $this->container->get(JWTService::class);
        
        $this->assertSame($jwtService1, $jwtService2);
    }

    public function test_get_method_with_unbound_service(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Service 'UnboundService' not found");
        
        $this->container->get('UnboundService');
    }

    public function test_services_property_is_private(): void
    {
        $reflection = new ReflectionClass($this->container);
        $servicesProperty = $reflection->getProperty('services');
        
        $this->assertTrue($servicesProperty->isPrivate());
    }

    public function test_instances_property_is_private(): void
    {
        $reflection = new ReflectionClass($this->container);
        $instancesProperty = $reflection->getProperty('instances');
        
        $this->assertTrue($instancesProperty->isPrivate());
    }

    public function test_register_services_method_is_private(): void
    {
        $reflection = new ReflectionClass($this->container);
        $method = $reflection->getMethod('registerServices');
        
        $this->assertTrue($method->isPrivate());
    }

    public function test_is_singleton_method_is_private(): void
    {
        $reflection = new ReflectionClass($this->container);
        $method = $reflection->getMethod('isSingleton');
        
        $this->assertTrue($method->isPrivate());
    }

    public function test_get_method_visibility(): void
    {
        $reflection = new ReflectionClass($this->container);
        $getMethod = $reflection->getMethod('get');
        
        $this->assertTrue($getMethod->isPublic());
    }

    public function test_has_method_visibility(): void
    {
        $reflection = new ReflectionClass($this->container);
        $hasMethod = $reflection->getMethod('has');
        
        $this->assertTrue($hasMethod->isPublic());
    }

    public function test_get_method_parameters(): void
    {
        $reflection = new ReflectionClass($this->container);
        $getMethod = $reflection->getMethod('get');
        $parameters = $getMethod->getParameters();
        
        $this->assertCount(1, $parameters);
        
        $serviceIdParam = $parameters[0];
        $this->assertEquals('serviceId', $serviceIdParam->getName());
        $this->assertEquals('string', $serviceIdParam->getType()->__toString());
    }

    public function test_has_method_parameters(): void
    {
        $reflection = new ReflectionClass($this->container);
        $hasMethod = $reflection->getMethod('has');
        $parameters = $hasMethod->getParameters();
        
        $this->assertCount(1, $parameters);
        
        $serviceIdParam = $parameters[0];
        $this->assertEquals('serviceId', $serviceIdParam->getName());
        $this->assertEquals('string', $serviceIdParam->getType()->__toString());
    }

    public function test_container_class_namespace(): void
    {
        $reflection = new ReflectionClass($this->container);
        
        $this->assertEquals('App\Infrastructure\DI\Container', $reflection->getName());
    }

    public function test_dependency_resolution(): void
    {
        // Test that complex dependencies are resolved correctly
        $loginUseCase = $this->container->get(LoginUseCase::class);
        
        // LoginUseCase depends on UserRepository, JWTService, and EventPublisher
        $this->assertInstanceOf(LoginUseCase::class, $loginUseCase);
        
        // Verify that JWTService is also resolvable
        $jwtService = $this->container->get(JWTService::class);
        $this->assertInstanceOf(JWTService::class, $jwtService);
    }

    public function test_constructor_calls_register_services(): void
    {
        // Test that constructor properly initializes services
        $newContainer = new Container();
        
        // If registerServices was called, these services should be available
        $this->assertTrue($newContainer->has(JWTService::class));
        $this->assertTrue($newContainer->has(LoginUseCase::class));
        $this->assertTrue($newContainer->has(RegisterUseCase::class));
        $this->assertTrue($newContainer->has(LogoutUseCase::class));
    }

    public function test_return_types(): void
    {
        $reflection = new ReflectionClass($this->container);
        
        $getMethod = $reflection->getMethod('get');
        $getReturnType = $getMethod->getReturnType();
        $this->assertEquals('object', $getReturnType->__toString());
        
        $hasMethod = $reflection->getMethod('has');
        $hasReturnType = $hasMethod->getReturnType();
        $this->assertEquals('bool', $hasReturnType->__toString());
    }

    public function test_all_registered_services_are_gettable(): void
    {
        $serviceIds = [
            JWTService::class,
            TokenBlacklistService::class,
            LoginUseCase::class,
            RegisterUseCase::class,
            LogoutUseCase::class,
            JWTConfig::class,
            EventPublisher::class,
            RequestValidator::class,
            UserRepositoryInterface::class,
        ];
        
        foreach ($serviceIds as $serviceId) {
            $this->assertTrue($this->container->has($serviceId), "Service {$serviceId} should be registered");
            $service = $this->container->get($serviceId);
            $this->assertNotNull($service, "Service {$serviceId} should be instantiable");
        }
    }

    public function test_database_service_registration(): void
    {
        // Test that database service is registered
        $this->assertTrue($this->container->has('database'));
    }

    public function test_token_blacklist_repository_registration(): void
    {
        // Test that token blacklist repository is registered
        $this->assertTrue($this->container->has('token_blacklist_repository'));
    }

    public function test_exception_message_format(): void
    {
        $serviceName = 'NonExistentService';
        
        try {
            $this->container->get($serviceName);
            $this->fail('Expected InvalidArgumentException was not thrown');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString($serviceName, $e->getMessage());
            $this->assertStringContainsString('not found in container', $e->getMessage());
        }
    }
}
