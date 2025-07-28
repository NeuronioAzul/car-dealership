<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Application\DTOs\CustomerDTO;
use App\Domain\Repositories\CustomerRepositoryInterface;
use PDO;

class CustomerRepository implements CustomerRepositoryInterface
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(CustomerDTO $customer): bool
    {
        $sql = '
            INSERT INTO customer_profiles (
                id, user_id, full_name, email, cpf, rg, birth_date, gender, marital_status,
                phone, mobile, whatsapp,
                street, number, complement, neighborhood, city, state, zip_code,
                occupation, company, monthly_income,
                preferred_contact, newsletter_subscription, sms_notifications,
                accept_terms, accept_privacy, accept_communications,
                total_purchases, total_spent, last_purchase_date,
                customer_score, customer_tier,
                created_at, updated_at
            ) VALUES (
                :id, :user_id, :full_name, :email, :cpf, :rg, :birth_date, :gender, :marital_status,
                :phone, :mobile, :whatsapp,
                :street, :number, :complement, :neighborhood, :city, :state, :zip_code,
                :occupation, :company, :monthly_income,
                :preferred_contact, :newsletter_subscription, :sms_notifications,
                :accept_terms, :accept_privacy, :accept_communications,
                :total_purchases, :total_spent, :last_purchase_date,
                :customer_score, :customer_tier,
                :created_at, :updated_at
            )
        ';

        $stmt = $this->connection->prepare($sql);

        $customerData = $customer->toArray();

        // Mesclar os dados do endereço com o array principal
        $customerData = array_merge($customerData, $customerData['address']);

        // Remover o array address aninhado e o campo deleted_at
        unset($customerData['address']);
        unset($customerData['deleted_at']);

        // Converter valores boolean para integer para compatibilidade com MySQL
        $customerData['newsletter_subscription'] = $customerData['newsletter_subscription'] ? 1 : 0;
        $customerData['sms_notifications'] = $customerData['sms_notifications'] ? 1 : 0;
        $customerData['accept_terms'] = $customerData['accept_terms'] ? 1 : 0;
        $customerData['accept_privacy'] = $customerData['accept_privacy'] ? 1 : 0;
        $customerData['accept_communications'] = $customerData['accept_communications'] ? 1 : 0;

        // // print generated SQL query for debugging
        // $debugSql = $sql;
        // foreach ($customerData as $key => $value) {
        //     $escapedValue = is_null($value) ? 'NULL' : $this->connection->quote((string)(is_array($value) ? json_encode($value) : $value));
        //     $debugSql = preg_replace('/:' . preg_quote($key, '/') . '\b/', $escapedValue, $debugSql);
        // }
        // echo '[DEBUG SQL] ' . $debugSql;
        // die;

        return $stmt->execute($customerData);
    }

    public function findById(string $id): ?CustomerDTO
    {
        $sql = 'SELECT * FROM customer_profiles WHERE id = :id AND deleted_at IS NULL';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToCustomer($data) : null;
    }

    public function findByUserId(string $id): ?CustomerDTO
    {
        $sql = 'SELECT * FROM customer_profiles WHERE user_id = :id AND deleted_at IS NULL';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->mapToCustomer($data) : null;
    }

    public function findByEmail(string $email): ?CustomerDTO
    {
        $sql = 'SELECT * FROM customer_profiles WHERE email = :email AND deleted_at IS NULL';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        # need to return a Customer object or null
        return $data ? $this->mapToCustomer($data) : null;
    }

    public function findAll(): array
    {
        $sql = 'SELECT * FROM customer_profiles WHERE deleted_at IS NULL ORDER BY created_at DESC';
        $stmt = $this->connection->query($sql);

        $customers = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $customers[] = $this->mapToCustomer($data);
        }

        return $customers;
    }

    public function update(CustomerDTO $customer): bool
    {
        $sql = '
            UPDATE customer_profiles SET
                user_id = :user_id,
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
        ';

        $stmt = $this->connection->prepare($sql);

        $customerData = $customer->toArray();

        // Mesclar os dados do endereço com o array principal
        $customerData = array_merge($customerData, $customerData['address']);

        // Remover o array address aninhado e o campo deleted_at
        unset($customerData['address']);
        unset($customerData['deleted_at']);
        unset($customerData['created_at']); // Não atualizamos created_at

        // Converter valores boolean para integer para compatibilidade com MySQL
        $customerData['newsletter_subscription'] = $customerData['newsletter_subscription'] ? 1 : 0;
        $customerData['sms_notifications'] = $customerData['sms_notifications'] ? 1 : 0;

        return $stmt->execute($customerData);
    }

    public function delete(string $id): bool
    {
        $sql = 'UPDATE customer_profiles SET deleted_at = NOW(), updated_at = NOW() WHERE id = :id';
        $stmt = $this->connection->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }

    public function existsByEmail(string $email): bool
    {
        $sql = 'SELECT COUNT(*) FROM customer_profiles WHERE email = :email AND deleted_at IS NULL';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['email' => $email]);

        return $stmt->fetchColumn() > 0;
    }

    private function mapToCustomer(array $data): CustomerDTO
    {
        // Converter os dados do banco para o formato esperado pelo CustomerDTO
        $input = [
            'id' => $data['id'],
            'userId' => $data['user_id'],
            'fullName' => $data['full_name'],
            'email' => $data['email'],
            'cpf' => $data['cpf'],
            'rg' => $data['rg'],
            'birthDate' => $data['birth_date'],
            'gender' => $data['gender'],
            'maritalStatus' => $data['marital_status'],
            'phone' => $data['phone'],
            'mobile' => $data['mobile'],
            'whatsapp' => $data['whatsapp'],
            'address' => [
                'street' => $data['street'] ?? '',
                'number' => $data['number'] ?? '',
                'complement' => $data['complement'] ?? '',
                'neighborhood' => $data['neighborhood'] ?? '',
                'city' => $data['city'] ?? '',
                'state' => $data['state'] ?? '',
                'zipCode' => $data['zip_code'] ?? '',
            ],
            'occupation' => $data['occupation'],
            'company' => $data['company'],
            'monthlyIncome' => $data['monthly_income'],
            'preferredContact' => $data['preferred_contact'] ?? 'email',
            'newsletterSubscription' => (bool) $data['newsletter_subscription'],
            'smsNotifications' => (bool) $data['sms_notifications'],
            'totalPurchases' => $data['total_purchases'] ?? 0,
            'totalSpent' => $data['total_spent'] ?? 0.0,
            'lastPurchaseDate' => $data['last_purchase_date'],
            'customerScore' => $data['customer_score'] ?? 0,
            'customerTier' => $data['customer_tier'] ?? 'bronze',
            'acceptTerms' => (bool) ($data['accept_terms'] ?? 0),
            'acceptPrivacy' => (bool) ($data['accept_privacy'] ?? 0),
            'acceptCommunications' => (bool) ($data['accept_communications'] ?? 0),
            'createdAt' => $data['created_at'],
            'updatedAt' => $data['updated_at'],
            'deletedAt' => $data['deleted_at'] ?? null,
        ];

        return new CustomerDTO($input);
    }
}
