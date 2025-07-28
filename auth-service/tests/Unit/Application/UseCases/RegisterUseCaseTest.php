<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCases;

use App\Application\Exceptions\UserCreationFailedException;
use App\Application\Exceptions\ValidationException;
use App\Application\UseCases\RegisterUseCase;
use App\Domain\Entities\User;
use App\Domain\Exceptions\UserAlreadyExistsException;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Infrastructure\Messaging\EventPublisher;
use Mockery;
use PHPUnit\Framework\TestCase;

class RegisterUseCaseTest extends TestCase
{
    public function testRegistrationFailsWhenEmailAlreadyExists(): void
    {
        $userRepository = Mockery::mock(UserRepositoryInterface::class);
        $eventPublisher = Mockery::mock(EventPublisher::class);
        $registerUseCase = new RegisterUseCase($userRepository, $eventPublisher);

        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@email.com',
            'password' => 'password123',
            'phone' => '11999999999',
            'birth_date' => '1990-01-01',
            'address' => [
                'street' => 'Rua das Flores',
                'number' => '123',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '01234-567'
            ],
            'accept_terms' => true,
            'accept_privacy' => true,
        ];

        $userRepository->shouldReceive('existsByEmail')->andReturn(true);

        $this->expectException(UserAlreadyExistsException::class);

        $registerUseCase->execute($userData);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
