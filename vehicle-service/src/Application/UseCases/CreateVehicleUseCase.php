<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\VehicleDTO;
use App\Domain\Repositories\VehicleRepositoryInterface;

class CreateVehicleUseCase
{
    public function __construct(private readonly VehicleRepositoryInterface $vehicleRepository)
    {
    }

    public function execute(VehicleDTO $vehicleDTO): VehicleDTO
    {
        // Aqui você pode adicionar validações de negócio, se necessário

        // Salva o veículo no repositório
        $this->vehicleRepository->save($vehicleDTO);

        // Retorna o DTO criado (pode ser útil para retornar o ID gerado, etc)
        return $vehicleDTO;
    }
}
