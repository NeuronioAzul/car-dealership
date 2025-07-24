<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\Entities\Sale;
use App\Domain\Repositories\SaleRepositoryInterface;
use DateTime;
use PDO;

class SaleRepository implements SaleRepositoryInterface
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(Sale $sale): bool
    {
        $sql = '
            INSERT INTO sales (
                id, customer_id, vehicle_id, reservation_id, payment_id, sale_price,
                status, contract_pdf_path, invoice_pdf_path, sale_date,
                created_at, updated_at
            ) VALUES (
                :id, :customer_id, :vehicle_id, :reservation_id, :payment_id, :sale_price,
                :status, :contract_pdf_path, :invoice_pdf_path, :sale_date,
                :created_at, :updated_at
            )
        ';

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            'id' => $sale->getId(),
            'customer_id' => $sale->getCustomerId(),
            'vehicle_id' => $sale->getVehicleId(),
            'reservation_id' => $sale->getReservationId(),
            'payment_id' => $sale->getPaymentId(),
            'sale_price' => $sale->getSalePrice(),
            'status' => $sale->getStatus(),
            'contract_pdf_path' => $sale->getContractPdfPath(),
            'invoice_pdf_path' => $sale->getInvoicePdfPath(),
            'sale_date' => $sale->getSaleDate()->format('Y-m-d H:i:s'),
            'created_at' => $sale->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $sale->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(string $id): ?Sale
    {
        $sql = 'SELECT * FROM sales WHERE id = :id AND deleted_at IS NULL';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch();

        return $data ? $this->mapToSale($data) : null;
    }

    public function findByCustomerId(string $customerId): array
    {
        $sql = 'SELECT * FROM sales WHERE customer_id = :customer_id AND deleted_at IS NULL ORDER BY created_at DESC';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['customer_id' => $customerId]);

        $sales = [];
        while ($data = $stmt->fetch()) {
            $sales[] = $this->mapToSale($data);
        }

        return $sales;
    }

    public function findByVehicleId(string $vehicleId): ?Sale
    {
        $sql = 'SELECT * FROM sales WHERE vehicle_id = :vehicle_id AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 1';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['vehicle_id' => $vehicleId]);

        $data = $stmt->fetch();

        return $data ? $this->mapToSale($data) : null;
    }

    public function findByReservationId(string $reservationId): ?Sale
    {
        $sql = 'SELECT * FROM sales WHERE reservation_id = :reservation_id AND deleted_at IS NULL';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['reservation_id' => $reservationId]);

        $data = $stmt->fetch();

        return $data ? $this->mapToSale($data) : null;
    }

    public function findByPaymentId(string $paymentId): ?Sale
    {
        $sql = 'SELECT * FROM sales WHERE payment_id = :payment_id AND deleted_at IS NULL';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['payment_id' => $paymentId]);

        $data = $stmt->fetch();

        return $data ? $this->mapToSale($data) : null;
    }

    public function findAll(): array
    {
        $sql = 'SELECT * FROM sales WHERE deleted_at IS NULL ORDER BY created_at DESC';
        $stmt = $this->connection->query($sql);

        $sales = [];
        while ($data = $stmt->fetch()) {
            $sales[] = $this->mapToSale($data);
        }

        return $sales;
    }

    public function update(Sale $sale): bool
    {
        $sql = '
            UPDATE sales SET
                customer_id = :customer_id,
                vehicle_id = :vehicle_id,
                reservation_id = :reservation_id,
                payment_id = :payment_id,
                sale_price = :sale_price,
                status = :status,
                contract_pdf_path = :contract_pdf_path,
                invoice_pdf_path = :invoice_pdf_path,
                sale_date = :sale_date,
                updated_at = :updated_at
            WHERE id = :id
        ';

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            'id' => $sale->getId(),
            'customer_id' => $sale->getCustomerId(),
            'vehicle_id' => $sale->getVehicleId(),
            'reservation_id' => $sale->getReservationId(),
            'payment_id' => $sale->getPaymentId(),
            'sale_price' => $sale->getSalePrice(),
            'status' => $sale->getStatus(),
            'contract_pdf_path' => $sale->getContractPdfPath(),
            'invoice_pdf_path' => $sale->getInvoicePdfPath(),
            'sale_date' => $sale->getSaleDate()->format('Y-m-d H:i:s'),
            'updated_at' => $sale->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function delete(string $id): bool
    {
        $sql = 'UPDATE sales SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id';
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    private function mapToSale(array $data): Sale
    {
        $sale = new Sale(
            $data['customer_id'],
            $data['vehicle_id'],
            $data['reservation_id'],
            $data['payment_id'],
            $data['sale_price']
        );

        // Usar reflection para definir propriedades privadas
        $reflection = new \ReflectionClass($sale);

        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($sale, $data['id']);

        $statusProperty = $reflection->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($sale, $data['status']);

        if ($data['contract_pdf_path']) {
            $contractProperty = $reflection->getProperty('contractPdfPath');
            $contractProperty->setAccessible(true);
            $contractProperty->setValue($sale, $data['contract_pdf_path']);
        }

        if ($data['invoice_pdf_path']) {
            $invoiceProperty = $reflection->getProperty('invoicePdfPath');
            $invoiceProperty->setAccessible(true);
            $invoiceProperty->setValue($sale, $data['invoice_pdf_path']);
        }

        $saleDateProperty = $reflection->getProperty('saleDate');
        $saleDateProperty->setAccessible(true);
        $saleDateProperty->setValue($sale, new DateTime($data['sale_date']));

        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($sale, new DateTime($data['created_at']));

        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($sale, new DateTime($data['updated_at']));

        if ($data['deleted_at']) {
            $deletedAtProperty = $reflection->getProperty('deletedAt');
            $deletedAtProperty->setAccessible(true);
            $deletedAtProperty->setValue($sale, new DateTime($data['deleted_at']));
        }

        return $sale;
    }
}
