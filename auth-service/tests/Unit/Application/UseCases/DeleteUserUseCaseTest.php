<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCases;

use Tests\TestCase;
use App\Application\UseCases\DeleteUserUseCase;
use App\Domain\Repositories\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;

class DeleteUserUseCaseTest extends TestCase
{
    private DeleteUserUseCase $deleteUserUseCase;
    /** @var MockObject&UserRepositoryInterface */
    private MockObject $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->deleteUserUseCase = new DeleteUserUseCase($this->userRepository);
    }

    public function testSuccessfulUserDeletion(): void
    {
        $userId = '01234567-89ab-cdef-0123-456789abcdef';

        $this->userRepository->expects($this->once())
            ->method('delete')
            ->with($userId)
            ->willReturn(true);

        $result = $this->deleteUserUseCase->execute($userId);

        $this->assertTrue($result);
    }

    public function testFailedUserDeletion(): void
    {
        $userId = '01234567-89ab-cdef-0123-456789abcdef';

        $this->userRepository->expects($this->once())
            ->method('delete')
            ->with($userId)
            ->willReturn(false);

        $result = $this->deleteUserUseCase->execute($userId);

        $this->assertFalse($result);
    }

    public function testDeleteUserWithEmptyId(): void
    {
        $this->userRepository->expects($this->never())
            ->method('delete');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ID do usuário inválido');

        $this->deleteUserUseCase->execute('');
    }

    public function testDeleteUserWithInvalidUuid(): void
    {
        $this->userRepository->expects($this->never())
            ->method('delete');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ID do usuário inválido');

        $this->deleteUserUseCase->execute('invalid-uuid');
    }

    public function testDeleteUserWithNullId(): void
    {
        $this->userRepository->expects($this->never())
            ->method('delete');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ID do usuário inválido');

        $this->deleteUserUseCase->execute('');
    }
}
