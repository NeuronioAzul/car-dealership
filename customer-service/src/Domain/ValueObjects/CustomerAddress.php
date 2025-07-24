<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

class CustomerAddress
{
    private string $street;
    private string $number;
    private string $complement;
    private string $neighborhood;
    private string $city;
    private string $state;
    private string $zipCode;

    public function __construct(
        string $street,
        string $number,
        string $complement,
        string $neighborhood,
        string $city,
        string $state,
        string $zipCode
    ) {
        $this->street = $street;
        $this->number = $number;
        $this->complement = $complement;
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

    public function getComplement(): string
    {
        return $this->complement;
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
            'complement' => $this->complement,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zipCode,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            street: $data['street'] ?? '',
            number: $data['number'] ?? '',
            complement: $data['complement'] ?? '',
            neighborhood: $data['neighborhood'] ?? '',
            city: $data['city'] ?? '',
            state: $data['state'] ?? '',
            zipCode: $data['zip_code'] ?? ''
        );
    }
}
