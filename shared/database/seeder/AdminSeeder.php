<?php

namespace Shared\Database\Seeder;

use Faker\Factory;
use Faker\Generator;

class AdminSeeder extends BaseSeeder
{
    private Generator $faker;

    public function __construct()
    {
        parent::__construct($this->getEnv('ADMIN_DB_NAME', 'admin_db'));
        $this->faker = Factory::create('pt_BR');
    }

    public function run(): void
    {
        echo "⚙️ Iniciando seed do Admin Service...\n";

        // Limpar tabelas
        $this->truncateTable('admin_notifications');
        $this->truncateTable('saved_reports');
        $this->truncateTable('audit_logs');
        $this->truncateTable('system_settings');

        // Criar dados administrativos
        $this->createSystemSettings();
        $this->createAuditLogs();
        $this->createSavedReports();
        $this->createNotifications();

        echo "✅ Seed do Admin Service concluído!\n\n";
    }

    private function createSystemSettings(): void
    {
        $settings = [
            [
                'id' => $this->generateUuid(),
                'setting_key' => 'company_name',
                'setting_value' => $this->getEnv('COMPANY_NAME', 'Concessionária M&D Ultra Max'),
                'setting_type' => 'string',
                'category' => 'company',
                'description' => 'Nome da empresa',
                'is_public' => true,
                'is_editable' => true,
                'created_at' => $this->getCurrentTimestamp(),
                'updated_at' => $this->getCurrentTimestamp()
            ],
            [
                'id' => $this->generateUuid(),
                'setting_key' => 'company_cnpj',
                'setting_value' => $this->getEnv('COMPANY_CNPJ', '12.345.678/0001-90'),
                'setting_type' => 'string',
                'category' => 'company',
                'description' => 'CNPJ da empresa',
                'is_public' => true,
                'is_editable' => true,
                'created_at' => $this->getCurrentTimestamp(),
                'updated_at' => $this->getCurrentTimestamp()
            ],
            [
                'id' => $this->generateUuid(),
                'setting_key' => 'company_address',
                'setting_value' => $this->getEnv('COMPANY_ADDRESS', 'Rua das Concessionárias, 123 - São Paulo/SP'),
                'setting_type' => 'string',
                'category' => 'company',
                'description' => 'Endereço da empresa',
                'is_public' => true,
                'is_editable' => true,
                'created_at' => $this->getCurrentTimestamp(),
                'updated_at' => $this->getCurrentTimestamp()
            ],
            [
                'id' => $this->generateUuid(),
                'setting_key' => 'company_phone',
                'setting_value' => $this->getEnv('COMPANY_PHONE', '(11) 3000-0000'),
                'setting_type' => 'string',
                'category' => 'company',
                'description' => 'Telefone da empresa',
                'is_public' => true,
                'is_editable' => true,
                'created_at' => $this->getCurrentTimestamp(),
                'updated_at' => $this->getCurrentTimestamp()
            ],
            [
                'id' => $this->generateUuid(),
                'setting_key' => 'company_email',
                'setting_value' => $this->getEnv('COMPANY_EMAIL', 'contato@mdultramax.com.br'),
                'setting_type' => 'string',
                'category' => 'company',
                'description' => 'Email da empresa',
                'is_public' => true,
                'is_editable' => true,
                'created_at' => $this->getCurrentTimestamp(),
                'updated_at' => $this->getCurrentTimestamp()
            ],
            [
                'id' => $this->generateUuid(),
                'setting_key' => 'reservation_expiry_hours',
                'setting_value' => $this->getEnv('RESERVATION_EXPIRY_HOURS', '24'),
                'setting_type' => 'number',
                'category' => 'business',
                'description' => 'Horas para expiração da reserva',
                'is_public' => false ? 0 : 1,
                'is_editable' => true,
                'created_at' => $this->getCurrentTimestamp(),
                'updated_at' => $this->getCurrentTimestamp()
            ],
            [
                'id' => $this->generateUuid(),
                'setting_key' => 'max_reservations_per_customer',
                'setting_value' => $this->getEnv('MAX_RESERVATIONS_PER_CUSTOMER', '3'),
                'setting_type' => 'number',
                'category' => 'business',
                'description' => 'Máximo de reservas por cliente',
                'is_public' => false ? 0 : 1,
                'is_editable' => true,
                'created_at' => $this->getCurrentTimestamp(),
                'updated_at' => $this->getCurrentTimestamp()
            ],
            [
                'id' => $this->generateUuid(),
                'setting_key' => 'payment_gateway_fee',
                'setting_value' => $this->getEnv('PAYMENT_GATEWAY_FEE', '3.5'),
                'setting_type' => 'number',
                'category' => 'payment',
                'description' => 'Taxa do gateway de pagamento (%)',
                'is_public' => false ? 0 : 1,
                'is_editable' => true,
                'created_at' => $this->getCurrentTimestamp(),
                'updated_at' => $this->getCurrentTimestamp()
            ],
            [
                'id' => $this->generateUuid(),
                'setting_key' => 'enable_notifications',
                'setting_value' => 'true',
                'setting_type' => 'boolean',
                'category' => 'system',
                'description' => 'Habilitar notificações',
                'is_public' => false ? 0 : 1,
                'is_editable' => true,
                'created_at' => $this->getCurrentTimestamp(),
                'updated_at' => $this->getCurrentTimestamp()
            ],
            [
                'id' => $this->generateUuid(),
                'setting_key' => 'maintenance_mode',
                'setting_value' => 'false',
                'setting_type' => 'boolean',
                'category' => 'system',
                'description' => 'Modo de manutenção',
                'is_public' => false ? 0 : 1,
                'is_editable' => true,
                'created_at' => $this->getCurrentTimestamp(),
                'updated_at' => $this->getCurrentTimestamp()
            ]
        ];

        $this->insertBatch('system_settings', $settings);
    }

