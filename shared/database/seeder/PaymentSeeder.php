<?php

declare(strict_types=1);

namespace Shared\Database\Seeder;

use Faker\Factory;
use Faker\Generator;

class PaymentSeeder extends BaseSeeder
{
    private Generator $faker;

    public function __construct()
    {
        parent::__construct($this->getEnv('PAYMENT_DB_NAME', 'payment_db'));
        $this->faker = Factory::create('pt_BR');
    }

    public function run(): void
    {
        echo "💳 Iniciando seed do Payment Service...\n";

        // Limpar tabelas
        $this->truncateTable('gateway_transactions');
        $this->truncateTable('payments');

        // Criar pagamentos
        $this->createPayments();

        echo "✅ Seed do Payment Service concluído!\n\n";
    }

    private function createPayments(): void
    {
        $payments = [];
        $transactions = [];

        // Buscar códigos de pagamento
        $reservationConnection = $this->getDbConnection($this->getEnv('RESERVATION_DB_NAME', 'reservation_db'));
        $paymentsCount = (int) $this->getEnv('SEED_PAYMENTS_COUNT', 25);

        $paymentCodes = $reservationConnection->query("
            SELECT pc.*, r.customer_id, r.id as reservation_id
            FROM payment_codes pc 
            JOIN reservations r ON pc.reservation_id = r.id 
            LIMIT {$paymentsCount}
        ")->fetchAll();

        foreach ($paymentCodes as $code) {
            $paymentId = $this->generateUuid();
            $transactionId = $this->generateUuid();

            $paymentMethod = $this->faker->randomElement(['credit_card', 'debit_card', 'pix', 'bank_transfer']);
            $status = $this->faker->randomElement(['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded']);

            $createdAt = $this->faker->dateTimeBetween('-30 days', 'now');
            $processedAt = $status !== 'pending' ? $this->faker->dateTimeBetween($createdAt, 'now') : null;
            $completedAt = $status === 'completed' ? $this->faker->dateTimeBetween($processedAt ?: $createdAt, 'now') : null;
            $failedAt = $status === 'failed' ? $this->faker->dateTimeBetween($processedAt ?: $createdAt, 'now') : null;
            $refundedAt = $status === 'refunded' ? $this->faker->dateTimeBetween($processedAt ?: $createdAt, 'now') : null;

            $gatewayFee = $this->calculateGatewayFee((float)$code['amount'], $paymentMethod);

            // Dados de cartão e PIX
            $cardLastFour = in_array($paymentMethod, ['credit_card', 'debit_card']) ? $this->getCardLastFour() : null;
            $cardBrand = in_array($paymentMethod, ['credit_card', 'debit_card']) ? $this->getCardBrand() : null;
            $cardHolderName = in_array($paymentMethod, ['credit_card', 'debit_card']) ? $this->faker->name : null;
            $pixKey = $paymentMethod === 'pix' ? $this->faker->uuid : null;
            $pixQrCode = $paymentMethod === 'pix' ? base64_encode($this->faker->uuid) : null;

            // Gateway
            $gatewayTransactionId = 'TXN' . mt_rand(100000000, 999999999);
            $gatewayResponse = json_encode([
                'status' => $status,
                'payment_method' => $paymentMethod,
                'timestamp' => date('c'),
                'gateway_id' => 'GW' . mt_rand(1000, 9999),
                'processing_time_ms' => mt_rand(500, 3000),
            ], JSON_UNESCAPED_UNICODE);

            // Tentativas
            $attempts = $this->faker->numberBetween(1, 3);
            $maxAttempts = 3;

            // Observações e falhas
            $notes = $this->faker->optional()->sentence;
            $failureReason = $status === 'failed' ? 'Transação negada - Saldo insuficiente' : null;

            // Estorno
            $refundAmount = $status === 'refunded' ? $code['amount'] : null;
            $refundReason = $status === 'refunded' ? 'Solicitado pelo cliente' : null;

            $payments[] = [
                'id' => $paymentId,
                'payment_code' => $code['payment_code'],
                'reservation_id' => $code['reservation_id'],
                'customer_id' => $code['customer_id'],
                'amount' => $code['amount'],
                'currency' => 'BRL',
                'payment_method' => $paymentMethod,
                'status' => $status,
                'card_last_four' => $cardLastFour,
                'card_brand' => $cardBrand,
                'card_holder_name' => $cardHolderName,
                'pix_key' => $pixKey,
                'pix_qr_code' => $pixQrCode,
                'gateway_transaction_id' => $gatewayTransactionId,
                'gateway_response' => $gatewayResponse,
                'gateway_fee' => $gatewayFee,
                'attempts' => $attempts,
                'max_attempts' => $maxAttempts,
                'processed_at' => $processedAt ? $processedAt->format('Y-m-d H:i:s') : null,
                'completed_at' => $completedAt ? $completedAt->format('Y-m-d H:i:s') : null,
                'failed_at' => $failedAt ? $failedAt->format('Y-m-d H:i:s') : null,
                'refunded_at' => $refundedAt ? $refundedAt->format('Y-m-d H:i:s') : null,
                'refund_amount' => $refundAmount,
                'refund_reason' => $refundReason,
                'notes' => $notes,
                'failure_reason' => $failureReason,
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $this->getCurrentTimestamp(),
            ];

            // Transação do gateway
            $transactions[] = [
                'id' => $transactionId,
                'payment_id' => $paymentId,
                'transaction_type' => $this->faker->randomElement(['payment', 'refund', 'chargeback']),
                'gateway_name' => $this->getGatewayName($paymentMethod),
                'external_id' => $gatewayTransactionId,
                'request_data' => json_encode(['request' => 'data'], JSON_UNESCAPED_UNICODE),
                'response_data' => json_encode(['response' => 'data'], JSON_UNESCAPED_UNICODE),
                'status' => $status,
                'amount' => $code['amount'],
                'fee' => $gatewayFee,
                'sent_at' => $createdAt->format('Y-m-d H:i:s'),
                'received_at' => $processedAt ? $processedAt->format('Y-m-d H:i:s') : null,
            ];
        }

        $this->insertBatch('payments', $payments);
        $this->insertBatch('gateway_transactions', $transactions);

        echo '📊 Criados: ' . count($payments) . " pagamentos com transações do gateway\n";
    }

    private function calculateGatewayFee(float $amount, string $paymentMethod): float
    {
        $defaultRate = (float) $this->getEnv('GATEWAY_FEE_PERCENTAGE', 3.5) / 100;

        $rates = [
            'credit_card' => $defaultRate,
            'debit_card' => $defaultRate * 0.7,
            'pix' => $defaultRate * 0.3,
            'bank_transfer' => $defaultRate * 0.5,
        ];

        $rate = $rates[$paymentMethod] ?? $defaultRate;

        return round($amount * $rate, 2);
    }

    private function getCardLastFour(): string
    {
        return str_pad((string) mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function getCardBrand(): string
    {
        return $this->faker->randomElement(['Visa', 'Mastercard', 'Elo', 'American Express']);
    }

    private function getGatewayName(string $paymentMethod): string
    {
        $gateways = [
            'credit_card' => $this->faker->randomElement(['PagSeguro', 'Mercado Pago', 'Cielo', 'Rede']),
            'debit_card' => $this->faker->randomElement(['PagSeguro', 'Mercado Pago', 'Cielo']),
            'pix' => $this->faker->randomElement(['Banco Central', 'PagSeguro', 'Mercado Pago']),
            'bank_transfer' => $this->faker->randomElement(['Itaú', 'Bradesco', 'Banco do Brasil', 'Santander']),
        ];

        return $gateways[$paymentMethod] ?? 'Gateway Genérico';
    }
}
