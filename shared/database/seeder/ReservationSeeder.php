<?php

declare(strict_types=1);

namespace Shared\Database\Seeder;

use Faker\Factory;
use Faker\Generator;

class ReservationSeeder extends BaseSeeder
{
    private Generator $faker;

    public function __construct()
    {
        parent::__construct($this->getEnv('RESERVATION_DB_NAME', 'reservation_db'));
        $this->faker = Factory::create('pt_BR');
    }

    public function run(): void
    {
        echo "📅 Iniciando seed do Reservation Service...\n";

        // Limpar tabelas
        $this->truncateTable('reservation_history');
        $this->truncateTable('payment_codes');
        $this->truncateTable('reservations');

        // Criar reservas
        $this->createReservations();

        echo "✅ Seed do Reservation Service concluído!\n\n";
    }

    private function createReservations(): void
    {
        $reservations = [];
        $paymentCodes = [];
        $history = [];

        // Buscar clientes e veículos
        $authConnection = $this->getDbConnection($this->getEnv('AUTH_DB_NAME', 'auth_db'));
        $vehicleConnection = $this->getDbConnection($this->getEnv('VEHICLE_DB_NAME', 'vehicle_db'));

        $customersCount = (int) $this->getEnv('SEED_CUSTOMERS_COUNT', 50);
        $reservationsCount = (int) $this->getEnv('SEED_RESERVATIONS_COUNT', 15);

        $customers = $authConnection->query("SELECT id FROM users WHERE role = 'customer' LIMIT {$customersCount}")->fetchAll();
        $vehicles = $vehicleConnection->query("SELECT id, price FROM vehicles WHERE status = 'available' LIMIT 20")->fetchAll();

        // Criar reservas
        for ($i = 0; $i < $reservationsCount; $i++) {
            $customer = $this->faker->randomElement($customers);
            $vehicle = $this->faker->randomElement($vehicles);
            $reservationId = $this->generateUuid();

            $createdAt = $this->faker->dateTimeBetween('-7 days', 'now');
            $expiryHours = (int) $this->getEnv('RESERVATION_EXPIRY_HOURS', 24);
            $expiresAt = (clone $createdAt)->modify("+{$expiryHours} hours");

            $status = $this->faker->randomElement(['active', 'expired', 'cancelled', 'completed']);

            $reservations[] = [
                'id' => $reservationId,
                'customer_id' => $customer['id'],
                'vehicle_id' => $vehicle['id'],
                'reservation_code' => $this->generateReservationCode(),
                'status' => $status,
                'reserved_at' => $createdAt->format('Y-m-d H:i:s'),
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'cancelled_at' => $status === 'cancelled' ? $this->faker->dateTimeBetween($createdAt, 'now')->format('Y-m-d H:i:s') : null,
                'completed_at' => $status === 'completed' ? $this->faker->dateTimeBetween($createdAt, 'now')->format('Y-m-d H:i:s') : null,
                'vehicle_price' => $vehicle['price'],
                'notes' => $this->faker->optional(0.4)->sentence(8),
                'cancellation_reason' => $status === 'cancelled' ? $this->faker->sentence(6) : null,
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'updated_at' => $this->getCurrentTimestamp(),
            ];

            // Código de pagamento para reservas ativas ou completadas
            if (in_array($status, ['active', 'completed'])) {
                $paymentCodes[] = [
                    'id' => $this->generateUuid(),
                    'reservation_id' => $reservationId,
                    'payment_code' => $this->generatePaymentCode(),
                    'amount' => $vehicle['price'],
                    'status' => match ($status) {
                        'completed' => 'used',
                        'active' => 'pending',
                        'expired' => 'expired',
                        'cancelled' => 'cancelled',
                        default => 'pending'
                    },
                    'generated_at' => $createdAt->format('Y-m-d H:i:s'),
                    'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                    'used_at' => $status === 'completed' ? $this->faker->dateTimeBetween($createdAt, 'now')->format('Y-m-d H:i:s') : null,
                    'payment_method' => $this->faker->randomElement(['credit_card', 'debit_card', 'pix', 'bank_transfer']),
                    'instructions' => $this->faker->optional(0.5)->sentence(10),
                    'created_at' => $createdAt->format('Y-m-d H:i:s'),
                    'updated_at' => $this->getCurrentTimestamp(),
                ];
            }

            // Histórico da reserva
            $history[] = [
                'id' => $this->generateUuid(),
                'reservation_id' => $reservationId,
                'previous_status' => null,
                'new_status' => 'active',
                'changed_by' => $customer['id'],
                'change_reason' => 'Reserva criada pelo cliente',
                'changed_at' => $createdAt->format('Y-m-d H:i:s'),
            ];

            // Se não é ativa, adicionar mudança de status
            if ($status !== 'active') {
                $statusChangeDate = $this->faker->dateTimeBetween($createdAt, 'now');
                $history[] = [
                    'id' => $this->generateUuid(),
                    'reservation_id' => $reservationId,
                    'previous_status' => 'active',
                    'new_status' => $status,
                    'changed_by' => $customer['id'],
                    'change_reason' => $this->getChangeReason($status),
                    'changed_at' => $statusChangeDate->format('Y-m-d H:i:s'),
                ];
            }
        }

        $this->insertBatch('reservations', $reservations);
        $this->insertBatch('payment_codes', $paymentCodes);
        $this->insertBatch('reservation_history', $history);

        echo "📊 Criadas: {$reservationsCount} reservas com códigos de pagamento\n";
    }

    private function generateReservationCode(): string
    {
        return 'RES' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    private function generatePaymentCode(): string
    {
        return 'PAY' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    private function getChangeReason(string $status): string
    {
        $reasons = [
            'expired' => 'Reserva expirou automaticamente após ' . $this->getEnv('RESERVATION_EXPIRY_HOURS', 24) . ' horas',
            'cancelled' => 'Cancelada pelo cliente',
            'completed' => 'Reserva convertida em venda',
        ];

        return $reasons[$status] ?? 'Mudança de status';
    }
}
