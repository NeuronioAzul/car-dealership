<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Infrastructure\Database\DatabaseConfig;
use PDO;

class DashboardService
{
    public function getDashboardStats(): array
    {
        try {
            // Estatísticas de usuários
            $authDb = DatabaseConfig::getAuthConnection();
            $userStats = $this->getUserStats($authDb);

            // Estatísticas de veículos
            $vehicleDb = DatabaseConfig::getVehicleConnection();
            $vehicleStats = $this->getVehicleStats($vehicleDb);

            // Estatísticas de reservas
            $reservationDb = DatabaseConfig::getReservationConnection();
            $reservationStats = $this->getReservationStats($reservationDb);

            // Estatísticas de pagamentos
            $paymentDb = DatabaseConfig::getPaymentConnection();
            $paymentStats = $this->getPaymentStats($paymentDb);

            // Estatísticas de vendas
            $salesDb = DatabaseConfig::getSalesConnection();
            $salesStats = $this->getSalesStats($salesDb);

            return [
                'users' => $userStats,
                'vehicles' => $vehicleStats,
                'reservations' => $reservationStats,
                'payments' => $paymentStats,
                'sales' => $salesStats,
                'summary' => [
                    'total_revenue' => $salesStats['total_revenue'],
                    'active_customers' => $userStats['active_customers'],
                    'available_vehicles' => $vehicleStats['available'],
                    'pending_reservations' => $reservationStats['active'],
                ],
            ];
        } catch (\Exception $e) {
            throw new \Exception('Erro ao obter estatísticas do dashboard: ' . $e->getMessage());
        }
    }

