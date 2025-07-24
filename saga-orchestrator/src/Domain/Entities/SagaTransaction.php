<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use DateTime;
use Ramsey\Uuid\Uuid;

class SagaTransaction
{
    private string $id;
    private string $customerId;
    private string $vehicleId;
    private string $type; // purchase_vehicle
    private string $status; // started, in_progress, completed, failed, compensating, compensated
    private array $steps;
    private array $completedSteps;
    private array $compensationSteps;
    private ?string $currentStep;
    private ?string $failureReason;
    private array $context;
    private DateTime $createdAt;
    private DateTime $updatedAt;
    private ?DateTime $completedAt;

    public function __construct(
        string $customerId,
        string $vehicleId,
        string $type = 'purchase_vehicle'
    ) {
        $this->id = Uuid::uuid6()->toString();
        $this->customerId = $customerId;
        $this->vehicleId = $vehicleId;
        $this->type = $type;
        $this->status = 'started';
        $this->steps = $this->getStepsForType($type);
        $this->completedSteps = [];
        $this->compensationSteps = [];
        $this->currentStep = null;
        $this->failureReason = null;
        $this->context = [];
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->completedAt = null;
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function getVehicleId(): string
    {
        return $this->vehicleId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getSteps(): array
    {
        return $this->steps;
    }

    public function getCompletedSteps(): array
    {
        return $this->completedSteps;
    }

    public function getCompensationSteps(): array
    {
        return $this->compensationSteps;
    }

    public function getCurrentStep(): ?string
    {
        return $this->currentStep;
    }

    public function getFailureReason(): ?string
    {
        return $this->failureReason;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getCompletedAt(): ?DateTime
    {
        return $this->completedAt;
    }

    // Setters
    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->updatedAt = new DateTime();

        if (in_array($status, ['completed', 'failed', 'compensated'])) {
            $this->completedAt = new DateTime();
        }
    }

    public function setCurrentStep(?string $step): void
    {
        $this->currentStep = $step;
        $this->updatedAt = new DateTime();
    }

    public function setFailureReason(?string $reason): void
    {
        $this->failureReason = $reason;
        $this->updatedAt = new DateTime();
    }

    public function addToContext(string $key, $value): void
    {
        $this->context[$key] = $value;
        $this->updatedAt = new DateTime();
    }

    public function getFromContext(string $key, $default = null)
    {
        return $this->context[$key] ?? $default;
    }

    // Business methods
    public function startProgress(): void
    {
        $this->setStatus('in_progress');
        $this->setCurrentStep($this->getNextStep());
    }

    public function completeStep(string $step, array $stepData = []): void
    {
        if (!in_array($step, $this->completedSteps)) {
            $this->completedSteps[] = $step;
            $this->addToContext($step . '_data', $stepData);
            $this->updatedAt = new DateTime();
        }

        $nextStep = $this->getNextStep();

        if ($nextStep) {
            $this->setCurrentStep($nextStep);
        } else {
            $this->complete();
        }
    }

    public function failStep(string $step, string $reason): void
    {
        $this->setCurrentStep($step);
        $this->setFailureReason($reason);
        $this->setStatus('failed');
        $this->startCompensation();
    }

    public function complete(): void
    {
        $this->setStatus('completed');
        $this->setCurrentStep(null);
    }

    public function startCompensation(): void
    {
        $this->setStatus('compensating');
        $this->compensationSteps = array_reverse($this->completedSteps);
    }

    public function completeCompensation(): void
    {
        $this->setStatus('compensated');
        $this->setCurrentStep(null);
    }

    public function getNextStep(): ?string
    {
        foreach ($this->steps as $step) {
            if (!in_array($step, $this->completedSteps)) {
                return $step;
            }
        }

        return null;
    }

    public function getNextCompensationStep(): ?string
    {
        foreach ($this->compensationSteps as $step) {
            if (!in_array($step . '_compensated', $this->completedSteps)) {
                return $step;
            }
        }

        return null;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isCompensating(): bool
    {
        return $this->status === 'compensating';
    }

    public function isCompensated(): bool
    {
        return $this->status === 'compensated';
    }

    private function getStepsForType(string $type): array
    {
        switch ($type) {
            case 'purchase_vehicle':
                return [
                    'create_reservation',
                    'generate_payment_code',
                    'process_payment',
                    'create_sale',
                    'update_vehicle_status',
                ];
            default:
                return [];
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customerId,
            'vehicle_id' => $this->vehicleId,
            'type' => $this->type,
            'status' => $this->status,
            'steps' => $this->steps,
            'completed_steps' => $this->completedSteps,
            'compensation_steps' => $this->compensationSteps,
            'current_step' => $this->currentStep,
            'failure_reason' => $this->failureReason,
            'context' => $this->context,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'completed_at' => $this->completedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
