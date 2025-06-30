<?php

namespace Shared\Database\Seeder;

use Faker\Factory;
use Faker\Generator;

class SagaSeeder extends BaseSeeder
{
    private Generator $faker;

    public function __construct()
    {
        parent::__construct($this->getEnv('SAGA_DB_NAME', 'saga_db'));
        $this->faker = Factory::create('pt_BR');
    }

    public function run(): void
    {
        echo "🔄 Iniciando seed do SAGA Service...\n";

        // Limpar tabelas
        $this->truncateTable('saga_events');
        $this->truncateTable('saga_steps');
        $this->truncateTable('saga_transactions');

        // Criar transações SAGA
        $this->createSagaTransactions();

        echo "✅ Seed do SAGA Service concluído!\n\n";
    }

    private function createSagaTransactions(): void
    {
        $transactions = [];
        $steps = [];
        $events = [];

        // Buscar vendas para criar SAGAs baseadas nelas
        $salesConnection = $this->getDbConnection($this->getEnv('SALES_DB_NAME', 'sales_db'));
        $sagaCount = (int) $this->getEnv('SEED_SAGA_TRANSACTIONS_COUNT', 10);

        $sales = $salesConnection->query("SELECT s.*, p.payment_method 
            FROM sales_db.sales s
            LEFT JOIN payment_db.payments p ON s.payment_id = p.id
            LIMIT {$sagaCount}
        ")->fetchAll();

        foreach ($sales as $sale) {
            $transactionId = $this->generateUuid();
            $status = $this->faker->randomElement(['pending', 'completed', 'failed', 'compensating', 'compensated']);

            $createdAt = $this->faker->dateTimeBetween($sale['created_at'], 'now');
            $completedAt = $status === 'completed' ? $this->faker->dateTimeBetween($createdAt, 'now') : null;

            // Transação SAGA principal
            $transactions[] = [
                'id' => $transactionId,
                'saga_type' => 'vehicle_purchase',
                'correlation_id' => $sale['id'],
                'status' => $status,
                'current_step' => $this->getCurrentStep($status),
                'context_data' => $this->generateContextData($sale),
                'started_at' => $createdAt->format('Y-m-d H:i:s'),
                'completed_at' => $completedAt ? $completedAt->format('Y-m-d H:i:s') : null,
                'error_message' => $status === 'failed' ? $this->generateErrorMessage() : null,
                'retry_count' => $status === 'failed' ? $this->faker->numberBetween(0, 3) : 0,
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $this->getCurrentTimestamp()
            ];

            // Passos da SAGA
            $sagaSteps = $this->getSagaSteps();
            $currentStepIndex = $this->getCurrentStepIndex($status);

            foreach ($sagaSteps as $index => $stepName) {
                $stepId = $this->generateUuid();
                $stepStatus = $this->getStepStatus($index, $currentStepIndex, $status);
                $stepStarted = $index <= $currentStepIndex ? $this->faker->dateTimeBetween($createdAt, 'now') : null;
                $stepCompleted = $stepStatus === 'completed' ? $this->faker->dateTimeBetween($stepStarted, 'now') : null;

                $steps[] = [
                    'id' => $stepId,
                    'saga_transaction_id' => $transactionId,
                    'step_name' => $stepName,
                    'step_order' => $index + 1,
                    'status' => $stepStatus,
                    'service_name' => $this->getServiceName($stepName),
                    'request_data' => $this->generateRequestData($stepName, $sale),
                    'response_data' => $stepStatus === 'completed' ? $this->generateResponseData($stepName) : null,
                    'compensation_data' => $this->generateCompensationData($stepName),
                    'started_at' => $stepStarted ? $stepStarted->format('Y-m-d H:i:s') : null,
                    'completed_at' => $stepCompleted ? $stepCompleted->format('Y-m-d H:i:s') : null,
                    'error_message' => $stepStatus === 'failed' ? $this->generateStepError($stepName) : null,
                    'retry_count' => $stepStatus === 'failed' ? $this->faker->numberBetween(0, 2) : 0,
                    'created_at' => $createdAt->format('Y-m-d H:i:s'),
                    'updated_at' => $this->getCurrentTimestamp()
                ];

                // Eventos para cada passo
                if ($stepStarted) {
                    $events[] = [
                        'id' => $this->generateUuid(),
                        'saga_transaction_id' => $transactionId,
                        'saga_step_id' => $stepId,
                        'event_type' => 'step_started',
                        'event_data' => json_encode(['step' => $stepName, 'order' => $index + 1], JSON_UNESCAPED_UNICODE),
                        'created_at' => $stepStarted->format('Y-m-d H:i:s')
                    ];
                }

                if ($stepCompleted) {
                    $events[] = [
                        'id' => $this->generateUuid(),
                        'saga_transaction_id' => $transactionId,
                        'saga_step_id' => $stepId,
                        'event_type' => 'step_completed',
                        'event_data' => json_encode(['step' => $stepName, 'result' => 'success'], JSON_UNESCAPED_UNICODE),
                        'created_at' => $stepCompleted->format('Y-m-d H:i:s')
                    ];
                }

                if ($stepStatus === 'failed') {
                    $events[] = [
                        'id' => $this->generateUuid(),
                        'saga_transaction_id' => $transactionId,
                        'saga_step_id' => $stepId,
                        'event_type' => 'step_failed',
                        'event_data' => json_encode(['step' => $stepName, 'error' => $this->generateStepError($stepName)], JSON_UNESCAPED_UNICODE),
                        'created_at' => $stepStarted ? $stepStarted->format('Y-m-d H:i:s') : $createdAt->format('Y-m-d H:i:s')
                    ];
                }
            }

            // Evento de início da transação
            $events[] = [
                'id' => $this->generateUuid(),
                'saga_transaction_id' => $transactionId,
                'saga_step_id' => null,
                'event_type' => 'saga_started',
                'event_data' => json_encode(['type' => 'vehicle_purchase', 'customer_id' => $sale['customer_id']], JSON_UNESCAPED_UNICODE),
                'created_at' => $createdAt->format('Y-m-d H:i:s')
            ];

            // Evento de conclusão se aplicável
            if ($completedAt) {
                $events[] = [
                    'id' => $this->generateUuid(),
                    'saga_transaction_id' => $transactionId,
                    'saga_step_id' => null,
                    'event_type' => $status === 'completed' ? 'saga_completed' : 'saga_failed',
                    'event_data' => json_encode(['final_status' => $status], JSON_UNESCAPED_UNICODE),
                    'created_at' => $completedAt->format('Y-m-d H:i:s')
                ];
            }
        }

        $this->insertBatch('saga_transactions', $transactions);
        $this->insertBatch('saga_steps', $steps);
        $this->insertBatch('saga_events', $events);

        echo "📊 Criadas: " . count($transactions) . " transações SAGA com passos e eventos\n";
    }

    private function getSagaSteps(): array
    {
        return [
            'create_reservation',
            'generate_payment_code',
            'process_payment',
            'create_sale',
            'update_vehicle_status'
        ];
    }

    private function getCurrentStep(string $status): string
    {
        $steps = $this->getSagaSteps();

        switch ($status) {
            case 'pending':
                return $this->faker->randomElement(array_slice($steps, 0, 3));
            case 'completed':
                return 'update_vehicle_status';
            case 'failed':
                return $this->faker->randomElement($steps);
            case 'compensating':
            case 'compensated':
                return $this->faker->randomElement(array_slice($steps, 1, 3));
            default:
                return $steps[0];
        }
    }

    private function getCurrentStepIndex(string $status): int
    {
        switch ($status) {
            case 'pending':
                return $this->faker->numberBetween(0, 2);
            case 'completed':
                return 4; // Último passo
            case 'failed':
                return $this->faker->numberBetween(0, 4);
            case 'compensating':
            case 'compensated':
                return $this->faker->numberBetween(1, 3);
            default:
                return 0;
        }
    }

    private function getStepStatus(int $stepIndex, int $currentStepIndex, string $sagaStatus): string
    {
        if ($stepIndex < $currentStepIndex) {
            return 'completed';
        } elseif ($stepIndex === $currentStepIndex) {
            if ($sagaStatus === 'failed') {
                return 'failed';
            } elseif ($sagaStatus === 'completed') {
                return 'completed';
            } else {
                return 'pending';
            }
        } else {
            return 'pending';
        }
    }

    private function getServiceName(string $stepName): string
    {
        $mapping = [
            'create_reservation' => 'reservation-service',
            'generate_payment_code' => 'reservation-service',
            'process_payment' => 'payment-service',
            'create_sale' => 'sales-service',
            'update_vehicle_status' => 'vehicle-service'
        ];

        return $mapping[$stepName] ?? 'unknown-service';
    }

    private function generateContextData(array $sale): string
    {
        return json_encode([
            'customer_id' => $sale['customer_id'],
            'vehicle_id' => $sale['vehicle_id'],
            'sale_id' => $sale['id'],
            'amount' => $sale['total_amount'],
            'payment_method' => $sale['payment_method']
        ], JSON_UNESCAPED_UNICODE);
    }

    private function generateRequestData(string $stepName, array $sale): string
    {
        $data = [
            'create_reservation' => [
                'customer_id' => $sale['customer_id'],
                'vehicle_id' => $sale['vehicle_id']
            ],
            'generate_payment_code' => [
                'reservation_id' => $this->generateUuid(),
                'amount' => $sale['total_amount']
            ],
            'process_payment' => [
                'payment_code' => 'PAY' . mt_rand(100000, 999999),
                'amount' => $sale['total_amount'],
                'method' => $sale['payment_method']
            ],
            'create_sale' => [
                'customer_id' => $sale['customer_id'],
                'vehicle_id' => $sale['vehicle_id'],
                'payment_id' => $this->generateUuid()
            ],
            'update_vehicle_status' => [
                'vehicle_id' => $sale['vehicle_id'],
                'status' => 'sold'
            ]
        ];

        return json_encode($data[$stepName] ?? [], JSON_UNESCAPED_UNICODE);
    }

    private function generateResponseData(string $stepName): string
    {
        $data = [
            'create_reservation' => [
                'reservation_id' => $this->generateUuid(),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+' . $this->getEnv('RESERVATION_EXPIRY_HOURS', 24) . ' hours'))
            ],
            'generate_payment_code' => [
                'payment_code' => 'PAY' . mt_rand(100000, 999999)
            ],
            'process_payment' => [
                'payment_id' => $this->generateUuid(),
                'status' => 'completed',
                'authorization_code' => 'AUTH' . mt_rand(100000, 999999)
            ],
            'create_sale' => [
                'sale_id' => $this->generateUuid(),
                'sale_number' => 'VND' . mt_rand(100000, 999999)
            ],
            'update_vehicle_status' => [
                'vehicle_id' => $this->generateUuid(),
                'previous_status' => 'available',
                'new_status' => 'sold'
            ]
        ];

        return json_encode($data[$stepName] ?? [], JSON_UNESCAPED_UNICODE);
    }

    private function generateCompensationData(string $stepName): string
    {
        $data = [
            'create_reservation' => [
                'action' => 'cancel_reservation',
                'endpoint' => '/reservations/{id}/cancel'
            ],
            'generate_payment_code' => [
                'action' => 'invalidate_payment_code',
                'endpoint' => '/payment-codes/{code}/invalidate'
            ],
            'process_payment' => [
                'action' => 'refund_payment',
                'endpoint' => '/payments/{id}/refund'
            ],
            'create_sale' => [
                'action' => 'cancel_sale',
                'endpoint' => '/sales/{id}/cancel'
            ],
            'update_vehicle_status' => [
                'action' => 'restore_vehicle_status',
                'endpoint' => '/vehicles/{id}/status'
            ]
        ];

        return json_encode($data[$stepName] ?? [], JSON_UNESCAPED_UNICODE);
    }

    private function generateErrorMessage(): string
    {
        $errors = [
            'Timeout na comunicação com o serviço de pagamento',
            'Veículo não disponível para reserva',
            'Falha na validação dos dados do cliente',
            'Erro interno do gateway de pagamento',
            'Limite de reservas atingido para o cliente'
        ];

        return $this->faker->randomElement($errors);
    }

    private function generateStepError(string $stepName): string
    {
        $errors = [
            'create_reservation' => 'Veículo já reservado por outro cliente',
            'generate_payment_code' => 'Falha ao gerar código único de pagamento',
            'process_payment' => 'Pagamento recusado pelo banco emissor',
            'create_sale' => 'Erro ao gerar documentos de venda',
            'update_vehicle_status' => 'Falha ao atualizar status no banco de dados'
        ];

        return $errors[$stepName] ?? 'Erro desconhecido no passo';
    }
}