    private function createAuditLogs(): void
    {
        $logs = [];

        // Buscar usuários admin
        $authConnection = $this->getDbConnection($this->getEnv('AUTH_DB_NAME', 'auth_db'));
        $admins = $authConnection->query("SELECT id, name, email, role FROM users WHERE role IN ('admin')")->fetchAll();

        $actions = [
            'user_created',
            'user_updated',
            'user_deleted',
            'vehicle_created',
            'vehicle_updated',
            'vehicle_deleted',
            'reservation_created',
            'reservation_cancelled',
            'payment_processed',
            'sale_created',
            'settings_updated',
            'report_generated'
        ];

        $logsCount = (int) $this->getEnv('SEED_AUDIT_LOGS_COUNT', 100);

        for ($i = 0; $i < $logsCount; $i++) {
            $admin = $this->faker->randomElement($admins);
            $action = $this->faker->randomElement($actions);

            $logs[] = [
                'id' => $this->generateUuid(),
                'action' => $action,
                'entity_type' => $this->getResourceType($action),
                'entity_id' => $this->generateUuid(),
                'user_id' => $admin['id'],
                'user_name' => $admin['name'],
                'user_email' => $admin['email'],
                'user_role' => $admin['role'],
                'old_values' => $this->generateOldValues($action),
                'new_values' => $this->generateNewValues($action),
                'changes' => json_encode(['field' => 'value'], JSON_UNESCAPED_UNICODE),
                'ip_address' => $this->faker->ipv4(),
                'user_agent' => $this->faker->userAgent(),
                'request_id' => $this->generateUuid(),
                'created_at' => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s')
            ];
        }

        $this->insertBatch('audit_logs', $logs);

        echo "📊 Criados: {$logsCount} logs de auditoria\n";
    }

