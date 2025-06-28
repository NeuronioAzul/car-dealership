<?php

namespace Tests\Unit\Saga;

use PHPUnit\Framework\TestCase;

// Incluir classes do SAGA
require_once __DIR__ . '/../../../saga-orchestrator/src/Domain/Entities/SagaTransaction.php';

use App\Domain\Entities\SagaTransaction;

class SagaTransactionTest extends TestCase
{
    private SagaTransaction $sagaTransaction;

    protected function setUp(): void
    {
        $this->sagaTransaction = new SagaTransaction(
            'customer-123',
            'vehicle-456',
            'purchase_vehicle'
        );
    }

    public function testSagaTransactionCreation(): void
    {
        $this->assertInstanceOf(SagaTransaction::class, $this->sagaTransaction);
        $this->assertEquals('customer-123', $this->sagaTransaction->getCustomerId());
        $this->assertEquals('vehicle-456', $this->sagaTransaction->getVehicleId());
        $this->assertEquals('purchase_vehicle', $this->sagaTransaction->getType());
        $this->assertEquals('started', $this->sagaTransaction->getStatus());
        $this->assertIsArray($this->sagaTransaction->getSteps());
        $this->assertEmpty($this->sagaTransaction->getCompletedSteps());
    }

    public function testSagaStepExecution(): void
    {
        // Iniciar progresso
        $this->sagaTransaction->startProgress();
        $this->assertEquals('in_progress', $this->sagaTransaction->getStatus());
        
        // Completar primeiro passo
        $stepData = ['reservation_id' => 'res-123'];
        $this->sagaTransaction->completeStep('create_reservation', $stepData);
        
        $completedSteps = $this->sagaTransaction->getCompletedSteps();
        $this->assertContains('create_reservation', $completedSteps);
        $this->assertEquals($stepData, $this->sagaTransaction->getFromContext('create_reservation_data'));
    }

    public function testSagaStepFailure(): void
    {
        $this->sagaTransaction->startProgress();
        
        $errorMessage = 'Veículo não disponível';
        $this->sagaTransaction->failStep('create_reservation', $errorMessage);
        
        $this->assertEquals('failed', $this->sagaTransaction->getStatus());
        $this->assertEquals($errorMessage, $this->sagaTransaction->getFailureReason());
        $this->assertEquals('create_reservation', $this->sagaTransaction->getFailedStep());
    }

    public function testSagaContextManagement(): void
    {
        $testData = ['key' => 'value', 'number' => 123];
        
        $this->sagaTransaction->addToContext('test_data', $testData);
        
        $retrievedData = $this->sagaTransaction->getFromContext('test_data');
        $this->assertEquals($testData, $retrievedData);
        
        // Testar contexto inexistente
        $this->assertNull($this->sagaTransaction->getFromContext('nonexistent'));
    }

    public function testSagaCompensation(): void
    {
        // Completar alguns passos
        $this->sagaTransaction->startProgress();
        $this->sagaTransaction->completeStep('create_reservation', ['res_id' => '123']);
        $this->sagaTransaction->completeStep('generate_payment_code', ['code' => 'PAY123']);
        
        // Falhar em um passo
        $this->sagaTransaction->failStep('process_payment', 'Pagamento recusado');
        
        // Iniciar compensação
        $this->sagaTransaction->startCompensation();
        $this->assertEquals('compensating', $this->sagaTransaction->getStatus());
        $this->assertTrue($this->sagaTransaction->isCompensating());
        
        // Obter próximo passo de compensação
        $nextCompensationStep = $this->sagaTransaction->getNextCompensationStep();
        $this->assertNotNull($nextCompensationStep);
        
        // Completar compensação
        $this->sagaTransaction->completeStep($nextCompensationStep . '_compensated');
        
        // Finalizar compensação quando todos os passos forem compensados
        if (!$this->sagaTransaction->getNextCompensationStep()) {
            $this->sagaTransaction->completeCompensation();
            $this->assertEquals('compensated', $this->sagaTransaction->getStatus());
        }
    }

