<?php

namespace App\Application\Services;

class FakePaymentGatewayService
{
    private int $successRate;

    public function __construct()
    {
        $this->successRate = (int) ($_ENV['PAYMENT_SUCCESS_RATE'] ?? 85);
    }

    public function processPayment(string $paymentCode, float $amount, string $method): array
    {
        // Simular tempo de processamento
        usleep(rand(500000, 2000000)); // 0.5 a 2 segundos

        // Simular taxa de sucesso configurável
        $success = rand(1, 100) <= $this->successRate;

        if ($success) {
            return [
                'success' => true,
                'transaction_id' => 'TXN_' . strtoupper(substr(md5($paymentCode . time()), 0, 12)),
                'status' => 'completed',
                'message' => 'Pagamento processado com sucesso',
                'gateway_response' => json_encode([
                    'gateway' => 'FakePaymentGateway',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'amount' => $amount,
                    'method' => $method,
                    'authorization_code' => strtoupper(substr(md5($paymentCode), 0, 8))
                ])
            ];
        } else {
            $errors = [
                'Cartão recusado pela operadora',
                'Saldo insuficiente',
                'Cartão expirado',
                'Dados do cartão inválidos',
                'Transação não autorizada',
                'Limite de crédito excedido'
            ];

            return [
                'success' => false,
                'transaction_id' => null,
                'status' => 'failed',
                'message' => $errors[array_rand($errors)],
                'gateway_response' => json_encode([
                    'gateway' => 'FakePaymentGateway',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'amount' => $amount,
                    'method' => $method,
                    'error_code' => 'ERR_' . rand(1000, 9999)
                ])
            ];
        }
    }

    public function refundPayment(string $transactionId, float $amount): array
    {
        // Simular tempo de processamento
        usleep(rand(300000, 1000000)); // 0.3 a 1 segundo

        // Estorno sempre bem-sucedido no gateway fictício
        return [
            'success' => true,
            'refund_id' => 'REF_' . strtoupper(substr(md5($transactionId . time()), 0, 12)),
            'status' => 'refunded',
            'message' => 'Estorno processado com sucesso',
            'gateway_response' => json_encode([
                'gateway' => 'FakePaymentGateway',
                'timestamp' => date('Y-m-d H:i:s'),
                'original_transaction' => $transactionId,
                'refund_amount' => $amount
            ])
        ];
    }

    public function getPaymentStatus(string $transactionId): array
    {
        // Simular consulta de status
        return [
            'transaction_id' => $transactionId,
            'status' => 'completed',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

