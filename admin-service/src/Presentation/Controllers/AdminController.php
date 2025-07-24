<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\DashboardService;
use App\Application\Services\ReportService;
use App\Presentation\Middleware\AuthMiddleware;

class AdminController
{
    private DashboardService $dashboardService;
    private ReportService $reportService;
    private AuthMiddleware $authMiddleware;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
        $this->reportService = new ReportService();
        $this->authMiddleware = new AuthMiddleware();
    }

    public function getDashboard(): void
    {
        try {
            $user = $this->authMiddleware->requireAdmin();

            $stats = $this->dashboardService->getDashboardStats();

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $stats,
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

    public function getSalesReport(): void
    {
        try {
            $user = $this->authMiddleware->requireAdmin();

            $filters = $_GET;
            $report = $this->reportService->generateSalesReport($filters);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $report,
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

    public function getCustomerReport(): void
    {
        try {
            $user = $this->authMiddleware->requireAdmin();

            $filters = $_GET;
            $report = $this->reportService->generateCustomerReport($filters);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $report,
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

    public function getVehicleReport(): void
    {
        try {
            $user = $this->authMiddleware->requireAdmin();

            $filters = $_GET;
            $report = $this->reportService->generateVehicleReport($filters);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $report,
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

    public function health(): void
    {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'service' => 'admin-service',
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }
}
