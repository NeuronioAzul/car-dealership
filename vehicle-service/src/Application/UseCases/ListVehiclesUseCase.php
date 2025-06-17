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
        if ($availableOnly) {
            $vehicles = $this->vehicleRepository->findAvailable();
        } else {
            $vehicles = $this->vehicleRepository->findAll();
        }

        return array_map(function($vehicle) {
            return $vehicle->toArray();
        }, $vehicles);
    }
}

