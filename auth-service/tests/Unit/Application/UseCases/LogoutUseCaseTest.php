<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCases;

use Tests\TestCase;
use App\Application\UseCases\LogoutUseCase;
use App\Application\Services\JWTService;
use PHPUnit\Framework\MockObject\MockObject;

class LogoutUseCaseTest extends TestCase
{
    private LogoutUseCase $logoutUseCase;
    /** @var MockObject&JWTService */
    private MockObject $jwtService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jwtService = $this->createMock(JWTService::class);
        $this->logoutUseCase = new LogoutUseCase($this->jwtService);
    }

    public function testSuccessfulLogout(): void
    {
        $token = 'valid.jwt.token';

        $this->jwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willReturn(['sub' => 'user123', 'email' => 'test@test.com']);

        $this->jwtService->expects($this->once())
            ->method('revokeToken')
            ->with($token);

        $this->logoutUseCase->execute($token);
    }

    public function testLogoutWithAlreadyRevokedToken(): void
    {
        $token = 'revoked.jwt.token';

        $this->jwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willThrowException(new \Exception('Token foi revogado', 401));

        $this->jwtService->expects($this->never())
            ->method('revokeToken');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token já foi invalidado ou é inválido');
        $this->expectExceptionCode(401);

        $this->logoutUseCase->execute($token);
    }

    public function testLogoutWithInvalidToken(): void
    {
        $token = 'invalid.jwt.token';

        $this->jwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willThrowException(new \Exception('Token inválido', 401));

        $this->jwtService->expects($this->never())
            ->method('revokeToken');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token já foi invalidado ou é inválido');
        $this->expectExceptionCode(401);

        $this->logoutUseCase->execute($token);
    }

    public function testLogoutWithOtherException(): void
    {
        $token = 'some.jwt.token';

        $this->jwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willThrowException(new \Exception('Database connection error', 500));

        $this->jwtService->expects($this->never())
            ->method('revokeToken');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database connection error');
        $this->expectExceptionCode(500);

        $this->logoutUseCase->execute($token);
    }

    public function testLogoutWithRevokeException(): void
    {
        $token = 'valid.jwt.token';

        $this->jwtService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willReturn(['sub' => 'user123', 'email' => 'test@test.com']);

        $this->jwtService->expects($this->once())
            ->method('revokeToken')
            ->with($token)
            ->willThrowException(new \Exception('Failed to revoke token', 500));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to revoke token');
        $this->expectExceptionCode(500);

        $this->logoutUseCase->execute($token);
    }
}
