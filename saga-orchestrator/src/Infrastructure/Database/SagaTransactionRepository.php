<?php

namespace App\Infrastructure\Database;

use App\Domain\Entities\SagaTransaction;
use App\Domain\Repositories\SagaTransactionRepositoryInterface;
use PDO;
use DateTime;

class SagaTransactionRepository implements SagaTransactionRepositoryInterface
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(SagaTransaction $transaction): bool
    {
        $sql = "INSERT INTO saga_transactions (
                id,
                transaction_type,
                status,
                current_step,
                context,
                customer_id,
                customer_name,
                customer_email,
                vehicle_id,
                vehicle_info,
                started_at,
                completed_at,
                failed_at,
                error_message,
                error_details,
                retry_count,
                max_retries,
                next_retry_at,
                created_at,
                updated_at,
                deleted_at
            ) VALUES (
                :id, :transaction_type, :status, :current_step, :context, :customer_id,
                :customer_name, :customer_email, :vehicle_id, :vehicle_info, :started_at,
                :completed_at, :failed_at, :error_message, :error_details, :retry_count,
                :max_retries, :next_retry_at, :created_at, :updated_at, :deleted_at
            )";

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            'id' => $transaction->getId(),
            'transaction_type' => $transaction->getType(),
            'status' => $transaction->getStatus(),
            'current_step' => $transaction->getCurrentStep(),
            'context' => json_encode($transaction->getContext()),
            'customer_id' => $transaction->getCustomerId(),
            'customer_name' => $transaction->getCustomerName(),
            'customer_email' => $transaction->getCustomerEmail(),
            'vehicle_id' => $transaction->getVehicleId(),
            'vehicle_info' => json_encode($transaction->getVehicleInfo()),
            'started_at' => $transaction->getCreatedAt()->format('Y-m-d H:i:s'),
            'completed_at' => $transaction->getCompletedAt()?->format('Y-m-d H:i:s'),
            'failed_at' => $transaction->getFailedAt()?->format('Y-m-d H:i:s'),
            'error_message' => $transaction->getErrorMessage(),
            'error_details' => json_encode($transaction->getErrorDetails()),
            'retry_count' => $transaction->getRetryCount(),
            'max_retries' => $transaction->getMaxRetries(),
            'next_retry_at' => $transaction->getNextRetryAt()?->format('Y-m-d H:i:s'),
            'created_at' => $transaction->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $transaction->getUpdatedAt()->format('Y-m-d H:i:s'),
            'completed_at' => $transaction->getCompletedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    public function findById(string $id): ?SagaTransaction
    {
        $sql = "SELECT * FROM saga_transactions WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch();

        return $data ? $this->mapToSagaTransaction($data) : null;
    }

    public function findByCustomerId(string $customerId): array
    {
        $sql = "SELECT * FROM saga_transactions WHERE customer_id = :customer_id ORDER BY created_at DESC";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['customer_id' => $customerId]);

        $transactions = [];
        while ($data = $stmt->fetch()) {
            $transactions[] = $this->mapToSagaTransaction($data);
        }

        return $transactions;
    }

    public function findByStatus(string $status): array
    {
        $sql = "SELECT * FROM saga_transactions WHERE status = :status ORDER BY created_at ASC";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['status' => $status]);

        $transactions = [];
        while ($data = $stmt->fetch()) {
            $transactions[] = $this->mapToSagaTransaction($data);
        }

        return $transactions;
    }

    public function findPendingTransactions(): array
    {
        $sql = "SELECT * FROM saga_transactions WHERE status IN ('started', 'in_progress', 'compensating') ORDER BY created_at ASC";
        $stmt = $this->connection->query($sql);

        $transactions = [];
        while ($data = $stmt->fetch()) {
            $transactions[] = $this->mapToSagaTransaction($data);
        }

        return $transactions;
    }

    public function update(SagaTransaction $transaction): bool
    {
        $sql = "
            UPDATE saga_transactions SET
                customer_id = :customer_id,
                vehicle_id = :vehicle_id,
                transaction_type = :transaction_type,
                status = :status,
                steps = :steps,
                completed_steps = :completed_steps,
                compensation_steps = :compensation_steps,
                current_step = :current_step,
                failure_reason = :failure_reason,
                context = :context,
                updated_at = :updated_at,
                completed_at = :completed_at
            WHERE id = :id
        ";

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            'id' => $transaction->getId(),
            'customer_id' => $transaction->getCustomerId(),
            'vehicle_id' => $transaction->getVehicleId(),
            'type' => $transaction->getType(),
            'status' => $transaction->getStatus(),
            'steps' => json_encode($transaction->getSteps()),
            'completed_steps' => json_encode($transaction->getCompletedSteps()),
            'compensation_steps' => json_encode($transaction->getCompensationSteps()),
            'current_step' => $transaction->getCurrentStep(),
            'failure_reason' => $transaction->getFailureReason(),
            'context' => json_encode($transaction->getContext()),
            'updated_at' => $transaction->getUpdatedAt()->format('Y-m-d H:i:s'),
            'completed_at' => $transaction->getCompletedAt()?->format('Y-m-d H:i:s')
        ]);
    }

    public function delete(string $id): bool
    {
        $sql = "DELETE FROM saga_transactions WHERE id = :id";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    private function mapToSagaTransaction(array $data): SagaTransaction
    {
        $transaction = new SagaTransaction(
            $data['customer_id'],
            $data['vehicle_id'],
            $data['type']
        );

        // Usar reflection para definir propriedades privadas
        $reflection = new \ReflectionClass($transaction);

        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($transaction, $data['id']);

        $statusProperty = $reflection->getProperty('status');
        $statusProperty->setAccessible(true);
        $statusProperty->setValue($transaction, $data['status']);

        $stepsProperty = $reflection->getProperty('steps');
        $stepsProperty->setAccessible(true);
        $stepsProperty->setValue($transaction, json_decode($data['steps'], true));

        $completedStepsProperty = $reflection->getProperty('completedSteps');
        $completedStepsProperty->setAccessible(true);
        $completedStepsProperty->setValue($transaction, json_decode($data['completed_steps'], true));

        $compensationStepsProperty = $reflection->getProperty('compensationSteps');
        $compensationStepsProperty->setAccessible(true);
        $compensationStepsProperty->setValue($transaction, json_decode($data['compensation_steps'], true));

        $currentStepProperty = $reflection->getProperty('currentStep');
        $currentStepProperty->setAccessible(true);
        $currentStepProperty->setValue($transaction, $data['current_step']);

        $failureReasonProperty = $reflection->getProperty('failureReason');
        $failureReasonProperty->setAccessible(true);
        $failureReasonProperty->setValue($transaction, $data['failure_reason']);

        $contextProperty = $reflection->getProperty('context');
        $contextProperty->setAccessible(true);
        $contextProperty->setValue($transaction, json_decode($data['context'], true));

        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($transaction, new DateTime($data['created_at']));

        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($transaction, new DateTime($data['updated_at']));

        if ($data['completed_at']) {
            $completedAtProperty = $reflection->getProperty('completedAt');
            $completedAtProperty->setAccessible(true);
            $completedAtProperty->setValue($transaction, new DateTime($data['completed_at']));
        }

        return $transaction;
    }
}
