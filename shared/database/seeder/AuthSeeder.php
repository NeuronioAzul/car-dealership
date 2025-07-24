<?php

declare(strict_types=1);

namespace Shared\Database\Seeder;

use Faker\Factory;
use Faker\Generator;

class AuthSeeder extends BaseSeeder
{
    private Generator $faker;

    public function __construct()
    {
        parent::__construct($this->getEnv('AUTH_DB_NAME', 'auth_db'));
        $this->faker = Factory::create('pt_BR');
    }

    public function run(): void
    {
        echo "\n🔐 Iniciando seed do Auth Service...\n";

        // Limpar tabelas
        $this->truncateTable('refresh_tokens');
        $this->truncateTable('users');

        // Criar usuários
        $this->createUsers();

        echo "✅ Seed do Auth Service concluído!\n\n";
    }

    private function createUsers(): void
    {
        $users = [];

        // Admin padrão
        $users[] = [
            'id' => $this->generateUuid(),
            'name' => 'Administrador Sistema',
            'email' => $this->getEnv('ADMIN_EMAIL', 'admin@concessionaria.com'),
            'password' => $this->hashPassword($this->getEnv('ADMIN_PASSWORD', 'admin123')),
            'phone' => '11999999999',
            'birth_date' => '1980-01-01',
            'street' => 'Rua da Administração',
            'number' => '100',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01000-000',
            'role' => 'admin',
            'accept_terms' => 1,
            'accept_privacy' => 1,
            'accept_communications' => 1,
            'created_at' => $this->getCurrentTimestamp(),
            'updated_at' => $this->getCurrentTimestamp(),
        ];

        // Clientes
        $customersCount = (int) $this->getEnv('SEED_CUSTOMERS_COUNT', 50);
        for ($i = 1; $i <= $customersCount; $i++) {
            $users[] = [
                'id' => $this->generateUuid(),
                'name' => $this->faker->name(),
                'email' => $this->faker->unique()->email(),
                'password' => $this->hashPassword($this->getEnv('CUSTOMER_PASSWORD', 'cliente123')),
                'phone' => $this->faker->cellphone(false),
                'birth_date' => $this->faker->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
                'street' => $this->faker->streetName(),
                'number' => $this->faker->buildingNumber(),
                'neighborhood' => $this->faker->citySuffix(),
                'city' => $this->faker->city(),
                'state' => $this->faker->stateAbbr(),
                'zip_code' => $this->faker->postcode(),
                'role' => 'customer',
                'accept_terms' => 1,
                'accept_privacy' => 1,
                'accept_communications' => 1,
                'created_at' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'),
                'updated_at' => $this->getCurrentTimestamp(),
            ];
        }

        $this->insertBatch('users', $users);

        echo "📊 Criados: 1 admin + {$customersCount} clientes\n";
    }
}
