<?php

namespace Tests\Unit\Vehicle;

use PHPUnit\Framework\TestCase;

// Incluir classe do Vehicle
require_once __DIR__ . '/../../../vehicle-service/src/Domain/Entities/Vehicle.php';

use App\Domain\Entities\Vehicle;

class VehicleEntityTest extends TestCase
{
    private Vehicle $vehicle;

    protected function setUp(): void
    {
        $this->vehicle = new Vehicle(
            'Toyota',
            'Corolla',
            2023,
            2023,
            'Branco',
            15000,
            'Flex',
            'Automático',
            85000.00
        );
    }

    public function testVehicleCreation(): void
    {
        $this->assertInstanceOf(Vehicle::class, $this->vehicle);
        $this->assertEquals('Toyota', $this->vehicle->getBrand());
        $this->assertEquals('Corolla', $this->vehicle->getModel());
        $this->assertEquals(2023, $this->vehicle->getManufacturingYear());
        $this->assertEquals(2023, $this->vehicle->getModelYear());
        $this->assertEquals('Branco', $this->vehicle->getColor());
        $this->assertEquals(15000, $this->vehicle->getMileage());
        $this->assertEquals('Flex', $this->vehicle->getFuelType());
        $this->assertEquals('Automático', $this->vehicle->getTransmissionType());
        $this->assertEquals(85000.00, $this->vehicle->getPrice());
        $this->assertEquals('available', $this->vehicle->getStatus());
    }

    public function testVehicleStatusTransitions(): void
    {
        // Testar transição para reservado
        $this->assertTrue($this->vehicle->canBeReserved());
        $this->vehicle->reserve();
        $this->assertEquals('reserved', $this->vehicle->getStatus());
        $this->assertFalse($this->vehicle->canBeReserved());

        // Testar transição para vendido
        $this->assertTrue($this->vehicle->canBeSold());
        $this->vehicle->sell();
        $this->assertEquals('sold', $this->vehicle->getStatus());
        $this->assertFalse($this->vehicle->canBeSold());
        $this->assertFalse($this->vehicle->canBeReserved());

        // Testar que não pode voltar para disponível depois de vendido
        $this->assertFalse($this->vehicle->canBeAvailable());
    }

    public function testVehicleAvailabilityCheck(): void
    {
        $this->assertTrue($this->vehicle->isAvailable());
        
        $this->vehicle->reserve();
        $this->assertFalse($this->vehicle->isAvailable());
        
        $this->vehicle->makeAvailable();
        $this->assertTrue($this->vehicle->isAvailable());
    }

    public function testVehiclePriceValidation(): void
    {
        $this->assertTrue($this->vehicle->isValidPrice(50000.00));
        $this->assertTrue($this->vehicle->isValidPrice(1000000.00));
        $this->assertFalse($this->vehicle->isValidPrice(0));
        $this->assertFalse($this->vehicle->isValidPrice(-1000));
    }

    public function testVehicleYearValidation(): void
    {
        $currentYear = (int) date('Y');
        
        $this->assertTrue($this->vehicle->isValidYear(2020));
        $this->assertTrue($this->vehicle->isValidYear($currentYear));
        $this->assertTrue($this->vehicle->isValidYear($currentYear + 1)); // Próximo ano
        $this->assertFalse($this->vehicle->isValidYear(1800));
        $this->assertFalse($this->vehicle->isValidYear($currentYear + 2)); // Muito no futuro
    }

    public function testVehicleFuelTypeValidation(): void
    {
        $validFuelTypes = ['Gasolina', 'Etanol', 'Flex', 'Diesel', 'Híbrido', 'Elétrico'];
        
        foreach ($validFuelTypes as $fuelType) {
            $this->assertTrue($this->vehicle->isValidFuelType($fuelType));
        }
        
        $this->assertFalse($this->vehicle->isValidFuelType('Carvão'));
        $this->assertFalse($this->vehicle->isValidFuelType(''));
    }

    public function testVehicleTransmissionValidation(): void
    {
        $validTransmissions = ['Manual', 'Automático', 'CVT'];
        
        foreach ($validTransmissions as $transmission) {
            $this->assertTrue($this->vehicle->isValidTransmission($transmission));
        }
        
        $this->assertFalse($this->vehicle->isValidTransmission('Telepático'));
        $this->assertFalse($this->vehicle->isValidTransmission(''));
    }

    public function testVehicleToArray(): void
    {
        $vehicleArray = $this->vehicle->toArray();
        
        $this->assertIsArray($vehicleArray);
        $this->assertArrayHasKey('id', $vehicleArray);
        $this->assertArrayHasKey('brand', $vehicleArray);
        $this->assertArrayHasKey('model', $vehicleArray);
        $this->assertArrayHasKey('manufacturing_year', $vehicleArray);
        $this->assertArrayHasKey('model_year', $vehicleArray);
        $this->assertArrayHasKey('color', $vehicleArray);
        $this->assertArrayHasKey('mileage', $vehicleArray);
        $this->assertArrayHasKey('fuel_type', $vehicleArray);
        $this->assertArrayHasKey('transmission_type', $vehicleArray);
        $this->assertArrayHasKey('price', $vehicleArray);
        $this->assertArrayHasKey('status', $vehicleArray);
        $this->assertArrayHasKey('created_at', $vehicleArray);
    }

    public function testVehicleUpdate(): void
    {
        $newPrice = 90000.00;
        $newMileage = 20000;
        
        $this->vehicle->setPrice($newPrice);
        $this->vehicle->setMileage($newMileage);
        
        $this->assertEquals($newPrice, $this->vehicle->getPrice());
        $this->assertEquals($newMileage, $this->vehicle->getMileage());
        $this->assertNotNull($this->vehicle->getUpdatedAt());
    }

    public function testVehicleSearchCriteria(): void
    {
        // Testar se o veículo atende aos critérios de busca
        $this->assertTrue($this->vehicle->matchesBrand('Toyota'));
        $this->assertFalse($this->vehicle->matchesBrand('Honda'));
        
        $this->assertTrue($this->vehicle->matchesModel('Corolla'));
        $this->assertFalse($this->vehicle->matchesModel('Civic'));
        
        $this->assertTrue($this->vehicle->matchesYearRange(2020, 2025));
        $this->assertFalse($this->vehicle->matchesYearRange(2010, 2020));
        
        $this->assertTrue($this->vehicle->matchesPriceRange(80000, 90000));
        $this->assertFalse($this->vehicle->matchesPriceRange(50000, 70000));
    }

    public function testVehicleSoftDelete(): void
    {
        $this->assertFalse($this->vehicle->isDeleted());
        
        $this->vehicle->softDelete();
        
        $this->assertTrue($this->vehicle->isDeleted());
        $this->assertNotNull($this->vehicle->getDeletedAt());
    }
}

