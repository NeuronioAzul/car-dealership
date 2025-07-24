<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Infrastructure\Messaging\EventPublisher;

class CancelReservationUseCase
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

        if ($reservation->isDeleted()) {
            throw new \Exception('Reserva já foi excluída', 410);
        }

        if ($reservation->isCancelled()) {
            throw new \Exception('Reserva já foi cancelada', 409);
        }

        if ($reservation->isPaid()) {
            throw new \Exception('Não é possível cancelar uma reserva já paga', 409);
        }

        // Cancelar reserva
        $reservation->cancel();

        // Salvar alterações
        if (!$this->reservationRepository->update($reservation)) {
            throw new \Exception('Erro ao cancelar reserva', 500);
        }

        // Publicar evento de reserva cancelada
        $this->eventPublisher->publish('reservation.cancelled', [
            'reservation_id' => $reservation->getId(),
            'customer_id' => $reservation->getCustomerId(),
            'vehicle_id' => $reservation->getVehicleId(),
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        return [
            'reservation' => $reservation->toArray(),
            'message' => 'Reserva cancelada com sucesso',
        ];
    }
}
