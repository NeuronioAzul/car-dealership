<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\ValueObjects\Address;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    public function testAddressCanBeCreated(): void
    {
        $address = new Address(
            'Rua das Flores',
            '123',
            'Centro',
            'São Paulo',
            'SP',
            '01234-567'
        );

        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals('Rua das Flores', $address->getStreet());
        $this->assertEquals('123', $address->getNumber());
        $this->assertEquals('Centro', $address->getNeighborhood());
        $this->assertEquals('São Paulo', $address->getCity());
        $this->assertEquals('SP', $address->getState());
        $this->assertEquals('01234-567', $address->getZipCode());
    }

    public function testAddressToArray(): void
    {
        $address = new Address(
            'Rua das Flores',
            '123',
            'Centro',
            'São Paulo',
            'SP',
            '01234-567'
        );

        $array = $address->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('Rua das Flores', $array['street']);
        $this->assertEquals('123', $array['number']);
        $this->assertEquals('Centro', $array['neighborhood']);
        $this->assertEquals('São Paulo', $array['city']);
        $this->assertEquals('SP', $array['state']);
        $this->assertEquals('01234-567', $array['zip_code']);
    }

    public function testAddressGetFullAddress(): void
    {
        $address = new Address(
            'Rua das Flores',
            '123',
            'Centro',
            'São Paulo',
            'SP',
            '01234-567'
        );

        $fullAddress = $address->getFullAddress();
        $expectedAddress = 'Rua das Flores, 123, Centro, São Paulo - SP, 01234-567';

        $this->assertEquals($expectedAddress, $fullAddress);
    }
}
