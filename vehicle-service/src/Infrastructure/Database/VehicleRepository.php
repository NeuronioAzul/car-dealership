<?php

namespace App\Infrastructure\Database;

use App\Application\DTOs\VehicleDTO;
use App\Domain\Entities\Vehicle;
use App\Domain\Repositories\VehicleRepositoryInterface;
use PDO;
use DateTime;

class VehicleRepository implements VehicleRepositoryInterface
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(VehicleDTO $vehicle): bool
    {
        $sql = "
            INSERT INTO vehicles (
                brand, model, year, color, fuel_type, transmission_type, mileage, price, description, status,
                features, engine_size, doors, seats, trunk_capacity, purchase_price, profit_margin, supplier,
                chassis_number, license_plate, renavam, created_at, updated_at
            ) VALUES (
                :brand, :model, :year, :color, :fuel_type, :transmission_type, :mileage, :price, :description, :status,
                :features, :engine_size, :doors, :seats, :trunk_capacity, :purchase_price, :profit_margin, :supplier,
                :chassis_number, :license_plate, :renavam, :created_at, :updated_at
            )
        ";

        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([
            'brand' => $vehicle->getBrand(),
            'model' => $vehicle->getModel(),
            'year' => $vehicle->getYear(),
            'color' => $vehicle->getColor(),
            'fuel_type' => $vehicle->getFuelType(),
            'transmission_type' => $vehicle->getTransmissionType(),
            'mileage' => $vehicle->getMileage(),
            'price' => $vehicle->getPrice(),
            'description' => $vehicle->getDescription(),
            'status' => $vehicle->getStatus(),
            'features' => json_encode($vehicle->getFeatures()),
            'engine_size' => $vehicle->getEngineSize(),
            'doors' => $vehicle->getDoors(),
            'seats' => $vehicle->getSeats(),
            'trunk_capacity' => $vehicle->getTrunkCapacity(),
            'purchase_price' => $vehicle->getPurchasePrice(),
            'profit_margin' => $vehicle->getProfitMargin(),
            'supplier' => $vehicle->getSupplier(),
            'chassis_number' => $vehicle->getChassisNumber(),
            'license_plate' => $vehicle->getLicensePlate(),
            'renavam' => $vehicle->getRenavam(),
            'created_at' => $vehicle->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $vehicle->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function findById(string $id): ?VehicleDTO
    {
        $sql = "SELECT * FROM vehicles WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $data = $stmt->fetch();
        
        return $data ? $this->mapToVehicle($data) : null;
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM vehicles WHERE deleted_at IS NULL ORDER BY created_at DESC";
        $stmt = $this->connection->query($sql);
        
        $vehicles = [];
        while ($data = $stmt->fetch()) {
            $vehicles[] = $this->mapToVehicle($data);
        }
        
        return $vehicles;
    }

    public function findAvailable(): array
    {
        $sql = "SELECT * FROM vehicles WHERE status = 'available' AND deleted_at IS NULL ORDER BY created_at DESC";
        $stmt = $this->connection->query($sql);
        
        $vehicles = [];
        while ($data = $stmt->fetch()) {
            $vehicles[] = $this->mapToVehicle($data);
        }
        
        return $vehicles;
    }

    public function search(array $criteria): array
    {
        $sql = "SELECT * FROM vehicles WHERE deleted_at IS NULL";
        $params = [];
        
        if (!empty($criteria['brand'])) {
            $sql .= " AND brand LIKE :brand";
            $params['brand'] = '%' . $criteria['brand'] . '%';
        }
        
        if (!empty($criteria['model'])) {
            $sql .= " AND model LIKE :model";
            $params['model'] = '%' . $criteria['model'] . '%';
        }
        
        if (!empty($criteria['year_from'])) {
            $sql .= " AND year >= :year_from";
            $params['year_from'] = $criteria['year_from'];
        }
        
        if (!empty($criteria['year_to'])) {
            $sql .= " AND year <= :year_to";
            $params['year_to'] = $criteria['year_to'];
        }
        
        if (!empty($criteria['price_from'])) {
            $sql .= " AND price >= :price_from";
            $params['price_from'] = $criteria['price_from'];
        }
        
        if (!empty($criteria['price_to'])) {
            $sql .= " AND price <= :price_to";
            $params['price_to'] = $criteria['price_to'];
        }
        
        if (!empty($criteria['fuel_type'])) {
            $sql .= " AND fuel_type = :fuel_type";
            $params['fuel_type'] = $criteria['fuel_type'];
        }
        
        if (!empty($criteria['transmission_type'])) {
            $sql .= " AND transmission_type = :transmission_type";
            $params['transmission_type'] = $criteria['transmission_type'];
        }
        
        if (!empty($criteria['color'])) {
            $sql .= " AND color LIKE :color";
            $params['color'] = '%' . $criteria['color'] . '%';
        }
        
        if (!empty($criteria['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $criteria['status'];
        } else {
            // Por padrão, mostrar apenas disponíveis
            $sql .= " AND status = 'available'";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        
        $vehicles = [];
        while ($data = $stmt->fetch()) {
            $vehicles[] = $this->mapToVehicle($data);
        }
        
        return $vehicles;
    }

    public function update(VehicleDTO $vehicle): bool
    {
        $sql = "
            UPDATE vehicles SET
                brand = :brand,
                model = :model,
                year = :year,
                color = :color,
                fuel_type = :fuel_type,
                transmission_type = :transmission_type,
                mileage = :mileage,
                price = :price,
                description = :description,
                status = :status,
                features = :features,
                engine_size = :engine_size,
                doors = :doors,
                seats = :seats,
                trunk_capacity = :trunk_capacity,
                purchase_price = :purchase_price,
                profit_margin = :profit_margin,
                supplier = :supplier,
                chassis_number = :chassis_number,
                license_plate = :license_plate,
                renavam = :renavam,
                updated_at = :updated_at
            WHERE id = :id
        ";

        $stmt = $this->connection->prepare($sql);
        return $stmt->execute([
            'id' => $vehicle->getId(),
            'brand' => $vehicle->getBrand(),
            'model' => $vehicle->getModel(),
            'year' => $vehicle->getYear(),
            'color' => $vehicle->getColor(),
            'fuel_type' => $vehicle->getFuelType(),
            'transmission_type' => $vehicle->getTransmissionType(),
            'mileage' => $vehicle->getMileage(),
            'price' => $vehicle->getPrice(),
            'description' => $vehicle->getDescription(),
            'status' => $vehicle->getStatus(),
            'features' => json_encode($vehicle->getFeatures()),
            'engine_size' => $vehicle->getEngineSize(),
            'doors' => $vehicle->getDoors(),
            'seats' => $vehicle->getSeats(),
            'trunk_capacity' => $vehicle->getTrunkCapacity(),
            'purchase_price' => $vehicle->getPurchasePrice(),
            'profit_margin' => $vehicle->getProfitMargin(),
            'supplier' => $vehicle->getSupplier(),
            'chassis_number' => $vehicle->getChassisNumber(),
            'license_plate' => $vehicle->getLicensePlate(),
            'renavam' => $vehicle->getRenavam(),
            'updated_at' => $vehicle->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function delete(string $id): bool
    {
        $sql = "UPDATE vehicles SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        
        return $stmt->execute(['id' => $id]);
    }

    public function updateStatus(string $id, string $status): bool
    {
        $sql = "UPDATE vehicles SET status = :status, updated_at = NOW() WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        
        return $stmt->execute(['id' => $id, 'status' => $status]);
    }

    private function mapToVehicle(array $data): VehicleDTO
    {
        return new VehicleDTO(
            $data['id'],
            $data['brand'],
            $data['model'],
            (int)$data['year'],
            $data['color'],
            $data['fuel_type'],
            $data['transmission_type'],
            (int)$data['mileage'],
            (float)$data['price'],
            $data['description'] ?? '',
            $data['status'] ?? 'available',
            isset($data['features']) ? json_decode($data['features'], true) : [],
            $data['engine_size'] ?? null,
            isset($data['doors']) ? (int)$data['doors'] : null,
            isset($data['seats']) ? (int)$data['seats'] : null,
            isset($data['trunk_capacity']) ? (int)$data['trunk_capacity'] : null,
            isset($data['purchase_price']) ? (float)$data['purchase_price'] : null,
            isset($data['profit_margin']) ? (float)$data['profit_margin'] : null,
            $data['supplier'] ?? null,
            $data['chassis_number'] ?? null,
            $data['license_plate'] ?? null,
            $data['renavam'] ?? null,
            new DateTime($data['created_at']),
            new DateTime($data['updated_at']),
            !empty($data['deleted_at']) ? new DateTime($data['deleted_at']) : null
        );
    }
}

