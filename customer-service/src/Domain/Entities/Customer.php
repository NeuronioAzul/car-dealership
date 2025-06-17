<?php

namespace App\Domain\Entities;

use Ramsey\Uuid\Uuid;
use DateTime;
use App\Domain\ValueObjects\Address;

class Customer
{
    private string $id;

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
    private Address $address;

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
        Address $address,
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
        string $customerTier = 'bronze'
    ) {
        $this->id = Uuid::uuid6()->toString();
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
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
        $this->deletedAt = null;
    }

    // Getters
    public function getId(): string { return $this->id; }
    public function getFullName(): string { return $this->fullName; }
    public function getEmail(): string { return $this->email; }
    public function getCpf(): string { return $this->cpf; }
    public function getRg(): ?string { return $this->rg; }
    public function getBirthDate(): ?DateTime { return $this->birthDate; }
    public function getGender(): ?string { return $this->gender; }
    public function getMaritalStatus(): ?string { return $this->maritalStatus; }
    public function getPhone(): ?string { return $this->phone; }
    public function getMobile(): ?string { return $this->mobile; }
    public function getWhatsapp(): ?string { return $this->whatsapp; }
    public function getAddress(): Address { return $this->address; }
    public function getOccupation(): ?string { return $this->occupation; }
    public function getCompany(): ?string { return $this->company; }
    public function getMonthlyIncome(): ?float { return $this->monthlyIncome; }
    public function getPreferredContact(): string { return $this->preferredContact; }
    public function isNewsletterSubscription(): bool { return $this->newsletterSubscription; }
    public function isSmsNotifications(): bool { return $this->smsNotifications; }
    public function getTotalPurchases(): int { return $this->totalPurchases; }
    public function getTotalSpent(): float { return $this->totalSpent; }
    public function getLastPurchaseDate(): ?DateTime { return $this->lastPurchaseDate; }
    public function getCustomerScore(): int { return $this->customerScore; }
    public function getCustomerTier(): string { return $this->customerTier; }
    public function getCreatedAt(): DateTime { return $this->createdAt; }
    public function getUpdatedAt(): DateTime { return $this->updatedAt; }
    public function getDeletedAt(): ?DateTime { return $this->deletedAt; }

    // Setters (exemplo para alguns campos)
    public function setFullName(string $fullName): void { $this->fullName = $fullName; $this->updatedAt = new DateTime(); }
    public function setEmail(string $email): void { $this->email = $email; $this->updatedAt = new DateTime(); }
    public function setCpf(string $cpf): void { $this->cpf = $cpf; $this->updatedAt = new DateTime(); }
    public function setRg(?string $rg): void { $this->rg = $rg; $this->updatedAt = new DateTime(); }
    public function setBirthDate(?DateTime $birthDate): void { $this->birthDate = $birthDate; $this->updatedAt = new DateTime(); }
    public function setGender(?string $gender): void { $this->gender = $gender; $this->updatedAt = new DateTime(); }
    public function setMaritalStatus(?string $maritalStatus): void { $this->maritalStatus = $maritalStatus; $this->updatedAt = new DateTime(); }
    public function setPhone(?string $phone): void { $this->phone = $phone; $this->updatedAt = new DateTime(); }
    public function setMobile(?string $mobile): void { $this->mobile = $mobile; $this->updatedAt = new DateTime(); }
    public function setWhatsapp(?string $whatsapp): void { $this->whatsapp = $whatsapp; $this->updatedAt = new DateTime(); }
    public function setAddress(Address $address): void { $this->address = $address; $this->updatedAt = new DateTime(); }
    public function setOccupation(?string $occupation): void { $this->occupation = $occupation; $this->updatedAt = new DateTime(); }
    public function setCompany(?string $company): void { $this->company = $company; $this->updatedAt = new DateTime(); }
    public function setMonthlyIncome(?float $monthlyIncome): void { $this->monthlyIncome = $monthlyIncome; $this->updatedAt = new DateTime(); }
    public function setPreferredContact(string $preferredContact): void { $this->preferredContact = $preferredContact; $this->updatedAt = new DateTime(); }
    public function setNewsletterSubscription(bool $newsletterSubscription): void { $this->newsletterSubscription = $newsletterSubscription; $this->updatedAt = new DateTime(); }
    public function setSmsNotifications(bool $smsNotifications): void { $this->smsNotifications = $smsNotifications; $this->updatedAt = new DateTime(); }
    public function setTotalPurchases(int $totalPurchases): void { $this->totalPurchases = $totalPurchases; $this->updatedAt = new DateTime(); }
    public function setTotalSpent(float $totalSpent): void { $this->totalSpent = $totalSpent; $this->updatedAt = new DateTime(); }
    public function setLastPurchaseDate(?DateTime $lastPurchaseDate): void { $this->lastPurchaseDate = $lastPurchaseDate; $this->updatedAt = new DateTime(); }
    public function setCustomerScore(int $customerScore): void { $this->customerScore = $customerScore; $this->updatedAt = new DateTime(); }
    public function setCustomerTier(string $customerTier): void { $this->customerTier = $customerTier; $this->updatedAt = new DateTime(); }

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
            'deleted_at' => $this->deletedAt?->format('Y-m-d H:i:s')
        ];
    }
}

