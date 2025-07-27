<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use Symfony\Component\Validator\Constraints as Assert;

class CustomerAddress
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $street;

    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    private string $number;

    #[Assert\Length(max: 100)]
    private string $complement;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $neighborhood;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private string $city;

    #[Assert\NotBlank]
    #[Assert\Length(max: 2)]
    #[Assert\Regex(pattern: '/^[A-Z]{2}$/')]
    private string $state;

    #[Assert\NotBlank]
    #[Assert\Length(max: 10)]
    #[Assert\Regex(pattern: '/^\d{5}-?\d{3}$/')]
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
