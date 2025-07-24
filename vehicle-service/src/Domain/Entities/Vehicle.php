<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use DateTime;

class Vehicle
{
    private string $id;
    private string $brand;
    private string $model;
    private int $year;
    private string $color;
    private string $fuelType;
    private string $transmissionType;
    private int $mileage;
    private float $price;
    private string $description;
    private string $status;
    private ?array $features;
    private ?string $engineSize;
    private ?int $doors;
    private ?int $seats;
    private ?int $trunkCapacity;
    private ?float $purchasePrice;
    private ?float $profitMargin;
    private ?string $supplier;
    private ?string $chassisNumber;
    private ?string $licensePlate;
    private ?string $renavam;
    private DateTime $createdAt;
    private DateTime $updatedAt;
    private ?DateTime $deletedAt;

    /**
     * Vehicle constructor.
     *
     * @param string|null $id
     * @param string $brand
     * @param string $model
     * @param int $year
     * @param string $color
     * @param string $fuelType
     * @param string $transmissionType
     * @param int $mileage
     * @param float $price
     * @param string $description
     * @param string $status
     * @param array|null $features
     * @param string|null $engineSize
     * @param int|null $doors
     * @param int|null $seats
     * @param int|null $trunkCapacity
     * @param float|null $purchasePrice
     * @param float|null $profitMargin
     * @param string|null $supplier
     * @param string|null $chassisNumber
     * @param string|null $licensePlate
     * @param string|null $renavam
     * @param DateTime|null $createdAt
     * @param DateTime|null $updatedAt
     * @param DateTime|null $deletedAt
     */
    public function __construct(
        ?string $id,
        string $brand,
        string $model,
        int $year,
        string $color,
        string $fuelType,
        string $transmissionType,
        int $mileage,
        float $price,
        string $description = '',
        string $status = 'available',
        ?array $features = [],
        ?string $engineSize = null,
        ?int $doors = null,
        ?int $seats = null,
        ?int $trunkCapacity = null,
        ?float $purchasePrice = null,
        ?float $profitMargin = null,
        ?string $supplier = null,
        ?string $chassisNumber = null,
        ?string $licensePlate = null,
        ?string $renavam = null,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null,
        ?DateTime $deletedAt = null
    ) {
        $this->id = $id ?? null;
        $this->brand = $brand;
        $this->model = $model;
        $this->year = $year;
        $this->color = $color;
        $this->fuelType = $fuelType;
        $this->transmissionType = $transmissionType;
        $this->mileage = $mileage;
        $this->price = $price;
        $this->description = $description;
        $this->status = $status;
        $this->features = $features;
        $this->engineSize = $engineSize;
        $this->doors = $doors;
        $this->seats = $seats;
        $this->trunkCapacity = $trunkCapacity;
        $this->purchasePrice = $purchasePrice;
        $this->profitMargin = $profitMargin;
        $this->supplier = $supplier;
        $this->chassisNumber = $chassisNumber;
        $this->licensePlate = $licensePlate;
        $this->renavam = $renavam;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
        $this->deletedAt = $deletedAt;
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

    public function getYear(): int
    {
        return $this->year;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getFuelType(): string
    {
        return $this->fuelType;
    }

    public function getTransmissionType(): string
    {
        return $this->transmissionType;
    }

    public function getMileage(): int
    {
        return $this->mileage;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getFeatures(): ?array
    {
        return $this->features;
    }

    public function getEngineSize(): ?string
    {
        return $this->engineSize;
    }

    public function getDoors(): ?int
    {
        return $this->doors;
    }

    public function getSeats(): ?int
    {
        return $this->seats;
    }

    public function getTrunkCapacity(): ?int
    {
        return $this->trunkCapacity;
    }

    public function getPurchasePrice(): ?float
    {
        return $this->purchasePrice;
    }

    public function getProfitMargin(): ?float
    {
        return $this->profitMargin;
    }

    public function getSupplier(): ?string
    {
        return $this->supplier;
    }

    public function getChassisNumber(): ?string
    {
        return $this->chassisNumber;
    }

    public function getLicensePlate(): ?string
    {
        return $this->licensePlate;
    }

    public function getRenavam(): ?string
    {
        return $this->renavam;
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

    public function setYear(int $year): void
    {
        $this->year = $year;
        $this->updatedAt = new DateTime();
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
        $this->updatedAt = new DateTime();
    }

    public function setFuelType(string $fuelType): void
    {
        $this->fuelType = $fuelType;
        $this->updatedAt = new DateTime();
    }

    public function setTransmissionType(string $transmissionType): void
    {
        $this->transmissionType = $transmissionType;
        $this->updatedAt = new DateTime();
    }

    public function setMileage(int $mileage): void
    {
        $this->mileage = $mileage;
        $this->updatedAt = new DateTime();
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
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

    public function setFeatures(?array $features): void
    {
        $this->features = $features;
        $this->updatedAt = new DateTime();
    }

    public function setEngineSize(?string $engineSize): void
    {
        $this->engineSize = $engineSize;
        $this->updatedAt = new DateTime();
    }

    public function setDoors(?int $doors): void
    {
        $this->doors = $doors;
        $this->updatedAt = new DateTime();
    }

    public function setSeats(?int $seats): void
    {
        $this->seats = $seats;
        $this->updatedAt = new DateTime();
    }

    public function setTrunkCapacity(?int $trunkCapacity): void
    {
        $this->trunkCapacity = $trunkCapacity;
        $this->updatedAt = new DateTime();
    }

    public function setPurchasePrice(?float $purchasePrice): void
    {
        $this->purchasePrice = $purchasePrice;
        $this->updatedAt = new DateTime();
    }

    public function setProfitMargin(?float $profitMargin): void
    {
        $this->profitMargin = $profitMargin;
        $this->updatedAt = new DateTime();
    }

    public function setSupplier(?string $supplier): void
    {
        $this->supplier = $supplier;
        $this->updatedAt = new DateTime();
    }

    public function setChassisNumber(?string $chassisNumber): void
    {
        $this->chassisNumber = $chassisNumber;
        $this->updatedAt = new DateTime();
    }

    public function setLicensePlate(?string $licensePlate): void
    {
        $this->licensePlate = $licensePlate;
        $this->updatedAt = new DateTime();
    }

    public function setRenavam(?string $renavam): void
    {
        $this->renavam = $renavam;
        $this->updatedAt = new DateTime();
    }

    // Métodos utilitários
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
            'year' => $this->year,
            'color' => $this->color,
            'fuel_type' => $this->fuelType,
            'transmission_type' => $this->transmissionType,
            'mileage' => $this->mileage,
            'price' => $this->price,
            'description' => $this->description,
            'status' => $this->status,
            'features' => $this->features,
            'engine_size' => $this->engineSize,
            'doors' => $this->doors,
            'seats' => $this->seats,
            'trunk_capacity' => $this->trunkCapacity,
            'purchase_price' => $this->purchasePrice,
            'profit_margin' => $this->profitMargin,
            'supplier' => $this->supplier,
            'chassis_number' => $this->chassisNumber,
            'license_plate' => $this->licensePlate,
            'renavam' => $this->renavam,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deletedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['brand'] ?? '',
            $data['model'] ?? '',
            (int) $data['year'] ?? 0,
            $data['color'] ?? '',
            $data['fuel_type'] ?? '',
            $data['transmission_type'] ?? '',
            (int) $data['mileage'] ?? 0,
            (float) $data['price'] ?? 0.0,
            $data['description'] ?? '',
            $data['status'] ?? 'available',
            $data['features'] ?? [],
            $data['engine_size'] ?? null,
            isset($data['doors']) ? (int) $data['doors'] : null,
            isset($data['seats']) ? (int) $data['seats'] : null,
            isset($data['trunk_capacity']) ? (int) $data['trunk_capacity'] : null,
            isset($data['purchase_price']) ? (float) $data['purchase_price'] : null,
            isset($data['profit_margin']) ? (float) $data['profit_margin'] : null,
            $data['supplier'] ?? null,
            $data['chassis_number'] ?? null,
            $data['license_plate'] ?? null,
            $data['renavam'] ?? null,
            isset($data['created_at']) && $data['created_at'] ? new \DateTime($data['created_at']) : null,
            isset($data['updated_at']) && $data['updated_at'] ? new \DateTime($data['updated_at']) : null,
            isset($data['deleted_at']) && $data['deleted_at'] ? new \DateTime($data['deleted_at']) : null
        );
    }
}
