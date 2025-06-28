<?php

namespace Tests\Scripts;

use PDO;
use PDOException;
use Faker\Factory as Faker;

class DatabaseSeeder
{
    private PDO $connection;
    private $faker;
    
    public function __construct()
    {
        $this->faker = Faker::create('pt_BR');
        $this->connectToDatabase();
    }
    
    private function connectToDatabase(): void
    {
        $host = TEST_DB_HOST;
        $port = TEST_DB_PORT;
        $username = TEST_DB_USERNAME;
        $password = TEST_DB_PASSWORD;
        
        try {
            $this->connection = new PDO(
                "mysql:host=$host;port=$port;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            
            echo "✅ Conectado ao MySQL com sucesso\n";
            
        } catch (PDOException $e) {
            die("❌ Erro ao conectar ao banco: " . $e->getMessage() . "\n");
        }
    }
    
    public function seedAll(): void
    {
        echo "🌱 Iniciando seeding do banco de dados...\n\n";
        
        $this->createAdminUser();
        $this->createSampleCustomers();
        $this->createSampleVehicles();
        
        echo "\n🎉 Seeding concluído com sucesso!\n";
    }
    
    private function createAdminUser(): void
    {
        echo "👤 Criando usuário administrador...\n";
        
        $adminData = [
            'id' => $this->generateUuid(),
            'name' => 'Administrador Sistema',
            'email' => 'admin@concessionaria.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'cpf' => '00000000000',
            'phone' => '11000000000',
            'role' => 'admin',
            'address' => json_encode([
                'street' => 'Rua da Administração, 1',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zip_code' => '00000-000'
            ]),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        try {
            $this->connection->exec("USE auth_db");
            
            $sql = "INSERT INTO users (id, name, email, password, cpf, phone, role, address, created_at, updated_at) 
                    VALUES (:id, :name, :email, :password, :cpf, :phone, :role, :address, :created_at, :updated_at)
                    ON DUPLICATE KEY UPDATE updated_at = :updated_at";
            
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($adminData);
            
            echo "  ✅ Administrador criado: {$adminData['email']}\n";
            
        } catch (PDOException $e) {
            echo "  ❌ Erro ao criar administrador: " . $e->getMessage() . "\n";
        }
    }
    
    private function createSampleCustomers(): void
    {
        echo "👥 Criando clientes de exemplo...\n";
        
        $customers = [
            [
                'name' => 'Maria Silva Santos',
                'email' => 'maria.silva@email.com',
                'cpf' => '11111111111',
                'phone' => '11111111111'
            ],
            [
                'name' => 'José Carlos Oliveira',
                'email' => 'jose.carlos@email.com',
                'cpf' => '22222222222',
                'phone' => '11222222222'
            ],
            [
                'name' => 'Ana Paula Costa',
                'email' => 'ana.paula@email.com',
                'cpf' => '33333333333',
                'phone' => '11333333333'
            ],
            [
                'name' => 'Roberto Ferreira',
                'email' => 'roberto.ferreira@email.com',
                'cpf' => '44444444444',
                'phone' => '11444444444'
            ],
            [
                'name' => 'Fernanda Lima',
                'email' => 'fernanda.lima@email.com',
                'cpf' => '55555555555',
                'phone' => '11555555555'
            ]
        ];
        
        try {
            $this->connection->exec("USE auth_db");
            
            $sql = "INSERT INTO users (id, name, email, password, cpf, phone, role, address, created_at, updated_at) 
                    VALUES (:id, :name, :email, :password, :cpf, :phone, :role, :address, :created_at, :updated_at)
                    ON DUPLICATE KEY UPDATE updated_at = :updated_at";
            
            $stmt = $this->connection->prepare($sql);
            
            foreach ($customers as $customer) {
                $customerData = [
                    'id' => $this->generateUuid(),
                    'name' => $customer['name'],
                    'email' => $customer['email'],
                    'password' => password_hash('senha123', PASSWORD_DEFAULT),
                    'cpf' => $customer['cpf'],
                    'phone' => $customer['phone'],
                    'role' => 'customer',
                    'address' => json_encode([
                        'street' => $this->faker->streetAddress,
                        'city' => $this->faker->city,
                        'state' => 'SP',
                        'zip_code' => $this->faker->postcode
                    ]),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $stmt->execute($customerData);
                echo "  ✅ Cliente criado: {$customer['name']}\n";
            }
            
        } catch (PDOException $e) {
            echo "  ❌ Erro ao criar clientes: " . $e->getMessage() . "\n";
        }
    }
    
    private function createSampleVehicles(): void
    {
        echo "🚗 Criando veículos de exemplo...\n";
        
        $brandsModels = [
            'Toyota' => ['Corolla', 'Camry', 'RAV4', 'Hilux', 'Prius'],
            'Honda' => ['Civic', 'Accord', 'CR-V', 'HR-V', 'Fit'],
            'Volkswagen' => ['Jetta', 'Passat', 'Tiguan', 'Golf', 'Polo'],
            'Ford' => ['Focus', 'Fusion', 'EcoSport', 'Ranger', 'Ka'],
            'Chevrolet' => ['Cruze', 'Malibu', 'Equinox', 'S10', 'Onix'],
            'Nissan' => ['Sentra', 'Altima', 'X-Trail', 'Frontier', 'March'],
            'Hyundai' => ['Elantra', 'Sonata', 'Tucson', 'Creta', 'HB20'],
            'BMW' => ['320i', 'X3', 'X5', 'Serie 1', 'Serie 5'],
            'Mercedes-Benz' => ['C-Class', 'E-Class', 'GLA', 'GLC', 'A-Class'],
            'Audi' => ['A3', 'A4', 'Q3', 'Q5', 'A6']
        ];
        
        $colors = ['Branco', 'Preto', 'Prata', 'Azul', 'Vermelho', 'Cinza', 'Bege', 'Verde'];
        $fuelTypes = ['Gasolina', 'Etanol', 'Flex', 'Diesel', 'Híbrido', 'Elétrico'];
        $transmissions = ['Manual', 'Automático', 'CVT'];
        
        try {
            $this->connection->exec("USE vehicle_db");
            
            $sql = "INSERT INTO vehicles (id, brand, model, manufacturing_year, model_year, color, mileage, 
                    fuel_type, transmission_type, price, status, description, features, created_at, updated_at) 
                    VALUES (:id, :brand, :model, :manufacturing_year, :model_year, :color, :mileage, 
                    :fuel_type, :transmission_type, :price, :status, :description, :features, :created_at, :updated_at)";
            
            $stmt = $this->connection->prepare($sql);
            
            $vehicleCount = 0;
            
            foreach ($brandsModels as $brand => $models) {
                foreach ($models as $model) {
                    // Criar 2-3 veículos por modelo
                    $vehiclesPerModel = rand(2, 3);
                    
                    for ($i = 0; $i < $vehiclesPerModel; $i++) {
                        $year = rand(2018, 2024);
                        $mileage = $year < 2024 ? rand(0, 80000) : rand(0, 20000);
                        
                        // Preços baseados na marca
                        if (in_array($brand, ['BMW', 'Mercedes-Benz', 'Audi'])) {
                            $basePrice = rand(80000, 200000);
                        } elseif (in_array($brand, ['Toyota', 'Honda', 'Volkswagen'])) {
                            $basePrice = rand(50000, 120000);
                        } else {
                            $basePrice = rand(30000, 90000);
                        }
                        
                        // Ajustar preço por ano
                        $priceFactor = 1.0 - (2024 - $year) * 0.1;
                        $finalPrice = (int)($basePrice * max($priceFactor, 0.5));
                        
                        $features = [
                            'Ar condicionado',
                            'Direção hidráulica',
                            'Vidros elétricos',
                            'Trava elétrica',
                            'Som automotivo'
                        ];
                        
                        // Adicionar features premium para marcas de luxo
                        if (in_array($brand, ['BMW', 'Mercedes-Benz', 'Audi'])) {
                            $features = array_merge($features, [
                                'Couro',
                                'GPS',
                                'Câmera de ré',
                                'Sensor de estacionamento',
                                'Teto solar'
                            ]);
                        }
                        
                        $vehicleData = [
                            'id' => $this->generateUuid(),
                            'brand' => $brand,
                            'model' => $model,
                            'manufacturing_year' => $year,
                            'model_year' => $year,
                            'color' => $colors[array_rand($colors)],
                            'mileage' => $mileage,
                            'fuel_type' => $fuelTypes[array_rand($fuelTypes)],
                            'transmission_type' => $transmissions[array_rand($transmissions)],
                            'price' => $finalPrice,
                            'status' => 'available',
                            'description' => "$brand $model $year em excelente estado de conservação.",
                            'features' => json_encode($features),
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        
                        $stmt->execute($vehicleData);
                        $vehicleCount++;
                        
                        if ($vehicleCount % 10 == 0) {
                            echo "  📝 $vehicleCount veículos criados...\n";
                        }
                    }
                }
            }
            
            echo "  ✅ Total de $vehicleCount veículos criados\n";
            
        } catch (PDOException $e) {
            echo "  ❌ Erro ao criar veículos: " . $e->getMessage() . "\n";
        }
    }
    
    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

// Executar seeding se chamado diretamente
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    require_once __DIR__ . '/../bootstrap.php';
    
    $seeder = new DatabaseSeeder();
    $seeder->seedAll();
}

