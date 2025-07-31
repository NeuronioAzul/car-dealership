<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Messaging;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Infrastructure\Messaging\EventPublisher;
use App\Infrastructure\Messaging\RabbitMQConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class EventPublisherCoverageTest extends TestCase
{
    private EventPublisher $eventPublisher;

    protected function setUp(): void
    {
        // EventPublisher não aceita parâmetros - usa RabbitMQConnection::getInstance() internamente
        $this->eventPublisher = $this->getMockBuilder(EventPublisher::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testPublishEventWithBasicData(): void
    {
        $eventType = 'user.created';
        $data = ['user_id' => 123, 'email' => 'test@example.com'];

        $this->channel
            ->expects($this->once())
            ->method('basic_publish')
            ->with($this->callback(function (AMQPMessage $message) use ($eventType, $data) {
                $body = json_decode($message->getBody(), true);
                return $body['event_type'] === $eventType && 
                       $body['data'] === $data &&
                       isset($body['timestamp']);
            }), '', 'auth_events');

        $this->eventPublisher->publish($eventType, $data);
    }

    public function testPublishEventWithComplexData(): void
    {
        $eventType = 'user.updated';
        $data = [
            'user_id' => 456,
            'changes' => [
                'name' => 'New Name',
                'email' => 'new@example.com'
            ],
            'metadata' => [
                'source' => 'api',
                'version' => '1.0'
            ]
        ];

        $this->channel
            ->expects($this->once())
            ->method('basic_publish')
            ->with($this->callback(function (AMQPMessage $message) use ($eventType, $data) {
                $body = json_decode($message->getBody(), true);
                return $body['event_type'] === $eventType && 
                       $body['data'] === $data &&
                       isset($body['timestamp']);
            }), '', 'auth_events');

        $this->eventPublisher->publish($eventType, $data);
    }

    public function testPublishEventWithEmptyData(): void
    {
        $eventType = 'system.health_check';
        $data = [];

        $this->channel
            ->expects($this->once())
            ->method('basic_publish')
            ->with($this->callback(function (AMQPMessage $message) use ($eventType) {
                $body = json_decode($message->getBody(), true);
                return $body['event_type'] === $eventType && 
                       $body['data'] === [] &&
                       isset($body['timestamp']);
            }), '', 'auth_events');

        $this->eventPublisher->publish($eventType, $data);
    }

    public function testPublishEventWithNullData(): void
    {
        $eventType = 'user.deleted';
        $data = ['user_id' => null, 'reason' => null];

        $this->channel
            ->expects($this->once())
            ->method('basic_publish')
            ->with($this->callback(function (AMQPMessage $message) use ($eventType, $data) {
                $body = json_decode($message->getBody(), true);
                return $body['event_type'] === $eventType && 
                       $body['data'] === $data &&
                       isset($body['timestamp']);
            }), '', 'auth_events');

        $this->eventPublisher->publish($eventType, $data);
    }

    public function testPublishEventWithBooleanData(): void
    {
        $eventType = 'user.verified';
        $data = ['verified' => true, 'auto_verified' => false];

        $this->channel
            ->expects($this->once())
            ->method('basic_publish')
            ->with($this->callback(function (AMQPMessage $message) use ($eventType, $data) {
                $body = json_decode($message->getBody(), true);
                return $body['event_type'] === $eventType && 
                       $body['data'] === $data &&
                       isset($body['timestamp']);
            }), '', 'auth_events');

        $this->eventPublisher->publish($eventType, $data);
    }

    public function testPublishEventMessageFormat(): void
    {
        $eventType = 'test.event';
        $data = ['test' => 'data'];

        $this->channel
            ->expects($this->once())
            ->method('basic_publish')
            ->with($this->callback(function (AMQPMessage $message) {
                // Check message properties
                $this->assertInstanceOf(AMQPMessage::class, $message);
                
                $body = json_decode($message->getBody(), true);
                $this->assertIsArray($body);
                $this->assertArrayHasKey('event_type', $body);
                $this->assertArrayHasKey('data', $body);
                $this->assertArrayHasKey('timestamp', $body);
                
                // Check timestamp format
                $this->assertIsString($body['timestamp']);
                $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $body['timestamp']);
                
                return true;
            }), '', 'auth_events');

        $this->eventPublisher->publish($eventType, $data);
    }

    public function testConstructorInitializesChannel(): void
    {
        $rabbitMQConnection = $this->createMock(RabbitMQConnection::class);
        $channel = $this->createMock(AMQPChannel::class);
        
        $rabbitMQConnection
            ->expects($this->once())
            ->method('getChannel')
            ->willReturn($channel);

        $eventPublisher = new EventPublisher($rabbitMQConnection);
        
        $this->assertInstanceOf(EventPublisher::class, $eventPublisher);
    }

    public function testPublishEventWithStringData(): void
    {
        $eventType = 'system.message';
        $data = ['message' => 'System started successfully', 'level' => 'info'];

        $this->channel
            ->expects($this->once())
            ->method('basic_publish')
            ->with($this->isInstanceOf(AMQPMessage::class), '', 'auth_events');

        $this->eventPublisher->publish($eventType, $data);
    }

    public function testPublishEventWithNumericData(): void
    {
        $eventType = 'metrics.counter';
        $data = ['count' => 100, 'total' => 1000, 'percentage' => 10.5];

        $this->channel
            ->expects($this->once())
            ->method('basic_publish')
            ->with($this->isInstanceOf(AMQPMessage::class), '', 'auth_events');

        $this->eventPublisher->publish($eventType, $data);
    }

    public function testEventPublisherClassStructure(): void
    {
        $reflection = new \ReflectionClass(EventPublisher::class);
        
        $this->assertTrue($reflection->hasProperty('channel'));
        $this->assertTrue($reflection->hasMethod('__construct'));
        $this->assertTrue($reflection->hasMethod('publish'));
        
        $constructMethod = $reflection->getMethod('__construct');
        $this->assertTrue($constructMethod->isPublic());
        
        $publishMethod = $reflection->getMethod('publish');
        $this->assertTrue($publishMethod->isPublic());
    }
}
