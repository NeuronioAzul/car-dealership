<?php

namespace Tests\Unit\Presentation\Controllers;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Presentation\Controllers\VehicleController;
use App\Application\UseCases\ListVehiclesUseCase;
use App\Application\UseCases\GetVehicleDetailsUseCase;
use App\Application\UseCases\SearchVehiclesUseCase;
use App\Application\UseCases\CreateVehicleUseCase;
use App\Application\UseCases\UpdateVehicleUseCase;
use App\Presentation\Middleware\AuthMiddleware;
use App\Application\DTOs\VehicleDTO;
use Exception;

class VehicleControllerTest extends TestCase
{
    private VehicleController $controller;
    private MockObject $listVehiclesUseCase;
    private MockObject $getVehicleDetailsUseCase;
    private MockObject $searchVehiclesUseCase;
    private MockObject $createVehicleUseCase;
    private MockObject $updateVehicleUseCase;
    private MockObject $authMiddleware;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock all dependencies
        $this->listVehiclesUseCase = $this->createMock(ListVehiclesUseCase::class);
        $this->getVehicleDetailsUseCase = $this->createMock(GetVehicleDetailsUseCase::class);
        $this->searchVehiclesUseCase = $this->createMock(SearchVehiclesUseCase::class);
        $this->createVehicleUseCase = $this->createMock(CreateVehicleUseCase::class);
        $this->updateVehicleUseCase = $this->createMock(UpdateVehicleUseCase::class);
        $this->authMiddleware = $this->createMock(AuthMiddleware::class);

        // Create controller with mocked dependencies
        $this->controller = new VehicleController();

        // Use reflection to inject mocks
        $reflection = new \ReflectionClass($this->controller);

        $listUseCaseProperty = $reflection->getProperty('listVehiclesUseCase');
        $listUseCaseProperty->setAccessible(true);
        $listUseCaseProperty->setValue($this->controller, $this->listVehiclesUseCase);

        $getDetailsProperty = $reflection->getProperty('getVehicleDetailsUseCase');
        $getDetailsProperty->setAccessible(true);
        $getDetailsProperty->setValue($this->controller, $this->getVehicleDetailsUseCase);

        $searchProperty = $reflection->getProperty('searchVehiclesUseCase');
        $searchProperty->setAccessible(true);
        $searchProperty->setValue($this->controller, $this->searchVehiclesUseCase);

        $createProperty = $reflection->getProperty('createVehicleUseCase');
        $createProperty->setAccessible(true);
        $createProperty->setValue($this->controller, $this->createVehicleUseCase);

        $updateProperty = $reflection->getProperty('updateVehicleUseCase');
        $updateProperty->setAccessible(true);
        $updateProperty->setValue($this->controller, $this->updateVehicleUseCase);

