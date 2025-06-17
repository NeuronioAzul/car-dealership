<?php

namespace App\Domain\Entities;

use Ramsey\Uuid\Uuid;
use DateTime;

class Vehicle
{
    private string $id;
    private string $brand;
    private string $model;
    private string $color;
    private int $manufacturingYear;
    private int $modelYear;
    private int $mileage;
    private string $fuelType; // Etanol, Gasolina, Flex, Diesel
    private string $bodyType;
    private string $steeringType; // Hidráulica, Elétrica, Mecânica
    private float $price;
    private string $transmissionType; // Manual, Automático, CVT
    private int $seats;
    private string $licensePlateEnd;
    private string $description;
    private string $status; // available, reserved, sold
    private DateTime $createdAt;
    private DateTime $updatedAt;
    private ?DateTime $deletedAt;

    public function __construct(
        string $brand,
        string $model,
        string $color,
        int $manufacturingYear,
        int $modelYear,
        int $mileage,
        string $fuelType,
        string $bodyType,
        string $steeringType,
        float $price,
        string $transmissionType,
        int $seats,
        string $licensePlateEnd,
        string $description
    ) {
        $this->id = Uuid::uuid6()->toString();
        $this->brand = $brand;
        $this->model = $model;
        $this->color = $color;
        $this->manufacturingYear = $manufacturingYear;
        $this->modelYear = $modelYear;
        $this->mileage = $mileage;
        $this->fuelType = $fuelType;
        $this->bodyType = $bodyType;
        $this->steeringType = $steeringType;
        $this->price = $price;
        $this->transmissionType = $transmissionType;
        $this->seats = $seats;
        $this->licensePlateEnd = $licensePlateEnd;
        $this->description = $description;
        $this->status = 'available';
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->deletedAt = null;
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getManufacturingYear(): int
    {
        return $this->manufacturingYear;
    }

    public function getModelYear(): int
    {
        return $this->modelYear;
    }

    public function getMileage(): int
    {
        return $this->mileage;
    }

    public function getFuelType(): string
    {
        return $this->fuelType;
    }

    public function getBodyType(): string
    {
        return $this->bodyType;
    }

    public function getSteeringType(): string
    {
        return $this->steeringType;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getTransmissionType(): string
    {
        return $this->transmissionType;
    }

    public function getSeats(): int
    {
        return $this->seats;
    }

    public function getLicensePlateEnd(): string
    {
        return $this->licensePlateEnd;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStatus(): string
    {
        return $this->status;
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
    public function setBrand(string $brand): void
    {
        $this->brand = $brand;
        $this->updatedAt = new DateTime();
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
        $this->updatedAt = new DateTime();
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
        $this->updatedAt = new DateTime();
    }

    public function setManufacturingYear(int $manufacturingYear): void
    {
        $this->manufacturingYear = $manufacturingYear;
        $this->updatedAt = new DateTime();
    }

    public function setModelYear(int $modelYear): void
    {
        $this->modelYear = $modelYear;
        $this->updatedAt = new DateTime();
    }

    public function setMileage(int $mileage): void
    {
        $this->mileage = $mileage;
        $this->updatedAt = new DateTime();
    }

    public function setFuelType(string $fuelType): void
    {
        $this->fuelType = $fuelType;
        $this->updatedAt = new DateTime();
    }

    public function setBodyType(string $bodyType): void
    {
        $this->bodyType = $bodyType;
        $this->updatedAt = new DateTime();
    }

    public function setSteeringType(string $steeringType): void
    {
        $this->steeringType = $steeringType;
        $this->updatedAt = new DateTime();
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
        $this->updatedAt = new DateTime();
    }

    public function setTransmissionType(string $transmissionType): void
    {
        $this->transmissionType = $transmissionType;
        $this->updatedAt = new DateTime();
    }

    public function setSeats(int $seats): void
    {
        $this->seats = $seats;
        $this->updatedAt = new DateTime();
    }

    public function setLicensePlateEnd(string $licensePlateEnd): void
    {
        $this->licensePlateEnd = $licensePlateEnd;
        $this->updatedAt = new DateTime();
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
        $this->updatedAt = new DateTime();
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->updatedAt = new DateTime();
    }

    public function reserve(): void
    {
        $this->status = 'reserved';
        $this->updatedAt = new DateTime();
    }

    public function markAsSold(): void
    {
        $this->status = 'sold';
        $this->updatedAt = new DateTime();
    }

    public function makeAvailable(): void
    {
        $this->status = 'available';
        $this->updatedAt = new DateTime();
    }

    public function delete(): void
    {
        $this->deletedAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available' && !$this->isDeleted();
    }

    public function isReserved(): bool
    {
        return $this->status === 'reserved' && !$this->isDeleted();
    }

    public function isSold(): bool
    {
        return $this->status === 'sold' && !$this->isDeleted();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'brand' => $this->brand,
            'model' => $this->model,
            'color' => $this->color,
            'manufacturing_year' => $this->manufacturingYear,
            'model_year' => $this->modelYear,
            'mileage' => $this->mileage,
            'fuel_type' => $this->fuelType,
            'body_type' => $this->bodyType,
            'steering_type' => $this->steeringType,
            'price' => $this->price,
            'transmission_type' => $this->transmissionType,
            'seats' => $this->seats,
            'license_plate_end' => $this->licensePlateEnd,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deletedAt?->format('Y-m-d H:i:s')
        ];
    }
}

