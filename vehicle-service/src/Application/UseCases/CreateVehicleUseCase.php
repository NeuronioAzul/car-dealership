<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\VehicleDTO;
use App\Application\Services\DatabaseErrorHandler;
use App\Domain\Repositories\VehicleRepositoryInterface;

class CreateVehicleUseCase
{
    public function __construct(private readonly VehicleRepositoryInterface $vehicleRepository)
    {
    }

    public function execute(VehicleDTO $vehicleDTO): VehicleDTO
    {
        // Validações de negócio antes de criar
        $this->validateBusinessRules($vehicleDTO);

        try {
            // Salva o veículo no repositório
            $this->vehicleRepository->save($vehicleDTO);
        } catch (\PDOException $e) {
            // Traduzir erros de banco para mensagens amigáveis
            DatabaseErrorHandler::handlePDOException($e);
        }

        // Retorna o DTO criado (pode ser útil para retornar o ID gerado, etc)
        return $vehicleDTO;
    }

    private function validateBusinessRules(VehicleDTO $vehicleDTO): void
    {
        // Regra: Verificar unicidade de chassi
        if ($vehicleDTO->chassisNumber) {
            $existingVehicle = $this->vehicleRepository->findByChassisNumber($vehicleDTO->chassisNumber);

            if ($existingVehicle) {
                throw new \Exception('Número do chassi já está em uso por outro veículo', 422);
            }
        }

        // Regra: Verificar unicidade de placa
        if ($vehicleDTO->licensePlate) {
            $existingVehicle = $this->vehicleRepository->findByLicensePlate($vehicleDTO->licensePlate);

            if ($existingVehicle) {
                throw new \Exception('Placa já está em uso por outro veículo', 422);
            }
        }

        // Regra: Verificar unicidade de RENAVAM
        if ($vehicleDTO->renavam) {
            $existingVehicle = $this->vehicleRepository->findByRenavam($vehicleDTO->renavam);

            if ($existingVehicle) {
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
