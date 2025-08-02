<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCases;

use App\Application\Exceptions\UserCreationFailedException;
use App\Application\Exceptions\ValidationException;
use App\Application\UseCases\RegisterUseCase;
use App\Application\Validation\RequestValidator;
use App\Domain\Entities\User;
use App\Domain\Exceptions\UserAlreadyExistsException;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Messaging\EventPublisher;
use Mockery;
use PHPUnit\Framework\TestCase;

class RegisterUseCaseTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private EventPublisher $eventPublisher;
    private RequestValidator $validator;
    private RegisterUseCase $registerUseCase;
    private array $validUserData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->eventPublisher = Mockery::mock(EventPublisher::class);
        $this->validator = Mockery::mock(RequestValidator::class);
        $this->registerUseCase = new RegisterUseCase(
            $this->userRepository,
            $this->eventPublisher,
            $this->validator
        );

        $this->validUserData = [
            'name' => 'João Silva',
            'email' => 'joao@email.com',
            'password' => 'senhaSegura123',
            'phone' => '11999999999',
            'role' => 'customer',
            'birth_date' => '1990-01-01',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_communications' => false
        ];
    }

    public function testSuccessfulUserRegistration(): void
    {
        $this->validator
            ->shouldReceive('getRegisterUserConstraints')
            ->once()
            ->andReturn([]);

        $this->validator
            ->shouldReceive('validate')
            ->with($this->validUserData, [])
            ->once()
            ->andReturn();

        $this->userRepository
            ->shouldReceive('existsByEmail')
            ->with('joao@email.com')
            ->once()
            ->andReturn(false);

        $this->userRepository
            ->shouldReceive('save')
            ->once()
            ->withArgs(function (User $user) {
                return $user->getName() === 'João Silva' &&
                       $user->getEmail() === 'joao@email.com' &&
                       $user->getPhone() === '11999999999' &&
                       $user->getRole() === 'customer';
            })
            ->andReturn(true);

        $this->eventPublisher
            ->shouldReceive('publish')
            ->once()
            ->withArgs(function ($eventName, $eventData) {
                return $eventName === 'auth.user_registered' &&
                       isset($eventData['user_id']) &&
                       $eventData['email'] === 'joao@email.com' &&
                       $eventData['name'] === 'João Silva' &&
                       $eventData['role'] === 'customer';
            });

        $result = $this->registerUseCase->execute($this->validUserData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('João Silva', $result['user']['name']);
        $this->assertEquals('joao@email.com', $result['user']['email']);
        $this->assertEquals('customer', $result['user']['role']);
        $this->assertEquals('Usuário criado com sucesso', $result['message']);
    }

    public function testRegistrationFailsWhenEmailAlreadyExists(): void
    {
        $this->validator
            ->shouldReceive('getRegisterUserConstraints')
            ->once()
            ->andReturn([]);

        $this->validator
            ->shouldReceive('validate')
            ->with($this->validUserData, [])
            ->once()
            ->andReturn();

        $this->userRepository
            ->shouldReceive('existsByEmail')
            ->with('joao@email.com')
            ->once()
            ->andReturn(true);

        $this->expectException(UserAlreadyExistsException::class);
        $this->expectExceptionMessage("User with email 'joao@email.com' already exists");
        $this->expectExceptionCode(409);

        $this->registerUseCase->execute($this->validUserData);
    }

    public function testRegistrationFailsWithMissingRequiredFields(): void
    {
        $invalidData = $this->validUserData;
        unset($invalidData['name']);

        $this->validator
            ->shouldReceive('getRegisterUserConstraints')
            ->once()
            ->andReturn([]);

        $this->validator
            ->shouldReceive('validate')
            ->with($invalidData, [])
            ->once()
            ->andThrow(new ValidationException(['name' => ['Nome é obrigatório']]));

        $this->expectException(ValidationException::class);
        $this->expectExceptionCode(422);

        $this->registerUseCase->execute($invalidData);
    }

    public function testRegistrationFailsWithInvalidEmail(): void
    {
        $invalidData = $this->validUserData;
        $invalidData['email'] = 'invalid-email';

        $this->validator
            ->shouldReceive('getRegisterUserConstraints')
            ->once()
            ->andReturn([]);

        $this->validator
            ->shouldReceive('validate')
            ->with($invalidData, [])
            ->once()
            ->andThrow(new ValidationException(['email' => ['Email deve ter um formato válido']]));

        $this->expectException(ValidationException::class);
        $this->expectExceptionCode(422);

        $this->registerUseCase->execute($invalidData);
    }

    public function testRegistrationFailsWithWeakPassword(): void
    {
        $invalidData = $this->validUserData;
        $invalidData['password'] = '123';

        $this->validator
            ->shouldReceive('getRegisterUserConstraints')
            ->once()
            ->andReturn([]);

        $this->validator
            ->shouldReceive('validate')
            ->with($invalidData, [])
            ->once()
            ->andThrow(new ValidationException(['password' => ['Senha deve ter pelo menos 8 caracteres']]));

        $this->expectException(ValidationException::class);
        $this->expectExceptionCode(422);

        $this->registerUseCase->execute($invalidData);
    }

    public function testRegistrationFailsWithoutAcceptingTerms(): void
    {
        $invalidData = $this->validUserData;
        $invalidData['accept_terms'] = false;

        $this->validator
            ->shouldReceive('getRegisterUserConstraints')
            ->once()
            ->andReturn([]);

        $this->validator
            ->shouldReceive('validate')
            ->with($invalidData, [])
            ->once()
            ->andThrow(new ValidationException(['accept_terms' => ['Você deve aceitar os termos de uso']]));

        $this->expectException(ValidationException::class);
        $this->expectExceptionCode(422);

        $this->registerUseCase->execute($invalidData);
    }

    public function testSaveUserFailure(): void
    {
        $this->validator
            ->shouldReceive('getRegisterUserConstraints')
            ->once()
            ->andReturn([]);

        $this->validator
            ->shouldReceive('validate')
            ->with($this->validUserData, [])
            ->once()
            ->andReturn();

        $this->userRepository
            ->shouldReceive('existsByEmail')
            ->with('joao@email.com')
            ->once()
            ->andReturn(false);

        $this->userRepository
            ->shouldReceive('save')
            ->once()
            ->andReturn(false);

        $this->expectException(UserCreationFailedException::class);
        $this->expectExceptionMessage('Failed to save user to repository');
        $this->expectExceptionCode(500);

        $this->registerUseCase->execute($this->validUserData);
    }

    public function testEventPublicationAfterSuccessfulRegistration(): void
    {
        $this->validator
            ->shouldReceive('getRegisterUserConstraints')
            ->once()
            ->andReturn([]);

        $this->validator
            ->shouldReceive('validate')
            ->with($this->validUserData, [])
            ->once()
            ->andReturn();

        $this->userRepository
            ->shouldReceive('existsByEmail')
            ->with('joao@email.com')
            ->once()
            ->andReturn(false);

        $this->userRepository
            ->shouldReceive('save')
            ->once()
            ->andReturn(true);

        $this->eventPublisher
            ->shouldReceive('publish')
            ->once()
            ->withArgs(function ($eventName, $eventData) {
                return $eventName === 'auth.user_registered' &&
                       is_array($eventData) &&
                       isset($eventData['user_id']) &&
                       $eventData['email'] === 'joao@email.com' &&
                       $eventData['name'] === 'João Silva' &&
                       $eventData['role'] === 'customer';
            });

        $result = $this->registerUseCase->execute($this->validUserData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('message', $result);
        $this->assertEquals('Usuário criado com sucesso', $result['message']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
