<?php

namespace App\Infrastructure\Database;

use App\Domain\Entities\Payment;
use App\Domain\Repositories\PaymentRepositoryInterface;
use PDO;
use DateTime;

class PaymentRepository implements PaymentRepositoryInterface
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(Payment $payment): bool
    {
        $sql = "
            INSERT INTO payments (
                id, customer_id, reservation_id, vehicle_id, payment_code, amount,
                status, method, transaction_id, gateway_response,
                created_at, updated_at, processed_at
            ) VALUES (
                :id, :customer_id, :reservation_id, :vehicle_id, :payment_code, :amount,
                :status, :method, :transaction_id, :gateway_response,
                :created_at, :updated_at, :processed_at
            )
        ";

        $stmt = $this->connection->prepare($sql);
        
        return $stmt->execute([
            'id' => $payment->getId(),
            'customer_id' => $payment->getCustomerId(),
            'reservation_id' => $payment->getReservationId(),
            'vehicle_id' => $payment->getVehicleId(),
            'payment_code' => $payment->getPaymentCode(),
            'amount' => $payment->getAmount(),
            'status' => $payment->getStatus(),
            'method' => $payment->getMethod(),
            'transaction_id' => $payment->getTransactionId(),
            'gateway_response' => $payment->getGatewayResponse(),
            'created_at' => $payment->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $payment->getUpdatedAt()->format('Y-m-d H:i:s'),
            'processed_at' => $payment->getProcessedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    public function findById(string $id): ?Payment
    {
        $sql = "SELECT * FROM payments WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $data = $stmt->fetch();
        
        return $data ? $this->mapToPayment($data) : null;
    }

    public function findByPaymentCode(string $paymentCode): ?Payment
    {
        $sql = "SELECT * FROM payments WHERE payment_code = :payment_code AND deleted_at IS NULL";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['payment_code' => $paymentCode]);
        
        $data = $stmt->fetch();
        
        return $data ? $this->mapToPayment($data) : null;
    }

    public function findByCustomerId(string $customerId): array
    {
        $sql = "SELECT * FROM payments WHERE customer_id = :customer_id AND deleted_at IS NULL ORDER BY created_at DESC";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['customer_id' => $customerId]);
        
        $payments = [];
        while ($data = $stmt->fetch()) {
            $payments[] = $this->mapToPayment($data);
        }
        
        return $payments;
    }

    public function findByReservationId(string $reservationId): ?Payment
    {
        $sql = "SELECT * FROM payments WHERE reservation_id = :reservation_id AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['reservation_id' => $reservationId]);
        
        $data = $stmt->fetch();
        
        return $data ? $this->mapToPayment($data) : null;
    }

    public function findByTransactionId(string $transactionId): ?Payment
    {
        $sql = "SELECT * FROM payments WHERE transaction_id = :transaction_id AND deleted_at IS NULL";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['transaction_id' => $transactionId]);
        
        $data = $stmt->fetch();
        
        return $data ? $this->mapToPayment($data) : null;
    }

    public function findPending(): array
    {
        $sql = "SELECT * FROM payments WHERE status = 'pending' AND deleted_at IS NULL ORDER BY created_at ASC";
        $stmt = $this->connection->query($sql);
        
        $payments = [];
        while ($data = $stmt->fetch()) {
            $payments[] = $this->mapToPayment($data);
        }
        
        return $payments;
    }

    public function update(Payment $payment): bool
    {
        $sql = "
            UPDATE payments SET
                customer_id = :customer_id,
                reservation_id = :reservation_id,
                vehicle_id = :vehicle_id,
                payment_code = :payment_code,
                amount = :amount,
                status = :status,
                method = :method,
                transaction_id = :transaction_id,
                gateway_response = :gateway_response,
                updated_at = :updated_at,
                processed_at = :processed_at
            WHERE id = :id
        ";

        $stmt = $this->connection->prepare($sql);
        
        return $stmt->execute([
            'id' => $payment->getId(),
            'customer_id' => $payment->getCustomerId(),
            'reservation_id' => $payment->getReservationId(),
            'vehicle_id' => $payment->getVehicleId(),
            'payment_code' => $payment->getPaymentCode(),
            'amount' => $payment->getAmount(),
            'status' => $payment->getStatus(),
            'method' => $payment->getMethod(),
            'transaction_id' => $payment->getTransactionId(),
            'gateway_response' => $payment->getGatewayResponse(),
            'updated_at' => $payment->getUpdatedAt()->format('Y-m-d H:i:s'),
            'processed_at' => $payment->getProcessedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    public function delete(string $id): bool
    {
        $sql = "UPDATE payments SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        
        return $stmt->execute(['id' => $id]);
    }

    private function mapToPayment(array $data): Payment
    {
        $payment = new Payment(
            $data['customer_id'],
            $data['reservation_id'],
            $data['vehicle_id'],
            $data['payment_code'],
            $data['amount'],
            $data['method']
        );

        // Usar reflection para definir propriedades privadas
        $reflection = new \ReflectionClass($payment);
        
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($payment, $data['id']);
        
        $statusProperty = $reflection->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($payment, $data['status']);
        
        if ($data['transaction_id']) {
            $transactionIdProperty = $reflection->getProperty('transactionId');
            $transactionIdProperty->setAccessible(true);
            $transactionIdProperty->setValue($payment, $data['transaction_id']);
        }
        
        if ($data['gateway_response']) {
            $gatewayResponseProperty = $reflection->getProperty('gatewayResponse');
            $gatewayResponseProperty->setAccessible(true);
            $gatewayResponseProperty->setValue($payment, $data['gateway_response']);
        }
        
        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($payment, new DateTime($data['created_at']));
        
        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($payment, new DateTime($data['updated_at']));
        
        if ($data['processed_at']) {
            $processedAtProperty = $reflection->getProperty('processedAt');
            $processedAtProperty->setAccessible(true);
            $processedAtProperty->setValue($payment, new DateTime($data['processed_at']));
        }
        
        if ($data['deleted_at']) {
            $deletedAtProperty = $reflection->getProperty('deletedAt');
            $deletedAtProperty->setAccessible(true);
            $deletedAtProperty->setValue($payment, new DateTime($data['deleted_at']));
        }

        return $payment;
    }
}

