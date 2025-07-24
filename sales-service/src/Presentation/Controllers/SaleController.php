<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\PDFGeneratorService;
use App\Application\UseCases\CreateSaleUseCase;
use App\Application\UseCases\GetSaleDetailsUseCase;
use App\Application\UseCases\ListCustomerSalesUseCase;
use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\Database\SaleRepository;
use App\Infrastructure\Messaging\EventPublisher;
use App\Presentation\Middleware\AuthMiddleware;

class SaleController
{
    private CreateSaleUseCase $createSaleUseCase;
    private GetSaleDetailsUseCase $getSaleDetailsUseCase;
    private ListCustomerSalesUseCase $listSalesUseCase;
    private PDFGeneratorService $pdfGenerator;
    private AuthMiddleware $authMiddleware;

    public function __construct()
    {
        $database = DatabaseConfig::getConnection();
        $saleRepository = new SaleRepository($database);
        $this->pdfGenerator = new PDFGeneratorService();
        $eventPublisher = new EventPublisher();
        $this->authMiddleware = new AuthMiddleware();

        $this->createSaleUseCase = new CreateSaleUseCase($saleRepository, $this->pdfGenerator, $eventPublisher);
        $this->getSaleDetailsUseCase = new GetSaleDetailsUseCase($saleRepository);
        $this->listSalesUseCase = new ListCustomerSalesUseCase($saleRepository);
    }

    public function createSale(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();
            $input = json_decode(file_get_contents('php://input'), true);

            $requiredFields = ['vehicle_id', 'reservation_id', 'payment_id', 'sale_price', 'customer_data', 'vehicle_data'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field])) {
                    throw new \Exception("Campo obrigatório: {$field}", 400);
                }
            }

            $result = $this->createSaleUseCase->execute(
                $user['user_id'],
                $input['vehicle_id'],
                $input['reservation_id'],
                $input['payment_id'],
                (float) $input['sale_price'],
                $input['customer_data'],
                $input['vehicle_data']
            );

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function listSales(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();

            $result = $this->listSalesUseCase->execute($user['user_id']);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getSaleDetails(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $pathParts = explode('/', trim($path, '/'));
            $saleId = end($pathParts);

            if (!$saleId) {
                throw new \Exception('ID da venda é obrigatório', 400);
            }

            $result = $this->getSaleDetailsUseCase->execute($saleId, $user['user_id']);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            $code = $e->getCode() ?: 500;
            http_response_code($code);
            echo json_encode([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function downloadDocument(): void
    {
        try {
            $user = $this->authMiddleware->requireCustomer();
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $pathParts = explode('/', trim($path, '/'));

            // Extrair sale_id e document_type da URL
            $saleId = $pathParts[count($pathParts) - 2];
            $documentType = end($pathParts);

            if (!$saleId || !in_array($documentType, ['contract', 'invoice'])) {
                throw new \Exception('Parâmetros inválidos', 400);
            }

            // Buscar venda
            $database = DatabaseConfig::getConnection();
            $saleRepository = new SaleRepository($database);
            $sale = $saleRepository->findById($saleId);

            if (!$sale) {
                throw new \Exception('Venda não encontrada', 404);
            }

            if ($sale->getCustomerId() !== $user['user_id']) {
                throw new \Exception('Acesso negado', 403);
            }

            // Determinar arquivo
            $filename = null;

            if ($documentType === 'contract') {
                $filename = $sale->getContractPdfPath();
            } elseif ($documentType === 'invoice') {
                $filename = $sale->getInvoicePdfPath();
            }

            if (!$filename) {
                throw new \Exception('Documento não encontrado', 404);
            }

            $filepath = $this->pdfGenerator->getFilePath($filename);

            if (!file_exists($filepath)) {
                throw new \Exception('Arquivo não encontrado', 404);
            }

            // Enviar arquivo
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
        } catch (\Exception $e) {
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
            'service' => 'sales-service',
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }
}
