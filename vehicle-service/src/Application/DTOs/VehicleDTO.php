<?php

declare(strict_types=1);
# Create the Vehicle DTO class

namespace App\Application\DTOs;

use DateTime;
use DateTimeImmutable;
use Ramsey\Uuid\Rfc4122\UuidV6;
use Symfony\Component\Validator\Constraints as Assert;

final class VehicleDTO
{
    #[Assert\Uuid]
    public readonly string $id;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    public readonly string $brand;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 100)]
    public readonly string $model;

    #[Assert\NotBlank]
    #[Assert\Range(min: 1900, max: 2030)]
    public readonly int $year;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 30)]
    public readonly string $color;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['gasoline', 'ethanol', 'flex', 'diesel', 'electric', 'hybrid'])]
    public readonly string $fuelType;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['manual', 'automatic', 'cvt'])]
    public readonly string $transmissionType;

    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    public readonly int $mileage;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public readonly float $price;

    #[Assert\Length(max: 1000)]
    public readonly string $description;

    #[Assert\Choice(choices: ['available', 'reserved', 'sold', 'maintenance'])]
    public readonly string $status;

    #[Assert\Type('array')]
    public readonly ?array $features;

    #[Assert\Length(max: 20)]
    public readonly ?string $engineSize;

    #[Assert\Range(min: 1, max: 10)]
    public readonly ?int $doors;

    #[Assert\Range(min: 1, max: 20)]
    public readonly ?int $seats;

    #[Assert\PositiveOrZero]
    public readonly ?int $trunkCapacity;

    #[Assert\PositiveOrZero]
    public readonly ?float $purchasePrice;

    #[Assert\Range(min: 0, max: 100)]
    public readonly ?float $profitMargin;

    #[Assert\Length(max: 100)]
    public readonly ?string $supplier;

    #[Assert\Length(exactly: 17)]
    public readonly ?string $chassisNumber;

    #[Assert\Length(min: 7, max: 8)]
    public readonly ?string $licensePlate;

    #[Assert\Length(exactly: 11)]
    public readonly ?string $renavam;

    #[Assert\NotBlank]
    #[Assert\Type(DateTimeImmutable::class)]
    public readonly DateTimeImmutable $createdAt;

    #[Assert\Optional]
    #[Assert\DateTime('Y-m-d H:i:s')]
    public readonly DateTime $updatedAt;

    #[Assert\Optional]
    #[Assert\DateTime('Y-m-d H:i:s')]
    public readonly ?DateTime $deletedAt;

    public function __construct(array $input)
    {
        $this->id = $input['id'] ?? UuidV6::uuid6()->toString();
        $this->brand = trim($input['brand'] ?? '');
        $this->model = trim($input['model'] ?? '');
        $this->year = (int) ($input['year'] ?? 0);
        $this->color = trim($input['color'] ?? '');
        $this->fuelType = strtolower(trim($input['fuel_type'] ?? ''));
        $this->transmissionType = strtolower(trim($input['transmission_type'] ?? ''));
        $this->mileage = (int) ($input['mileage'] ?? 0);
        $this->price = (float) ($input['price'] ?? 0.0);
        $this->description = trim($input['description'] ?? '');
        $this->status = strtolower(trim($input['status'] ?? 'available'));
        $this->features = is_array($input['features']) ? $input['features'] : json_decode($input['features'], true) ?? [];
        $this->engineSize = isset($input['engine_size']) ? trim($input['engine_size']) : null;
        $this->doors = isset($input['doors']) ? (int) $input['doors'] : null;
        $this->seats = isset($input['seats']) ? (int) $input['seats'] : null;
        $this->trunkCapacity = isset($input['trunk_capacity']) ? (int) $input['trunk_capacity'] : null;
        $this->purchasePrice = isset($input['purchase_price']) ? (float) $input['purchase_price'] : null;
        $this->profitMargin = isset($input['profit_margin']) ? (float) $input['profit_margin'] : null;
        $this->supplier = isset($input['supplier']) ? trim($input['supplier']) : null;
        $this->chassisNumber = isset($input['chassis_number']) ? trim($input['chassis_number']) : null;
        $this->licensePlate = isset($input['license_plate']) ? strtoupper(trim($input['license_plate'])) : null;
        $this->renavam = isset($input['renavam']) ? trim($input['renavam']) : null;
        $this->createdAt = isset($input['created_at']) && $input['created_at']
            ? new DateTimeImmutable($input['created_at'])
            : new DateTimeImmutable('now');
        $this->updatedAt = isset($input['updated_at']) && $input['updated_at']
            ? new DateTime($input['updated_at'])
            : new DateTime('now');
        $this->deletedAt = isset($input['deleted_at']) && $input['deleted_at']
            ? new DateTime($input['deleted_at'])
            : null;
    }

    public static function fromArray(array $input): self
    {
        return new self($input);
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
            'features' => (isset($this->features) && is_array($this->features)) ? json_encode($this->features) : $this->features,
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

    // Utility methods for status checking
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

    public function isInMaintenance(): bool
    {
        return $this->status === 'maintenance' && !$this->isDeleted();
    }
}
