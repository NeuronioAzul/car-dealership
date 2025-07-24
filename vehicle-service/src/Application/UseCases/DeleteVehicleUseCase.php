<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Repositories\VehicleRepositoryInterface;
use Ramsey\Uuid\Uuid;

class DeleteVehicleUseCase
{
    private VehicleRepositoryInterface $vehicleRepository;

    public function __construct(VehicleRepositoryInterface $vehicleRepository)
    {
        $this->vehicleRepository = $vehicleRepository;
    }

    public function execute(string $id): void
    {
        // Validar o ID do veículo
        if (empty($id) || !Uuid::isValid($id)) {
            throw new \InvalidArgumentException('ID do veículo inválido');
        }

        // Chamar o repositório para deletar o veículo
        $this->vehicleRepository->delete($id);
    }
}
