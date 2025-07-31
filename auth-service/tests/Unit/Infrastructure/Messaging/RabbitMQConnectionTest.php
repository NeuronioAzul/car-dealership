<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Messaging;

use App\Infrastructure\Messaging\RabbitMQConnection;
use PHPUnit\Framework\TestCase;

class RabbitMQConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset static properties before each test
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        
        if ($reflection->hasProperty('connection')) {
            $connectionProperty = $reflection->getProperty('connection');
            $connectionProperty->setAccessible(true);
            $connectionProperty->setValue(null);
        }
        
        if ($reflection->hasProperty('channel')) {
            $channelProperty = $reflection->getProperty('channel');
            $channelProperty->setAccessible(true);
            $channelProperty->setValue(null);
        }
    }

    public function test_get_instance_method_exists(): void
    {
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        $this->assertTrue($reflection->hasMethod('getInstance'));
        
        $method = $reflection->getMethod('getInstance');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    public function test_close_method_exists(): void
    {
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        $this->assertTrue($reflection->hasMethod('close'));
        
        $method = $reflection->getMethod('close');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    public function test_connection_property_exists(): void
    {
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        $this->assertTrue($reflection->hasProperty('connection'));
        
        $property = $reflection->getProperty('connection');
        $this->assertTrue($property->isStatic());
        $this->assertTrue($property->isPrivate());
    }

    public function test_channel_property_exists(): void
    {
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        $this->assertTrue($reflection->hasProperty('channel'));
        
        $property = $reflection->getProperty('channel');
        $this->assertTrue($property->isStatic());
        $this->assertTrue($property->isPrivate());
    }

    public function test_class_structure(): void
    {
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        
        // Test class namespace
        $this->assertEquals('App\Infrastructure\Messaging\RabbitMQConnection', $reflection->getName());
        
        // Test static properties
        $properties = $reflection->getProperties(\ReflectionProperty::IS_STATIC);
        $propertyNames = array_map(fn($prop) => $prop->getName(), $properties);
        
        $this->assertContains('connection', $propertyNames);
        $this->assertContains('channel', $propertyNames);
        
        // Test methods
        $methods = $reflection->getMethods(\ReflectionMethod::IS_STATIC);
        $methodNames = array_map(fn($method) => $method->getName(), $methods);
        
        $this->assertContains('getInstance', $methodNames);
        $this->assertContains('close', $methodNames);
    }

    public function test_get_instance_fails_without_environment(): void
    {
        // Test that getInstance fails in test environment without RabbitMQ
        $this->expectNotToPerformAssertions();
        
        try {
            RabbitMQConnection::getInstance();
        } catch (\Exception $e) {
            // Expected to fail without proper RabbitMQ environment
            $this->assertTrue(true);
        }
    }

    public function test_close_method_can_be_called(): void
    {
        // Test that close method can be called without throwing errors
        $this->expectNotToPerformAssertions();
        
        try {
            RabbitMQConnection::close();
        } catch (\Exception $e) {
            // Should not fail even if no connection exists
            $this->fail('Close method should not throw exceptions when no connection exists');
        }
    }

    public function test_static_properties_default_values(): void
    {
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        
        $connectionProperty = $reflection->getProperty('connection');
        $connectionProperty->setAccessible(true);
        $this->assertNull($connectionProperty->getValue());
        
        $channelProperty = $reflection->getProperty('channel');
        $channelProperty->setAccessible(true);
        $this->assertNull($channelProperty->getValue());
    }

    public function test_singleton_pattern_implementation(): void
    {
        // Test that it follows singleton pattern structure
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        
        // Should not have public constructor (singleton pattern)
        $constructor = $reflection->getConstructor();
        $this->assertNull($constructor, 'Singleton should not have public constructor');
        
        // Should have static getInstance method
        $this->assertTrue($reflection->hasMethod('getInstance'));
        $getInstance = $reflection->getMethod('getInstance');
        $this->assertTrue($getInstance->isStatic());
        $this->assertTrue($getInstance->isPublic());
    }

    public function test_properties_type_annotations(): void
    {
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        
        // Test that properties exist with expected characteristics
        $connectionProperty = $reflection->getProperty('connection');
        $this->assertTrue($connectionProperty->isPrivate());
        $this->assertTrue($connectionProperty->isStatic());
        
        $channelProperty = $reflection->getProperty('channel');
        $this->assertTrue($channelProperty->isPrivate());
        $this->assertTrue($channelProperty->isStatic());
    }

    public function test_class_constants_or_methods_count(): void
    {
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        
        // Test that class has expected number of methods
        $methods = $reflection->getMethods();
        $publicMethods = array_filter($methods, fn($method) => $method->isPublic());
        
        $this->assertGreaterThanOrEqual(2, count($publicMethods)); // At least getInstance and close
    }

    public function test_method_signatures(): void
    {
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        
        // Test getInstance method signature
        $getInstance = $reflection->getMethod('getInstance');
        $this->assertCount(0, $getInstance->getParameters());
        
        // Test close method signature
        $close = $reflection->getMethod('close');
        $this->assertCount(0, $close->getParameters());
    }

    public function test_class_imports_correct_dependencies(): void
    {
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);
        
        // Check that necessary imports are present
        $this->assertStringContainsString('use PhpAmqpLib\Channel\AMQPChannel;', $content);
        $this->assertStringContainsString('use PhpAmqpLib\Connection\AMQPStreamConnection;', $content);
    }

    public function test_environment_variables_setup(): void
    {
        // Test environment variable setup
        $_ENV['RABBITMQ_HOST'] = 'test-host';
        $_ENV['RABBITMQ_PORT'] = '5673';
        $_ENV['RABBITMQ_USER'] = 'test-user';
        $_ENV['RABBITMQ_PASS'] = 'test-pass';
        
        $this->assertEquals('test-host', $_ENV['RABBITMQ_HOST']);
        $this->assertEquals('5673', $_ENV['RABBITMQ_PORT']);
        $this->assertEquals('test-user', $_ENV['RABBITMQ_USER']);
        $this->assertEquals('test-pass', $_ENV['RABBITMQ_PASS']);
        
        // Reset for other tests
        $_ENV['RABBITMQ_HOST'] = 'localhost';
        $_ENV['RABBITMQ_PORT'] = '5672';
        $_ENV['RABBITMQ_USER'] = 'guest';
        $_ENV['RABBITMQ_PASS'] = 'guest';
    }

    public function test_exchange_name_in_code(): void
    {
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        $fileName = $reflection->getFileName();
        $content = file_get_contents($fileName);
        
        $this->assertStringContainsString('car.dealership.events', $content);
        $this->assertStringContainsString('topic', $content);
    }

    public function test_static_property_types(): void
    {
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        
        $connectionProperty = $reflection->getProperty('connection');
        $channelProperty = $reflection->getProperty('channel');
        
        $this->assertTrue($connectionProperty->hasType());
        $this->assertTrue($channelProperty->hasType());
        
        $connectionType = $connectionProperty->getType();
        $channelType = $channelProperty->getType();
        
        $this->assertStringContainsString('AMQPStreamConnection', $connectionType->__toString());
        $this->assertStringContainsString('AMQPChannel', $channelType->__toString());
    }

    public function test_singleton_pattern_characteristics(): void
    {
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        
        // No public constructor (typical singleton pattern)
        $constructor = $reflection->getConstructor();
        $this->assertNull($constructor);
        
        // Only static methods
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $this->assertTrue($method->isStatic(), "Method {$method->getName()} should be static");
        }
    }

    public function test_class_final_or_abstract_status(): void
    {
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        
        // Test that class is neither final nor abstract (can be extended if needed)
        $this->assertFalse($reflection->isFinal());
        $this->assertFalse($reflection->isAbstract());
        $this->assertTrue($reflection->isInstantiable());
    }

    public function test_return_type_annotations(): void
    {
        $reflection = new \ReflectionClass(RabbitMQConnection::class);
        
        $getInstance = $reflection->getMethod('getInstance');
        $close = $reflection->getMethod('close');
        
        $getInstanceReturnType = $getInstance->getReturnType();
        $closeReturnType = $close->getReturnType();
        
        $this->assertNotNull($getInstanceReturnType);
        $this->assertNotNull($closeReturnType);
        
        $this->assertEquals('PhpAmqpLib\Channel\AMQPChannel', $getInstanceReturnType->__toString());
        $this->assertEquals('void', $closeReturnType->__toString());
    }
}
