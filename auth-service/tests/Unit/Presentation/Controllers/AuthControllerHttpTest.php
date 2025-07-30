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

class AuthControllerHttpTest extends TestCase
{
    private Container $mockContainer;
    private LoginUseCase $mockLoginUseCase;
    private RegisterUseCase $mockRegisterUseCase;
    private LogoutUseCase $mockLogoutUseCase;
    private JWTService $mockJwtService;
    private TokenBlacklistService $mockBlacklistService;
    private AuthController $controller;

    protected function setUp(): void
    {
        // Create service mocks
        $this->mockLoginUseCase = $this->createMock(LoginUseCase::class);
        $this->mockRegisterUseCase = $this->createMock(RegisterUseCase::class);
        $this->mockLogoutUseCase = $this->createMock(LogoutUseCase::class);
        $this->mockJwtService = $this->createMock(JWTService::class);
        $this->mockBlacklistService = $this->createMock(TokenBlacklistService::class);

        // Create container mock
        $this->mockContainer = $this->createMock(Container::class);
        $this->mockContainer->method('get')
            ->willReturnCallback(function ($className) {
                switch ($className) {
                    case LoginUseCase::class:
                        return $this->mockLoginUseCase;
                    case RegisterUseCase::class:
                        return $this->mockRegisterUseCase;
                    case LogoutUseCase::class:
                        return $this->mockLogoutUseCase;
                    case JWTService::class:
                        return $this->mockJwtService;
                    case TokenBlacklistService::class:
                        return $this->mockBlacklistService;
                    default:
                        return null;
                }
            });

        $this->controller = new AuthController($this->mockContainer);
    }

