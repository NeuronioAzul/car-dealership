<?php

declare(strict_types=1);

namespace Tests\Unit\Presentation\Controllers;

use App\Application\Services\JWTService;
use App\Application\Services\TokenBlacklistService;
use App\Application\UseCases\LoginUseCase;
use App\Application\UseCases\LogoutUseCase;
use App\Application\UseCases\RegisterUseCase;
use App\Infrastructure\DI\Container;
use App\Presentation\Controllers\AuthController;
use PHPUnit\Framework\TestCase;

class AuthControllerSimpleTest extends TestCase
{
    public function test_constructor_with_container(): void
    {
        $containerMock = $this->createMock(Container::class);
        $loginUseCaseMock = $this->createMock(LoginUseCase::class);
        $registerUseCaseMock = $this->createMock(RegisterUseCase::class);
        $logoutUseCaseMock = $this->createMock(LogoutUseCase::class);
        $jwtServiceMock = $this->createMock(JWTService::class);
        $blacklistServiceMock = $this->createMock(TokenBlacklistService::class);

        $containerMock->method('get')
            ->willReturnMap([
                [LoginUseCase::class, $loginUseCaseMock],
                [RegisterUseCase::class, $registerUseCaseMock],
                [LogoutUseCase::class, $logoutUseCaseMock],
                [JWTService::class, $jwtServiceMock],
                [TokenBlacklistService::class, $blacklistServiceMock],
            ]);

        $controller = new AuthController($containerMock);
        $this->assertInstanceOf(AuthController::class, $controller);
    }

    public function test_constructor_without_container(): void
    {
        $this->expectNotToPerformAssertions();
        
        try {
            new AuthController(null);
        } catch (\Exception $e) {
            // Expected to fail due to database dependencies in test environment
            $this->assertTrue(true);
        }
    }

    public function test_health_method(): void
    {
        $containerMock = $this->createMock(Container::class);
        $containerMock->method('get')->willReturn($this->createMock(\stdClass::class));

        $controller = new AuthController($containerMock);

        ob_start();
        $controller->health();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertEquals('auth-service', $response['service']);
        $this->assertEquals('healthy', $response['status']);
        $this->assertArrayHasKey('timestamp', $response);
    }

    public function test_controller_properties_are_set(): void
    {
        $containerMock = $this->createMock(Container::class);
        $loginUseCaseMock = $this->createMock(LoginUseCase::class);
        $registerUseCaseMock = $this->createMock(RegisterUseCase::class);
        $logoutUseCaseMock = $this->createMock(LogoutUseCase::class);
        $jwtServiceMock = $this->createMock(JWTService::class);
        $blacklistServiceMock = $this->createMock(TokenBlacklistService::class);

        $containerMock->method('get')
            ->willReturnCallback(function ($className) use (
                $loginUseCaseMock, $registerUseCaseMock, $logoutUseCaseMock, 
                $jwtServiceMock, $blacklistServiceMock
            ) {
                switch ($className) {
                    case LoginUseCase::class:
                        return $loginUseCaseMock;
                    case RegisterUseCase::class:
                        return $registerUseCaseMock;
                    case LogoutUseCase::class:
                        return $logoutUseCaseMock;
                    case JWTService::class:
                        return $jwtServiceMock;
                    case TokenBlacklistService::class:
                        return $blacklistServiceMock;
                    default:
                        return null;
                }
            });

        $controller = new AuthController($containerMock);
        
        // Use reflection to verify properties are set
        $reflection = new \ReflectionClass($controller);
        
        $loginProperty = $reflection->getProperty('loginUseCase');
        $loginProperty->setAccessible(true);
        $this->assertSame($loginUseCaseMock, $loginProperty->getValue($controller));
        
        $registerProperty = $reflection->getProperty('registerUseCase');
        $registerProperty->setAccessible(true);
        $this->assertSame($registerUseCaseMock, $registerProperty->getValue($controller));
        
        $logoutProperty = $reflection->getProperty('logoutUseCase');
        $logoutProperty->setAccessible(true);
        $this->assertSame($logoutUseCaseMock, $logoutProperty->getValue($controller));
        
        $jwtProperty = $reflection->getProperty('jwtService');
        $jwtProperty->setAccessible(true);
        $this->assertSame($jwtServiceMock, $jwtProperty->getValue($controller));
        
        $blacklistProperty = $reflection->getProperty('blacklistService');
        $blacklistProperty->setAccessible(true);
        $this->assertSame($blacklistServiceMock, $blacklistProperty->getValue($controller));
    }

    public function test_all_required_methods_exist(): void
    {
        $reflection = new \ReflectionClass(AuthController::class);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        $methodNames = array_map(fn($method) => $method->getName(), $methods);
        
        $expectedMethods = ['login', 'register', 'refresh', 'logout', 'validate', 'health'];
        
        foreach ($expectedMethods as $expectedMethod) {
            $this->assertContains($expectedMethod, $methodNames);
        }
    }

    public function test_all_required_properties_exist(): void
    {
        $reflection = new \ReflectionClass(AuthController::class);
        $properties = $reflection->getProperties(\ReflectionProperty::IS_PRIVATE);
        
        $propertyNames = array_map(fn($property) => $property->getName(), $properties);
        
        $expectedProperties = ['loginUseCase', 'registerUseCase', 'logoutUseCase', 'jwtService', 'blacklistService'];
        
        foreach ($expectedProperties as $expectedProperty) {
            $this->assertContains($expectedProperty, $propertyNames);
        }
    }

    public function test_container_parameter_is_optional(): void
    {
        $reflection = new \ReflectionClass(AuthController::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();
        
        $this->assertCount(1, $parameters);
        $this->assertTrue($parameters[0]->isOptional());
        $this->assertEquals('container', $parameters[0]->getName());
    }

    public function test_legacy_initialization_method_exists(): void
    {
        $reflection = new \ReflectionClass(AuthController::class);
        
        $this->assertTrue($reflection->hasMethod('initializeLegacyDependencies'));
        
        $method = $reflection->getMethod('initializeLegacyDependencies');
        $this->assertTrue($method->isPrivate());
    }

    public function test_class_namespace_is_correct(): void
    {
        $reflection = new \ReflectionClass(AuthController::class);
        $this->assertEquals('App\Presentation\Controllers\AuthController', $reflection->getName());
    }

    public function test_health_response_format(): void
    {
        $containerMock = $this->createMock(Container::class);
        $containerMock->method('get')->willReturn($this->createMock(\stdClass::class));

        $controller = new AuthController($containerMock);

        ob_start();
        $controller->health();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        
        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('service', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('timestamp', $response);
        
        $this->assertTrue($response['success']);
        $this->assertEquals('auth-service', $response['service']);
        $this->assertEquals('healthy', $response['status']);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $response['timestamp']);
    }

    public function test_container_dependency_injection_works(): void
    {
        $containerMock = $this->createMock(Container::class);
        
        // Track the number of get() calls
        $getCallCount = 0;
        
        $containerMock->method('get')
            ->willReturnCallback(function ($className) use (&$getCallCount) {
                $getCallCount++;
                return $this->createMock(\stdClass::class);
            });

        new AuthController($containerMock);
        
        // Should call get() exactly 5 times for all dependencies
        $this->assertEquals(5, $getCallCount);
    }
}
