<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\Entities\Reservation;
use App\Domain\Repositories\ReservationRepositoryInterface;
use DateTime;
use PDO;

class ReservationRepository implements ReservationRepositoryInterface
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(Reservation $reservation): bool
    {
        $sql = '
            INSERT INTO reservations (
                id, customer_id, vehicle_id, status, expires_at, payment_code,
                created_at, updated_at
            ) VALUES (
                :id, :customer_id, :vehicle_id, :status, :expires_at, :payment_code,
                :created_at, :updated_at
            )
        ';

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            'id' => $reservation->getId(),
            'customer_id' => $reservation->getCustomerId(),
            'vehicle_id' => $reservation->getVehicleId(),
            'status' => $reservation->getStatus(),
            'expires_at' => $reservation->getExpiresAt()->format('Y-m-d H:i:s'),
            'payment_code' => $reservation->getPaymentCode(),
            'created_at' => $reservation->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $reservation->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(string $id): ?Reservation
    {
        $sql = 'SELECT * FROM reservations WHERE id = :id AND deleted_at IS NULL';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch();

        return $data ? $this->mapToReservation($data) : null;
    }

    public function findByCustomerId(string $customerId): array
    {
        $sql = 'SELECT * FROM reservations WHERE customer_id = :customer_id AND deleted_at IS NULL ORDER BY created_at DESC';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['customer_id' => $customerId]);

        $reservations = [];
        while ($data = $stmt->fetch()) {
            $reservations[] = $this->mapToReservation($data);
        }

        return $reservations;
    }

    public function findActiveByCustomerId(string $customerId): array
    {
        $sql = "
            SELECT * FROM reservations 
            WHERE customer_id = :customer_id 
            AND status = 'active' 
            AND expires_at > NOW() 
            AND deleted_at IS NULL 
            ORDER BY created_at DESC
        ";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['customer_id' => $customerId]);

        $reservations = [];
        while ($data = $stmt->fetch()) {
            $reservations[] = $this->mapToReservation($data);
        }

        return $reservations;
    }

    public function findActiveByVehicleId(string $vehicleId): ?Reservation
    {
        $sql = "
            SELECT * FROM reservations 
            WHERE vehicle_id = :vehicle_id 
            AND status = 'active' 
            AND expires_at > NOW() 
            AND deleted_at IS NULL 
            ORDER BY created_at DESC 
            LIMIT 1
        ";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['vehicle_id' => $vehicleId]);

        $data = $stmt->fetch();

        return $data ? $this->mapToReservation($data) : null;
    }

    public function findByPaymentCode(string $paymentCode): ?Reservation
    {
        $sql = 'SELECT * FROM reservations WHERE payment_code = :payment_code AND deleted_at IS NULL';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['payment_code' => $paymentCode]);

        $data = $stmt->fetch();

        return $data ? $this->mapToReservation($data) : null;
    }

    public function findExpired(): array
    {
        $sql = "
            SELECT * FROM reservations 
            WHERE status = 'active' 
            AND expires_at <= NOW() 
            AND deleted_at IS NULL
        ";
        $stmt = $this->connection->query($sql);

        $reservations = [];
        while ($data = $stmt->fetch()) {
            $reservations[] = $this->mapToReservation($data);
        }

        return $reservations;
    }

    public function update(Reservation $reservation): bool
    {
        $sql = '
            UPDATE reservations SET
                customer_id = :customer_id,
                vehicle_id = :vehicle_id,
                status = :status,
                expires_at = :expires_at,
                payment_code = :payment_code,
                updated_at = :updated_at
            WHERE id = :id
        ';

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            'id' => $reservation->getId(),
            'customer_id' => $reservation->getCustomerId(),
            'vehicle_id' => $reservation->getVehicleId(),
            'status' => $reservation->getStatus(),
            'expires_at' => $reservation->getExpiresAt()->format('Y-m-d H:i:s'),
            'payment_code' => $reservation->getPaymentCode(),
            'updated_at' => $reservation->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function delete(string $id): bool
    {
        $sql = 'UPDATE reservations SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id';
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    private function mapToReservation(array $data): Reservation
    {
        $reservation = new Reservation(
            $data['customer_id'],
            $data['vehicle_id']
        );

        // Usar reflection para definir propriedades privadas
        $reflection = new \ReflectionClass($reservation);

        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($reservation, $data['id']);

        $statusProperty = $reflection->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($reservation, $data['status']);

        $expiresAtProperty = $reflection->getProperty('expiresAt');
        $expiresAtProperty->setAccessible(true);
        $expiresAtProperty->setValue($reservation, new DateTime($data['expires_at']));

        if ($data['payment_code']) {
            $paymentCodeProperty = $reflection->getProperty('paymentCode');
            $paymentCodeProperty->setAccessible(true);
            $paymentCodeProperty->setValue($reservation, $data['payment_code']);
        }

        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($reservation, new DateTime($data['created_at']));

        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($reservation, new DateTime($data['updated_at']));

        if ($data['deleted_at']) {
            $deletedAtProperty = $reflection->getProperty('deletedAt');
            $deletedAtProperty->setAccessible(true);
            $deletedAtProperty->setValue($reservation, new DateTime($data['deleted_at']));
        }

        return $reservation;
    }
}
