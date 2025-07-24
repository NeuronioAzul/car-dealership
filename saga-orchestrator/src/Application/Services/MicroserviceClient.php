<?php

declare(strict_types=1);

namespace App\Application\Services;

class MicroserviceClient
{
    private array $baseUrls;

    public function __construct()
    {
        $this->baseUrls = [
            'vehicle' => $_ENV['VEHICLE_SERVICE_URL'],
            'reservation' => $_ENV['RESERVATION_SERVICE_URL'],
            'payment' => $_ENV['PAYMENT_SERVICE_URL'],
            'sales' => $_ENV['SALES_SERVICE_URL'],
        ];
    }

    public function makeRequest(string $service, string $method, string $endpoint, array $data = [], array $headers = []): array
    {
        if (!isset($this->baseUrls[$service])) {
            throw new \Exception("Serviço '{$service}' não configurado");
        }

        $url = $this->baseUrls[$service] . $endpoint;

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array_merge([
                'Content-Type: application/json',
                'Accept: application/json',
            ], $headers),
        ]);

        if (in_array($method, ['POST', 'PUT', 'PATCH']) && !empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new \Exception("Erro na requisição para {$service}: {$error}");
        }

        $decodedResponse = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMessage = $decodedResponse['message'] ?? 'Erro desconhecido';

            throw new \Exception("Erro {$httpCode} em {$service}: {$errorMessage}");
        }

        return [
            'status_code' => $httpCode,
            'data' => $decodedResponse,
        ];
    }

    // Métodos específicos para cada serviço
    public function createReservation(string $customerId, string $vehicleId, string $authToken): array
    {
        return $this->makeRequest('reservation', 'POST', '/reservations', [
            'vehicle_id' => $vehicleId,
        ], [
            'Authorization: Bearer ' . $authToken,
        ]);
    }

    public function generatePaymentCode(string $reservationId, string $authToken): array
    {
        return $this->makeRequest('reservation', 'POST', '/reservations/generate-payment-code', [
            'reservation_id' => $reservationId,
        ], [
            'Authorization: Bearer ' . $authToken,
        ]);
    }

    public function createPayment(string $customerId, string $reservationId, string $vehicleId, string $paymentCode, float $amount, string $authToken): array
    {
        return $this->makeRequest('payment', 'POST', '/payments/create', [
            'reservation_id' => $reservationId,
            'vehicle_id' => $vehicleId,
            'payment_code' => $paymentCode,
            'amount' => $amount,
        ], [
            'Authorization: Bearer ' . $authToken,
        ]);
    }

    public function processPayment(string $paymentCode, string $method, string $authToken): array
    {
        return $this->makeRequest('payment', 'POST', '/payments', [
            'payment_code' => $paymentCode,
            'method' => $method,
        ], [
            'Authorization: Bearer ' . $authToken,
        ]);
    }

    public function createSale(string $customerId, string $vehicleId, string $reservationId, string $paymentId, float $salePrice, array $customerData, array $vehicleData, string $authToken): array
    {
        return $this->makeRequest('sales', 'POST', '/sales', [
            'vehicle_id' => $vehicleId,
            'reservation_id' => $reservationId,
            'payment_id' => $paymentId,
            'sale_price' => $salePrice,
            'customer_data' => $customerData,
            'vehicle_data' => $vehicleData,
        ], [
            'Authorization: Bearer ' . $authToken,
        ]);
    }

    public function getVehicleDetails(string $vehicleId): array
    {
        return $this->makeRequest('vehicle', 'GET', "/vehicles/{$vehicleId}");
    }

    public function updateVehicleStatus(string $vehicleId, string $status): array
    {
        return $this->makeRequest('vehicle', 'PUT', "/vehicles/{$vehicleId}/status", [
            'status' => $status,
        ]);
    }

    // Métodos de compensação
    public function cancelReservation(string $reservationId, string $authToken): array
    {
        return $this->makeRequest('reservation', 'DELETE', "/reservations/{$reservationId}", [], [
            'Authorization: Bearer ' . $authToken,
        ]);
    }

    public function refundPayment(string $paymentId, string $authToken): array
    {
        return $this->makeRequest('payment', 'POST', "/payments/{$paymentId}/refund", [], [
            'Authorization: Bearer ' . $authToken,
        ]);
    }

    public function cancelSale(string $saleId, string $authToken): array
    {
        return $this->makeRequest('sales', 'DELETE', "/sales/{$saleId}", [], [
            'Authorization: Bearer ' . $authToken,
        ]);
    }
}
