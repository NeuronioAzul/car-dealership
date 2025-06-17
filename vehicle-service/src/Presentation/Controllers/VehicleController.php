<?php

namespace App\Presentation\Controllers;

use App\Application\UseCases\ListVehiclesUseCase;
use App\Application\UseCases\GetVehicleDetailsUseCase;
use App\Application\UseCases\SearchVehiclesUseCase;
use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\Database\VehicleRepository;
use App\Presentation\Middleware\AuthMiddleware;

class VehicleController
{
    private ListVehiclesUseCase $listVehiclesUseCase;
    private GetVehicleDetailsUseCase $getVehicleDetailsUseCase;
    private SearchVehiclesUseCase $searchVehiclesUseCase;
    private AuthMiddleware $authMiddleware;

    public function __construct()
    {
        $database = DatabaseConfig::getConnection();
        $vehicleRepository = new VehicleRepository($database);
        $this->authMiddleware = new AuthMiddleware();

        $this->listVehiclesUseCase = new ListVehiclesUseCase($vehicleRepository);
        $this->getVehicleDetailsUseCase = new GetVehicleDetailsUseCase($vehicleRepository);
        $this->searchVehiclesUseCase = new SearchVehiclesUseCase($vehicleRepository);
    }

    public function listVehicles(): void
    {
        try {
            // Verificar se é admin para mostrar todos os veículos
            $showAll = false;
            try {
                $user = $this->authMiddleware->authenticate();
                $showAll = $user['role'] === 'admin';
            } catch (\Exception $e) {
                // Usuário não autenticado, mostrar apenas disponíveis
            }

            $vehicles = $this->listVehiclesUseCase->execute(!$showAll);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $vehicles,
                'total' => count($vehicles)
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

    public function getVehicleDetails(): void
    {
        try {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $pathParts = explode('/', trim($path, '/'));
            $vehicleId = end($pathParts);

            if (!$vehicleId) {
                throw new \Exception('ID do veículo é obrigatório', 400);
            }

            $vehicle = $this->getVehicleDetailsUseCase->execute($vehicleId);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $vehicle
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

    public function searchVehicles(): void
    {
        try {
            $criteria = $_GET;
            
            $result = $this->searchVehiclesUseCase->execute($criteria);
            
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
            'service' => 'vehicle-service',
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

