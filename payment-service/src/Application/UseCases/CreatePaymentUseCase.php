<?php

namespace App\Application\UseCases;

use App\Domain\Entities\Payment;
use App\Domain\Repositories\PaymentRepositoryInterface;

class CreatePaymentUseCase
{
    private PaymentRepositoryInterface $paymentRepository;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function execute(
        string $customerId,
        string $reservationId,
        string $vehicleId,
        string $paymentCode,
        float $amount,
        string $method = 'credit_card'
    ): array {
        // Verificar se já existe pagamento para este código
        $existingPayment = $this->paymentRepository->findByPaymentCode($paymentCode);
        if ($existingPayment) {
            return $existingPayment->toArray();
        }

        // Verificar se já existe pagamento para esta reserva
        $existingReservationPayment = $this->paymentRepository->findByReservationId($reservationId);
        if ($existingReservationPayment) {
            throw new \Exception('Já existe um pagamento para esta reserva', 409);
        }

        // Validar dados
        if ($amount <= 0) {
            throw new \Exception('Valor do pagamento deve ser maior que zero', 400);
        }

        $validMethods = ['credit_card', 'debit_card', 'pix', 'bank_transfer'];
        if (!in_array($method, $validMethods)) {
            throw new \Exception('Método de pagamento inválido', 400);
        }

        // Criar novo pagamento
        $payment = new Payment(
            $customerId,
            $reservationId,
            $vehicleId,
            $paymentCode,
            $amount,
            $method
        );

        // Salvar pagamento
        if (!$this->paymentRepository->save($payment)) {
            throw new \Exception('Erro ao criar pagamento', 500);
        }

        return $payment->toArray();
    }
}

