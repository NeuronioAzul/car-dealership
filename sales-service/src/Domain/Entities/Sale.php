<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use DateTime;
use Ramsey\Uuid\Uuid;

class Sale
{
    private string $id;
    private string $customerId;
    private string $vehicleId;
    private string $reservationId;
    private string $paymentId;
    private float $salePrice;
    private string $status; // pending, completed, cancelled
    private ?string $contractPdfPath;
    private ?string $invoicePdfPath;
    private DateTime $saleDate;
    private DateTime $createdAt;
    private DateTime $updatedAt;
    private ?DateTime $deletedAt;

    public function __construct(
        string $customerId,
        string $vehicleId,
        string $reservationId,
        string $paymentId,
        float $salePrice
    ) {
        $this->id = Uuid::uuid6()->toString();
        $this->customerId = $customerId;
        $this->vehicleId = $vehicleId;
        $this->reservationId = $reservationId;
        $this->paymentId = $paymentId;
        $this->salePrice = $salePrice;
        $this->status = 'pending';
        $this->contractPdfPath = null;
        $this->invoicePdfPath = null;
        $this->saleDate = new DateTime();
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

    public function getReservationId(): string
    {
        return $this->reservationId;
    }

    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    public function getSalePrice(): float
    {
        return $this->salePrice;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getContractPdfPath(): ?string
    {
        return $this->contractPdfPath;
    }

    public function getInvoicePdfPath(): ?string
    {
        return $this->invoicePdfPath;
    }

    public function getSaleDate(): DateTime
    {
        return $this->saleDate;
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

    public function setContractPdfPath(string $contractPdfPath): void
    {
        $this->contractPdfPath = $contractPdfPath;
        $this->updatedAt = new DateTime();
    }

    public function setInvoicePdfPath(string $invoicePdfPath): void
    {
        $this->invoicePdfPath = $invoicePdfPath;
        $this->updatedAt = new DateTime();
    }

    public function setSaleDate(DateTime $saleDate): void
    {
        $this->saleDate = $saleDate;
        $this->updatedAt = new DateTime();
    }

    // Business methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function complete(): void
    {
        $this->setStatus('completed');
    }

    public function cancel(): void
    {
        $this->setStatus('cancelled');
    }

    public function delete(): void
    {
        $this->deletedAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function hasDocuments(): bool
    {
        return $this->contractPdfPath !== null && $this->invoicePdfPath !== null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customerId,
            'vehicle_id' => $this->vehicleId,
            'reservation_id' => $this->reservationId,
            'payment_id' => $this->paymentId,
            'sale_price' => $this->salePrice,
            'status' => $this->status,
            'contract_pdf_path' => $this->contractPdfPath,
            'invoice_pdf_path' => $this->invoicePdfPath,
            'sale_date' => $this->saleDate->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deletedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
