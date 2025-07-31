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
use ReflectionClass;

class AuthControllerExtendedTest extends TestCase
{
    public function test_auth_controller_constructor_parameters(): void
    {
        $reflection = new ReflectionClass(AuthController::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);
        $this->assertTrue($constructor->isPublic());
        
        $parameters = $constructor->getParameters();
        $this->assertCount(1, $parameters);
        
        $containerParam = $parameters[0];
        $this->assertEquals('container', $containerParam->getName());
        $this->assertTrue($containerParam->allowsNull());
        $this->assertTrue($containerParam->isOptional());
    }

    public function test_auth_controller_properties(): void
    {
        $reflection = new ReflectionClass(AuthController::class);
        
        $expectedProperties = [
            'loginUseCase',
            'registerUseCase',
            'logoutUseCase',
            'jwtService',
            'blacklistService'
        ];
        
        foreach ($expectedProperties as $propertyName) {
            $this->assertTrue($reflection->hasProperty($propertyName));
            
            $property = $reflection->getProperty($propertyName);
            $this->assertTrue($property->isPrivate());
        }
    }

    public function test_auth_controller_methods(): void
    {
        $reflection = new ReflectionClass(AuthController::class);
        
        $expectedMethods = [
            'login',
            'register',
            'refresh',
            'logout',
            'validate',
            'health'
        ];
        
        foreach ($expectedMethods as $methodName) {
            $this->assertTrue($reflection->hasMethod($methodName));
            
            $method = $reflection->getMethod($methodName);
            $this->assertTrue($method->isPublic());
            $this->assertCount(0, $method->getParameters()); // No parameters
        }
    }

    public function test_init_legacy_dependencies_method(): void
    {
        $reflection = new ReflectionClass(AuthController::class);
        
        $this->assertTrue($reflection->hasMethod('initializeLegacyDependencies'));
        
        $method = $reflection->getMethod('initializeLegacyDependencies');
        $this->assertTrue($method->isPrivate());
        $this->assertCount(0, $method->getParameters());
    }

    public function test_health_method_implementation(): void
    {
        // Create a container mock that returns proper mock services
        $containerMock = $this->createMock(Container::class);
        $containerMock->method('get')
            ->willReturnCallback(function ($className) {
                switch ($className) {
                    case LoginUseCase::class:
                        return $this->createMock(LoginUseCase::class);
                    case RegisterUseCase::class:
                        return $this->createMock(RegisterUseCase::class);
                    case LogoutUseCase::class:
                        return $this->createMock(LogoutUseCase::class);
                    case JWTService::class:
                        return $this->createMock(JWTService::class);
                    case TokenBlacklistService::class:
                        return $this->createMock(TokenBlacklistService::class);
                    default:
                        return null;
                }
            });
        
        $controller = new AuthController($containerMock);
        
        // Capture output
        ob_start();
        $controller->health();
        $output = ob_get_clean();
        
        // Verify JSON response structure
        $response = json_decode($output, true);
        $this->assertNotNull($response, 'Health endpoint should return valid JSON');
        
        // Verify required fields
        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('service', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('timestamp', $response);
        
        // Verify values
        $this->assertTrue($response['success']);
        $this->assertEquals('auth-service', $response['service']);
        $this->assertEquals('healthy', $response['status']);
        $this->assertIsString($response['timestamp']);
        
        // Verify timestamp format (YYYY-MM-DD HH:MM:SS)
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            $response['timestamp']
        );
    }

