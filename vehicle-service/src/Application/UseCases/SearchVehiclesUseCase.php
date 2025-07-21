<?php

namespace App\Application\UseCases;

use App\Domain\Repositories\VehicleRepositoryInterface;

class SearchVehiclesUseCase
{
    private VehicleRepositoryInterface $vehicleRepository;

    public function __construct(VehicleRepositoryInterface $vehicleRepository)
    {
        $this->vehicleRepository = $vehicleRepository;
    }

    public function execute(array $criteria): array
    {
        // Validar critérios de busca
        $validCriteria = $this->validateCriteria($criteria);
        
        $vehicles = $this->vehicleRepository->search($validCriteria);

        return [
            'vehicles' => array_map(function($vehicle) {
                return $vehicle->toArray();
            }, $vehicles),
            'total' => count($vehicles),
            'criteria' => $validCriteria
        ];
    }

    private function validateCriteria(array $criteria): array
    {
        $validCriteria = [];

        // Marca
        if (!empty($criteria['brand'])) {
            $validCriteria['brand'] = trim($criteria['brand']);
        }

        // Modelo
        if (!empty($criteria['model'])) {
            $validCriteria['model'] = trim($criteria['model']);
        }

        // Ano (faixa)
        if (!empty($criteria['year_from']) && is_numeric($criteria['year_from'])) {
            $validCriteria['year_from'] = (int) $criteria['year_from'];
        }

        if (!empty($criteria['year_to']) && is_numeric($criteria['year_to'])) {
            $validCriteria['year_to'] = (int) $criteria['year_to'];
        }

        // Preço (faixa)
        if (!empty($criteria['price_from']) && is_numeric($criteria['price_from'])) {
            $validCriteria['price_from'] = (float) $criteria['price_from'];
        }

        if (!empty($criteria['price_to']) && is_numeric($criteria['price_to'])) {
            $validCriteria['price_to'] = (float) $criteria['price_to'];
        }

        // Tipo de combustível
        if (!empty($criteria['fuel_type'])) {
            $validFuelTypes = ['Gasolina', 'Etanol', 'Flex', 'Diesel', 'Hibrido', 'Eletrico'];
            if (in_array($criteria['fuel_type'], $validFuelTypes)) {
                $validCriteria['fuel_type'] = $criteria['fuel_type'];
            }
        }

        // Tipo de transmissão
        if (!empty($criteria['transmission_type'])) {
            $validTransmissionTypes = ['Manual', 'Automatico', 'CVT'];
            if (in_array($criteria['transmission_type'], $validTransmissionTypes)) {
                $validCriteria['transmission_type'] = $criteria['transmission_type'];
            }
        }

        // Cor
        if (!empty($criteria['color'])) {
            $validCriteria['color'] = trim($criteria['color']);
        }

        // Status (para admin)
        if (!empty($criteria['status'])) {
            $validStatuses = ['available', 'reserved', 'sold'];
            if (in_array($criteria['status'], $validStatuses)) {
                $validCriteria['status'] = $criteria['status'];
            }
        }

        return $validCriteria;
    }
}

