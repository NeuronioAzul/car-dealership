<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Repositories\VehicleRepositoryInterface;

class GetVehicleDetailsUseCase
{
    private VehicleRepositoryInterface $vehicleRepository;

    public function __construct(VehicleRepositoryInterface $vehicleRepository)
    {
        $this->vehicleRepository = $vehicleRepository;
    }

    public function execute(string $vehicleId): array
    {
        $vehicle = $this->vehicleRepository->findById($vehicleId);

        if (!$vehicle) {
            throw new \Exception('Veículo não encontrado', 404);
        }

        if ($vehicle->isDeleted()) {
            throw new \Exception('Veículo não disponível', 404);
        }

        return $vehicle->toArray();
    }
}
