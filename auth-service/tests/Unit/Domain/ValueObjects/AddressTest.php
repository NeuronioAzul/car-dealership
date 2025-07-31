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

    public function test_address_creation_with_all_fields(): void
    {
        $address = new Address(
            'Main Street',
            '123',
            'Downtown',
            'New York',
            'NY',
            '10001'
        );

        $this->assertEquals('Main Street', $address->getStreet());
        $this->assertEquals('123', $address->getNumber());
        $this->assertEquals('Downtown', $address->getNeighborhood());
        $this->assertEquals('New York', $address->getCity());
        $this->assertEquals('NY', $address->getState());
        $this->assertEquals('10001', $address->getZipCode());
    }

    public function test_address_to_array(): void
    {
        $address = new Address(
            'Broadway',
            '456',
            'Hollywood',
            'Los Angeles',
            'CA',
            '90210'
        );

        $expected = [
            'street' => 'Broadway',
            'number' => '456',
            'neighborhood' => 'Hollywood',
            'city' => 'Los Angeles',
            'state' => 'CA',
            'zip_code' => '90210'
        ];

        $this->assertEquals($expected, $address->toArray());
    }

    public function test_address_with_empty_strings(): void
    {
        $address = new Address('', '', '', '', '', '');

        $this->assertEquals('', $address->getStreet());
        $this->assertEquals('', $address->getNumber());
        $this->assertEquals('', $address->getNeighborhood());
        $this->assertEquals('', $address->getCity());
        $this->assertEquals('', $address->getState());
        $this->assertEquals('', $address->getZipCode());
    }

    public function test_address_with_special_characters(): void
    {
        $address = new Address(
            'Rua São João',
            '123',
            'Centro',
            'São Paulo',
            'SP',
            '01234-567'
        );

        $this->assertEquals('Rua São João', $address->getStreet());
        $this->assertEquals('123', $address->getNumber());
        $this->assertEquals('Centro', $address->getNeighborhood());
        $this->assertEquals('São Paulo', $address->getCity());
        $this->assertEquals('SP', $address->getState());
        $this->assertEquals('01234-567', $address->getZipCode());
    }

    public function test_address_with_very_long_strings(): void
    {
        $longString = str_repeat('A', 100);
        
        $address = new Address(
            $longString,
            $longString,
            $longString,
            $longString,
            $longString,
            $longString
        );

        $this->assertEquals($longString, $address->getStreet());
        $this->assertEquals($longString, $address->getNumber());
        $this->assertEquals($longString, $address->getNeighborhood());
        $this->assertEquals($longString, $address->getCity());
        $this->assertEquals($longString, $address->getState());
        $this->assertEquals($longString, $address->getZipCode());
    }

    public function test_address_with_numeric_strings(): void
    {
        $address = new Address('123', '456', '789', '000', '111', '22222');

        $this->assertEquals('123', $address->getStreet());
        $this->assertEquals('456', $address->getNumber());
        $this->assertEquals('789', $address->getNeighborhood());
        $this->assertEquals('000', $address->getCity());
        $this->assertEquals('111', $address->getState());
        $this->assertEquals('22222', $address->getZipCode());
    }

    public function test_address_get_full_address(): void
    {
        $address = new Address('Main St', '100', 'Center', 'Boston', 'MA', '02101');
        $expected = 'Main St, 100, Center, Boston - MA, 02101';
        
        $this->assertEquals($expected, $address->getFullAddress());
    }

    public function test_address_get_full_address_with_empty_fields(): void
    {
        $address = new Address('', '', '', '', '', '');
        $expected = ', , ,  - , ';
        
        $this->assertEquals($expected, $address->getFullAddress());
    }

    public function test_address_immutability(): void
    {
        $address = new Address('Street', 'Number', 'Neighborhood', 'City', 'State', 'Zip');

        // Verify that getters return the same values consistently
        $this->assertEquals('Street', $address->getStreet());
        $this->assertEquals('Street', $address->getStreet());
        
        $this->assertEquals('City', $address->getCity());
        $this->assertEquals('City', $address->getCity());
    }

    public function test_multiple_address_instances_independence(): void
    {
        $address1 = new Address('Street1', 'Number1', 'Neighborhood1', 'City1', 'State1', 'Zip1');
        $address2 = new Address('Street2', 'Number2', 'Neighborhood2', 'City2', 'State2', 'Zip2');

        $this->assertNotEquals($address1->getStreet(), $address2->getStreet());
        $this->assertNotEquals($address1->getNumber(), $address2->getNumber());
        $this->assertNotEquals($address1->getNeighborhood(), $address2->getNeighborhood());
        $this->assertNotEquals($address1->getCity(), $address2->getCity());
        $this->assertNotEquals($address1->getState(), $address2->getState());
        $this->assertNotEquals($address1->getZipCode(), $address2->getZipCode());
    }

    public function test_address_construction_with_variables(): void
    {
        $street = 'Variable Street';
        $number = 'Variable Number';
        $neighborhood = 'Variable Neighborhood';
        $city = 'Variable City';
        $state = 'Variable State';
        $zipCode = 'Variable Zip';

        $address = new Address($street, $number, $neighborhood, $city, $state, $zipCode);

        $this->assertEquals($street, $address->getStreet());
        $this->assertEquals($number, $address->getNumber());
        $this->assertEquals($neighborhood, $address->getNeighborhood());
        $this->assertEquals($city, $address->getCity());
        $this->assertEquals($state, $address->getState());
        $this->assertEquals($zipCode, $address->getZipCode());
    }

    public function test_address_class_exists(): void
    {
        $this->assertTrue(class_exists(Address::class));
    }

    public function test_address_methods_exist(): void
    {
        $reflection = new \ReflectionClass(Address::class);
        
        $this->assertTrue($reflection->hasMethod('getStreet'));
        $this->assertTrue($reflection->hasMethod('getNumber'));
        $this->assertTrue($reflection->hasMethod('getNeighborhood'));
        $this->assertTrue($reflection->hasMethod('getCity'));
        $this->assertTrue($reflection->hasMethod('getState'));
        $this->assertTrue($reflection->hasMethod('getZipCode'));
        $this->assertTrue($reflection->hasMethod('getFullAddress'));
        $this->assertTrue($reflection->hasMethod('toArray'));
    }

    public function test_address_constructor_parameters(): void
    {
        $reflection = new \ReflectionClass(Address::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        $this->assertCount(6, $parameters);
        
        $this->assertEquals('street', $parameters[0]->getName());
        $this->assertEquals('number', $parameters[1]->getName());
        $this->assertEquals('neighborhood', $parameters[2]->getName());
        $this->assertEquals('city', $parameters[3]->getName());
        $this->assertEquals('state', $parameters[4]->getName());
        $this->assertEquals('zipCode', $parameters[5]->getName());
    }

    public function test_address_to_array_structure(): void
    {
        $address = new Address('Test', 'Test', 'Test', 'Test', 'Test', 'Test');
        $array = $address->toArray();
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('street', $array);
        $this->assertArrayHasKey('number', $array);
        $this->assertArrayHasKey('neighborhood', $array);
        $this->assertArrayHasKey('city', $array);
        $this->assertArrayHasKey('state', $array);
        $this->assertArrayHasKey('zip_code', $array);
        $this->assertCount(6, $array);
    }

    public function test_address_method_return_types(): void
    {
        $address = new Address('Test', 'Test', 'Test', 'Test', 'Test', 'Test');
        
        $this->assertIsString($address->getStreet());
        $this->assertIsString($address->getNumber());
        $this->assertIsString($address->getNeighborhood());
        $this->assertIsString($address->getCity());
        $this->assertIsString($address->getState());
        $this->assertIsString($address->getZipCode());
        $this->assertIsString($address->getFullAddress());
        $this->assertIsArray($address->toArray());
    }

    public function test_address_full_address_format(): void
    {
        $address = new Address('Oak Street', '42', 'Midtown', 'Chicago', 'IL', '60601');
        $fullAddress = $address->getFullAddress();
        
        // Test that the format includes all components
        $this->assertStringContainsString('Oak Street', $fullAddress);
        $this->assertStringContainsString('42', $fullAddress);
        $this->assertStringContainsString('Midtown', $fullAddress);
        $this->assertStringContainsString('Chicago', $fullAddress);
        $this->assertStringContainsString('IL', $fullAddress);
        $this->assertStringContainsString('60601', $fullAddress);
        
        // Test the exact format
        $expected = 'Oak Street, 42, Midtown, Chicago - IL, 60601';
        $this->assertEquals($expected, $fullAddress);
    }

    public function test_address_properties_are_private(): void
    {
        $reflection = new \ReflectionClass(Address::class);
        
        $street = $reflection->getProperty('street');
        $this->assertTrue($street->isPrivate());
        
        $number = $reflection->getProperty('number');
        $this->assertTrue($number->isPrivate());
        
        $neighborhood = $reflection->getProperty('neighborhood');
        $this->assertTrue($neighborhood->isPrivate());
        
        $city = $reflection->getProperty('city');
        $this->assertTrue($city->isPrivate());
        
        $state = $reflection->getProperty('state');
        $this->assertTrue($state->isPrivate());
        
        $zipCode = $reflection->getProperty('zipCode');
        $this->assertTrue($zipCode->isPrivate());
    }
}
