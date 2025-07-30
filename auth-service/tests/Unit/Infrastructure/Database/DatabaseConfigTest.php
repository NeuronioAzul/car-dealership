<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Database;

use App\Infrastructure\Database\DatabaseConfig;
use PDO;
use PHPUnit\Framework\TestCase;

class DatabaseConfigTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset static connection before each test
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $connectionProperty = $reflection->getProperty('connection');
        $connectionProperty->setAccessible(true);
        $connectionProperty->setValue(null);
    }

    public function test_get_connection_method_exists(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $this->assertTrue($reflection->hasMethod('getConnection'));
        
        $method = $reflection->getMethod('getConnection');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    public function test_connection_property_exists(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $this->assertTrue($reflection->hasProperty('connection'));
        
        $property = $reflection->getProperty('connection');
        $this->assertTrue($property->isStatic());
        $this->assertTrue($property->isPrivate());
    }

    public function test_get_connection_fails_without_environment(): void
    {
        // Test that getConnection fails in test environment without proper DB config
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Erro na conexão com o banco de dados:');
        
        DatabaseConfig::getConnection();
    }

    public function test_static_connection_property_default_value(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $connectionProperty = $reflection->getProperty('connection');
        $connectionProperty->setAccessible(true);
        
        $this->assertNull($connectionProperty->getValue());
    }

    public function test_class_structure(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        
        // Test class namespace
        $this->assertEquals('App\Infrastructure\Database\DatabaseConfig', $reflection->getName());
        
        // Test that it has one static property
        $staticProperties = $reflection->getProperties(\ReflectionProperty::IS_STATIC);
        $this->assertCount(1, $staticProperties);
        $this->assertEquals('connection', $staticProperties[0]->getName());
        
        // Test that it has one public static method
        $publicStaticMethods = array_filter(
            $reflection->getMethods(\ReflectionMethod::IS_STATIC),
            fn($method) => $method->isPublic()
        );
        $this->assertCount(1, $publicStaticMethods);
        $this->assertEquals('getConnection', $publicStaticMethods[0]->getName());
    }

    public function test_method_signature(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $getConnectionMethod = $reflection->getMethod('getConnection');
        
        // Should have no parameters
        $this->assertCount(0, $getConnectionMethod->getParameters());
        
        // Should return PDO
        $returnType = $getConnectionMethod->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('PDO', $returnType->__toString());
    }

    public function test_singleton_pattern_implementation(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        
        // Should not have public constructor (singleton pattern)
        $constructor = $reflection->getConstructor();
        $this->assertNull($constructor, 'Singleton should not have public constructor');
        
        // Should have static getConnection method
        $this->assertTrue($reflection->hasMethod('getConnection'));
        $getConnection = $reflection->getMethod('getConnection');
        $this->assertTrue($getConnection->isStatic());
        $this->assertTrue($getConnection->isPublic());
    }

    public function test_class_imports_correct_dependencies(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);
        
        // Check that necessary imports are present
        $this->assertStringContainsString('use PDO;', $content);
        $this->assertStringContainsString('use PDOException;', $content);
    }

    public function test_exception_handling_structure(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);
        
        // Verify exception handling is present
        $this->assertStringContainsString('try {', $content);
        $this->assertStringContainsString('} catch (PDOException $e) {', $content);
        $this->assertStringContainsString('throw new \Exception(', $content);
    }

    public function test_pdo_configuration_options(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);
        
        // Verify PDO configuration options are set
        $this->assertStringContainsString('PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION', $content);
        $this->assertStringContainsString('PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC', $content);
        $this->assertStringContainsString('PDO::ATTR_EMULATE_PREPARES => false', $content);
    }

    public function test_connection_string_format(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);
        
        // Verify DSN format
        $this->assertStringContainsString('mysql:host=', $content);
        $this->assertStringContainsString('port=', $content);
        $this->assertStringContainsString('dbname=', $content);
        $this->assertStringContainsString('charset=utf8mb4', $content);
    }

    public function test_environment_variables_usage(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);
        
        // Verify environment variables are used
        $this->assertStringContainsString('$_ENV[\'DB_HOST\']', $content);
        $this->assertStringContainsString('$_ENV[\'DB_PORT\']', $content);
        $this->assertStringContainsString('$_ENV[\'DB_DATABASE\']', $content);
        $this->assertStringContainsString('$_ENV[\'DB_USERNAME\']', $content);
        $this->assertStringContainsString('$_ENV[\'DB_PASSWORD\']', $content);
    }

    public function test_class_has_no_instance_methods(): void
    {
        $reflection = new \ReflectionClass(DatabaseConfig::class);
        $instanceMethods = array_filter(
            $reflection->getMethods(),
            fn($method) => !$method->isStatic()
        );
        
        // Should have no instance methods (pure static class)
        $this->assertEmpty($instanceMethods);
    }
}