    private function createSavedReports(): void
    {
        $reports = [];

        // Buscar usuários admin
        $authConnection = $this->getDbConnection($this->getEnv('AUTH_DB_NAME', 'auth_db'));
        $admins = $authConnection->query("SELECT id, name FROM users WHERE role = 'admin'")->fetchAll();

        $reportTypes = ['sales', 'customers', 'vehicles', 'payments', 'reservations'];
        $reportsCount = (int) $this->getEnv('SEED_REPORTS_COUNT', 20);

        for ($i = 0; $i < $reportsCount; $i++) {
            $admin = $this->faker->randomElement($admins);
            $reportType = $this->faker->randomElement($reportTypes);

            $reports[] = [
                'id' => $this->generateUuid(),
                'report_name' => $this->generateReportName($reportType),
                'report_type' => $reportType,
                'description' => $this->faker->sentence(8),
                'filters' => json_encode(['date_from' => $this->faker->date('Y-m-01'), 'date_to' => $this->faker->date('Y-m-t')], JSON_UNESCAPED_UNICODE),
                'columns' => json_encode($this->faker->randomElements(['id', 'name', 'value', 'status', 'created_at', 'updated_at'], $this->faker->numberBetween(3, 6)), JSON_UNESCAPED_UNICODE),
                'sort_config' => json_encode(['column' => $this->faker->randomElement(['created_at', 'name', 'value']), 'direction' => $this->faker->randomElement(['asc', 'desc'])], JSON_UNESCAPED_UNICODE),
                'created_by' => $admin['id'],
                'creator_name' => $admin['name'],
                'is_public' => $this->faker->boolean(20) ? 1 : 0,
                'shared_with' => json_encode($this->faker->boolean(30) ? [$this->faker->uuid()] : [], JSON_UNESCAPED_UNICODE),
                'is_scheduled' => $this->faker->boolean(25) ? 1 : 0,
                'schedule_config' => json_encode($this->faker->boolean(25) ? ['frequency' => $this->faker->randomElement(['daily', 'weekly', 'monthly']), 'time' => $this->faker->time('H:i')] : null, JSON_UNESCAPED_UNICODE),
                'last_generated_at' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d H:i:s') : null,
                'created_at' => $this->getCurrentTimestamp(),
                'updated_at' => $this->getCurrentTimestamp()
            ];
        }

        $this->insertBatch('saved_reports', $reports);

        echo "📊 Criados: {$reportsCount} relatórios salvos\n";
    }

