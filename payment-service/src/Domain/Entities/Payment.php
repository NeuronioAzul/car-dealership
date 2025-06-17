<?php

namespace App\Domain\Entities;

use Ramsey\Uuid\Uuid;
use DateTime;

class Payment
{
    private string $id;
    private string $customerId;
    private string $reservationId;
    private string $vehicleId;
    private string $paymentCode;
    private float $amount;
    private string $status; // pending, processing, completed, failed, refunded
    private string $method; // credit_card, debit_card, pix, bank_transfer
    private ?string $transactionId;
    private ?string $gatewayResponse;
    private DateTime $createdAt;
    private DateTime $updatedAt;
    private ?DateTime $processedAt;
    private ?DateTime $deletedAt;

    public function __construct(
        string $customerId,
        string $reservationId,
        string $vehicleId,
        string $paymentCode,
        float $amount,
        string $method = 'credit_card'
    ) {
        $this->id = Uuid::uuid6()->toString();
        $this->customerId = $customerId;
        $this->reservationId = $reservationId;
        $this->vehicleId = $vehicleId;
        $this->paymentCode = $paymentCode;
        $this->amount = $amount;
        $this->status = 'pending';
        $this->method = $method;
        $this->transactionId = null;
        $this->gatewayResponse = null;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->processedAt = null;
        $this->deletedAt = null;
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getReservationId(): string
    {
        return $this->reservationId;
    }

    public function getVehicleId(): string
    {
        return $this->vehicleId;
    }

    public function getPaymentCode(): string
    {
        return $this->paymentCode;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function getGatewayResponse(): ?string
    {
        return $this->gatewayResponse;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getProcessedAt(): ?DateTime
    {
        return $this->processedAt;
    }

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    // Setters
    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->updatedAt = new DateTime();
        
        if (in_array($status, ['completed', 'failed', 'refunded'])) {
            $this->processedAt = new DateTime();
        }
    }

    public function setTransactionId(string $transactionId): void
    {
        $this->transactionId = $transactionId;
        $this->updatedAt = new DateTime();
    }

    public function setGatewayResponse(string $gatewayResponse): void
    {
        $this->gatewayResponse = $gatewayResponse;
        $this->updatedAt = new DateTime();
    }

    // Business methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function startProcessing(): void
    {
        $this->setStatus('processing');
    }

    public function markAsCompleted(string $transactionId, string $gatewayResponse = null): void
    {
        $this->setTransactionId($transactionId);
        if ($gatewayResponse) {
            $this->setGatewayResponse($gatewayResponse);
        }
        $this->setStatus('completed');
    }

    public function markAsFailed(string $gatewayResponse = null): void
    {
        if ($gatewayResponse) {
            $this->setGatewayResponse($gatewayResponse);
        }
        $this->setStatus('failed');
    }

    public function refund(string $gatewayResponse = null): void
    {
        if ($gatewayResponse) {
            $this->setGatewayResponse($gatewayResponse);
        }
        $this->setStatus('refunded');
    }

    public function delete(): void
    {
        $this->deletedAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customerId,
            'reservation_id' => $this->reservationId,
            'vehicle_id' => $this->vehicleId,
            'payment_code' => $this->paymentCode,
            'amount' => $this->amount,
            'status' => $this->status,
            'method' => $this->method,
            'transaction_id' => $this->transactionId,
            'gateway_response' => $this->gatewayResponse,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'processed_at' => $this->processedAt?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deletedAt?->format('Y-m-d H:i:s')
        ];
    }
}

