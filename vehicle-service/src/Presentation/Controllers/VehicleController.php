<?php

namespace App\Presentation\Controllers;

use App\Application\DTOs\VehicleDTO;
use App\Application\UseCases\CreateVehicleUseCase;
use App\Application\UseCases\GetVehicleDetailsUseCase;
use App\Application\UseCases\ListVehiclesUseCase;
use App\Application\UseCases\SearchVehiclesUseCase;
use App\Application\UseCases\UpdateVehicleUseCase;
use App\Application\UseCases\DeleteVehicleUseCase;
use App\Application\Validation\Requests\CreateVehicleRequest;
use App\Application\Validation\Requests\UpdateVehicleRequest;
use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\Database\VehicleRepository;
use App\Presentation\Middleware\AuthMiddleware;
use Exception;

class VehicleController
{
    private CreateVehicleUseCase $createVehicleUseCase;
    private UpdateVehicleUseCase $updateVehicleUseCase;
    private ListVehiclesUseCase $listVehiclesUseCase;
    private GetVehicleDetailsUseCase $getVehicleDetailsUseCase;
    private SearchVehiclesUseCase $searchVehiclesUseCase;
    private AuthMiddleware $authMiddleware;
    private DeleteVehicleUseCase $deleteVehicleUseCase;

    public function __construct()
    {
        $database = DatabaseConfig::getConnection();
        $vehicleRepository = new VehicleRepository($database);
        $this->authMiddleware = new AuthMiddleware();

        $this->listVehiclesUseCase = new ListVehiclesUseCase($vehicleRepository);
        $this->getVehicleDetailsUseCase = new GetVehicleDetailsUseCase($vehicleRepository);
        $this->searchVehiclesUseCase = new SearchVehiclesUseCase($vehicleRepository);
        $this->createVehicleUseCase = new CreateVehicleUseCase($vehicleRepository);
        $this->updateVehicleUseCase = new UpdateVehicleUseCase($vehicleRepository);
        $this->deleteVehicleUseCase = new DeleteVehicleUseCase($vehicleRepository);
    }

    public function createVehicle(): void
    {
        try {
            // Verificar se é admin antes de prosseguir
            $user = $this->authMiddleware->requireAdmin();

            $data = json_decode(file_get_contents('php://input'), true);

            $request = new CreateVehicleRequest($data);

            if (!$request->validate()) {
                http_response_code(422);
                echo json_encode([
                    'error' => true,
                    'message' => 'Validation failed',
                    'errors' => $request->errors(),
                ]);

                return;
            }

            $vehicleDTO = VehicleDTO::fromArray($request->validated());

            $existingChassisVehicle = $this->searchVehiclesUseCase->execute([
                'chassis_number' => $vehicleDTO->chassisNumber,
            ]);

            $existingLicensePlateVehicle = $this->searchVehiclesUseCase->execute([
                'license_plate' => $vehicleDTO->licensePlate,
            ]);

            if ($existingChassisVehicle['total'] > 0 || $existingLicensePlateVehicle['total'] > 0) {
                throw new Exception('Veículo com chassis ou placa já existente', 409);
            }

            $createdVehicle = $this->createVehicleUseCase->execute($vehicleDTO);

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Veículo criado com sucesso',
                'data' => $createdVehicle,
                'created_by' => $user['user_id'], // Adicionar informação de quem criou
            ]);
        } catch (Exception $e) {
            if (!is_numeric($e->getCode())) {
                $code = 500;
            } else {
                $code = $e->getCode();
            }
            http_response_code($code);

            $response = [
                'error' => true,
                'message' => $e->getMessage(),
                'code' => $code,
            ];

            // Adicionar contexto adicional para erros de autenticação
            if ($code === 401) {
                $response['type'] = 'authentication_error';
                $response['action'] = 'redirect_to_login';
            } elseif ($code === 403) {
                $response['type'] = 'authorization_error';
                $response['action'] = 'insufficient_permissions';
            }

            echo json_encode($response);
        }
    }

    public function updateVehicle(string $id): void
    {
        try {
            $user = $this->authMiddleware->requireAdmin();

            $inputData = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            $inputData['id'] = $id;

            $request = new UpdateVehicleRequest($inputData);

            if (!$request->validate()) {
                http_response_code(422);
                echo json_encode([
                    'error' => true,
                    'message' => 'Validation failed',
                    'errors' => $request->errors(),
                ]);

                return;
            }

            $vehicleDTO = VehicleDTO::fromArray($request->validated());

            $updatedVehicle = $this->updateVehicleUseCase->execute($vehicleDTO);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Veículo atualizado com sucesso',
                'data' => $updatedVehicle->toArray(),
                'updated_by' => $user['user_id'],
            ]);
        } catch (Exception $e) {
            if (!is_numeric($e->getCode())) {
                $code = 500;
            } else {
                $code = $e->getCode();
            }
            http_response_code($code);

            $response = [
                'error' => true,
                'message' => $e->getCode() . ' ' . $e->getMessage(),
                'code' => $code,
            ];

            // Adicionar contexto adicional para erros de autenticação
            if ($code === 401) {
                $response['type'] = 'authentication_error';
                $response['action'] = 'redirect_to_login';
            } elseif ($code === 403) {
                $response['type'] = 'authorization_error';
                $response['action'] = 'insufficient_permissions';
            }

            echo json_encode($response);
        }
    }

    public function listVehicles(): void
    {
        try {
            // Verificar se é admin para mostrar todos os veículos
            $showAll = false;

            try {
                $user = $this->authMiddleware->authenticate();
                $showAll = $user['role'] === 'admin';
            } catch (Exception $e) {
                // Usuário não autenticado, mostrar apenas disponíveis
            }

            $vehicles = $this->listVehiclesUseCase->execute(!$showAll);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $vehicles,
                'total' => count($vehicles),
            ]);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getVehicleDetails(string $id): void
    {
        try {
            if (!$id) {
                throw new Exception('ID do veículo é obrigatório', 400);
            }

            $vehicle = $this->getVehicleDetailsUseCase->execute($id);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $vehicle,
            ]);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage(),
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
                'data' => $result,
            ]);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage(),
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
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    public function deleteVehicle(string $id): void
    {
        try {
            // Verificar autenticação - apenas admin pode deletar
            $user = $this->authMiddleware->authenticate();

            if ($user['role'] !== 'admin') {
                throw new Exception('Acesso negado. Apenas administradores podem deletar veículos.', 403);
            }

            if (!$id) {
                throw new Exception('ID do veículo é obrigatório', 400);
            }

            $this->deleteVehicleUseCase->execute($id);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Veículo deletado com sucesso',
            ]);
        } catch (Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
