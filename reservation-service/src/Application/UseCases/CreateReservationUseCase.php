<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Entities\Reservation;
use App\Domain\Repositories\ReservationRepositoryInterface;
use App\Infrastructure\Messaging\EventPublisher;

class CreateReservationUseCase
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

    public function execute(string $customerId, string $vehicleId): array
    {
        // Verificar se já existe reserva ativa para este veículo
        $existingReservation = $this->reservationRepository->findActiveByVehicleId($vehicleId);

        if ($existingReservation) {
            throw new \Exception('Veículo já está reservado', 409);
        }

        // Verificar se o cliente já tem reservas ativas (limite de 1 por cliente)
        $customerActiveReservations = $this->reservationRepository->findActiveByCustomerId($customerId);

        if (count($customerActiveReservations) >= 3) { // Limite de 3 reservas ativas
            throw new \Exception('Limite de reservas ativas atingido', 409);
        }

        // Criar nova reserva
        $reservation = new Reservation($customerId, $vehicleId);

        // Salvar reserva
        if (!$this->reservationRepository->save($reservation)) {
            throw new \Exception('Erro ao criar reserva', 500);
        }

        // Publicar evento de reserva criada
        $this->eventPublisher->publish('reservation.created', [
            'reservation_id' => $reservation->getId(),
            'customer_id' => $reservation->getCustomerId(),
            'vehicle_id' => $reservation->getVehicleId(),
            'expires_at' => $reservation->getExpiresAt()->format('Y-m-d H:i:s'),
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        return $reservation->toArray();
    }
}