        $authProperty = $reflection->getProperty('authMiddleware');
        $authProperty->setAccessible(true);
        $authProperty->setValue($this->controller, $this->authMiddleware);
    }

    public function testHealthReturnsSuccessResponse(): void
    {
        ob_start();
        $this->controller->health();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertIsArray($response);
        $this->assertTrue($response['success']);
        $this->assertEquals('vehicle-service', $response['service']);
        $this->assertEquals('healthy', $response['status']);
        $this->assertArrayHasKey('timestamp', $response);
    }

    public function testListVehiclesAsGuestShowsOnlyAvailable(): void
    {
        $this->authMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willThrowException(new Exception('Not authenticated'));

        $expectedVehicles = [
            ['id' => '1', 'brand' => 'Toyota', 'status' => 'available'],
            ['id' => '2', 'brand' => 'Honda', 'status' => 'available']
        ];

        $this->listVehiclesUseCase
            ->expects($this->once())
            ->method('execute')
            ->with(true) // onlyAvailable = true
            ->willReturn($expectedVehicles);

        ob_start();
        $this->controller->listVehicles();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertEquals($expectedVehicles, $response['data']);
        $this->assertEquals(2, $response['total']);
    }

    public function testListVehiclesAsAdminShowsAll(): void
    {
        $this->authMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn(['user_id' => '123', 'role' => 'admin']);

        $expectedVehicles = [
            ['id' => '1', 'brand' => 'Toyota', 'status' => 'available'],
            ['id' => '2', 'brand' => 'Honda', 'status' => 'sold'],
            ['id' => '3', 'brand' => 'Ford', 'status' => 'maintenance']
        ];

        $this->listVehiclesUseCase
            ->expects($this->once())
            ->method('execute')
            ->with(false) // onlyAvailable = false
            ->willReturn($expectedVehicles);

        ob_start();
        $this->controller->listVehicles();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertEquals($expectedVehicles, $response['data']);
        $this->assertEquals(3, $response['total']);
    }

    public function testGetVehicleDetailsWithValidId(): void
    {
        $vehicleId = 'uuid-123';
        $expectedVehicle = [
            'id' => $vehicleId,
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020
        ];

        $this->getVehicleDetailsUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($vehicleId)
            ->willReturn($expectedVehicle);

        ob_start();
        $this->controller->getVehicleDetails($vehicleId);
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertEquals($expectedVehicle, $response['data']);
    }

    public function testGetVehicleDetailsWithEmptyId(): void
    {
        ob_start();
        $this->controller->getVehicleDetails('');
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['error']);
        $this->assertEquals('ID do veículo é obrigatório', $response['message']);
    }

    public function testCreateVehicleAsAdminSuccess(): void
    {
        $this->authMiddleware
            ->expects($this->once())
            ->method('requireAdmin')
            ->willReturn(['user_id' => '123', 'role' => 'admin']);

        $vehicleData = [
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'price' => 80000
        ];

        $expectedVehicle = new VehicleDTO();
        $expectedVehicle->setBrand('Toyota');
        $expectedVehicle->setModel('Corolla');
        $expectedVehicle->setYear(2020);
        $expectedVehicle->setPrice(80000);

        $this->createVehicleUseCase
            ->expects($this->once())
            ->method('execute')
            ->willReturn($expectedVehicle);

        // Mock php://input
        $this->mockPhpInput(json_encode($vehicleData));

        ob_start();
        $this->controller->createVehicle();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertEquals('Veículo criado com sucesso', $response['message']);
        $this->assertArrayHasKey('data', $response);
        $this->assertEquals('123', $response['created_by']);
    }

    public function testCreateVehicleAsNonAdminFails(): void
    {
        $this->authMiddleware
            ->expects($this->once())
            ->method('requireAdmin')
            ->willThrowException(new Exception('Acesso negado', 403));

        ob_start();
        $this->controller->createVehicle();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['error']);
        $this->assertEquals('Acesso negado', $response['message']);
        $this->assertEquals(403, $response['code']);
        $this->assertEquals('authorization_error', $response['type']);
        $this->assertEquals('insufficient_permissions', $response['action']);
    }

    public function testDeleteVehicleAsAdminSuccess(): void
    {
        $this->authMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn(['user_id' => '123', 'role' => 'admin']);

        ob_start();
        $this->controller->deleteVehicle('uuid-123');
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertEquals('Veículo deletado com sucesso', $response['message']);
    }

    public function testDeleteVehicleAsNonAdminFails(): void
    {
        $this->authMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn(['user_id' => '123', 'role' => 'user']);

        ob_start();
        $this->controller->deleteVehicle('uuid-123');
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['error']);
        $this->assertStringContainsString('Acesso negado', $response['message']);
    }

    public function testDeleteVehicleWithEmptyId(): void
    {
        $this->authMiddleware
            ->expects($this->once())
            ->method('authenticate')
            ->willReturn(['user_id' => '123', 'role' => 'admin']);

        ob_start();
        $this->controller->deleteVehicle('');
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['error']);
        $this->assertEquals('ID do veículo é obrigatório', $response['message']);
    }

    public function testSearchVehiclesSuccess(): void
    {
        $_GET = ['brand' => 'Toyota', 'year' => '2020'];

        $expectedResult = [
            ['id' => '1', 'brand' => 'Toyota', 'year' => 2020],
            ['id' => '2', 'brand' => 'Toyota', 'year' => 2020]
        ];

        $this->searchVehiclesUseCase
            ->expects($this->once())
            ->method('execute')
            ->with($_GET)
            ->willReturn($expectedResult);

        ob_start();
        $this->controller->searchVehicles();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['success']);
        $this->assertEquals($expectedResult, $response['data']);
    }

    public function testSearchVehiclesWithException(): void
    {
        $_GET = ['invalid' => 'criteria'];

        $this->searchVehiclesUseCase
            ->expects($this->once())
            ->method('execute')
            ->willThrowException(new Exception('Invalid search criteria', 400));

        ob_start();
        $this->controller->searchVehicles();
        $output = ob_get_clean();

        $response = json_decode($output, true);

        $this->assertTrue($response['error']);
        $this->assertEquals('Invalid search criteria', $response['message']);
    }

    private function mockPhpInput(string $data): void
    {
        // This would require additional setup for testing php://input
        // In real tests, you might use a framework that handles this better
    }
}
