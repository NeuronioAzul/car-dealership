<?php

namespace App\Application\UseCases;

use App\Infrastructure\Database\VehicleRepository;
use App\Application\DTOs\VehicleDTO;
use App\Domain\Repositories\VehicleRepositoryInterface;

class UpdateVehicleUseCase
{
    public function __construct(private readonly VehicleRepositoryInterface $vehicleRepository){}

    public function execute(VehicleDTO $vehicleDTO): VehicleDTO
    {
        // Aqui você pode adicionar validações de negócio, se necessário

        // Atualiza o veículo no repositório
        $this->vehicleRepository->update($vehicleDTO);

        // Retorna o DTO atualizado (pode ser útil para retornar o ID gerado, etc)
        return $vehicleDTO;
    }
}