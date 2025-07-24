<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Repositories\PaymentRepositoryInterface;

class GetPaymentStatusUseCase
{
    private PaymentRepositoryInterface $paymentRepository;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function execute(string $paymentCode, string $customerId): array
    {
        $payment = $this->paymentRepository->findByPaymentCode($paymentCode);

        if (!$payment) {
            throw new \Exception('Pagamento não encontrado', 404);
        }

        if ($payment->getCustomerId() !== $customerId) {
            throw new \Exception('Acesso negado. Este pagamento não pertence ao cliente', 403);
        }

        if ($payment->isDeleted()) {
            throw new \Exception('Pagamento inválido', 410);
        }

        return $payment->toArray();
    }
}
