<?php

namespace App\Infrastructure\Messaging;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class EventConsumer
{
    private AMQPStreamConnection $connection;
    private $channel;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            $_ENV['RABBITMQ_HOST'],
            $_ENV['RABBITMQ_PORT'],
            $_ENV['RABBITMQ_USER'],
            $_ENV['RABBITMQ_PASS']
        );
        $this->channel = $this->connection->channel();
        
        // Declarar filas se não existirem
        $this->setupQueues();
    }

    private function setupQueues(): void
    {
        // Fila para eventos de pagamento
        $this->channel->queue_declare('saga.payment.events', false, true, false, false);
        
        // Fila para eventos de reserva
        $this->channel->queue_declare('saga.reservation.events', false, true, false, false);
        
        // Fila para eventos de venda
        $this->channel->queue_declare('saga.sales.events', false, true, false, false);
        
        // Fila para eventos de veículo
        $this->channel->queue_declare('saga.vehicle.events', false, true, false, false);
    }

    public function consumePaymentEvents(callable $callback): void
    {
        $this->channel->basic_consume(
            'saga.payment.events',
            '',
            false,
            false,
            false,
            false,
            function (AMQPMessage $msg) use ($callback) {
                try {
                    $data = json_decode($msg->body, true);
                    $callback($data);
                    $msg->ack();
                } catch (\Exception $e) {
                    error_log("Erro ao processar evento de pagamento: " . $e->getMessage());
                    $msg->nack(false, true); // Rejeitar e recolocar na fila
                }
            }
        );
    }

    public function consumeReservationEvents(callable $callback): void
    {
        $this->channel->basic_consume(
            'saga.reservation.events',
            '',
            false,
            false,
            false,
            false,
            function (AMQPMessage $msg) use ($callback) {
                try {
                    $data = json_decode($msg->body, true);
                    $callback($data);
                    $msg->ack();
                } catch (\Exception $e) {
                    error_log("Erro ao processar evento de reserva: " . $e->getMessage());
                    $msg->nack(false, true);
                }
            }
        );
    }

    public function startConsuming(): void
    {
        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function close(): void
    {
        $this->channel->close();
        $this->connection->close();
    }
}

