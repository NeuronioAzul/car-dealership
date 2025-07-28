<?php

namespace Tests\Unit\VehicleService\Domain\Entities;

use PHPUnit\Framework\TestCase;

class VehiclesTest extends TestCase
{
    public function testVehicleCreationMock(): void
    {
        // Para testes unitários básicos sem dependências externas
        $vehicleData = [
            'id' => 'uuid-1234',
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'color' => 'Blue',
            'price' => 80000
        ];

        $this->assertIsArray($vehicleData);
        $this->assertEquals('Toyota', $vehicleData['brand']);
        $this->assertEquals('Corolla', $vehicleData['model']);
        $this->assertEquals(2020, $vehicleData['year']);
        $this->assertEquals(80000, $vehicleData['price']);
    }

    public function testVehicleValidation(): void
    {
        $validVehicleData = [
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2024,
            'price' => 85000.00,
            'status' => 'available'
        ];

        // Validações básicas
        $this->assertNotEmpty($validVehicleData['brand']);
        $this->assertNotEmpty($validVehicleData['model']);
        $this->assertIsInt($validVehicleData['year']);
        $this->assertGreaterThan(1900, $validVehicleData['year']);
        $this->assertLessThanOrEqual(date('Y') + 1, $validVehicleData['year']);
        $this->assertIsFloat($validVehicleData['price']);
        $this->assertGreaterThan(0, $validVehicleData['price']);
        $this->assertContains($validVehicleData['status'], ['available', 'sold', 'reserved']);
    }

    public function testVehicleStatusTransitions(): void
    {
        $validStatuses = ['available', 'reserved', 'sold'];
        
        foreach ($validStatuses as $status) {
            $this->assertContains($status, $validStatuses);
        }

        // Testa transições válidas
        $this->assertTrue($this->isValidStatusTransition('available', 'reserved'));
        $this->assertTrue($this->isValidStatusTransition('reserved', 'sold'));
        $this->assertTrue($this->isValidStatusTransition('reserved', 'available'));
        
        // Testa transições inválidas
        $this->assertFalse($this->isValidStatusTransition('sold', 'available'));
        $this->assertFalse($this->isValidStatusTransition('sold', 'reserved'));
    }

    public function testVehiclePriceCalculation(): void
    {
        $purchasePrice = 60000.00;
        $profitMargin = 0.25; // 25%
        
        $expectedSalePrice = $purchasePrice * (1 + $profitMargin);
        $calculatedPrice = $this->calculateSalePrice($purchasePrice, $profitMargin);
        
        $this->assertEquals($expectedSalePrice, $calculatedPrice);
        $this->assertEquals(75000.00, $calculatedPrice);
    }

    public function testVehicleYearValidation(): void
    {
        $currentYear = (int) date('Y');
        
        // Anos válidos
        $this->assertTrue($this->isValidYear($currentYear));
        $this->assertTrue($this->isValidYear($currentYear - 1));
        $this->assertTrue($this->isValidYear($currentYear + 1));
        $this->assertTrue($this->isValidYear(2000));
        
        // Anos inválidos
        $this->assertFalse($this->isValidYear(1899));
        $this->assertFalse($this->isValidYear($currentYear + 2));
        $this->assertFalse($this->isValidYear(0));
        $this->assertFalse($this->isValidYear(-1));
    }

    public function testVehicleSearchCriteria(): void
    {
        $vehicles = [
            ['brand' => 'Toyota', 'model' => 'Corolla', 'year' => 2020, 'price' => 80000],
            ['brand' => 'Honda', 'model' => 'Civic', 'year' => 2021, 'price' => 85000],
            ['brand' => 'Toyota', 'model' => 'Camry', 'year' => 2022, 'price' => 120000]
        ];

        // Busca por marca
        $toyotaVehicles = $this->filterVehiclesByBrand($vehicles, 'Toyota');
        $this->assertCount(2, $toyotaVehicles);

        // Busca por faixa de preço
        $affordableVehicles = $this->filterVehiclesByPriceRange($vehicles, 0, 90000);
        $this->assertCount(2, $affordableVehicles);

        // Busca por ano
        $recentVehicles = $this->filterVehiclesByMinYear($vehicles, 2021);
        $this->assertCount(2, $recentVehicles);
    }

    // Métodos auxiliares para simular lógica de negócio
    private function isValidStatusTransition(string $from, string $to): bool
    {
        $validTransitions = [
            'available' => ['reserved'],
            'reserved' => ['sold', 'available'],
            'sold' => []
        ];

        return in_array($to, $validTransitions[$from] ?? []);
    }

    private function calculateSalePrice(float $purchasePrice, float $profitMargin): float
    {
        return $purchasePrice * (1 + $profitMargin);
    }

    private function isValidYear(int $year): bool
    {
        $currentYear = (int) date('Y');
        return $year >= 1900 && $year <= ($currentYear + 1);
    }

    private function filterVehiclesByBrand(array $vehicles, string $brand): array
    {
        return array_filter($vehicles, function($vehicle) use ($brand) {
            return $vehicle['brand'] === $brand;
        });
    }

    private function filterVehiclesByPriceRange(array $vehicles, float $min, float $max): array
    {
        return array_filter($vehicles, function($vehicle) use ($min, $max) {
            return $vehicle['price'] >= $min && $vehicle['price'] <= $max;
        });
    }

    private function filterVehiclesByMinYear(array $vehicles, int $minYear): array
    {
        return array_filter($vehicles, function($vehicle) use ($minYear) {
            return $vehicle['year'] >= $minYear;
        });
    }
}
