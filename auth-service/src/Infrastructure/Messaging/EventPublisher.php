<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging;

use PhpAmqpLib\Message\AMQPMessage;

class EventPublisher
{
    private $channel;

    public function __construct()
    {
        $this->channel = RabbitMQConnection::getInstance();
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

        $this->channel->basic_publish(
            $message,
            'car.dealership.events',
            $routingKey
        );
    }
}