    private function createNotifications(): void
    {
        $notifications = [];

        // Buscar usuários admin
        $authConnection = $this->getDbConnection($this->getEnv('AUTH_DB_NAME', 'auth_db'));
        $admins = $authConnection->query("SELECT id FROM users WHERE role = 'admin'")->fetchAll();

        $types = ['info', 'warning', 'error', 'success'];
        $titles = [
            'Sistema atualizado com sucesso',
            'Backup realizado automaticamente',
            'Falha na conexão com gateway de pagamento',
            'Novo cliente cadastrado',
            'Venda realizada com sucesso',
            'Reserva expirada automaticamente',
            'Relatório mensal gerado'
        ];

        $notificationsCount = (int) $this->getEnv('SEED_NOTIFICATIONS_COUNT', 30);

        for ($i = 0; $i < $notificationsCount; $i++) {
            $admin = $this->faker->randomElement($admins);
            $type = $this->faker->randomElement($types);
            $title = $this->faker->randomElement($titles);

            // Ajusta o título conforme o tipo e chance, para maior variedade e realismo
            switch ($type) {
                case 'error':
                    if ($this->faker->boolean(30)) {
                        $title = 'Erro crítico: ' . $title;
                    } elseif ($this->faker->boolean(30)) {
                        $title = 'Falha: ' . $title;
                    }
                    break;
                case 'warning':
                    if ($this->faker->boolean(40)) {
                        $title = 'Atenção: ' . $title;
                    } elseif ($this->faker->boolean(20)) {
                        $title = 'Aviso: ' . $title;
                    }
                    break;
                case 'success':
                    if ($this->faker->boolean(60)) {
                        $title = 'Sucesso: ' . $title;
                    } elseif ($this->faker->boolean(20)) {
                        $title = 'Concluído: ' . $title;
                    }
                    break;
                case 'info':
                    if ($this->faker->boolean(50)) {
                        $title = 'Informação: ' . $title;
                    } elseif ($this->faker->boolean(20)) {
                        $title = 'Nota: ' . $title;
                    }
                    break;
            }

            $notifications[] = [
                'id' => $this->generateUuid(),
                'title' => $title,
                'message' => $this->generateNotificationMessage($type, $title),
                'notification_type' => $type,
                'target_users' => json_encode([$admin['id']], JSON_UNESCAPED_UNICODE),
                'target_roles' => json_encode(['admin'], JSON_UNESCAPED_UNICODE),
                'is_global' => 0,
                'is_read' => 0,
                'read_by' => json_encode([], JSON_UNESCAPED_UNICODE),
                'action_url' => '/admin/notifications/' . $this->generateUuid(),
                'action_label' => 'Ver Detalhes',
                'expires_at' => $this->faker->boolean(70) ? $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d H:i:s') : null,
                'created_by' => $admin['id'],
                'created_at' => $this->getCurrentTimestamp(),
                'updated_at' => $this->getCurrentTimestamp(),
                'deleted_at' => null
            ];
        }

        $this->insertBatch('admin_notifications', $notifications);

        echo "📊 Criadas: {$notificationsCount} notificações\n";
    }

    private function getResourceType(string $action): string
    {
        $mapping = [
            'user_created' => 'user',
            'user_updated' => 'user',
            'user_deleted' => 'user',
            'vehicle_created' => 'vehicle',
            'vehicle_updated' => 'vehicle',
            'vehicle_deleted' => 'vehicle',
            'reservation_created' => 'reservation',
            'reservation_cancelled' => 'reservation',
            'payment_processed' => 'payment',
            'sale_created' => 'sale',
            'settings_updated' => 'setting',
            'report_generated' => 'report'
        ];

        return $mapping[$action] ?? 'unknown';
    }

    private function generateOldValues(string $action): ?string
    {
        if (strpos($action, 'created') !== false) {
            return null;
        }

        $values = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'status' => 'active',
            'price' => $this->faker->numberBetween(50000, 100000)
        ];

        return json_encode($values, JSON_UNESCAPED_UNICODE);
    }

    private function generateNewValues(string $action): string
    {
        $values = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'status' => $this->faker->randomElement(['active', 'inactive', 'pending']),
            'price' => $this->faker->numberBetween(50000, 100000)
        ];

        return json_encode($values, JSON_UNESCAPED_UNICODE);
    }

    private function generateReportName(string $type): string
    {
        $names = [
            'sales' => 'Relatório de Vendas - ' . $this->faker->monthName() . ' ' . $this->faker->year(),
            'customers' => 'Relatório de Clientes - ' . $this->faker->date('Y-m'),
            'vehicles' => 'Relatório de Estoque - ' . $this->faker->date('Y-m-d'),
            'payments' => 'Relatório Financeiro - ' . $this->faker->monthName(),
            'reservations' => 'Relatório de Reservas - Semanal'
        ];

        return $names[$type] ?? 'Relatório Personalizado';
    }

    private function generateNotificationMessage(string $type, string $title): string
    {
        $messages = [
            'info' => 'Informação do sistema: ' . $title . '. Nenhuma ação necessária.',
            'warning' => 'Atenção: ' . $title . '. Verifique se alguma ação é necessária.',
            'error' => 'Erro detectado: ' . $title . '. Ação imediata requerida.',
            'success' => 'Sucesso: ' . $title . '. Operação concluída com êxito.'
        ];

        return $messages[$type] ?? 'Notificação do sistema.';
    }
}