    public function test_login_with_valid_credentials(): void
    {
        // Mock the input data
        $inputData = json_encode([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        // Mock file_get_contents('php://input')
        $this->mockPhpInput($inputData);

        // Configure login use case mock
        $this->mockLoginUseCase->expects($this->once())
            ->method('execute')
            ->with('test@example.com', 'password123')
            ->willReturn([
                'access_token' => 'mock_token',
                'refresh_token' => 'mock_refresh_token',
                'user' => ['id' => 'user-123', 'email' => 'test@example.com']
            ]);

        // Capture output
        ob_start();
        $this->controller->login();
        $output = ob_get_clean();

        // Verify JSON response
        $response = json_decode($output, true);
        $this->assertNotNull($response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('access_token', $response['data']);
    }

    public function test_login_with_missing_email(): void
    {
        // Mock input with missing email
        $inputData = json_encode([
            'password' => 'password123'
        ]);

        $this->mockPhpInput($inputData);

        ob_start();
        $this->controller->login();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertNotNull($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertStringContains('Email e senha são obrigatórios', $response['message']);
    }

    public function test_login_with_missing_password(): void
    {
        // Mock input with missing password
        $inputData = json_encode([
            'email' => 'test@example.com'
        ]);

        $this->mockPhpInput($inputData);

        ob_start();
        $this->controller->login();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertNotNull($response);
        $this->assertArrayHasKey('error', $response);
    }

    public function test_login_with_exception(): void
    {
        $inputData = json_encode([
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $this->mockPhpInput($inputData);

        // Make login use case throw an exception
        $this->mockLoginUseCase->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Invalid credentials'));

        ob_start();
        $this->controller->login();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertNotNull($response);
        $this->assertArrayHasKey('error', $response);
    }

    public function test_register_with_valid_data(): void
    {
        $inputData = json_encode([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'phone' => '11999999999',
            'birth_date' => '1990-01-01',
            'street' => 'Rua A',
            'number' => '123',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01000-000',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ]);

        $this->mockPhpInput($inputData);

        $this->mockRegisterUseCase->expects($this->once())
            ->method('execute')
            ->willReturn([
                'user_id' => 'user-123',
                'message' => 'User created successfully'
            ]);

        ob_start();
        $this->controller->register();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertNotNull($response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('data', $response);
    }

    public function test_register_with_invalid_data(): void
    {
        // Mock empty input
        $this->mockPhpInput('');

        ob_start();
        $this->controller->register();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertNotNull($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertStringContains('Dados inválidos', $response['message']);
    }

    public function test_register_with_exception(): void
    {
        $inputData = json_encode([
            'name' => 'John Doe',
            'email' => 'existing@example.com'
        ]);

        $this->mockPhpInput($inputData);

        $this->mockRegisterUseCase->expects($this->once())
            ->method('execute')
            ->willThrowException(new \Exception('Email already exists'));

        ob_start();
        $this->controller->register();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertNotNull($response);
        $this->assertArrayHasKey('error', $response);
    }

    public function test_refresh_with_valid_token(): void
    {
        $inputData = json_encode([
            'refresh_token' => 'valid_refresh_token'
        ]);

        $this->mockPhpInput($inputData);

        // Set up environment variable
        $_ENV['JWT_EXPIRATION'] = '3600';

        $this->mockJwtService->expects($this->once())
            ->method('refreshToken')
            ->with('valid_refresh_token')
            ->willReturn('new_access_token');

        ob_start();
        $this->controller->refresh();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertNotNull($response);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('new_access_token', $response['data']['access_token']);
        $this->assertEquals('Bearer', $response['data']['token_type']);
    }

    public function test_refresh_with_missing_token(): void
    {
        $inputData = json_encode([]);

        $this->mockPhpInput($inputData);

        ob_start();
        $this->controller->refresh();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertNotNull($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertStringContains('Token de refresh não fornecido', $response['message']);
    }

    public function test_refresh_with_empty_token(): void
    {
        $inputData = json_encode([
            'refresh_token' => ''
        ]);

        $this->mockPhpInput($inputData);

        ob_start();
        $this->controller->refresh();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertNotNull($response);
        $this->assertArrayHasKey('error', $response);
    }

    public function test_logout_with_valid_token(): void
    {
        // Mock Authorization header
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer valid_token';

        $this->mockLogoutUseCase->expects($this->once())
            ->method('execute')
            ->with('valid_token');

        ob_start();
        $this->controller->logout();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertNotNull($response);
        $this->assertTrue($response['success']);
        $this->assertStringContains('Logout realizado com sucesso', $response['message']);
    }

    public function test_logout_without_authorization_header(): void
    {
        // Ensure no Authorization header is set
        unset($_SERVER['HTTP_AUTHORIZATION']);
        unset($_SERVER['Authorization']);

        ob_start();
        $this->controller->logout();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertNotNull($response);
        $this->assertArrayHasKey('error', $response);
        $this->assertStringContains('Token não fornecido para logout', $response['message']);
    }

    public function test_validate_with_valid_token(): void
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer valid_token';

        $this->mockJwtService->expects($this->once())
            ->method('validateToken')
            ->with('valid_token')
            ->willReturn([
                'sub' => 'user-123',
                'email' => 'test@example.com',
                'role' => 'customer',
                'exp' => time() + 3600
            ]);

        ob_start();
        $this->controller->validate();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertNotNull($response);
        $this->assertTrue($response['success']);
        $this->assertTrue($response['data']['valid']);
        $this->assertEquals('user-123', $response['data']['user_id']);
        $this->assertEquals('test@example.com', $response['data']['email']);
        $this->assertEquals('customer', $response['data']['role']);
    }

    public function test_validate_without_token(): void
    {
        unset($_SERVER['HTTP_AUTHORIZATION']);
        unset($_SERVER['Authorization']);

        ob_start();
        $this->controller->validate();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertNotNull($response);
        $this->assertTrue($response['error']);
        $this->assertFalse($response['valid']);
        $this->assertStringContains('Token não fornecido', $response['message']);
    }

    private function mockPhpInput(string $data): void
    {
        // Create a temporary file with the mock data
        $tempFile = tmpfile();
        fwrite($tempFile, $data);
        rewind($tempFile);
        
        // Store the temp file path for cleanup
        $this->tempInputFile = $tempFile;
    }

    protected function tearDown(): void
    {
        // Clean up
        unset($_SERVER['HTTP_AUTHORIZATION']);
        unset($_SERVER['Authorization']);
        unset($_ENV['JWT_EXPIRATION']);
        
        if (isset($this->tempInputFile)) {
            fclose($this->tempInputFile);
        }
    }
}