    public function testSagaCompletion(): void
    {
        $this->sagaTransaction->startProgress();
        
        // Completar todos os passos
        $steps = [
            'create_reservation' => ['reservation_id' => 'res-123'],
            'generate_payment_code' => ['payment_code' => 'PAY123'],
            'process_payment' => ['payment_id' => 'pay-456'],
            'create_sale' => ['sale_id' => 'sale-789'],
            'update_vehicle_status' => ['status' => 'sold']
        ];
        
        foreach ($steps as $step => $data) {
            $this->sagaTransaction->completeStep($step, $data);
        }
        
        // Verificar se todos os passos foram completados
        $completedSteps = $this->sagaTransaction->getCompletedSteps();
        $this->assertCount(count($steps), $completedSteps);
        
        // Marcar como completada
        $this->sagaTransaction->complete();
        $this->assertEquals('completed', $this->sagaTransaction->getStatus());
        $this->assertTrue($this->sagaTransaction->isCompleted());
    }

    public function testSagaCurrentStep(): void
    {
        $this->sagaTransaction->startProgress();
        
        // Primeiro passo deve ser create_reservation
        $currentStep = $this->sagaTransaction->getCurrentStep();
        $this->assertEquals('create_reservation', $currentStep);
        
        // Completar primeiro passo
        $this->sagaTransaction->completeStep('create_reservation', ['res_id' => '123']);
        
        // Próximo passo deve ser generate_payment_code
        $currentStep = $this->sagaTransaction->getCurrentStep();
        $this->assertEquals('generate_payment_code', $currentStep);
    }

    public function testSagaToArray(): void
    {
        $this->sagaTransaction->addToContext('test_data', ['key' => 'value']);
        $this->sagaTransaction->startProgress();
        
        $sagaArray = $this->sagaTransaction->toArray();
        
        $this->assertIsArray($sagaArray);
        $this->assertArrayHasKey('id', $sagaArray);
        $this->assertArrayHasKey('customer_id', $sagaArray);
        $this->assertArrayHasKey('vehicle_id', $sagaArray);
        $this->assertArrayHasKey('type', $sagaArray);
        $this->assertArrayHasKey('status', $sagaArray);
        $this->assertArrayHasKey('steps', $sagaArray);
        $this->assertArrayHasKey('completed_steps', $sagaArray);
        $this->assertArrayHasKey('context', $sagaArray);
        $this->assertArrayHasKey('created_at', $sagaArray);
    }

    public function testSagaStatusChecks(): void
    {
        // Estado inicial
        $this->assertTrue($this->sagaTransaction->isStarted());
        $this->assertFalse($this->sagaTransaction->isInProgress());
        $this->assertFalse($this->sagaTransaction->isCompleted());
        $this->assertFalse($this->sagaTransaction->isFailed());
        $this->assertFalse($this->sagaTransaction->isCompensating());
        
        // Em progresso
        $this->sagaTransaction->startProgress();
        $this->assertFalse($this->sagaTransaction->isStarted());
        $this->assertTrue($this->sagaTransaction->isInProgress());
        
        // Falha
        $this->sagaTransaction->failStep('test_step', 'Erro de teste');
        $this->assertTrue($this->sagaTransaction->isFailed());
        $this->assertFalse($this->sagaTransaction->isInProgress());
    }

    public function testSagaIdempotency(): void
    {
        // Tentar completar o mesmo passo duas vezes
        $this->sagaTransaction->startProgress();
        
        $stepData1 = ['data' => 'first'];
        $stepData2 = ['data' => 'second'];
        
        $this->sagaTransaction->completeStep('create_reservation', $stepData1);
        $this->sagaTransaction->completeStep('create_reservation', $stepData2);
        
        // Deve manter apenas o primeiro resultado
        $retrievedData = $this->sagaTransaction->getFromContext('create_reservation_data');
        $this->assertEquals($stepData1, $retrievedData);
        
        // Deve aparecer apenas uma vez na lista de passos completados
        $completedSteps = $this->sagaTransaction->getCompletedSteps();
        $this->assertEquals(1, array_count_values($completedSteps)['create_reservation']);
    }
}

