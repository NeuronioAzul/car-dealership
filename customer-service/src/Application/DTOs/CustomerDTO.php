<?php

declare(strict_types=1);

namespace App\Application\DTOs;

use App\Domain\ValueObjects\CustomerAddress;
use DateTime;
use Ramsey\Uuid\Uuid;

class CustomerDTO
{
    private string $id;
    private ?string $userId = null; // ID do usuário associado, se aplicável

    // Informações pessoais
    private string $fullName;
    private string $email;
    private string $cpf;
    private ?string $rg;
    private ?DateTime $birthDate;
    private ?string $gender;
    private ?string $maritalStatus;

    // Contato
    private ?string $phone;
    private ?string $mobile;
    private ?string $whatsapp;

    // Endereço principal
    private CustomerAddress $address;

    // Informações profissionais
    private ?string $occupation;
    private ?string $company;
    private ?float $monthlyIncome;

    // Preferências
    private string $preferredContact;
    private bool $newsletterSubscription;
    private bool $smsNotifications;

    // Histórico de compras
    private int $totalPurchases;
    private float $totalSpent;
    private ?DateTime $lastPurchaseDate;

    // Score e classificação
    private int $customerScore;
    private string $customerTier;

    // Timestamps
    private DateTime $createdAt;
    private DateTime $updatedAt;
    private ?DateTime $deletedAt;

