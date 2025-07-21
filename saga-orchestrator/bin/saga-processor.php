#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Carregar variáveis de ambiente
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            putenv($line);
            list($key, $value) = explode('=', $line, 2);
            $_ENV[$key] = $value;
        }
    }
}

use App\Infrastructure\Database\DatabaseConfig;
use App\Infrastructure\Database\SagaTransactionRepository;
use App\Application\Services\MicroserviceClient;
use App\Application\Sagas\VehiclePurchaseSaga;
use App\Application\Services\SagaProcessorService;
use App\Infrastructure\Messaging\EventPublisher;
use App\Infrastructure\Messaging\EventConsumer;

echo "Iniciando processador de SAGAs...\n";

try {
    // Configurar dependências
    $database = DatabaseConfig::getConnection();
    $transactionRepository = new SagaTransactionRepository($database);
    $microserviceClient = new MicroserviceClient();
    $eventPublisher = new EventPublisher();
    $consumer = new EventConsumer();
    
    $vehiclePurchaseSaga = new VehiclePurchaseSaga($transactionRepository, $microserviceClient, $eventPublisher);
    $sagaProcessor = new SagaProcessorService($transactionRepository, $vehiclePurchaseSaga);
    
    echo "Processando transações pendentes...\n";
    
    while (true) {
        echo "Verificando novamente por transações pendentes...\n";

        $results = $sagaProcessor->processAllPendingTransactions();
        
        if (!empty($results)) {
            echo "Processadas " . count($results) . " transações:\n";
            foreach ($results as $result) {
                echo "- Transação {$result['transaction_id']}: {$result['status']}\n";
                if ($result['status'] === 'error') {
                    echo "  Erro: {$result['error']}\n";
                }
            }
        }
        
        // Aguardar 5 segundos antes da próxima verificação
        sleep(5);
    }
    
} catch (\Exception $e) {
    echo "Erro no processador de SAGAs: " . $e->getMessage() . "\n";
    exit(1);
}

// Exemplo de callback para eventos de pagamento
$consumer->consumePaymentEvents(function ($data) {
    // Processa o evento de pagamento
    print_r($data);
});

// Exemplo de callback para eventos de reserva
$consumer->consumeReservationEvents(function ($data) {
    // Processa o evento de reserva
    print_r($data);
});

// Inicia o loop de consumo
$consumer->startConsuming();

