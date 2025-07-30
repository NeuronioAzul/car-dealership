<?php

namespace Tests\Feature\VehicleService;

use Tests\TestCase;

class VehiclesApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        if (!$this->isServiceRunning($this->vehicleServiceUrl)) {
            $this->markTestSkipped('Vehicle service não está disponível');
        }
    }

    public function testGetVehiclesEndpoint(): void
    {
        $response = $this->makeRequest("{$this->vehicleServiceUrl}");
        
        $this->assertEquals(200, $response['code']);
        $this->assertIsArray($response['body']);
        $this->assertArrayHasKey('data', $response['body']);
    }

    public function testVehicleServiceHealth(): void
    {
        $response = $this->makeRequest("{$this->vehicleServiceUrl}/health");
        
        $this->assertEquals(200, $response['code']);
        $this->assertTrue($response['body']['success']);
        $this->assertEquals('vehicle-service', $response['body']['service']);
        $this->assertEquals('healthy', $response['body']['status']);
    }

    public function testGetVehiclesWithAuthentication(): void
    {
        // Obter token de autenticação
        $loginResult = $this->loginAndGetToken();
        $token = $loginResult['access_token'];

        $response = $this->makeRequest(
            "{$this->vehicleServiceUrl}",
            'GET',
            null,
            $this->getAuthHeaders($token)
        );
        
        $this->assertEquals(200, $response['code']);
        $this->assertIsArray($response['body']);
    }

    public function testCreateVehicleRequiresAuthentication(): void
    {
        $vehicleData = [
            'make' => 'Test',
            'model' => 'Vehicle',
            'year' => 2024,
            'price' => 25000.00,
            'status' => 'available'
        ];

        // Tentar criar sem autenticação
        $response = $this->makeRequest(
            "{$this->vehicleServiceUrl}/create",
            'POST',
            $vehicleData
        );
        
        $this->assertEquals(401, $response['code']);
    }

    public function testCreateVehicleWithAuthentication(): void
    {
        // Obter token de autenticação
        $loginResult = $this->loginAndGetToken();
        $token = $loginResult['access_token'];

        $vehicleData = [
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2024,
            'price' => 85000.00,
            'status' => 'available',
            'description' => 'Veículo teste criado via API'
        ];

        $response = $this->makeRequest(
            "{$this->vehicleServiceUrl}/create",
            'POST',
            $vehicleData,
            $this->getAuthHeaders($token)
        );
        
        // Pode retornar 201 (created) ou 200 dependendo da implementação
        $this->assertContains($response['code'], [200, 201]);
        
        if (isset($response['body']['success'])) {
            $this->assertTrue($response['body']['success']);
        }
    }

    public function testSearchVehicles(): void
    {
        $response = $this->makeRequest("{$this->vehicleServiceUrl}/search?make=Toyota");
        
        $this->assertEquals(200, $response['code']);
        $this->assertIsArray($response['body']);
    }

    public function testUpdateVehicleRequiresAuthentication(): void
    {
        $updateData = [
            'id' => '1',
            'price' => 90000.00
        ];

        $response = $this->makeRequest(
            "{$this->vehicleServiceUrl}/update",
            'PUT',
            $updateData
        );
        
        $this->assertEquals(401, $response['code']);
    }

    public function testDeleteVehicleRequiresAuthentication(): void
    {
        $deleteData = [
            'id' => '1'
        ];

        $response = $this->makeRequest(
            "{$this->vehicleServiceUrl}/delete",
            'DELETE',
            $deleteData
        );
        
        $this->assertEquals(401, $response['code']);
    }
}
