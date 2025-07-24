<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use PDO;
use PDOException;

class DatabaseConfig
{
    private static array $connections = [];

    public static function getConnection(string $database): PDO
    {
        if (!isset(self::$connections[$database])) {
            try {
                $host = $_ENV['DB_HOST'];
                $port = $_ENV['DB_PORT'];
                $username = $_ENV['DB_USERNAME'];
                $password = $_ENV['DB_PASSWORD'];

                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

                self::$connections[$database] = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                throw new \Exception('Erro na conexão com o banco de dados: ' . $e->getMessage());
            }
        }

        return self::$connections[$database];
    }

    public static function getAuthConnection(): PDO
    {
        return self::getConnection($_ENV['AUTH_DB']);
    }

    public static function getCustomerConnection(): PDO
    {
        return self::getConnection($_ENV['CUSTOMER_DB']);
    }

    public static function getVehicleConnection(): PDO
    {
        return self::getConnection($_ENV['VEHICLE_DB']);
    }

    public static function getReservationConnection(): PDO
    {
        return self::getConnection($_ENV['RESERVATION_DB']);
    }

    public static function getPaymentConnection(): PDO
    {
        return self::getConnection($_ENV['PAYMENT_DB']);
    }

    public static function getSalesConnection(): PDO
    {
        return self::getConnection($_ENV['SALES_DB']);
    }
}
