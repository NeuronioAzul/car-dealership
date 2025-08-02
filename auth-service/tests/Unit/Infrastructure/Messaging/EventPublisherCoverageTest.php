<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Messaging;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use App\Infrastructure\Messaging\EventPublisher;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use ReflectionClass;

class EventPublisherCoverageTest extends TestCase
{
    private EventPublisher $eventPublisher;
    private MockObject $channel;

    protected function setUp(): void
    {
        $this->channel = $this->createMock(AMQPChannel::class);
        
        $reflector = new ReflectionClass(EventPublisher::class);
        $this->eventPublisher = $reflector->newInstanceWithoutConstructor();

        $property = $reflector->getProperty('channel');
        $property->setAccessible(true);
        $property->setValue($this->eventPublisher, $this->channel);
    }

    public function testPublishEventWithBasicData(): void
    {
        $eventType = 'user.created';
        $data = ['user_id' => 123, 'email' => 'test@example.com'];

        $this->channel
            ->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function (AMQPMessage $message) use ($data) {
                    $body = json_decode($message->getBody(), true);
                    return $body === $data;
                }),
                'car.dealership.events',
                $eventType
            );

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
            ->with(
                $this->callback(function (AMQPMessage $message) use ($data) {
                    $body = json_decode($message->getBody(), true);
                    return $body === $data;
                }),
                'car.dealership.events',
                $eventType
            );

        $this->eventPublisher->publish($eventType, $data);
    }

    public function testPublishEventWithEmptyData(): void
    {
        $eventType = 'system.health_check';
        $data = [];

        $this->channel
            ->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function (AMQPMessage $message) use ($data) {
                    $body = json_decode($message->getBody(), true);
                    return $body === $data;
                }),
                'car.dealership.events',
                $eventType
            );

        $this->eventPublisher->publish($eventType, $data);
    }

    public function testPublishEventWithNullData(): void
    {
        $eventType = 'user.deleted';
        $data = ['user_id' => null, 'reason' => null];

        $this->channel
            ->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function (AMQPMessage $message) use ($data) {
                    $body = json_decode($message->getBody(), true);
                    return $body === $data;
                }),
                'car.dealership.events',
                $eventType
            );

        $this->eventPublisher->publish($eventType, $data);
    }

    public function testPublishEventWithBooleanData(): void
    {
        $eventType = 'feature.toggled';
        $data = ['feature_x' => true, 'feature_y' => false];

        $this->channel
            ->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function (AMQPMessage $message) use ($data) {
                    $body = json_decode($message->getBody(), true);
                    return $body === $data;
                }),
                'car.dealership.events',
                $eventType
            );

        $this->eventPublisher->publish($eventType, $data);
    }

    public function testPublishEventMessageFormat(): void
    {
        $eventType = 'test.event';
        $data = ['key' => 'value'];

        $this->channel
            ->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function (AMQPMessage $message) {
                    $this->assertEquals('application/json', $message->get('content_type'));
                    $this->assertEquals(AMQPMessage::DELIVERY_MODE_PERSISTENT, $message->get('delivery_mode'));
                    return true;
                }),
                'car.dealership.events',
                $eventType
            );

        $this->eventPublisher->publish($eventType, $data);
    }

    public function testPublishEventWithStringData(): void
    {
        $eventType = 'log.info';
        $data = ['message' => 'This is a log message'];

        $this->channel
            ->expects($this->once())
            ->method('basic_publish');

        $this->eventPublisher->publish($eventType, $data);
    }
}
