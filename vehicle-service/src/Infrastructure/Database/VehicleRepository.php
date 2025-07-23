<?php

namespace App\Infrastructure\Database;

use App\Application\DTOs\VehicleDTO;
use App\Domain\Entities\Vehicle;
use App\Domain\Repositories\VehicleRepositoryInterface;
use PDO;
use PDOException;
use Exception;
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

        $arrVehicle = $vehicle->toArray();
        unset($arrVehicle['id']);
        unset($arrVehicle['deleted_at']);

        $stmt = $this->connection->prepare($sql);

        $result = $stmt->execute($arrVehicle);

        return $result;
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
        return $stmt->execute($vehicle->toArray());

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
        $vehicle = new VehicleDTO($data);
        return $vehicle;
    }
}
