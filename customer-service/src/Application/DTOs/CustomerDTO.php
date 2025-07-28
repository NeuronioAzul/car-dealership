<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\ValueObjects\CustomerAddress;
use DateTime;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerDTO
{
    #[Assert\Uuid]
    public readonly string $id;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    public readonly ?string $userId;

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public readonly string $fullName;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 255)]
    public readonly string $email;

    #[Assert\NotBlank]
    #[Assert\Length(max: 11)]
    #[Assert\Regex(pattern: '/^\d{11}$/')]
    public readonly string $cpf;

    #[Assert\Length(max: 20)]
    public readonly ?string $rg;

    #[Assert\NotBlank]
    public readonly ?DateTime $birthDate;

    #[Assert\Choice(choices: ['M', 'F', 'Other'])]
    public readonly ?string $gender;

    #[Assert\Choice(choices: ['single', 'married', 'divorced', 'widowed', 'common_law'])]
    public readonly ?string $maritalStatus;

    #[Assert\NotBlank]
    #[Assert\Length(max: 20)]
    public readonly ?string $phone;

    public readonly ?string $mobile;

    public readonly ?string $whatsapp;

    public readonly CustomerAddress $address;

    #[Assert\Length(max: 255)]
    public readonly ?string $occupation;

    #[Assert\Length(max: 255)]
    public readonly ?string $company;

    #[Assert\Type(type: 'numeric')]
    #[Assert\GreaterThanOrEqual(value: 0)]
    public readonly ?float $monthlyIncome;

    #[Assert\Choice(choices: ['email', 'phone', 'whatsapp'])]
    public readonly string $preferredContact;

    #[Assert\Type(type: 'bool')]
    public readonly bool $newsletterSubscription;

    #[Assert\Type(type: 'bool')]
    public readonly bool $smsNotifications;

    #[Assert\Type(type: 'numeric')]
    #[Assert\GreaterThanOrEqual(value: 0)]
    public readonly int $totalPurchases;

    #[Assert\Type(type: 'numeric')]
    #[Assert\GreaterThanOrEqual(value: 0)]
    public readonly float $totalSpent;

    public readonly ?DateTime $lastPurchaseDate;

    #[Assert\Type(type: 'numeric')]
    #[Assert\Range(min: 0, max: 1000)]
    public readonly int $customerScore;

    #[Assert\Choice(choices: ['bronze', 'silver', 'gold', 'platinum'])]
    public readonly string $customerTier;

    #[Assert\NotBlank]
    #[Assert\Type(type: 'bool')]
    public readonly bool $acceptTerms;

    #[Assert\NotBlank]
    #[Assert\Type(type: 'bool')]
    public readonly bool $acceptPrivacy;

    #[Assert\NotBlank]
    #[Assert\Type(type: 'bool')]
    public readonly bool $acceptCommunications;

    #[Assert\Type(DateTimeImmutable::class)]
    public readonly DateTimeImmutable $createdAt;

    public readonly DateTime $updatedAt;

    public readonly ?DateTime $deletedAt;

    public function __construct(array $input)
    {
        $this->id = $input['id'] ?? Uuid::uuid6()->toString();
        $this->userId = $input['user_id'] ?? null;
        $this->fullName = $input['full_name'] ?? '';
        $this->email = $input['email'] ?? '';
        $this->cpf = $input['cpf'] ?? '';
        $this->rg = $input['rg'] ?? null;
        $this->birthDate = isset($input['birth_date']) ? new \DateTime($input['birth_date']) : null;
        $this->gender = $input['gender'] ?? null;
        $this->maritalStatus = $input['marital_status'] ?? null;
        $this->phone = $input['phone'] ?? null;
        $this->mobile = $input['mobile'] ?? null;
        $this->whatsapp = $input['whatsapp'] ?? null;
        $this->address = new CustomerAddress(
            street: $input['address']['street'] ?? '',
            number: $input['address']['number'] ?? '',
            complement: $input['address']['complement'] ?? '',
            neighborhood: $input['address']['neighborhood'] ?? '',
            city: $input['address']['city'] ?? '',
            state: $input['address']['state'] ?? '',
            zipCode: $input['address']['zip_code'] ?? ''
        );
        $this->occupation = $input['occupation'] ?? null;
        $this->company = $input['company'] ?? null;
        $this->monthlyIncome = (float)($input['monthly_income'] ?? 0);
        $this->preferredContact = $input['preferred_contact'] ?? 'email';
        $this->newsletterSubscription = $input['newsletter_subscription'] ?? false;
        $this->smsNotifications = $input['sms_notifications'] ?? false;
        $this->totalPurchases = $input['total_purchases'] ?? 0;
        $this->totalSpent = (float)($input['total_spent'] ?? 0.0);
        $this->lastPurchaseDate = isset($input['last_purchase_date']) ? new \DateTime($input['last_purchase_date']) : null;
        $this->customerScore = $input['customer_score'] ?? 0;
        $this->customerTier = $input['customer_tier'] ?? 'bronze';
        $this->acceptTerms = $input['accept_terms'] ?? false;
        $this->acceptPrivacy = $input['accept_privacy'] ?? false;
        $this->acceptCommunications = $input['accept_communications'] ?? false;
        $this->createdAt = isset($input['created_at']) && $input['created_at']
            ? new DateTimeImmutable($input['created_at'])
            : new DateTimeImmutable('now');
        $this->updatedAt = isset($input['updated_at']) && $input['updated_at']
            ? new DateTime($input['updated_at'])
            : new DateTime('now');
        $this->deletedAt = isset($input['deleted_at']) && $input['deleted_at']
            ? new DateTime($input['deleted_at'])
            : null;
    }

    public static function fromArray(array $input): self
    {
        return new self($input);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'full_name' => $this->fullName,
            'email' => $this->email,
            'cpf' => $this->cpf,
            'rg' => $this->rg,
            'birth_date' => $this->birthDate?->format('Y-m-d'),
            'gender' => $this->gender,
            'marital_status' => $this->maritalStatus,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'whatsapp' => $this->whatsapp,
            'address' => $this->address->toArray(),
            'occupation' => $this->occupation,
            'company' => $this->company,
            'monthly_income' => $this->monthlyIncome,
            'preferred_contact' => $this->preferredContact,
            'newsletter_subscription' => $this->newsletterSubscription,
            'sms_notifications' => $this->smsNotifications,
            'total_purchases' => $this->totalPurchases,
            'total_spent' => $this->totalSpent,
            'last_purchase_date' => $this->lastPurchaseDate?->format('Y-m-d H:i:s'),
            'customer_score' => $this->customerScore,
            'customer_tier' => $this->customerTier,
            'accept_terms' => $this->acceptTerms,
            'accept_privacy' => $this->acceptPrivacy,
            'accept_communications' => $this->acceptCommunications,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deletedAt?->format('Y-m-d H:i:s'),
        ];
    }

    // Métodos de manipulação do cliente
    public function restore(): void
    {
        $this->deletedAt = null;
        $this->updatedAt = new DateTime();
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }
}
