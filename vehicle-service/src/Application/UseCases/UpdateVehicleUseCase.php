<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\VehicleDTO;
use App\Application\Services\DatabaseErrorHandler;
use App\Domain\Repositories\VehicleRepositoryInterface;

class UpdateVehicleUseCase
{
    public function __construct(private readonly VehicleRepositoryInterface $vehicleRepository)
    {
    }

    public function execute(VehicleDTO $vehicleDTO): VehicleDTO
    {
        // Verificar se o veículo existe
        $existingVehicle = $this->vehicleRepository->findById($vehicleDTO->id);
        if (!$existingVehicle) {
            throw new \Exception('Veículo não encontrado', 404);
        }

        // Validações de negócio antes de atualizar
        $this->validateBusinessRules($vehicleDTO, $existingVehicle);

        try {
            // Atualiza o veículo no repositório
            $this->vehicleRepository->update($vehicleDTO);
        } catch (\PDOException $e) {
            // Traduzir erros de banco para mensagens amigáveis
            DatabaseErrorHandler::handlePDOException($e);
        }

        // Retorna o DTO atualizado (pode ser útil para retornar o ID gerado, etc)
        return $vehicleDTO;
    }

    private function validateBusinessRules(VehicleDTO $vehicleDTO, VehicleDTO $existingVehicle): void
    {
        // Regra: Não permitir alterar status de 'sold' para outros estados
        if ($existingVehicle->status === 'sold' && $vehicleDTO->status !== 'sold') {
            throw new \Exception('Não é possível alterar o status de um veículo já vendido', 422);
        }

        // Regra: Verificar unicidade de chassi se alterado
        if ($vehicleDTO->chassisNumber && $vehicleDTO->chassisNumber !== $existingVehicle->chassisNumber) {
            $vehicleWithChassis = $this->vehicleRepository->findByChassisNumber($vehicleDTO->chassisNumber);
            if ($vehicleWithChassis && $vehicleWithChassis->id !== $existingVehicle->id) {
                throw new \Exception('Número do chassi já está em uso por outro veículo', 422);
            }
        }

        // Regra: Verificar unicidade de placa se alterada
        if ($vehicleDTO->licensePlate && $vehicleDTO->licensePlate !== $existingVehicle->licensePlate) {
            $vehicleWithPlate = $this->vehicleRepository->findByLicensePlate($vehicleDTO->licensePlate);
            if ($vehicleWithPlate && $vehicleWithPlate->id !== $existingVehicle->id) {
                throw new \Exception('Placa já está em uso por outro veículo', 422);
            }
        }

        // Regra: Verificar unicidade de RENAVAM se alterado
        if ($vehicleDTO->renavam && $vehicleDTO->renavam !== $existingVehicle->renavam) {
            $vehicleWithRenavam = $this->vehicleRepository->findByRenavam($vehicleDTO->renavam);
            if ($vehicleWithRenavam && $vehicleWithRenavam->id !== $existingVehicle->id) {
                throw new \Exception('RENAVAM já está em uso por outro veículo', 422);
            }
        }

        // Regra: Preço de venda deve ser maior que preço de compra
        if ($vehicleDTO->purchasePrice && $vehicleDTO->price && 
            $vehicleDTO->price <= $vehicleDTO->purchasePrice) {
            throw new \Exception('Preço de venda deve ser maior que o preço de compra', 422);
        }
    }
}
