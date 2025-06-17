<?php

namespace App\Presentation\Controllers;

use App\Application\UseCases\CreateReservationUseCase;
use App\Application\UseCases\CancelReservationUseCase;
use App\Application\UseCases\GeneratePaymentCodeUseCase;
use App\Application\UseCases\ListCustomerReservationsUseCase;
use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\Database\ReservationRepository;
use App\Infrastructure\Messaging\EventPublisher;
use App\Presentation\Middleware\AuthMiddleware;

class ReservationController
{
    private CreateReservationUseCase $createReservationUseCase;
    private CancelReservationUseCase $cancelReservationUseCase;
    private GeneratePaymentCodeUseCase $generatePaymentCodeUseCase;
    private ListCustomerReservationsUseCase $listReservationsUseCase;
    private AuthMiddleware $authMiddleware;

    public function __construct()
    {
        $database = DatabaseConfig::getConnection();
        $reservationRepository = new ReservationRepository($database);
        $eventPublisher = new EventPublisher();
        $this->authMiddleware = new AuthMiddleware();

        $this->createReservationUseCase = new CreateReservationUseCase($reservationRepository, $eventPublisher);
        $this->cancelReservationUseCase = new CancelReservationUseCase($reservationRepository, $eventPublisher);
        $this->generatePaymentCodeUseCase = new GeneratePaymentCodeUseCase($reservationRepository, $eventPublisher);
        $this->listReservationsUseCase = new ListCustomerReservationsUseCase($reservationRepository);
    }

    public function createReservation(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['vehicle_id'])) {
                throw new \Exception('ID do veículo é obrigatório', 400);
            }

            $result = $this->createReservationUseCase->execute($user['user_id'], $input['vehicle_id']);
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'data' => $result,
                'message' => 'Reserva criada com sucesso'
            ]);
            
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function listReservations(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();
            $activeOnly = isset($_GET['active_only']) && $_GET['active_only'] === 'true';
            
            $result = $this->listReservationsUseCase->execute($user['user_id'], $activeOnly);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getReservationDetails(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $pathParts = explode('/', trim($path, '/'));
            $reservationId = end($pathParts);

            if (!$reservationId) {
                throw new \Exception('ID da reserva é obrigatório', 400);
            }

            $database = DatabaseConfig::getConnection();
            $reservationRepository = new ReservationRepository($database);
            $reservation = $reservationRepository->findById($reservationId);

            if (!$reservation) {
                throw new \Exception('Reserva não encontrada', 404);
            }

            if ($reservation->getCustomerId() !== $user['user_id']) {
                throw new \Exception('Acesso negado', 403);
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $reservation->toArray()
            ]);
            
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function cancelReservation(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $pathParts = explode('/', trim($path, '/'));
            $reservationId = end($pathParts);

            if (!$reservationId) {
                throw new \Exception('ID da reserva é obrigatório', 400);
            }

            $result = $this->cancelReservationUseCase->execute($reservationId, $user['user_id']);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function generatePaymentCode(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['reservation_id'])) {
                throw new \Exception('ID da reserva é obrigatório', 400);
            }

            $result = $this->generatePaymentCodeUseCase->execute($input['reservation_id'], $user['user_id']);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function health(): void
    {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'service' => 'reservation-service',
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

