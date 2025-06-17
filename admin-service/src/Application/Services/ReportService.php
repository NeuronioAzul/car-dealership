<?php

namespace App\Application\Services;

use App\Infrastructure\Database\DatabaseConfig;
use PDO;

class ReportService
{
    public function generateSalesReport(array $filters = []): array
    {
        $salesDb = DatabaseConfig::getSalesConnection();
        
        $sql = "
            SELECT 
                s.*,
                DATE_FORMAT(s.sale_date, '%Y-%m-%d') as sale_date_formatted,
                DATE_FORMAT(s.sale_date, '%Y-%m') as sale_month
            FROM sales s 
            WHERE s.deleted_at IS NULL
        ";
        
        $params = [];
        
        // Filtros
        if (!empty($filters['start_date'])) {
            $sql .= " AND s.sale_date >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND s.sale_date <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND s.status = :status";
            $params['status'] = $filters['status'];
        }
        
        $sql .= " ORDER BY s.sale_date DESC";
        
        $stmt = $salesDb->prepare($sql);
        $stmt->execute($params);
        
        $sales = [];
        $totalRevenue = 0;
        $monthlyBreakdown = [];
        
        while ($row = $stmt->fetch()) {
            $sales[] = $row;
            
            if ($row['status'] === 'completed') {
                $totalRevenue += $row['sale_price'];
                
                $month = $row['sale_month'];
                if (!isset($monthlyBreakdown[$month])) {
                    $monthlyBreakdown[$month] = ['count' => 0, 'revenue' => 0];
                }
                $monthlyBreakdown[$month]['count']++;
                $monthlyBreakdown[$month]['revenue'] += $row['sale_price'];
            }
        }
        
        return [
            'sales' => $sales,
            'summary' => [
                'total_sales' => count($sales),
                'total_revenue' => $totalRevenue,
                'average_sale_value' => count($sales) > 0 ? $totalRevenue / count($sales) : 0
            ],
            'monthly_breakdown' => $monthlyBreakdown,
            'filters_applied' => $filters
        ];
    }

    public function generateCustomerReport(array $filters = []): array
    {
        $authDb = DatabaseConfig::getAuthConnection();
        $salesDb = DatabaseConfig::getSalesConnection();
        $reservationDb = DatabaseConfig::getReservationConnection();
        
        // Buscar dados dos clientes
        $sql = "
            SELECT 
                u.*,
                DATE_FORMAT(u.created_at, '%Y-%m-%d') as registration_date
            FROM users u 
            WHERE u.deleted_at IS NULL AND u.role = 'customer'
        ";
        
        $params = [];
        
        if (!empty($filters['start_date'])) {
            $sql .= " AND u.created_at >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $sql .= " AND u.created_at <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        $stmt = $authDb->prepare($sql);
        $stmt->execute($params);
        
        $customers = [];
        while ($row = $stmt->fetch()) {
            // Buscar estatísticas de vendas do cliente
            $salesStmt = $salesDb->prepare("
                SELECT COUNT(*) as total_purchases, SUM(sale_price) as total_spent 
                FROM sales 
                WHERE customer_id = :customer_id AND deleted_at IS NULL AND status = 'completed'
            ");
            $salesStmt->execute(['customer_id' => $row['id']]);
            $salesData = $salesStmt->fetch();
            
            // Buscar estatísticas de reservas do cliente
            $reservationStmt = $reservationDb->prepare("
                SELECT COUNT(*) as total_reservations 
                FROM reservations 
                WHERE customer_id = :customer_id AND deleted_at IS NULL
            ");
            $reservationStmt->execute(['customer_id' => $row['id']]);
            $reservationData = $reservationStmt->fetch();
            
            $customers[] = array_merge($row, [
                'total_purchases' => $salesData['total_purchases'] ?: 0,
                'total_spent' => $salesData['total_spent'] ?: 0,
                'total_reservations' => $reservationData['total_reservations'] ?: 0
            ]);
        }
        
        return [
            'customers' => $customers,
            'summary' => [
                'total_customers' => count($customers),
                'active_customers' => count(array_filter($customers, fn($c) => $c['total_purchases'] > 0)),
                'average_spent_per_customer' => count($customers) > 0 ? 
                    array_sum(array_column($customers, 'total_spent')) / count($customers) : 0
            ],
            'filters_applied' => $filters
        ];
    }

    public function generateVehicleReport(array $filters = []): array
    {
        $vehicleDb = DatabaseConfig::getVehicleConnection();
        $salesDb = DatabaseConfig::getSalesConnection();
        
        $sql = "
            SELECT 
                v.*,
                DATE_FORMAT(v.created_at, '%Y-%m-%d') as added_date
            FROM vehicles v 
            WHERE v.deleted_at IS NULL
        ";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND v.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['brand'])) {
            $sql .= " AND v.brand = :brand";
            $params['brand'] = $filters['brand'];
        }
        
        if (!empty($filters['year_from'])) {
            $sql .= " AND v.model_year >= :year_from";
            $params['year_from'] = $filters['year_from'];
        }
        
        if (!empty($filters['year_to'])) {
            $sql .= " AND v.model_year <= :year_to";
            $params['year_to'] = $filters['year_to'];
        }
        
        $sql .= " ORDER BY v.created_at DESC";
        
        $stmt = $vehicleDb->prepare($sql);
        $stmt->execute($params);
        
        $vehicles = [];
        $statusBreakdown = [];
        $brandBreakdown = [];
        
        while ($row = $stmt->fetch()) {
            // Verificar se foi vendido
            $saleStmt = $salesDb->prepare("
                SELECT sale_date, sale_price 
                FROM sales 
                WHERE vehicle_id = :vehicle_id AND deleted_at IS NULL AND status = 'completed'
                LIMIT 1
            ");
            $saleStmt->execute(['vehicle_id' => $row['id']]);
            $saleData = $saleStmt->fetch();
            
            $vehicles[] = array_merge($row, [
                'sold_date' => $saleData['sale_date'] ?? null,
                'sold_price' => $saleData['sale_price'] ?? null,
                'is_sold' => $saleData ? true : false
            ]);
            
            // Estatísticas
            $status = $row['status'];
            $statusBreakdown[$status] = ($statusBreakdown[$status] ?? 0) + 1;
            
            $brand = $row['brand'];
            $brandBreakdown[$brand] = ($brandBreakdown[$brand] ?? 0) + 1;
        }
        
        return [
            'vehicles' => $vehicles,
            'summary' => [
                'total_vehicles' => count($vehicles),
                'available_vehicles' => $statusBreakdown['available'] ?? 0,
                'sold_vehicles' => $statusBreakdown['sold'] ?? 0,
                'reserved_vehicles' => $statusBreakdown['reserved'] ?? 0
            ],
            'breakdown' => [
                'by_status' => $statusBreakdown,
                'by_brand' => $brandBreakdown
            ],
            'filters_applied' => $filters
        ];
    }
}