    private function getUserStats(PDO $authDb): array
    {
        $stats = [];

        // Total de usuários
        $stmt = $authDb->query('SELECT COUNT(*) as total FROM users WHERE deleted_at IS NULL');
        $stats['total'] = $stmt->fetch()['total'];

        // Usuários ativos (logaram nos últimos 30 dias)
        $stmt = $authDb->query('SELECT COUNT(*) as active FROM users WHERE deleted_at IS NULL AND updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)');
        $stats['active_customers'] = $stmt->fetch()['active'];

        // Novos usuários (últimos 7 dias)
        $stmt = $authDb->query('SELECT COUNT(*) as new_users FROM users WHERE deleted_at IS NULL AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
        $stats['new_this_week'] = $stmt->fetch()['new_users'];

        // Usuários por tipo
        $stmt = $authDb->query('SELECT role, COUNT(*) as count FROM users WHERE deleted_at IS NULL GROUP BY role');
        $roleStats = [];
        while ($row = $stmt->fetch()) {
            $roleStats[$row['role']] = $row['count'];
        }
        $stats['by_role'] = $roleStats;

        return $stats;
    }

    private function getVehicleStats(PDO $vehicleDb): array
    {
        $stats = [];

        // Total de veículos
        $stmt = $vehicleDb->query('SELECT COUNT(*) as total FROM vehicles WHERE deleted_at IS NULL');
        $stats['total'] = $stmt->fetch()['total'];

        // Veículos por status
        $stmt = $vehicleDb->query('SELECT status, COUNT(*) as count FROM vehicles WHERE deleted_at IS NULL GROUP BY status');
        while ($row = $stmt->fetch()) {
            $stats[$row['status']] = $row['count'];
        }

        // Veículos por marca (top 5)
        $stmt = $vehicleDb->query('SELECT brand, COUNT(*) as count FROM vehicles WHERE deleted_at IS NULL GROUP BY brand ORDER BY count DESC LIMIT 5');
        $brandStats = [];
        while ($row = $stmt->fetch()) {
            $brandStats[$row['brand']] = $row['count'];
        }
        $stats['top_brands'] = $brandStats;

        // Preço médio
        $stmt = $vehicleDb->query("SELECT AVG(price) as avg_price FROM vehicles WHERE deleted_at IS NULL AND status = 'available'");
        $stats['average_price'] = (float) $stmt->fetch()['avg_price'];

        return $stats;
    }

    private function getReservationStats(PDO $reservationDb): array
    {
        $stats = [];

        // Total de reservas
        $stmt = $reservationDb->query('SELECT COUNT(*) as total FROM reservations WHERE deleted_at IS NULL');
        $stats['total'] = $stmt->fetch()['total'];

        // Reservas por status
        $stmt = $reservationDb->query('SELECT status, COUNT(*) as count FROM reservations WHERE deleted_at IS NULL GROUP BY status');
        while ($row = $stmt->fetch()) {
            $stats[$row['status']] = $row['count'];
        }

        // Reservas ativas (não expiradas)
        $stmt = $reservationDb->query("SELECT COUNT(*) as active FROM reservations WHERE deleted_at IS NULL AND status = 'active' AND expires_at > NOW()");
        $stats['active'] = $stmt->fetch()['active'];

        // Reservas criadas hoje
        $stmt = $reservationDb->query('SELECT COUNT(*) as today FROM reservations WHERE deleted_at IS NULL AND DATE(created_at) = CURDATE()');
        $stats['created_today'] = $stmt->fetch()['today'];

        return $stats;
    }

    private function getPaymentStats(PDO $paymentDb): array
    {
        $stats = [];

        // Total de pagamentos
        $stmt = $paymentDb->query('SELECT COUNT(*) as total FROM payments WHERE deleted_at IS NULL');
        $stats['total'] = $stmt->fetch()['total'];

        // Pagamentos por status
        $stmt = $paymentDb->query('SELECT status, COUNT(*) as count FROM payments WHERE deleted_at IS NULL GROUP BY status');
        while ($row = $stmt->fetch()) {
            $stats[$row['status']] = $row['count'];
        }

        // Valor total processado
        $stmt = $paymentDb->query("SELECT SUM(amount) as total_amount FROM payments WHERE deleted_at IS NULL AND status = 'completed'");
        $stats['total_processed'] = (float) $stmt->fetch()['total_amount'];

        // Taxa de aprovação
        $stmt = $paymentDb->query("
            SELECT 
                (SELECT COUNT(*) FROM payments WHERE deleted_at IS NULL AND status = 'completed') as completed,
                (SELECT COUNT(*) FROM payments WHERE deleted_at IS NULL AND status IN ('completed', 'failed')) as total_processed
        ");
        $row = $stmt->fetch();
        $stats['approval_rate'] = $row['total_processed'] > 0 ? ($row['completed'] / $row['total_processed']) * 100 : 0;

        return $stats;
    }

    private function getSalesStats(PDO $salesDb): array
    {
        $stats = [];

        // Total de vendas
        $stmt = $salesDb->query('SELECT COUNT(*) as total FROM sales WHERE deleted_at IS NULL');
        $stats['total'] = $stmt->fetch()['total'];

        // Vendas por status
        $stmt = $salesDb->query('SELECT status, COUNT(*) as count FROM sales WHERE deleted_at IS NULL GROUP BY status');
        while ($row = $stmt->fetch()) {
            $stats[$row['status']] = $row['count'];
        }

        // Receita total
        $stmt = $salesDb->query("SELECT SUM(sale_price) as total_revenue FROM sales WHERE deleted_at IS NULL AND status = 'completed'");
        $stats['total_revenue'] = (float) $stmt->fetch()['total_revenue'];

        // Vendas por mês (últimos 6 meses)
        $stmt = $salesDb->query("
            SELECT 
                DATE_FORMAT(sale_date, '%Y-%m') as month,
                COUNT(*) as count,
                SUM(sale_price) as revenue
            FROM sales 
            WHERE deleted_at IS NULL 
            AND status = 'completed'
            AND sale_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(sale_date, '%Y-%m')
            ORDER BY month DESC
        ");
        $monthlyStats = [];
        while ($row = $stmt->fetch()) {
            $monthlyStats[] = [
                'month' => $row['month'],
                'sales_count' => $row['count'],
                'revenue' => (float) $row['revenue'],
            ];
        }
        $stats['monthly'] = $monthlyStats;

        // Vendas hoje
        $stmt = $salesDb->query('SELECT COUNT(*) as today FROM sales WHERE deleted_at IS NULL AND DATE(sale_date) = CURDATE()');
        $stats['sales_today'] = $stmt->fetch()['today'];

        return $stats;
    }
}
