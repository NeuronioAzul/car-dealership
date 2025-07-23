<?php

namespace Shared\Database\Seeder;

use Faker\Factory;
use Faker\Generator;

class VehicleSeeder extends BaseSeeder
{
    private Generator $faker;
    private array $brands = [
        'Toyota' => ['Corolla', 'Camry', 'RAV4', 'Hilux', 'Prius', 'Yaris', 'Etios'],
        'Honda' => ['Civic', 'Accord', 'CR-V', 'HR-V', 'Fit', 'City', 'WR-V'],
        'Volkswagen' => ['Jetta', 'Passat', 'Tiguan', 'Golf', 'Polo', 'Virtus', 'T-Cross'],
        'Ford' => ['Focus', 'Fusion', 'EcoSport', 'Ka', 'Ranger', 'Territory', 'Bronco'],
        'Chevrolet' => ['Cruze', 'Malibu', 'Equinox', 'Onix', 'Prisma', 'Tracker', 'S10'],
        'Nissan' => ['Sentra', 'Altima', 'X-Trail', 'Kicks', 'March', 'Versa', 'Frontier'],
        'Hyundai' => ['Elantra', 'Sonata', 'Tucson', 'Creta', 'HB20', 'Azera', 'Santa Fe'],
        'Fiat' => ['Cronos', 'Toro', 'Argo', 'Mobi', 'Strada', 'Pulse', 'Fastback']
    ];

    private array $colors = ['Branco', 'Preto', 'Prata', 'Cinza', 'Vermelho', 'Azul',
        'Bege', 'Dourado', 'Verde', 'Marrom', 'Amarelo', 'Roxo', 'Laranja', 'Rosa', 'Turquesa'];

    private array $fuelTypes = ['gasoline', 'ethanol', 'flex', 'diesel', 'hybrid', 'electric'];
    private array $transmissions = ['manual', 'automatic', 'cvt'];

    public function __construct()
    {
        parent::__construct($this->getEnv('VEHICLE_DB_NAME', 'vehicle_db'));
        $this->faker = Factory::create('pt_BR');
    }

    public function run(): void
    {
        echo "🚗 Iniciando seed do Vehicle Service...\n";

        // Limpar tabelas
        $this->truncateTable('vehicle_images');
        $this->truncateTable('vehicles');

        // Criar veículos
        $this->createVehicles();

        echo "✅ Seed do Vehicle Service concluído!\n\n";
    }

