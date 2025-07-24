<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Infrastructure\Messaging\EventPublisher;

class GeneratePaymentCodeUseCase
{
    private ReservationRepositoryInterface $reservationRepository;
    private EventPublisher $eventPublisher;

    public function __construct(
        ReservationRepositoryInterface $reservationRepository,
        EventPublisher $eventPublisher
    ) {
        $this->reservationRepository = $reservationRepository;
        $this->eventPublisher = $eventPublisher;
    }

    public function execute(string $reservationId, string $customerId): array
    {
        $reservation = $this->reservationRepository->findById($reservationId);

        if (!$reservation) {
            throw new \Exception('Reserva não encontrada', 404);
        }

        if ($reservation->getCustomerId() !== $customerId) {
            throw new \Exception('Acesso negado. Esta reserva não pertence ao cliente', 403);
        }

        if (!$reservation->isActive()) {
            if ($reservation->isExpired()) {
                throw new \Exception('Reserva expirada', 410);
            }

            if ($reservation->isCancelled()) {
                throw new \Exception('Reserva cancelada', 410);
            }

            if ($reservation->isPaid()) {
                throw new \Exception('Reserva já foi paga', 409);
            }

            throw new \Exception('Reserva não está ativa', 409);
        }

        // Verificar se já tem código de pagamento
        if ($reservation->getPaymentCode()) {
            return [
                'reservation' => $reservation->toArray(),
                'payment_code' => $reservation->getPaymentCode(),
                'message' => 'Código de pagamento já existe',
            ];
        }

        // Gerar código de pagamento
        $paymentCode = $reservation->generatePaymentCode();

        // Salvar alterações
        if (!$this->reservationRepository->update($reservation)) {
            throw new \Exception('Erro ao gerar código de pagamento', 500);
        }

        // Publicar evento de código de pagamento gerado
        $this->eventPublisher->publish('payment.code_generated', [
            'reservation_id' => $reservation->getId(),
            'customer_id' => $reservation->getCustomerId(),
            'vehicle_id' => $reservation->getVehicleId(),
            'payment_code' => $paymentCode,
            'expires_at' => $reservation->getExpiresAt()->format('Y-m-d H:i:s'),
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        return [
            'reservation' => $reservation->toArray(),
            'payment_code' => $paymentCode,
            'message' => 'Código de pagamento gerado com sucesso',
        ];
    }
}
