<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCases;

use App\Application\Services\JWTService;
use App\Application\UseCases\LoginUseCase;
use App\Domain\Entities\User;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\ValueObjects\Address;
use App\Infrastructure\Messaging\EventPublisher;
use DateTime;
use Mockery;
use PHPUnit\Framework\TestCase;

class LoginUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private JWTService $jwtService;
    private EventPublisher $eventPublisher;
    private LoginUseCase $loginUseCase;
    private User $validUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->jwtService = Mockery::mock(JWTService::class);
        $this->eventPublisher = Mockery::mock(EventPublisher::class);
        $this->loginUseCase = new LoginUseCase(
            $this->userRepository,
            $this->jwtService,
            $this->eventPublisher
        );

        $address = new Address(
            'Rua das Flores',
            '123',
            'Centro',
            'São Paulo',
            'SP',
            '01234-567'
        );

        $this->validUser = new User(
            'João Silva',
            'joao@email.com',
            'password123',
            '11999999999',
            new DateTime('1990-01-01'),
            $address,
            'customer',
            true,
            true,
            false
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testSuccessfulLogin(): void
    {
        $email = 'joao@email.com';
        $password = 'password123';

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->with($email)
            ->once()
            ->andReturn($this->validUser);

        $this->jwtService
            ->shouldReceive('generateToken')
            ->with($this->validUser)
            ->once()
            ->andReturn('mock_access_token');

        $this->jwtService
            ->shouldReceive('generateRefreshToken')
            ->with($this->validUser)
            ->once()
            ->andReturn('mock_refresh_token');

        $this->eventPublisher
            ->shouldReceive('publish')
            ->once()
            ->withArgs(function ($eventName, $eventData) {
                return $eventName === 'auth.user_logged_in' &&
                       $eventData['user_id'] === $this->validUser->getId() &&
                       $eventData['email'] === 'joao@email.com' &&
                       $eventData['role'] === 'customer';
            });

        $_ENV['JWT_EXPIRATION'] = '3600';

        $result = $this->loginUseCase->execute($email, $password);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('refresh_token', $result);
        $this->assertArrayHasKey('token_type', $result);
        $this->assertArrayHasKey('expires_in', $result);

        $this->assertEquals('João Silva', $result['user']['name']);
        $this->assertEquals('joao@email.com', $result['user']['email']);
        $this->assertEquals('customer', $result['user']['role']);
        $this->assertEquals('mock_access_token', $result['access_token']);
        $this->assertEquals('mock_refresh_token', $result['refresh_token']);
        $this->assertEquals('Bearer', $result['token_type']);
        $this->assertEquals('3600', $result['expires_in']);
    }

    public function testLoginFailsWithNonExistentUser(): void
    {
        $email = 'nonexistent@email.com';
        $password = 'password123';

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->with($email)
            ->once()
            ->andReturn(null);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionCode(404);

        $this->loginUseCase->execute($email, $password);
    }

    public function testLoginFailsWithDeletedUser(): void
    {
        $email = 'joao@email.com';
        $password = 'password123';

        // Marcar usuário como deletado
        $this->validUser->delete();

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->with($email)
            ->once()
            ->andReturn($this->validUser);

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionCode(401);

        $this->loginUseCase->execute($email, $password);
    }

    public function testLoginFailsWithWrongPassword(): void
    {
        $email = 'joao@email.com';
        $wrongPassword = 'wrong_password';

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->with($email)
            ->once()
            ->andReturn($this->validUser);

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionCode(401);

        $this->loginUseCase->execute($email, $wrongPassword);
    }
}