    private function createVehicles(): void
    {
        $vehicles = [];
        $vehicleImages = [];
        $vehiclesCount = (int) $this->getEnv('SEED_VEHICLES_COUNT', 100);

        // Caminhos das imagens reais
        $carsDir = 'public-assets/seeder-images/cars';
        $motorsDir = 'public-assets/seeder-images/motors';

        if (is_dir($carsDir)) {
            exec('ls -la ' . $carsDir);
            $carImages = glob($carsDir . '/*.{jpg,png,jpeg}', GLOB_BRACE);
        } else {
            echo "Diretório de carros não encontrado: $carsDir\n";
            $carImages = [];
        }

        if (is_dir($motorsDir)) {
            exec('ls -la ' . $motorsDir);
            $motorImages = glob($motorsDir . '/*.{jpg,png,jpeg}', GLOB_BRACE);
        } else {
            echo "Diretório de motores não encontrado: $motorsDir\n";
            $motorImages = [];
        }

        // Caminhos relativos para uso no campo image_url
        $carImagesRel = array_map(function ($path) {
            return '/seeder-images/cars/' . basename($path);
        }, $carImages);

        $motorImagesRel = array_map(function ($path) {
            return '/seeder-images/motors/' . basename($path);
        }, $motorImages);

        for ($i = 1; $i <= $vehiclesCount; $i++) {
            $brand = $this->faker->randomElement(array_keys($this->brands));
            $model = $this->faker->randomElement($this->brands[$brand]);
            $year = $this->faker->numberBetween(2018, 2024);
            $color = $this->faker->randomElement($this->colors);
            $fuelType = $this->faker->randomElement($this->fuelTypes);
            $transmission = $this->faker->randomElement($this->transmissions);

            $basePrice = $this->faker->numberBetween(45000, 150000);
            $mileage = $year < 2023 ? $this->faker->numberBetween(5000, 80000) : $this->faker->numberBetween(0, 15000);

            $vehicleId = $this->generateUuid();
            $features = [
                'Ar condicionado',
                'Direção hidráulica',
                'Vidros elétricos',
                'Travas elétricas',
                'Airbag duplo',
                'Freios ABS',
                'Rodas de liga leve',
                'Câmera de ré',
                'Sensor de estacionamento',
                'Central multimídia',
                'Bluetooth',
                'Controle de cruzeiro'
            ];

            $vehicles[] = [
                'id' => $vehicleId,
                'brand' => $brand,
                'model' => $model,
                'year' => $year,
                'color' => $color,
                'fuel_type' => $fuelType,
                'transmission_type' => $transmission,
                'mileage' => $mileage,
                'price' => $basePrice,
                'description' => $this->generateVehicleDescription($brand, $model, $year, $color),
                'status' => $this->faker->randomElement(['available', 'reserved', 'sold']),
                'features' => json_encode($this->faker->randomElements($features, $this->faker->boolean(0.7) ? $this->faker->numberBetween(3, 6) : $this->faker->numberBetween(1, 3))),
                'engine_size' => $this->faker->randomElement(['1.0', '1.4', '1.6', '2.0', '2.4', '3.0']),
                'doors' => $this->faker->randomElement([2, 4, 5]),
                'seats' => $this->faker->numberBetween(2, 7),
                'trunk_capacity' => $this->faker->numberBetween(200, 600),
                'purchase_price' => $this->faker->randomFloat(2, 30000, 120000),
                'profit_margin' => $this->faker->randomFloat(2, 5, 30), // Margem de lucro entre 5% e 30%
                'supplier' => $this->faker->company(),
                'chassis_number' => $this->generateChassisNumber(),
                'license_plate' => $this->generateLicensePlate(),
                'renavam' => strtoupper($this->faker->bothify('??######')),
                'created_at' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
                'updated_at' => $this->getCurrentTimestamp(),
                'deleted_at' => $this->faker->boolean(0.1) ? $this->getCurrentTimestamp() : null
            ];

            // Gerar imagens para o veículo
            $imageCount = $this->faker->numberBetween(3, 8);

            // Embaralhar as listas para cada veículo
            $carImagesShuffled = $carImagesRel;
            $motorImagesShuffled = $motorImagesRel;
            shuffle($carImagesShuffled);
            shuffle($motorImagesShuffled);

            $carIdx = 0;
            $motorIdx = 0;

            for ($j = 1; $j <= $imageCount; $j++) {
                if ($j === 1) {
                    // Main image: usar uma imagem de carro
                    $img = $carImagesShuffled[$carIdx % count($carImagesShuffled)];
                    $type = 'main';
                    $carIdx++;
                } elseif ($j <= 4) {
                    // Exterior: usar imagens de carro
                    $img = $carImagesShuffled[$carIdx % count($carImagesShuffled)];
                    $type = 'exterior';
                    $carIdx++;
                } else {
                    // Interior: usar imagens de motor
                    $img = $motorImagesShuffled[$motorIdx % count($motorImagesShuffled)];
                    $type = 'interior';
                    $motorIdx++;
                }

                $vehicleImages[] = [
                    'id' => $this->generateUuid(),
                    'vehicle_id' => $vehicleId,
                    'image_url' => $img,
                    'image_type' => $type,
                    'display_order' => $j,
                    'alt_text' => "{$brand} {$model} {$year} - " . ($j === 1 ? 'Foto Principal' : "Foto {$j}"),
                    'created_at' => $this->getCurrentTimestamp(),
                    'updated_at' => $this->getCurrentTimestamp()
                ];
            }
        }

        $this->insertBatch('vehicles', $vehicles);
        $this->insertBatch('vehicle_images', $vehicleImages);

        echo "📊 Criados: {$vehiclesCount} veículos com imagens\n";
    }

    private function generateVehicleDescription(string $brand, string $model, int $year, string $color): string
    {
        $features = [
            'ar condicionado',
            'direção hidráulica',
            'vidros elétricos',
            'travas elétricas',
            'airbag duplo',
            'freios ABS',
            'som original',
            'rodas de liga leve'
        ];

        $selectedFeatures = $this->faker->randomElements($features, $this->faker->numberBetween(3, 6));

        return "Excelente {$brand} {$model} {$year} na cor {$color}. Veículo em ótimo estado de conservação, " .
            "com " . implode(', ', $selectedFeatures) . ". " .
            "Ideal para quem busca conforto, economia e segurança. " .
            "Documentação em dia, pronto para transferência.";
    }

    private function generateChassisNumber(): string
    {
        $letters = 'ABCDEFGHJKLMNPRSTUVWXYZ';
        $numbers = '0123456789';

        $chassis = '';
        for ($i = 0; $i < 17; $i++) {
            if (in_array($i, [0, 1, 3, 4, 5, 7, 8])) {
                $chassis .= $letters[mt_rand(0, strlen($letters) - 1)];
            } else {
                $chassis .= $numbers[mt_rand(0, strlen($numbers) - 1)];
            }
        }

        return $chassis;
    }

    private function generateLicensePlate(): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';

        // Formato Mercosul: ABC1D23
        return $letters[mt_rand(0, 25)] . $letters[mt_rand(0, 25)] . $letters[mt_rand(0, 25)] .
            $numbers[mt_rand(0, 9)] . $letters[mt_rand(0, 25)] . $numbers[mt_rand(0, 9)] . $numbers[mt_rand(0, 9)];
    }

}
