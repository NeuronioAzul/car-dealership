<?php

namespace Shared\Database\Seeder;

use Faker\Factory;
use Faker\Generator;

class SalesSeeder extends BaseSeeder
{
    private Generator $faker;
    
    public function __construct()
    {
        parent::__construct($this->getEnv('SALES_DB_NAME', 'sales_db'));
        $this->faker = Factory::create('pt_BR');
    }
    
    public function run(): void
    {
        echo "📄 Iniciando seed do Sales Service...\n";
        
        // Limpar tabelas
        $this->truncateTable('sale_items');
        $this->truncateTable('sale_documents');
        $this->truncateTable('sales');
        
        // Criar vendas
        $this->createSales();
        
        echo "✅ Seed do Sales Service concluído!\n\n";
    }
    
    private function createSales(): void
    {
        $sales = [];
        $documents = [];
        $items = [];
        
        // Buscar dados necessários
        $paymentConnection = $this->getDbConnection($this->getEnv('PAYMENT_DB_NAME', 'payment_db'));
        $authConnection = $this->getDbConnection($this->getEnv('AUTH_DB_NAME', 'auth_db'));
        $vehicleConnection = $this->getDbConnection($this->getEnv('VEHICLE_DB_NAME', 'vehicle_db'));
        $reservationConnection = $this->getDbConnection($this->getEnv('RESERVATION_DB_NAME', 'reservation_db'));
        
        $salesCount = (int) $this->getEnv('SEED_SALES_COUNT', 15);
        
        $completedPayments = $paymentConnection->query("
            SELECT * FROM payments WHERE status = 'completed' LIMIT {$salesCount}
        ")->fetchAll();
                
        foreach ($completedPayments as $payment) {
            $saleId = $this->generateUuid();
            
            // Buscar informações do veículo via reservation
            $reservation = $reservationConnection->query("
                SELECT 
                    vehicle_id, 
                    r.id 
                FROM payment_codes pc 
                JOIN reservations r 
                    ON pc.reservation_id = r.id 
                WHERE pc.payment_code = '{$payment['payment_code']}' 
                LIMIT 1
            ")->fetch();
            
            if (!$reservation) continue;
            
            $vehicle = $vehicleConnection->query("
                SELECT * FROM vehicles WHERE id = '{$reservation['vehicle_id']}' LIMIT 1
            ")->fetch();
            
            if (!$vehicle) continue;
            
            $saleDate = null;
            if (!empty($payment['processed_at'])) {
                $saleDate = $this->faker->dateTimeBetween($payment['processed_at'], 'now');
            } else {
                $saleDate = $this->faker->dateTimeBetween('-30 days', 'now');
            }

            // Calcular valores
            $vehiclePrice = $vehicle['price'];
            $discount = $this->faker->numberBetween(0, $vehiclePrice * 0.1); // Até 10% desconto
            $subtotal = $vehiclePrice - $discount;
            $taxes = $subtotal * 0.05; // 5% de impostos
            $totalAmount = $subtotal + $taxes;
            
            $status = $this->faker->randomElement(['pending', 'completed', 'cancelled']);
            
            $deliveryDate = $this->faker->optional(0.8)->dateTimeBetween($saleDate, '+30 days');
            $contract_signed_at = $this->faker->optional(0.8)->dateTimeBetween($saleDate, 'now');

            $sales[] = [
                'id' => $saleId,
                'sale_number' => $this->generateSaleNumber(),

                'customer_id' => $payment['customer_id'],
                'vehicle_id' => $vehicle['id'],
                'reservation_id' => $reservation['id'],
                'payment_id' => $payment['id'],

                'sale_price' => $vehiclePrice,
                'discount_amount' => $discount,
                'tax_amount' => $taxes,
                'total_amount' => $totalAmount,

                'status' => $status,
                'sale_date' => $saleDate ? $saleDate->format('Y-m-d') : date('Y-m-d'),
                'delivery_date' => $deliveryDate ? $deliveryDate->format('Y-m-d') : null,
                'contract_signed_at' => $contract_signed_at ? $contract_signed_at->format('Y-m-d H:i:s') : null,

                'notes' => $this->faker->optional(0.4)->sentence(10),
                'terms_conditions' => $this->faker->text(200),

                'created_at' => $saleDate ? $saleDate->format('Y-m-d H:i:s') : $this->getCurrentTimestamp(),
                'updated_at' => $this->getCurrentTimestamp()
            ];
            
            // Documentos da venda
            $documents[] = [
                'id' => $this->generateUuid(),
                'sale_id' => $saleId,
                'document_type' => 'contract',
                'document_name' => 'Contrato de Compra e Venda',
                'file_path' => "/documents/sales/{$saleId}/contract.pdf",
                'file_size' => $this->faker->numberBetween(50000, 200000),
                'mime_type' => 'application/pdf',
                'generated_at' => $saleDate ? $saleDate->format('Y-m-d H:i:s') : $this->getCurrentTimestamp(),
                'updated_at' => $this->getCurrentTimestamp(),
                'deleted_at' => null
            ];
            
            $documents[] = [
                'id' => $this->generateUuid(),
                'sale_id' => $saleId,
                'document_type' => 'invoice',
                'document_name' => 'Nota Fiscal de Venda',
                'file_path' => "/documents/sales/{$saleId}/invoice.pdf",
                'file_size' => $this->faker->numberBetween(30000, 100000),
                'mime_type' => 'application/pdf',
                'generated_at' => $saleDate ? $saleDate->format('Y-m-d H:i:s') : $this->getCurrentTimestamp(),
                'updated_at' => $this->getCurrentTimestamp(),
                'deleted_at' => null
            ];
            
            // Itens adicionais da venda
            $additionalItems = $this->faker->numberBetween(0, 3);
            for ($i = 0; $i < $additionalItems; $i++) {
                $itemPrice = $this->faker->numberBetween(500, 5000);
                $items[] = [
                    'id' => $this->generateUuid(),
                    'sale_id' => $saleId,
                    'item_type' => $this->faker->randomElement(['service', 'accessory', 'insurance', 'warranty', 'fee', 'other']),
                    'item_name' => $this->generateItemName(),
                    'item_description' => $this->faker->sentence(8),
                    'quantity' => 1,
                    'unit_price' => $itemPrice,
                    'total_price' => $itemPrice,
                    'created_at' => $saleDate ? $saleDate->format('Y-m-d H:i:s') : $this->getCurrentTimestamp(),
                    'updated_at' => $this->getCurrentTimestamp(),
                    'deleted_at' => null
                ];
            }
        }
        
        $this->insertBatch('sales', $sales);
        $this->insertBatch('sale_documents', $documents);
        $this->insertBatch('sale_items', $items);
        
        echo "📊 Criadas: " . count($sales) . " vendas com documentos e itens\n";
    }
    
    private function generateSaleNumber(): string
    {
        return 'VND' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }
    
    private function generateItemName(): string
    {
        $items = [
            'Seguro Auto Completo',
            'Garantia Estendida 2 Anos',
            'Kit Multimídia Premium',
            'Película Protetora',
            'Alarme com Bloqueador',
            'Rodas de Liga Leve',
            'Engate para Reboque',
            'Revisão Programada',
            'Proteção de Carter',
            'Kit GNV Completo'
        ];
        
        return $this->faker->randomElement($items);
    }
}

