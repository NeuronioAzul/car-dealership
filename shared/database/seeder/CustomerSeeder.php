<?php

namespace Shared\Database\Seeder;

use Faker\Factory;
use Faker\Generator;

class CustomerSeeder extends BaseSeeder
{
    private Generator $faker;
    
    public function __construct()
    {
        parent::__construct($this->getEnv('CUSTOMER_DB_NAME', 'customer_db'));
        $this->faker = Factory::create('pt_BR');
    }
    
    public function run(): void
    {
        echo "\n👥 Iniciando seed do Customer Service...\n";
        
        // Limpar tabelas
        $this->truncateTable('customer_profiles');
        
        // Criar perfis de clientes
        $this->createCustomerProfiles();
        
        echo "✅ Seed do Customer Service concluído!\n\n";
    }
    
    private function createCustomerProfiles(): void
    {
        $profiles = [];
        
        // Buscar usuários clientes do auth_db
        $authConnection = $this->getDbConnection($this->getEnv('AUTH_DB_NAME', 'auth_db'));
        $stmt = $authConnection->query("SELECT id, name, email, phone FROM users WHERE role = 'customer'");
        $customers = $stmt->fetchAll();
        
        foreach ($customers as $customer) {
            
            // Perfil do cliente
            $profiles[] = [
                'id' => $customer['id'],
                'full_name' => $customer['name'],
                'birth_date' => $this->faker->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
                'cpf' => $this->faker->cpf(false),
                'rg' => $this->faker->optional(0.5)->regexify('[0-9]{2}\.[0-9]{3}\.[0-9]{3}-[0-9]{2}'),
                'gender' => $this->faker->randomElement(['M', 'F', 'Other']),
                'marital_status' => $this->faker->randomElement(['Single', 'Married', 'Divorced', 'Widowed', 'Common Law']),
                
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'mobile' => $this->faker->optional(0.5)->phoneNumber(),
                'whatsapp' => $this->faker->optional(0.5)->phoneNumber(),

                'street' => $this->faker->streetAddress(),
                'number' => $this->faker->buildingNumber(),
                'complement' => $this->faker->optional(0.3)->secondaryAddress(),
                'neighborhood' => $this->faker->streetName(),
                'city' => $this->faker->city(),
                'state' => $this->faker->stateAbbr(),
                'zip_code' => $this->faker->postcode(),

                'occupation' => $this->faker->jobTitle(),
                'company' => $this->faker->optional(0.5)->company(),
                'monthly_income' => $this->faker->numberBetween(2000, 25000),

                'preferred_contact' => $this->faker->randomElement(['email', 'phone', 'whatsapp']),
                'newsletter_subscription' => $this->faker->boolean(70) ? 1 : 0,
                'sms_notifications' => $this->faker->boolean(50) ? 1 : 0,

                'total_purchases' => $this->faker->numberBetween(0, 100000),
                'total_spent' => $this->faker->randomFloat(2, 0, 100000),
                'last_purchase_date' => ($this->faker->optional(0.5, null)) ? $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d') : null,

                'customer_score' => $this->faker->numberBetween(300, 850),
                'customer_tier' => $this->faker->randomElement(['bronze', 'silver', 'gold', 'platinum']),

                'created_at' => $this->getCurrentTimestamp(),
                'updated_at' => $this->getCurrentTimestamp(),
                'deleted_at' => ($this->faker->boolean(0.5)) ? $this->getCurrentTimestamp() : null
            ];
        }
        
        $this->insertBatch('customer_profiles', $profiles);

        echo "📊 Criados: " . count($profiles) . " perfis de clientes\n";
    }

}

