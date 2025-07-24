<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Repositories\ReservationRepositoryInterface;

class ListCustomerReservationsUseCase
{
    private ReservationRepositoryInterface $reservationRepository;

    public function __construct(ReservationRepositoryInterface $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }

    public function execute(string $customerId, bool $activeOnly = false): array
    {
        if ($activeOnly) {
            $reservations = $this->reservationRepository->findActiveByCustomerId($customerId);
        } else {
            $reservations = $this->reservationRepository->findByCustomerId($customerId);
        }

        return [
            'reservations' => array_map(function ($reservation) {
                return $reservation->toArray();
            }, $reservations),
            'total' => count($reservations),
        ];
    }
}
