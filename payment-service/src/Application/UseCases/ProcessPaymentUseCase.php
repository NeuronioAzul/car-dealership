<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\Services\FakePaymentGatewayService;
use App\Domain\Repositories\PaymentRepositoryInterface;
use App\Infrastructure\Messaging\EventPublisher;

class ProcessPaymentUseCase
{
    private PaymentRepositoryInterface $paymentRepository;
    private FakePaymentGatewayService $paymentGateway;
    private EventPublisher $eventPublisher;

    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        FakePaymentGatewayService $paymentGateway,
        EventPublisher $eventPublisher
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->paymentGateway = $paymentGateway;
        $this->eventPublisher = $eventPublisher;
    }

    public function execute(string $paymentCode, string $customerId, array $paymentData): array
    {
        // Buscar pagamento pelo código
        $payment = $this->paymentRepository->findByPaymentCode($paymentCode);

        if (!$payment) {
            throw new \Exception('Código de pagamento não encontrado', 404);
        }

        if ($payment->getCustomerId() !== $customerId) {
            throw new \Exception('Acesso negado. Este pagamento não pertence ao cliente', 403);
        }

        if ($payment->isDeleted()) {
            throw new \Exception('Pagamento inválido', 410);
        }

        if (!$payment->isPending()) {
            if ($payment->isCompleted()) {
                throw new \Exception('Pagamento já foi processado', 409);
            }

            if ($payment->isFailed()) {
                throw new \Exception('Pagamento falhou anteriormente', 409);
            }

            if ($payment->isProcessing()) {
                throw new \Exception('Pagamento já está sendo processado', 409);
            }

            throw new \Exception('Status do pagamento inválido', 409);
        }

        // Marcar como processando
        $payment->startProcessing();
        $this->paymentRepository->update($payment);

        // Publicar evento de início do processamento
        $this->eventPublisher->publish('payment.processing_started', [
            'payment_id' => $payment->getId(),
            'customer_id' => $payment->getCustomerId(),
            'reservation_id' => $payment->getReservationId(),
            'vehicle_id' => $payment->getVehicleId(),
            'payment_code' => $payment->getPaymentCode(),
            'amount' => $payment->getAmount(),
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        try {
            // Processar pagamento no gateway
            $gatewayResult = $this->paymentGateway->processPayment(
                $payment->getPaymentCode(),
                $payment->getAmount(),
                $paymentData['method'] ?? 'credit_card'
            );

            if ($gatewayResult['success']) {
                // Pagamento aprovado
                $payment->markAsCompleted(
                    $gatewayResult['transaction_id'],
                    $gatewayResult['gateway_response']
                );

                // Publicar evento de pagamento aprovado
                $this->eventPublisher->publish('payment.completed', [
                    'payment_id' => $payment->getId(),
                    'customer_id' => $payment->getCustomerId(),
                    'reservation_id' => $payment->getReservationId(),
                    'vehicle_id' => $payment->getVehicleId(),
                    'payment_code' => $payment->getPaymentCode(),
                    'amount' => $payment->getAmount(),
                    'transaction_id' => $gatewayResult['transaction_id'],
                    'timestamp' => date('Y-m-d H:i:s'),
                ]);

                $message = 'Pagamento processado com sucesso';
            } else {
                // Pagamento recusado
                $payment->markAsFailed($gatewayResult['gateway_response']);

                // Publicar evento de pagamento falhou
                $this->eventPublisher->publish('payment.failed', [
                    'payment_id' => $payment->getId(),
                    'customer_id' => $payment->getCustomerId(),
                    'reservation_id' => $payment->getReservationId(),
                    'vehicle_id' => $payment->getVehicleId(),
                    'payment_code' => $payment->getPaymentCode(),
                    'amount' => $payment->getAmount(),
                    'error_message' => $gatewayResult['message'],
                    'timestamp' => date('Y-m-d H:i:s'),
                ]);

                $message = $gatewayResult['message'];
            }

            // Salvar alterações
            $this->paymentRepository->update($payment);

            return [
                'payment' => $payment->toArray(),
                'success' => $gatewayResult['success'],
                'message' => $message,
            ];
        } catch (\Exception $e) {
            // Erro no processamento
            $payment->markAsFailed('Erro interno no processamento: ' . $e->getMessage());
            $this->paymentRepository->update($payment);

            // Publicar evento de erro
            $this->eventPublisher->publish('payment.error', [
                'payment_id' => $payment->getId(),
                'customer_id' => $payment->getCustomerId(),
                'reservation_id' => $payment->getReservationId(),
                'vehicle_id' => $payment->getVehicleId(),
                'payment_code' => $payment->getPaymentCode(),
                'error_message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s'),
            ]);

            throw new \Exception('Erro no processamento do pagamento: ' . $e->getMessage(), 500);
        }
    }
}
