<?php

namespace App\Infrastructure\Database;

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

    public function save(Vehicle $vehicle): bool
    {
        $sql = "
            INSERT INTO vehicles (
                id, brand, model, color, manufacturing_year, model_year, mileage,
                fuel_type, body_type, steering_type, price, transmission_type,
                seats, license_plate_end, description, status, created_at, updated_at
            ) VALUES (
                :id, :brand, :model, :color, :manufacturing_year, :model_year, :mileage,
                :fuel_type, :body_type, :steering_type, :price, :transmission_type,
                :seats, :license_plate_end, :description, :status, :created_at, :updated_at
            )
        ";

        $stmt = $this->connection->prepare($sql);
        
        return $stmt->execute([
            'id' => $vehicle->getId(),
            'brand' => $vehicle->getBrand(),
            'model' => $vehicle->getModel(),
            'color' => $vehicle->getColor(),
            'manufacturing_year' => $vehicle->getManufacturingYear(),
            'model_year' => $vehicle->getModelYear(),
            'mileage' => $vehicle->getMileage(),
            'fuel_type' => $vehicle->getFuelType(),
            'body_type' => $vehicle->getBodyType(),
            'steering_type' => $vehicle->getSteeringType(),
            'price' => $vehicle->getPrice(),
            'transmission_type' => $vehicle->getTransmissionType(),
            'seats' => $vehicle->getSeats(),
            'license_plate_end' => $vehicle->getLicensePlateEnd(),
            'description' => $vehicle->getDescription(),
            'status' => $vehicle->getStatus(),
            'created_at' => $vehicle->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $vehicle->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function findById(string $id): ?Vehicle
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
            $sql .= " AND model_year >= :year_from";
            $params['year_from'] = $criteria['year_from'];
        }
        
        if (!empty($criteria['year_to'])) {
            $sql .= " AND model_year <= :year_to";
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

    public function update(Vehicle $vehicle): bool
    {
        $sql = "
            UPDATE vehicles SET
                brand = :brand,
                model = :model,
                color = :color,
                manufacturing_year = :manufacturing_year,
                model_year = :model_year,
                mileage = :mileage,
                fuel_type = :fuel_type,
                body_type = :body_type,
                steering_type = :steering_type,
                price = :price,
                transmission_type = :transmission_type,
                seats = :seats,
                license_plate_end = :license_plate_end,
                description = :description,
                status = :status,
                updated_at = :updated_at
            WHERE id = :id
        ";

        $stmt = $this->connection->prepare($sql);
        
        return $stmt->execute([
            'id' => $vehicle->getId(),
            'brand' => $vehicle->getBrand(),
            'model' => $vehicle->getModel(),
            'color' => $vehicle->getColor(),
            'manufacturing_year' => $vehicle->getManufacturingYear(),
            'model_year' => $vehicle->getModelYear(),
            'mileage' => $vehicle->getMileage(),
            'fuel_type' => $vehicle->getFuelType(),
            'body_type' => $vehicle->getBodyType(),
            'steering_type' => $vehicle->getSteeringType(),
            'price' => $vehicle->getPrice(),
            'transmission_type' => $vehicle->getTransmissionType(),
            'seats' => $vehicle->getSeats(),
            'license_plate_end' => $vehicle->getLicensePlateEnd(),
            'description' => $vehicle->getDescription(),
            'status' => $vehicle->getStatus(),
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

    private function mapToVehicle(array $data): Vehicle
    {
        $vehicle = new Vehicle(
            $data['brand'],
            $data['model'],
            $data['color'],
            $data['manufacturing_year'],
            $data['model_year'],
            $data['mileage'],
            $data['fuel_type'],
            $data['body_type'],
            $data['steering_type'],
            $data['price'],
            $data['transmission_type'],
            $data['seats'],
            $data['license_plate_end'],
            $data['description']
        );

        // Usar reflection para definir propriedades privadas
        $reflection = new \ReflectionClass($vehicle);
        
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($vehicle, $data['id']);
        
        $statusProperty = $reflection->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($vehicle, $data['status']);
        
        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($vehicle, new DateTime($data['created_at']));
        
        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($vehicle, new DateTime($data['updated_at']));
        
        if ($data['deleted_at']) {
            $deletedAtProperty = $reflection->getProperty('deletedAt');
            $deletedAtProperty->setAccessible(true);
            $deletedAtProperty->setValue($vehicle, new DateTime($data['deleted_at']));
        }

        return $vehicle;
    }
}

