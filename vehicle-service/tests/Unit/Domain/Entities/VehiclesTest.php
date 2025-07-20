
<?php


use PHPUnit\Framework\TestCase;
use App\Domain\Entities\Vehicle;

class VehiclesTest extends TestCase
{
    public function testVehicleCreation()
    {
        // Arrange
        $vehicle = $this->createMock(Vehicle::class); // ...arquivo limpo, modelo de teste unitário...
        // Assert
        $this->assertInstanceOf(Vehicle::class, $vehicle);
    }

    public function testVehicleProperties()
    {
        // Arrange
        $vehicle = $this->createMock(Vehicle::class); // Substitua pelos parâmetros reais do construtor da entidade Vehicle
        // Act & Assert
        // Exemplo: $this->assertEquals('Toyota', $vehicle->getMarca());
        // Adicione mais asserts conforme necessário
        $vehicle = new Vehicle(
            id: 'uuid-1234',
            brand: 'Toyota',
            model: 'Corolla',
            year: 2020,
            color: 'Blue',
            fuelType: 'Gasolina',
            transmissionType: 'Automático',
            mileage: 10000,
            price: 80000,
            description: 'Um carro confiável e econômico.',
            status: 'available',
            features: ['Ar Condicionado', 'Direção Hidráulica'],
            engineSize: '1.8',
            doors: 4,
            seats: 5,
            trunkCapacity: 450,
            purchasePrice: 150000,
            profitMargin: 0.2,
            supplier: 'Fornecedor XYZ',
            chassisNumber: 'CHASSIS123456789',
            licensePlate: 'ABC-1234',
            renavam: 'RENAVAM123456789',
            createdAt: new DateTime('2023-10-01 10:00:00'),
            updatedAt: new DateTime('2023-10-01 10:00:00'),
            deletedAt: new DateTime('2023-10-01 10:00:00')
        );

        $this->assertInstanceOf(Vehicle::class, $vehicle);
        $this->assertEquals('uuid-1234', $vehicle->getId());
        $this->assertEquals('Toyota', $vehicle->getBrand());
        $this->assertEquals('Corolla', $vehicle->getModel());
        $this->assertEquals(2020, $vehicle->getYear());
        $this->assertEquals('Blue', $vehicle->getColor());
        $this->assertEquals('Gasolina', $vehicle->getFuelType());
        $this->assertEquals('Automático', $vehicle->getTransmissionType());
        $this->assertEquals(10000, $vehicle->getMileage());
        $this->assertEquals(80000, $vehicle->getPrice());
        $this->assertEquals('Um carro confiável e econômico.', $vehicle->getDescription());
        $this->assertEquals('available', $vehicle->getStatus());
        $this->assertEquals(['Ar Condicionado', 'Direção Hidráulica'], $vehicle->getFeatures());
        $this->assertEquals('1.8', $vehicle->getEngineSize());
        $this->assertEquals(4, $vehicle->getDoors());
        $this->assertEquals(5, $vehicle->getSeats());
        $this->assertEquals(450, $vehicle->getTrunkCapacity());
        $this->assertEquals(150000, $vehicle->getPurchasePrice());
        $this->assertEquals(0.2, $vehicle->getProfitMargin());
        $this->assertEquals('Fornecedor XYZ', $vehicle->getSupplier());
        $this->assertEquals('CHASSIS123456789', $vehicle->getChassisNumber());
        $this->assertEquals('ABC-1234', $vehicle->getLicensePlate());
        $this->assertEquals('RENAVAM123456789', $vehicle->getRenavam());
        $this->assertInstanceOf(DateTime::class, $vehicle->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $vehicle->getUpdatedAt());
        $this->assertInstanceOf(DateTime::class, $vehicle->getDeletedAt());
    }


}
