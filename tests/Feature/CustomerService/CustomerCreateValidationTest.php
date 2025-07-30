<?php

require_once '../customer-service/vendor/autoload.php';

use App\Application\Validation\CreateCustomerProfileRequest;

// Dados de teste baseados no JSON fornecido
$testData = [
    "user_id" => "123e4567-e89b-12d3-a456-426614174000",
    "full_name" => "Maria Silva",
    "email" => "maria@email.com",
    "cpf" => "12345678909",
    "rg" => "12.345.678-9",
    "birth_date" => "1990-01-01",
    "gender" => "M",
    "marital_status" => "Single",
    "phone" => "(11) 1234-5678",
    "mobile" => "(11) 91234-5678",
    "whatsapp" => "(11) 91234-5678",
    "address" => [
        "street" => "Rua Exemplo",
        "number" => "123",
        "neighborhood" => "Centro",
        "city" => "São Paulo",
        "state" => "SP",
        "zip_code" => "01234-567",
        "complement" => "Apto 1"
    ],
    "occupation" => "Desenvolvedor",
    "company" => "Empresa Exemplo",
    "monthly_income" => 5000.00,
    "preferred_contact" => "email",
    "newsletter_subscription" => true,
    "sms_notifications" => true,
    "accept_terms" => true,
    "accept_privacy" => true,
    "accept_communications" => true
];

echo "=== Testando validação ===\n";

try {
    $request = new CreateCustomerProfileRequest($testData);
    
    if ($request->validate()) {
        echo "✅ Validação passou!\n";
        $validated = $request->validated();
        echo "Dados validados: " . json_encode($validated, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo "❌ Validação falhou!\n";
        $errors = $request->errors();
        echo "Erros: " . json_encode($errors, JSON_PRETTY_PRINT) . "\n";
    }
} catch (Exception $e) {
    echo "❌ Erro durante validação: " . $e->getMessage() . "\n";
}
