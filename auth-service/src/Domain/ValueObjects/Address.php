<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

class Address
{
    private string $street;
    private string $number;
    private string $neighborhood;
    private string $city;
    private string $state;
    private string $zipCode;

    public function __construct(
        string $street,
        string $number,
        string $neighborhood,
        string $city,
        string $state,
        string $zipCode
    ) {
        $this->street = $street;
        $this->number = $number;
        $this->neighborhood = $neighborhood;
        $this->city = $city;
        $this->state = $state;
        $this->zipCode = $zipCode;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getNeighborhood(): string
    {
        return $this->neighborhood;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function getFullAddress(): string
    {
        return "{$this->street}, {$this->number}, {$this->neighborhood}, {$this->city} - {$this->state}, {$this->zipCode}";
    }

    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'number' => $this->number,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zipCode,
        ];
    }
}
