<?php

namespace App\Infrastructure\Database;

use App\Domain\Entities\Customer;
use App\Domain\ValueObjects\Address;
use App\Domain\Repositories\CustomerRepositoryInterface;
use PDO;
use DateTime;

class CustomerRepository implements CustomerRepositoryInterface
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(Customer $customer): bool
    {
        $sql = "
            INSERT INTO customer_profiles (
                id, full_name, email, cpf, rg, birth_date, gender, marital_status,
                phone, mobile, whatsapp,
                street, number, complement, neighborhood, city, state, zip_code,
                occupation, company, monthly_income,
                preferred_contact, newsletter_subscription, sms_notifications,
                total_purchases, total_spent, last_purchase_date,
                customer_score, customer_tier,
                created_at, updated_at
            ) VALUES (
                :id, :user_id, :full_name, :email, :cpf, :rg, :birth_date, :gender, :marital_status,
                :phone, :mobile, :whatsapp,
                :street, :number, :complement, :neighborhood, :city, :state, :zip_code,
                :occupation, :company, :monthly_income,
                :preferred_contact, :newsletter_subscription, :sms_notifications,
                :total_purchases, :total_spent, :last_purchase_date,
                :customer_score, :customer_tier,
                :created_at, :updated_at
            )
        ";

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            'id' => $customer->getId(),
            'full_name' => $customer->getFullName(),
            'email' => $customer->getEmail(),
            'cpf' => $customer->getCpf(),
            'rg' => $customer->getRg(),
            'birth_date' => $customer->getBirthDate() ? $customer->getBirthDate()->format('Y-m-d') : null,
            'gender' => $customer->getGender(),
            'marital_status' => $customer->getMaritalStatus(),
            'phone' => $customer->getPhone(),
            'mobile' => $customer->getMobile(),
            'whatsapp' => $customer->getWhatsapp(),
            'street' => $customer->getAddress()->getStreet(),
            'number' => $customer->getAddress()->getNumber(),
            'complement' => $customer->getAddress()->getComplement(),
            'neighborhood' => $customer->getAddress()->getNeighborhood(),
            'city' => $customer->getAddress()->getCity(),
            'state' => $customer->getAddress()->getState(),
            'zip_code' => $customer->getAddress()->getZipCode(),
            'occupation' => $customer->getOccupation(),
            'company' => $customer->getCompany(),
            'monthly_income' => $customer->getMonthlyIncome(),
            'preferred_contact' => $customer->getPreferredContact(),
            'newsletter_subscription' => $customer->isNewsletterSubscription(),
            'sms_notifications' => $customer->isSmsNotifications(),
            'total_purchases' => $customer->getTotalPurchases(),
            'total_spent' => $customer->getTotalSpent(),
            'last_purchase_date' => $customer->getLastPurchaseDate() ? $customer->getLastPurchaseDate()->format('Y-m-d H:i:s') : null,
            'customer_score' => $customer->getCustomerScore(),
            'customer_tier' => $customer->getCustomerTier(),
            'created_at' => $customer->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $customer->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function findById(string $id): ?Customer
    {
        $sql = "SELECT * FROM customer_profiles WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToCustomer($data) : null;
    }

    public function findByEmail(string $email): ?Customer
    {
        $sql = "SELECT * FROM customer_profiles WHERE email = :email AND deleted_at IS NULL";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToCustomer($data) : null;
    }

    public function findAll(): array
    {
        $sql = "SELECT * FROM customer_profiles WHERE deleted_at IS NULL ORDER BY created_at DESC";
        $stmt = $this->connection->query($sql);

        $customers = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $customers[] = $this->mapToCustomer($data);
        }

        return $customers;
    }

    public function update(Customer $customer): bool
    {
        $sql = "
            UPDATE customer_profiles SET
                full_name = :full_name,
                email = :email,
                cpf = :cpf,
                rg = :rg,
                birth_date = :birth_date,
                gender = :gender,
                marital_status = :marital_status,
                phone = :phone,
                mobile = :mobile,
                whatsapp = :whatsapp,
                street = :street,
                number = :number,
                complement = :complement,
                neighborhood = :neighborhood,
                city = :city,
                state = :state,
                zip_code = :zip_code,
                occupation = :occupation,
                company = :company,
                monthly_income = :monthly_income,
                preferred_contact = :preferred_contact,
                newsletter_subscription = :newsletter_subscription,
                sms_notifications = :sms_notifications,
                total_purchases = :total_purchases,
                total_spent = :total_spent,
                last_purchase_date = :last_purchase_date,
                customer_score = :customer_score,
                customer_tier = :customer_tier,
                updated_at = :updated_at
            WHERE id = :id
        ";

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute([
            'id' => $customer->getId(),
            'full_name' => $customer->getFullName(),
            'email' => $customer->getEmail(),
            'cpf' => $customer->getCpf(),
            'rg' => $customer->getRg(),
            'birth_date' => $customer->getBirthDate() ? $customer->getBirthDate()->format('Y-m-d') : null,
            'gender' => $customer->getGender(),
            'marital_status' => $customer->getMaritalStatus(),
            'phone' => $customer->getPhone(),
            'mobile' => $customer->getMobile(),
            'whatsapp' => $customer->getWhatsapp(),
            'street' => $customer->getAddress()->getStreet(),
            'number' => $customer->getAddress()->getNumber(),
            'complement' => $customer->getAddress()->getComplement(),
            'neighborhood' => $customer->getAddress()->getNeighborhood(),
            'city' => $customer->getAddress()->getCity(),
            'state' => $customer->getAddress()->getState(),
            'zip_code' => $customer->getAddress()->getZipCode(),
            'occupation' => $customer->getOccupation(),
            'company' => $customer->getCompany(),
            'monthly_income' => $customer->getMonthlyIncome(),
            'preferred_contact' => $customer->getPreferredContact(),
            'newsletter_subscription' => $customer->isNewsletterSubscription(),
            'sms_notifications' => $customer->isSmsNotifications(),
            'total_purchases' => $customer->getTotalPurchases(),
            'total_spent' => $customer->getTotalSpent(),
            'last_purchase_date' => $customer->getLastPurchaseDate() ? $customer->getLastPurchaseDate()->format('Y-m-d H:i:s') : null,
            'customer_score' => $customer->getCustomerScore(),
            'customer_tier' => $customer->getCustomerTier(),
            'updated_at' => $customer->getUpdatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    public function delete(string $id): bool
    {
        $sql = "UPDATE customer_profiles SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id";
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    public function existsByEmail(string $email): bool
    {
        $sql = "SELECT COUNT(*) FROM customer_profiles WHERE email = :email AND deleted_at IS NULL";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['email' => $email]);

        return $stmt->fetchColumn() > 0;
    }

    private function mapToCustomer(array $data): Customer
    {
        $address = new Address(
            $data['street'] ?? '',
            $data['number'] ?? '',
            $data['neighborhood'] ?? '',
            $data['city'] ?? '',
            $data['state'] ?? '',
            $data['zip_code'] ?? '',
            $data['complement'] ?? ''
        );

        $customer = new Customer(
            $data['full_name'] ?? '',
            $data['email'] ?? '',
            $data['cpf'] ?? '',
            $data['rg'] ?? '',
            isset($data['birth_date']) ? new DateTime($data['birth_date']) : null,
            $data['gender'] ?? null,
            $data['marital_status'] ?? null,
            $data['phone'] ?? '',
            $data['mobile'] ?? '',
            $data['whatsapp'] ?? '',
            $address,
            $data['occupation'] ?? null,
            $data['company'] ?? null,
            $data['monthly_income'] ?? null,
            $data['preferred_contact'] ?? null,
            $data['newsletter_subscription'] ?? false,
            $data['sms_notifications'] ?? false,
            $data['total_purchases'] ?? 0,
            $data['total_spent'] ?? 0.0,
            isset($data['last_purchase_date']) ? new DateTime($data['last_purchase_date']) : null,
            $data['customer_score'] ?? 0,
            $data['customer_tier'] ?? 'bronze'
        );

        // Reflection para setar propriedades privadas (id, createdAt, updatedAt, deletedAt)
        $reflection = new \ReflectionClass($customer);

        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($customer, $data['id']);

        $createdAtProperty = $reflection->getProperty('createdAt');
        $createdAtProperty->setAccessible(true);
        $createdAtProperty->setValue($customer, isset($data['created_at']) ? new DateTime($data['created_at']) : new DateTime());

        $updatedAtProperty = $reflection->getProperty('updatedAt');
        $updatedAtProperty->setAccessible(true);
        $updatedAtProperty->setValue($customer, isset($data['updated_at']) ? new DateTime($data['updated_at']) : new DateTime());

        if (!empty($data['deleted_at'])) {
            $deletedAtProperty = $reflection->getProperty('deletedAt');
            $deletedAtProperty->setAccessible(true);
            $deletedAtProperty->setValue($customer, new DateTime($data['deleted_at']));
        }

        return $customer;
    }
}