    public function __construct(
        string $userId,
        string $fullName,
        string $email,
        string $cpf,
        ?string $rg,
        ?DateTime $birthDate,
        ?string $gender,
        ?string $maritalStatus,
        ?string $phone,
        ?string $mobile,
        ?string $whatsapp,
        CustomerAddress $address,
        ?string $occupation,
        ?string $company,
        ?float $monthlyIncome,
        string $preferredContact = 'email',
        bool $newsletterSubscription = false,
        bool $smsNotifications = false,
        int $totalPurchases = 0,
        float $totalSpent = 0.0,
        ?DateTime $lastPurchaseDate = null,
        int $customerScore = 0,
        string $customerTier = 'bronze',
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null,
        ?DateTime $deletedAt = null
    ) {
        $this->id = $id ?? Uuid::uuid6()->toString();
        $this->userId = $userId; // Inicialmente sem usuário associado
        $this->fullName = $fullName;
        $this->email = $email;
        $this->cpf = $cpf;
        $this->rg = $rg;
        $this->birthDate = $birthDate;
        $this->gender = $gender;
        $this->maritalStatus = $maritalStatus;
        $this->phone = $phone;
        $this->mobile = $mobile;
        $this->whatsapp = $whatsapp;
        $this->address = $address;
        $this->occupation = $occupation;
        $this->company = $company;
        $this->monthlyIncome = $monthlyIncome;
        $this->preferredContact = $preferredContact;
        $this->newsletterSubscription = $newsletterSubscription;
        $this->smsNotifications = $smsNotifications;
        $this->totalPurchases = $totalPurchases;
        $this->totalSpent = $totalSpent;
        $this->lastPurchaseDate = $lastPurchaseDate;
        $this->customerScore = $customerScore;
        $this->customerTier = $customerTier;
        $this->createdAt ??= new DateTime();
        $this->updatedAt ??= new DateTime();
        $this->deletedAt = null;
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function getRg(): ?string
    {
        return $this->rg;
    }

    public function getBirthDate(): ?DateTime
    {
        return $this->birthDate;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function getMaritalStatus(): ?string
    {
        return $this->maritalStatus;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function getWhatsapp(): ?string
    {
        return $this->whatsapp;
    }

    public function getAddress(): CustomerAddress
    {
        return $this->address;
    }

    public function getOccupation(): ?string
    {
        return $this->occupation;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function getMonthlyIncome(): ?float
    {
        return $this->monthlyIncome;
    }

    public function getPreferredContact(): string
    {
        return $this->preferredContact;
    }

    public function isNewsletterSubscription(): bool
    {
        return $this->newsletterSubscription;
    }

    public function isSmsNotifications(): bool
    {
        return $this->smsNotifications;
    }

    public function getTotalPurchases(): int
    {
        return $this->totalPurchases;
    }

    public function getTotalSpent(): float
    {
        return $this->totalSpent;
    }

    public function getLastPurchaseDate(): ?DateTime
    {
        return $this->lastPurchaseDate;
    }

    public function getCustomerScore(): int
    {
        return $this->customerScore;
    }

    public function getCustomerTier(): string
    {
        return $this->customerTier;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    // Setters (exemplo para alguns campos)

    // Pega o ID do usuário a partir do token JWT, se aplicável
    // Se não houver usuário associado, retorna null
    public function setUserId(?string $userId): void
    {
        $this->userId = $userId;
        $this->updatedAt = new DateTime();
    }

    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
        $this->updatedAt = new DateTime();
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
        $this->updatedAt = new DateTime();
    }

    public function setCpf(string $cpf): void
    {
        $this->cpf = $cpf;
        $this->updatedAt = new DateTime();
    }

    public function setRg(?string $rg): void
    {
        $this->rg = $rg;
        $this->updatedAt = new DateTime();
    }

    public function setBirthDate(?DateTime $birthDate): void
    {
        $this->birthDate = $birthDate;
        $this->updatedAt = new DateTime();
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
        $this->updatedAt = new DateTime();
    }

    public function setMaritalStatus(?string $maritalStatus): void
    {
        $this->maritalStatus = $maritalStatus;
        $this->updatedAt = new DateTime();
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
        $this->updatedAt = new DateTime();
    }

    public function setMobile(?string $mobile): void
    {
        $this->mobile = $mobile;
        $this->updatedAt = new DateTime();
    }

    public function setWhatsapp(?string $whatsapp): void
    {
        $this->whatsapp = $whatsapp;
        $this->updatedAt = new DateTime();
    }

    public function setAddress(CustomerAddress $address): void
    {
        $this->address = $address;
        $this->updatedAt = new DateTime();
    }

    public function setOccupation(?string $occupation): void
    {
        $this->occupation = $occupation;
        $this->updatedAt = new DateTime();
    }

    public function setCompany(?string $company): void
    {
        $this->company = $company;
        $this->updatedAt = new DateTime();
    }

    public function setMonthlyIncome(?float $monthlyIncome): void
    {
        $this->monthlyIncome = $monthlyIncome;
        $this->updatedAt = new DateTime();
    }

    public function setPreferredContact(string $preferredContact): void
    {
        $this->preferredContact = $preferredContact;
        $this->updatedAt = new DateTime();
    }

    public function setNewsletterSubscription(bool $newsletterSubscription): void
    {
        $this->newsletterSubscription = $newsletterSubscription;
        $this->updatedAt = new DateTime();
    }

    public function setSmsNotifications(bool $smsNotifications): void
    {
        $this->smsNotifications = $smsNotifications;
        $this->updatedAt = new DateTime();
    }

    public function setTotalPurchases(int $totalPurchases): void
    {
        $this->totalPurchases = $totalPurchases;
        $this->updatedAt = new DateTime();
    }

    public function setTotalSpent(float $totalSpent): void
    {
        $this->totalSpent = $totalSpent;
        $this->updatedAt = new DateTime();
    }

    public function setLastPurchaseDate(?DateTime $lastPurchaseDate): void
    {
        $this->lastPurchaseDate = $lastPurchaseDate;
        $this->updatedAt = new DateTime();
    }

    public function setCustomerScore(int $customerScore): void
    {
        $this->customerScore = $customerScore;
        $this->updatedAt = new DateTime();
    }

    public function setCustomerTier(string $customerTier): void
    {
        $this->customerTier = $customerTier;
        $this->updatedAt = new DateTime();
    }

    // Métodos de manipulação do cliente
    public function restore(): void
    {
        $this->deletedAt = null;
        $this->updatedAt = new DateTime();
    }

    public function delete(): void
    {
        $this->deletedAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
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
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deletedAt?->format('Y-m-d H:i:s'),
        ];
    }

    public static function fromArray(array $data): self
    {
        $address = CustomerAddress::fromArray($data['address']);
        $birthDate = isset($data['birth_date']) ? new DateTime($data['birth_date']) : null;
        $lastPurchaseDate = isset($data['last_purchase_date']) ? new DateTime($data['last_purchase_date']) : null;

        self::$id = $data['id'] ?? Uuid::uuid6()->toString(); // Gera um novo ID se não estiver presente

        return new self(
            userId: $data['user_id'] ?? null,
            fullName: $data['full_name'],
            email: $data['email'],
            cpf: $data['cpf'],
            rg: $data['rg'] ?? null,
            birthDate: $birthDate,
            gender: $data['gender'] ?? null,
            maritalStatus: $data['marital_status'] ?? null,
            phone: $data['phone'] ?? null,
            mobile: $data['mobile'] ?? null,
            whatsapp: $data['whatsapp'] ?? null,
            address: $address,
            occupation: $data['occupation'] ?? null,
            company: $data['company'] ?? null,
            monthlyIncome: $data['monthly_income'] ?? null,
            preferredContact: $data['preferred_contact'] ?? null,
            newsletterSubscription: $data['newsletter_subscription'] ?? null,
            smsNotifications: $data['sms_notifications'] ?? null,
            totalPurchases: $data['total_purchases'] ?? null,
            totalSpent: $data['total_spent'] ?? null,
            lastPurchaseDate: $lastPurchaseDate,
            customerScore: $data['customer_score'] ?? null,
            customerTier: $data['customer_tier'] ?? null,
            createdAt: isset($data['created_at']) ? new DateTime($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTime($data['updated_at']) : null,
            deletedAt: isset($data['deleted_at']) ? new DateTime($data['deleted_at']) : null
        );
    }

    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
