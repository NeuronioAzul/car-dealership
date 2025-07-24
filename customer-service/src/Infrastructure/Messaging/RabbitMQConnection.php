<?php

declare(strict_types=1);

namespace App\Infrastructure\Messaging;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQConnection
{
    private static ?AMQPStreamConnection $connection = null;
    private static ?AMQPChannel $channel = null;

    public static function getInstance(): AMQPChannel
    {
        if (self::$connection === null) {
            self::$connection = new AMQPStreamConnection(
                $_ENV['RABBITMQ_HOST'],
                $_ENV['RABBITMQ_PORT'],
                $_ENV['RABBITMQ_USER'],
                $_ENV['RABBITMQ_PASS']
            );
        }

        if (self::$channel === null) {
            self::$channel = self::$connection->channel();

            // Declarar exchange
            self::$channel->exchange_declare(
                'car.dealership.events',
                'topic',
                false,
                true,
                false
            );
        }

        return self::$channel;
    }

    public static function close(): void
    {
        if (self::$channel) {
            self::$channel->close();
            self::$channel = null;
        }

        if (self::$connection) {
            self::$connection->close();
            self::$connection = null;
        }
    }
}
