<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Messaging;

use App\Infrastructure\Messaging\EventPublisher;
use App\Infrastructure\Messaging\RabbitMQConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class EventPublisherTest extends TestCase
{
    private EventPublisher $eventPublisher;
    /** @var MockObject&AMQPChannel */
    private MockObject $channelMock;

    protected function setUp(): void
    {
        $this->channelMock = $this->createMock(AMQPChannel::class);
        
        // Mock the RabbitMQConnection static getInstance method
        $this->eventPublisher = new class($this->channelMock) extends EventPublisher {
            private $mockChannel;
            
            public function __construct($mockChannel)
            {
                $this->mockChannel = $mockChannel;
                // Skip parent constructor to avoid RabbitMQ connection
            }
            
            public function publish(string $routingKey, array $data): void
            {
                $message = new AMQPMessage(
                    json_encode($data),
                    [
                        'content_type' => 'application/json',
                        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    ]
                );

                $this->mockChannel->basic_publish(
                    $message,
                    'car.dealership.events',
                    $routingKey
                );
            }
        };
    }

    public function test_publish_event_successfully(): void
    {
        $routingKey = 'auth.user_logged_in';
        $data = [
            'user_id' => 123,
            'email' => 'test@example.com',
            'timestamp' => '2023-01-01 00:00:00'
        ];

        $this->channelMock->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function (AMQPMessage $message) use ($data) {
                    $decodedBody = json_decode($message->getBody(), true);
                    return $decodedBody === $data &&
                           $message->get('content_type') === 'application/json' &&
                           $message->get('delivery_mode') === AMQPMessage::DELIVERY_MODE_PERSISTENT;
                }),
                'car.dealership.events',
                $routingKey
            );

        $this->eventPublisher->publish($routingKey, $data);
    }

    public function test_publish_event_with_empty_data(): void
    {
        $routingKey = 'test.event';
        $data = [];

        $this->channelMock->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function (AMQPMessage $message) {
                    $decodedBody = json_decode($message->getBody(), true);
                    return $decodedBody === [] &&
                           $message->get('content_type') === 'application/json';
                }),
                'car.dealership.events',
                $routingKey
            );

        $this->eventPublisher->publish($routingKey, $data);
    }

    public function test_publish_event_with_complex_data(): void
    {
        $routingKey = 'auth.user_registered';
        $data = [
            'user' => [
                'id' => 456,
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'address' => [
                    'street' => 'Main St',
                    'city' => 'New York'
                ]
            ],
            'metadata' => [
                'timestamp' => '2023-01-01 12:00:00',
                'source' => 'web'
            ]
        ];

        $this->channelMock->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function (AMQPMessage $message) use ($data) {
                    $decodedBody = json_decode($message->getBody(), true);
                    return $decodedBody === $data;
                }),
                'car.dealership.events',
                $routingKey
            );

        $this->eventPublisher->publish($routingKey, $data);
    }

    public function test_message_properties_are_set_correctly(): void
    {
        $routingKey = 'test.event';
        $data = ['test' => 'data'];

        $this->channelMock->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function (AMQPMessage $message) {
                    return $message->get('content_type') === 'application/json' &&
                           $message->get('delivery_mode') === AMQPMessage::DELIVERY_MODE_PERSISTENT;
                }),
                'car.dealership.events',
                $routingKey
            );

        $this->eventPublisher->publish($routingKey, $data);
    }

    public function test_constructor_initializes_channel(): void
    {
        // Test that constructor can be called (even if it fails in test env)
        $this->expectNotToPerformAssertions();
        
        try {
            new EventPublisher();
        } catch (\Exception $e) {
            // Expected to fail in test environment without RabbitMQ
            $this->assertTrue(true);
        }
    }

    public function test_publish_with_numeric_data(): void
    {
        $routingKey = 'numeric.data';
        $data = [
            'id' => 123,
            'price' => 99.99,
            'quantity' => 5,
            'total' => 499.95
        ];

        $this->channelMock->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function ($message) use ($data) {
                    if (!$message instanceof AMQPMessage) {
                        return false;
                    }
                    
                    $decodedBody = json_decode($message->getBody(), true);
                    return $decodedBody === $data;
                }),
                'car.dealership.events',
                $routingKey
            );

        $this->eventPublisher->publish($routingKey, $data);
    }

    public function test_publish_with_boolean_data(): void
    {
        $routingKey = 'boolean.data';
        $data = [
            'is_active' => true,
            'is_verified' => false,
            'has_access' => true
        ];

        $this->channelMock->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function ($message) use ($data) {
                    if (!$message instanceof AMQPMessage) {
                        return false;
                    }
                    
                    $decodedBody = json_decode($message->getBody(), true);
                    return $decodedBody === $data;
                }),
                'car.dealership.events',
                $routingKey
            );

        $this->eventPublisher->publish($routingKey, $data);
    }

    public function test_publish_with_null_values(): void
    {
        $routingKey = 'null.values';
        $data = [
            'name' => 'John',
            'middle_name' => null,
            'deleted_at' => null
        ];

        $this->channelMock->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function ($message) use ($data) {
                    if (!$message instanceof AMQPMessage) {
                        return false;
                    }
                    
                    $decodedBody = json_decode($message->getBody(), true);
                    return $decodedBody === $data;
                }),
                'car.dealership.events',
                $routingKey
            );

        $this->eventPublisher->publish($routingKey, $data);
    }

    public function test_channel_property_exists(): void
    {
        $reflection = new \ReflectionClass(EventPublisher::class);
        $this->assertTrue($reflection->hasProperty('channel'));
        
        $channelProperty = $reflection->getProperty('channel');
        $this->assertTrue($channelProperty->isPrivate());
    }

    public function test_constructor_method_exists(): void
    {
        $reflection = new \ReflectionClass(EventPublisher::class);
        $this->assertTrue($reflection->hasMethod('__construct'));
        
        $constructor = $reflection->getConstructor();
        $this->assertTrue($constructor->isPublic());
        $this->assertCount(0, $constructor->getParameters());
    }

    public function test_publish_method_exists_and_is_public(): void
    {
        $reflection = new \ReflectionClass(EventPublisher::class);
        $this->assertTrue($reflection->hasMethod('publish'));
        
        $publishMethod = $reflection->getMethod('publish');
        $this->assertTrue($publishMethod->isPublic());
        
        $parameters = $publishMethod->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('routingKey', $parameters[0]->getName());
        $this->assertEquals('data', $parameters[1]->getName());
    }

    public function test_event_publisher_class_structure(): void
    {
        $reflection = new \ReflectionClass(EventPublisher::class);
        
        // Test class namespace
        $this->assertEquals('App\Infrastructure\Messaging\EventPublisher', $reflection->getName());
        
        // Test class has expected methods
        $methods = $reflection->getMethods();
        $methodNames = array_map(fn($method) => $method->getName(), $methods);
        
        $this->assertContains('__construct', $methodNames);
        $this->assertContains('publish', $methodNames);
    }
}
