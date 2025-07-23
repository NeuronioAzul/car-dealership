<?php

namespace App\Application\UseCases;

use App\Domain\Repositories\VehicleRepositoryInterface;

class ListVehiclesUseCase
{
    private VehicleRepositoryInterface $vehicleRepository;

    public function __construct(VehicleRepositoryInterface $vehicleRepository)
    {
        $this->vehicleRepository = $vehicleRepository;
    }

    public function execute(bool $availableOnly = true): array
    {
        match ($availableOnly) {
            true => $vehicles = $this->vehicleRepository->findAvailable(),
            false => $vehicles = $this->vehicleRepository->findAll(),
        };

        return array_map(function($vehicle) {
            return $vehicle->toArray();
        }, $vehicles);
    }
}

