<?php

namespace App\Domain\Entities;

use Ramsey\Uuid\Uuid;
use DateTime;

class Reservation
{
    private string $id;
    private string $customerId;
    private string $vehicleId;
    private string $status; // active, expired, cancelled, paid
    private DateTime $expiresAt;
    private ?string $paymentCode;
    private DateTime $createdAt;
    private DateTime $updatedAt;
    private ?DateTime $deletedAt;

    public function __construct(
        string $customerId,
        string $vehicleId
    ) {
        $this->id = Uuid::uuid6()->toString();
        $this->customerId = $customerId;
        $this->vehicleId = $vehicleId;
        $this->status = 'active';
        $this->expiresAt = new DateTime('+' . ($_ENV['RESERVATION_EXPIRY_HOURS'] ?? 24) . ' hours');
        $this->paymentCode = null;
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
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

    public function getVehicleId(): string
    {
        return $this->vehicleId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getExpiresAt(): DateTime
    {
        return $this->expiresAt;
    }

    public function getPaymentCode(): ?string
    {
        return $this->paymentCode;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
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
    }

    public function setPaymentCode(string $paymentCode): void
    {
        $this->paymentCode = $paymentCode;
        $this->updatedAt = new DateTime();
    }

    public function extendExpiry(int $hours = 24): void
    {
        $this->expiresAt = new DateTime('+' . $hours . ' hours');
        $this->updatedAt = new DateTime();
    }

    // Business methods
    public function isExpired(): bool
    {
        return new DateTime() > $this->expiresAt;
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired() && !$this->isDeleted();
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->updatedAt = new DateTime();
    }

    public function markAsPaid(): void
    {
        $this->status = 'paid';
        $this->updatedAt = new DateTime();
    }

    public function expire(): void
    {
        $this->status = 'expired';
        $this->updatedAt = new DateTime();
    }

    public function delete(): void
    {
        $this->deletedAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function generatePaymentCode(): string
    {
        // Gerar código de pagamento único
        $code = 'PAY' . strtoupper(substr(md5($this->id . time()), 0, 10));
        $this->setPaymentCode($code);
        return $code;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customerId,
            'vehicle_id' => $this->vehicleId,
            'status' => $this->status,
            'expires_at' => $this->expiresAt->format('Y-m-d H:i:s'),
            'payment_code' => $this->paymentCode,
            'is_expired' => $this->isExpired(),
            'is_active' => $this->isActive(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deletedAt?->format('Y-m-d H:i:s')
        ];
    }
}

