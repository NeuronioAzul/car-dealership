<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Config;

use App\Infrastructure\Config\JWTConfig;
use PHPUnit\Framework\TestCase;

class JWTConfigTest extends TestCase
{
    private array $originalEnv;

    protected function setUp(): void
    {
        // Store original environment variables
        $this->originalEnv = [
            'JWT_SECRET' => $_ENV['JWT_SECRET'] ?? null,
            'JWT_ALGORITHM' => $_ENV['JWT_ALGORITHM'] ?? null,
            'JWT_EXPIRATION' => $_ENV['JWT_EXPIRATION'] ?? null,
            'JWT_REFRESH_EXPIRATION' => $_ENV['JWT_REFRESH_EXPIRATION'] ?? null,
        ];
    }

    protected function tearDown(): void
    {
        // Restore original environment variables
        foreach ($this->originalEnv as $key => $value) {
            if ($value === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $value;
            }
        }
    }

    public function test_constructor_with_all_env_variables(): void
    {
        $_ENV['JWT_SECRET'] = 'test-secret-key';
        $_ENV['JWT_ALGORITHM'] = 'HS512';
        $_ENV['JWT_EXPIRATION'] = '7200';
        $_ENV['JWT_REFRESH_EXPIRATION'] = '1209600';

        $config = new JWTConfig();

        $this->assertEquals('test-secret-key', $config->secret);
        $this->assertEquals('HS512', $config->algorithm);
        $this->assertEquals(7200, $config->expiration);
        $this->assertEquals(1209600, $config->refreshExpiration);
    }

    public function test_constructor_with_default_algorithm(): void
    {
        $_ENV['JWT_SECRET'] = 'test-secret-key';
        unset($_ENV['JWT_ALGORITHM']);
        $_ENV['JWT_EXPIRATION'] = '3600';
        $_ENV['JWT_REFRESH_EXPIRATION'] = '604800';

        $config = new JWTConfig();

        $this->assertEquals('test-secret-key', $config->secret);
        $this->assertEquals('HS256', $config->algorithm);
        $this->assertEquals(3600, $config->expiration);
        $this->assertEquals(604800, $config->refreshExpiration);
    }

    public function test_constructor_with_default_expiration(): void
    {
        $_ENV['JWT_SECRET'] = 'test-secret-key';
        $_ENV['JWT_ALGORITHM'] = 'HS256';
        unset($_ENV['JWT_EXPIRATION']);
        $_ENV['JWT_REFRESH_EXPIRATION'] = '604800';

        $config = new JWTConfig();

        $this->assertEquals('test-secret-key', $config->secret);
        $this->assertEquals('HS256', $config->algorithm);
        $this->assertEquals(3600, $config->expiration);
        $this->assertEquals(604800, $config->refreshExpiration);
    }

    public function test_constructor_with_default_refresh_expiration(): void
    {
        $_ENV['JWT_SECRET'] = 'test-secret-key';
        $_ENV['JWT_ALGORITHM'] = 'HS256';
        $_ENV['JWT_EXPIRATION'] = '3600';
        unset($_ENV['JWT_REFRESH_EXPIRATION']);

        $config = new JWTConfig();

        $this->assertEquals('test-secret-key', $config->secret);
        $this->assertEquals('HS256', $config->algorithm);
        $this->assertEquals(3600, $config->expiration);
        $this->assertEquals(604800, $config->refreshExpiration);
    }

    public function test_constructor_with_all_defaults(): void
    {
        $_ENV['JWT_SECRET'] = 'test-secret-key';
        unset($_ENV['JWT_ALGORITHM']);
        unset($_ENV['JWT_EXPIRATION']);
        unset($_ENV['JWT_REFRESH_EXPIRATION']);

        $config = new JWTConfig();

        $this->assertEquals('test-secret-key', $config->secret);
        $this->assertEquals('HS256', $config->algorithm);
        $this->assertEquals(3600, $config->expiration);
        $this->assertEquals(604800, $config->refreshExpiration);
    }

    public function test_constructor_throws_exception_when_secret_missing(): void
    {
        unset($_ENV['JWT_SECRET']);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('JWT_SECRET not found');

        new JWTConfig();
    }

    public function test_properties_are_readonly(): void
    {
        $_ENV['JWT_SECRET'] = 'test-secret-key';

        $config = new JWTConfig();

        // Verify properties are readonly by checking they're public readonly
        $reflection = new \ReflectionClass($config);
        
        $secretProperty = $reflection->getProperty('secret');
        $this->assertTrue($secretProperty->isReadOnly());
        
        $algorithmProperty = $reflection->getProperty('algorithm');
        $this->assertTrue($algorithmProperty->isReadOnly());
        
        $expirationProperty = $reflection->getProperty('expiration');
        $this->assertTrue($expirationProperty->isReadOnly());
        
        $refreshExpirationProperty = $reflection->getProperty('refreshExpiration');
        $this->assertTrue($refreshExpirationProperty->isReadOnly());
    }

    public function test_expiration_values_are_integers(): void
    {
        $_ENV['JWT_SECRET'] = 'test-secret-key';
        $_ENV['JWT_EXPIRATION'] = '7200';
        $_ENV['JWT_REFRESH_EXPIRATION'] = '1209600';

        $config = new JWTConfig();

        $this->assertIsInt($config->expiration);
        $this->assertIsInt($config->refreshExpiration);
        $this->assertEquals(7200, $config->expiration);
        $this->assertEquals(1209600, $config->refreshExpiration);
    }
}
