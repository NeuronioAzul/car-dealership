<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class VehicleServiceIntegrationTest extends TestCase
{
    private Client $httpClient;
    private string $baseUrl;

    protected function setUp(): void
    {
        $this->baseUrl = TEST_BASE_URL;
        $this->httpClient = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
    }

    public function testHealthCheck(): void
    {
        try {
            $response = $this->httpClient->get('/vehicles/health');
            
            $this->assertEquals(200, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            $this->assertEquals('vehicle-service', $body['service']);
            
        } catch (RequestException $e) {
            $this->fail('Vehicle Service health check failed: ' . $e->getMessage());
        }
    }

    public function testListVehicles(): void
    {
        try {
            $response = $this->httpClient->get('/vehicles');
            
            $this->assertEquals(200, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            $this->assertArrayHasKey('data', $body);
            $this->assertArrayHasKey('vehicles', $body['data']);
            $this->assertIsArray($body['data']['vehicles']);
            
        } catch (RequestException $e) {
            $this->fail('List vehicles failed: ' . $e->getMessage());
        }
    }

    public function testListAvailableVehicles(): void
    {
        try {
            $response = $this->httpClient->get('/vehicles?available_only=true');
            
            $this->assertEquals(200, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            
            $vehicles = $body['data']['vehicles'];
            
            // Verificar se todos os veículos retornados estão disponíveis
            foreach ($vehicles as $vehicle) {
                $this->assertEquals('available', $vehicle['status']);
            }
            
        } catch (RequestException $e) {
            $this->fail('List available vehicles failed: ' . $e->getMessage());
        }
    }

    public function testSearchVehicles(): void
    {
        try {
            $searchParams = [
                'brand' => 'Toyota',
                'min_price' => 50000,
                'max_price' => 100000,
                'fuel_type' => 'Flex'
            ];
            
            $queryString = http_build_query($searchParams);
            $response = $this->httpClient->get('/vehicles/search?' . $queryString);
            
            $this->assertEquals(200, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            $this->assertArrayHasKey('vehicles', $body['data']);
            
            $vehicles = $body['data']['vehicles'];
            
            // Verificar se os filtros foram aplicados
            foreach ($vehicles as $vehicle) {
                if (isset($searchParams['brand'])) {
                    $this->assertEquals($searchParams['brand'], $vehicle['brand']);
                }
                if (isset($searchParams['fuel_type'])) {
                    $this->assertEquals($searchParams['fuel_type'], $vehicle['fuel_type']);
                }
                if (isset($searchParams['min_price'])) {
                    $this->assertGreaterThanOrEqual($searchParams['min_price'], $vehicle['price']);
                }
                if (isset($searchParams['max_price'])) {
                    $this->assertLessThanOrEqual($searchParams['max_price'], $vehicle['price']);
                }
            }
            
        } catch (RequestException $e) {
            $this->fail('Search vehicles failed: ' . $e->getMessage());
        }
    }

    public function testGetVehicleDetails(): void
    {
        // Primeiro obter lista de veículos para pegar um ID
        $listResponse = $this->httpClient->get('/vehicles?limit=1');
        $listBody = json_decode($listResponse->getBody()->getContents(), true);
        
        if (empty($listBody['data']['vehicles'])) {
            $this->markTestSkipped('Nenhum veículo disponível para teste');
        }
        
        $vehicleId = $listBody['data']['vehicles'][0]['id'];
        
        try {
            $response = $this->httpClient->get('/vehicles/' . $vehicleId);
            
            $this->assertEquals(200, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            $this->assertArrayHasKey('vehicle', $body['data']);
            
            $vehicle = $body['data']['vehicle'];
            $this->assertEquals($vehicleId, $vehicle['id']);
            $this->assertArrayHasKey('brand', $vehicle);
            $this->assertArrayHasKey('model', $vehicle);
            $this->assertArrayHasKey('price', $vehicle);
            $this->assertArrayHasKey('status', $vehicle);
            
        } catch (RequestException $e) {
            $this->fail('Get vehicle details failed: ' . $e->getMessage());
        }
    }

    public function testGetNonexistentVehicle(): void
    {
        $nonexistentId = 'nonexistent-vehicle-id';
        
        try {
            $response = $this->httpClient->get('/vehicles/' . $nonexistentId);
            
            $this->assertEquals(404, $response->getStatusCode());
            
        } catch (RequestException $e) {
            // Esperamos uma exceção 404
            $this->assertEquals(404, $e->getResponse()->getStatusCode());
        }
    }

    public function testSearchWithInvalidFilters(): void
    {
        try {
            $invalidParams = [
                'min_price' => 'invalid',
                'max_year' => 'not-a-year',
                'fuel_type' => 'invalid-fuel'
            ];
            
            $queryString = http_build_query($invalidParams);
            $response = $this->httpClient->get('/vehicles/search?' . $queryString);
            
            $this->assertEquals(400, $response->getStatusCode());
            
        } catch (RequestException $e) {
            // Esperamos uma exceção 400 (Bad Request)
            $this->assertEquals(400, $e->getResponse()->getStatusCode());
        }
    }

    public function testVehiclePagination(): void
    {
        try {
            $response = $this->httpClient->get('/vehicles?page=1&limit=5');
            
            $this->assertEquals(200, $response->getStatusCode());
            
            $body = json_decode($response->getBody()->getContents(), true);
            $this->assertTrue($body['success']);
            $this->assertArrayHasKey('pagination', $body['data']);
            
            $pagination = $body['data']['pagination'];
            $this->assertArrayHasKey('current_page', $pagination);
            $this->assertArrayHasKey('total_pages', $pagination);
            $this->assertArrayHasKey('total_items', $pagination);
            $this->assertArrayHasKey('items_per_page', $pagination);
            
            $this->assertEquals(1, $pagination['current_page']);
            $this->assertEquals(5, $pagination['items_per_page']);
            
        } catch (RequestException $e) {
            $this->fail('Vehicle pagination failed: ' . $e->getMessage());
        }
    }

    public function testVehicleFiltersValidation(): void
    {
        try {
            // Testar filtros válidos
            $validParams = [
                'brand' => 'Toyota',
                'model' => 'Corolla',
                'min_year' => 2020,
                'max_year' => 2024,
                'min_price' => 50000,
                'max_price' => 100000,
                'color' => 'Branco',
                'fuel_type' => 'Flex',
                'transmission_type' => 'Automático'
            ];
            
            $queryString = http_build_query($validParams);
            $response = $this->httpClient->get('/vehicles/search?' . $queryString);
            
            $this->assertEquals(200, $response->getStatusCode());
            
        } catch (RequestException $e) {
            $this->fail('Valid filters validation failed: ' . $e->getMessage());
        }
    }
}

