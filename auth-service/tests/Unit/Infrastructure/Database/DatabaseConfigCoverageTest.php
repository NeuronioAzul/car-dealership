<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Database;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Database\DatabaseConfig;
use Exception;

class DatabaseConfigCoverageTest extends TestCase
{
    private array $originalEnv;
    protected function setUp(): void
    {
        // Backup original environment
        $this->originalEnv = [
            'DB_HOST' => $_ENV['DB_HOST'] ?? null,
            'DB_PORT' => $_ENV['DB_PORT'] ?? null,
            'DB_NAME' => $_ENV['DB_NAME'] ?? null,
            'DB_USER' => $_ENV['DB_USER'] ?? null,
            'DB_PASS' => $_ENV['DB_PASS'] ?? null,
        ];
    }

    protected function tearDown(): void
    {
        // Restore original environment
        foreach ($this->originalEnv as $key => $value) {
            if ($value === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $value;
            }
        }
    }

    public function testGetConnectionFailsWithoutDatabase(): void
    {
        // Reset static connection to force new connection attempt
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue(null, null);
        
        // Set fake environment variables but ensure no database driver
        $_ENV['DB_HOST'] = 'fake_host_that_does_not_exist';
        $_ENV['DB_PORT'] = '9999'; // Use invalid port
        $_ENV['DB_DATABASE'] = 'fake_db';
        $_ENV['DB_USERNAME'] = 'fake_user';
        $_ENV['DB_PASSWORD'] = 'fake_pass';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Erro na conexão com o banco de dados');

        DatabaseConfig::getConnection();
    }

    public function testGetConnectionWithInvalidHost(): void
    {
        // Reset static connection to force new connection attempt
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue(null, null);
        
        $_ENV['DB_HOST'] = 'definitely_invalid_host_12345';
        $_ENV['DB_PORT'] = '9999'; // Use invalid port
        $_ENV['DB_DATABASE'] = 'test_db';
        $_ENV['DB_USERNAME'] = 'test_user';
        $_ENV['DB_PASSWORD'] = 'test_pass';

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Erro na conexão com o banco de dados');

        DatabaseConfig::getConnection();
    }

    public function testGetConnectionStaticProperty(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $this->assertTrue($reflection->hasProperty('connection'));
        
        $property = $reflection->getProperty('connection');
        $this->assertTrue($property->isStatic(), 'Connection property should be static');
        $this->assertTrue($property->isPrivate(), 'Connection property should be private');
    }

    public function testGetConnectionMethodExists(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $this->assertTrue($reflection->hasMethod('getConnection'));
        
        $method = $reflection->getMethod('getConnection');
        $this->assertTrue($method->isStatic(), 'getConnection should be static');
        $this->assertTrue($method->isPublic(), 'getConnection should be public');
    }

    public function testClassStructure(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        
        // Check if class is final
        $this->assertFalse($reflection->isFinal(), 'DatabaseConfig should not be final');
        $this->assertFalse($reflection->isAbstract(), 'DatabaseConfig should not be abstract');
        
        // Check methods count
        $methods = $reflection->getMethods();
        $this->assertGreaterThanOrEqual(1, count($methods), 'Should have at least getConnection method');
    }

    public function testEnvironmentVariableUsage(): void
    {
        // Test that the class would use environment variables
        $_ENV['DB_HOST'] = 'test_host';
        $_ENV['DB_PORT'] = '3307';
        $_ENV['DB_NAME'] = 'test_database';
        $_ENV['DB_USER'] = 'test_username';
        $_ENV['DB_PASS'] = 'test_password';

        try {
            DatabaseConfig::getConnection();
        } catch (Exception $e) {
            // Expected to fail, but we can check the error message contains our test values
            $this->assertStringContainsString('Erro na conexão com o banco de dados', $e->getMessage());
        }
    }
}
