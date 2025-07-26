<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\VehicleDTO;
use App\Domain\Repositories\VehicleRepositoryInterface;
use Exception;

class PatchVehicleUseCase
{
    public function __construct(private readonly VehicleRepositoryInterface $vehicleRepository)
    {
    }

    public function execute(string $vehicleId, array $fieldsToUpdate): VehicleDTO
    {
        // Verificar se o veículo existe
        $existingVehicle = $this->vehicleRepository->findById($vehicleId);
        if (!$existingVehicle) {
            throw new Exception('Veículo não encontrado', 404);
        }

        // Validações de negócio específicas para PATCH
        $this->validateBusinessRules($fieldsToUpdate, $existingVehicle);

        // Atualizar apenas os campos fornecidos
        $updated = $this->vehicleRepository->partialUpdate($vehicleId, $fieldsToUpdate);
        
        if (!$updated) {
            throw new Exception('Falha ao atualizar o veículo', 500);
        }

        // Retornar o veículo atualizado
        $updatedVehicle = $this->vehicleRepository->findById($vehicleId);
        if (!$updatedVehicle) {
            throw new Exception('Erro ao recuperar veículo atualizado', 500);
        }

        return $updatedVehicle;
    }

    private function validateBusinessRules(array $fieldsToUpdate, VehicleDTO $existingVehicle): void
    {
        // Regra: Não permitir alterar status de 'sold' para outros estados
        if (isset($fieldsToUpdate['status']) && 
            $existingVehicle->status === 'sold' && 
            $fieldsToUpdate['status'] !== 'sold') {
            throw new Exception('Não é possível alterar o status de um veículo já vendido', 422);
        }

        // Regra: Verificar unicidade de chassi se fornecido
        if (isset($fieldsToUpdate['chassis_number']) && 
            $fieldsToUpdate['chassis_number'] !== $existingVehicle->chassisNumber) {
            // Buscar por chassi exato, removendo outros filtros para não interferir
            $existingChassis = $this->vehicleRepository->search([
                'chassis_number' => $fieldsToUpdate['chassis_number'],
                'status' => '' // Remove filtro de status para buscar em todos
            ]);
            // Filtrar para garantir match exato
            foreach ($existingChassis as $vehicle) {
                if ($vehicle->chassisNumber === $fieldsToUpdate['chassis_number']) {
                    throw new Exception('Número do chassi já está em uso por outro veículo', 422);
                }
            }
        }

        // Regra: Verificar unicidade de placa se fornecida
        if (isset($fieldsToUpdate['license_plate']) && 
            $fieldsToUpdate['license_plate'] !== $existingVehicle->licensePlate) {
            // Buscar por placa exata, removendo outros filtros para não interferir
            $existingPlate = $this->vehicleRepository->search([
                'license_plate' => $fieldsToUpdate['license_plate'],
                'status' => '' // Remove filtro de status para buscar em todos
            ]);
            // Filtrar para garantir match exato
            foreach ($existingPlate as $vehicle) {
                if (strtoupper($vehicle->licensePlate) === strtoupper($fieldsToUpdate['license_plate'])) {
                    throw new Exception('Placa já está em uso por outro veículo', 422);
                }
            }
        }

        // Regra: Preço de venda deve ser maior que preço de compra
        $purchasePrice = $fieldsToUpdate['purchase_price'] ?? $existingVehicle->purchasePrice;
        $salePrice = $fieldsToUpdate['price'] ?? $existingVehicle->price;
        
        if ($purchasePrice && $salePrice && $salePrice <= $purchasePrice) {
            throw new Exception('Preço de venda deve ser maior que o preço de compra', 422);
        }

        // Regra: Não permitir zerar campos obrigatórios
        $requiredFields = ['brand', 'model', 'year', 'color', 'fuel_type', 'transmission_type', 'price'];
        foreach ($requiredFields as $field) {
            if (isset($fieldsToUpdate[$field]) && empty($fieldsToUpdate[$field])) {
                throw new Exception("Campo obrigatório '{$field}' não pode ser vazio", 422);
            }
        }
    }
}
