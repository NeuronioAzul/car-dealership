<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCases;

use App\Application\Services\JWTService;
use App\Application\UseCases\LoginUseCase;
use App\Domain\Entities\User;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Messaging\EventPublisher;
use DateTime;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class LoginUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private UserRepositoryInterface|MockInterface $userRepository;
    private JWTService|MockInterface $jwtService;
    private EventPublisher|MockInterface $eventPublisher;
    private LoginUseCase $loginUseCase;

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
    }

    private function createUser(): User
    {
        $user = new User(
            'John Doe',
            'john.doe@example.com',
            'password123',
            '11999999999',
            new DateTime('1990-01-01'),
            'customer',
            true,
            true,
            true
        );
        return $user;
    }

    public function test_successful_login(): void
    {
        $user = $this->createUser();
        $email = 'john.doe@example.com';
        $password = 'password123';

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->with($email)
            ->andReturn($user);

        $this->jwtService
            ->shouldReceive('generateToken')
            ->andReturn('fake_token');
        $this->jwtService
            ->shouldReceive('generateRefreshToken')
            ->andReturn('fake_refresh_token');

        $this->eventPublisher
            ->shouldReceive('publish')
            ->once();

        $result = $this->loginUseCase->execute($email, $password);

        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('refresh_token', $result);
        $this->assertArrayHasKey('user', $result);
    }

    public function test_login_fails_with_non_existent_user(): void
    {
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionCode(401);

        $this->userRepository->shouldReceive('findByEmail')->andReturn(null);

        $this->loginUseCase->execute('nonexistent@example.com', 'password');
    }

    public function test_login_fails_with_deleted_user(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        $user = $this->createUser();
        $user->delete();

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->andReturn($user);

        $this->loginUseCase->execute('john.doe@example.com', 'password123');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->expectException(InvalidCredentialsException::class);

        $user = $this->createUser();

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->andReturn($user);

        $this->loginUseCase->execute('john.doe@example.com', 'wrongpassword');
    }
}
