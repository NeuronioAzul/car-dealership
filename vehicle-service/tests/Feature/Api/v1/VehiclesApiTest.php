<?php


use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

class VehiclesApiTest extends TestCase
{


    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new Client([
            'base_uri' => 'http://vehicle-service', // ajuste se necessário
            'http_errors' => false
        ]);
    }

    public function testGetEndpointBase()
    {
        $response = $this->client->get('');
        $statusCode = $response->getStatusCode();
        $this->assertEquals(200, $statusCode);
    }

    public function testGetVehiclesEndpoint()
    {
        $response = $this->client->get('/api/v1/vehicles/');
        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody(), true);

        $this->assertEquals(200, $statusCode);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('data', $body);
    }

    // insert a new vehicle
    public function testInsertVehicleEndpoint()
    {
        $response = $this->client->post('/api/v1/vehicles/', [
            'json' => [
                'make' => 'Test',
                'model' => 'Vehicle',
                'year' => 2020
            ]
        ]);
        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody(), true);

        $this->assertEquals(201, $statusCode);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('data', $body);
        $this->assertEquals('Test', $body['data']['make']);
        $this->assertEquals('Vehicle', $body['data']['model']);
        $this->assertEquals(2020, $body['data']['year']);
    }

    public function testGetVehicleByIdEndpoint()
    {
        $response = $this->client->get('/api/v1/vehicles/1f05f884-7c35-616a-9407-f2b79333dd0e');
        $statusCode = $response->getStatusCode();
        $body = json_decode($response->getBody(), true);

        $this->assertEquals(200, $statusCode);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('data', $body);
        $this->assertEquals('1f05f884-7c35-616a-9407-f2b79333dd0e', $body['data']['id']);
    }

}