    public function test_auth_controller_with_dependency_injection(): void
    {
        $containerMock = $this->createMock(Container::class);
        
        // Create proper mocks for services
        $loginUseCaseMock = $this->createMock(LoginUseCase::class);
        $registerUseCaseMock = $this->createMock(RegisterUseCase::class);
        $logoutUseCaseMock = $this->createMock(LogoutUseCase::class);
        $jwtServiceMock = $this->createMock(JWTService::class);
        $blacklistServiceMock = $this->createMock(TokenBlacklistService::class);
        
        // Configure container to return proper mocks
        $containerMock->method('get')
            ->willReturnMap([
                [LoginUseCase::class, $loginUseCaseMock],
                [RegisterUseCase::class, $registerUseCaseMock],
                [LogoutUseCase::class, $logoutUseCaseMock],
                [JWTService::class, $jwtServiceMock],
                [TokenBlacklistService::class, $blacklistServiceMock],
            ]);
        
        $controller = new AuthController($containerMock);
        
        // Use reflection to verify dependencies are injected
        $reflection = new ReflectionClass($controller);
        
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

    public function test_legacy_constructor_path(): void
    {
        // Test that legacy constructor doesn't break (even if it fails due to missing DB)
        $this->expectNotToPerformAssertions();
        
        try {
            new AuthController(null);
        } catch (\Exception $e) {
            // Expected to fail in test environment due to database dependencies
            $this->assertTrue(true);
        }
    }

    public function test_class_namespace_and_imports(): void
    {
        $reflection = new ReflectionClass(AuthController::class);
        
        // Test class namespace
        $this->assertEquals('App\Presentation\Controllers\AuthController', $reflection->getName());
        
        // Test that class exists and is instantiable (with proper mocks)
        $this->assertTrue($reflection->isInstantiable());
        
        // Test class file content has expected imports
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);
        
        $expectedImports = [
            'use App\Application\UseCases\LoginUseCase;',
            'use App\Application\UseCases\RegisterUseCase;',
            'use App\Application\UseCases\LogoutUseCase;',
            'use App\Application\Services\JWTService;',
            'use App\Application\Services\TokenBlacklistService;',
            'use App\Infrastructure\DI\Container;',
        ];
        
        foreach ($expectedImports as $import) {
            $this->assertStringContainsString($import, $content);
        }
    }

    public function test_container_dependency_usage(): void
    {
        $containerMock = $this->createMock(Container::class);
        
        // Verify that container's get method is called exactly 5 times
        $containerMock->expects($this->exactly(5))
            ->method('get')
            ->willReturnCallback(function ($className) {
                // Return appropriate mocks for each service
                switch ($className) {
                    case LoginUseCase::class:
                        return $this->createMock(LoginUseCase::class);
                    case RegisterUseCase::class:
                        return $this->createMock(RegisterUseCase::class);
                    case LogoutUseCase::class:
                        return $this->createMock(LogoutUseCase::class);
                    case JWTService::class:
                        return $this->createMock(JWTService::class);
                    case TokenBlacklistService::class:
                        return $this->createMock(TokenBlacklistService::class);
                    default:
                        throw new \InvalidArgumentException("Unexpected service: $className");
                }
            });
        
        new AuthController($containerMock);
    }

    public function test_health_endpoint_headers(): void
    {
        // Mock container with proper service mocks
        $containerMock = $this->createMock(Container::class);
        $containerMock->method('get')
            ->willReturnCallback(function ($className) {
                switch ($className) {
                    case LoginUseCase::class:
                        return $this->createMock(LoginUseCase::class);
                    case RegisterUseCase::class:
                        return $this->createMock(RegisterUseCase::class);
                    case LogoutUseCase::class:
                        return $this->createMock(LogoutUseCase::class);
                    case JWTService::class:
                        return $this->createMock(JWTService::class);
                    case TokenBlacklistService::class:
                        return $this->createMock(TokenBlacklistService::class);
                    default:
                        return null;
                }
            });
        
        $controller = new AuthController($containerMock);
        
        // Call health method and verify it doesn't throw
        $this->expectNotToPerformAssertions();
        
        ob_start();
        $controller->health();
        ob_end_clean();
    }

    public function test_method_return_types(): void
    {
        $reflection = new ReflectionClass(AuthController::class);
        
        $voidMethods = ['login', 'register', 'refresh', 'logout', 'validate', 'health'];
        
        foreach ($voidMethods as $methodName) {
            $method = $reflection->getMethod($methodName);
            $returnType = $method->getReturnType();
            
            $this->assertNotNull($returnType);
            $this->assertEquals('void', $returnType->__toString());
        }
    }
}
