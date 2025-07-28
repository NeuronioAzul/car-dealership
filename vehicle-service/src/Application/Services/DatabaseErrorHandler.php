<?php

declare(strict_types=1);

namespace App\Application\Services;

use Exception;

class DatabaseErrorHandler
{
    public static function handlePDOException(\PDOException $e): void
    {
        $errorMessage = $e->getMessage();
        $errorCode = $e->getCode();

        // Tratar erros específicos de integridade de dados
        if (strpos($errorMessage, 'Duplicate entry') !== false) {
            if (strpos($errorMessage, 'chassis_number') !== false) {
                throw new Exception('Este número de chassi já está sendo usado por outro veículo', 422);
            }

            if (strpos($errorMessage, 'license_plate') !== false) {
                throw new Exception('Esta placa já está sendo usada por outro veículo', 422);
            }

            if (strpos($errorMessage, 'renavam') !== false) {
                throw new Exception('Este RENAVAM já está sendo usado por outro veículo', 422);
            }

            // Erro genérico de duplicação
            throw new Exception('Já existe um veículo com estes dados. Verifique chassi, placa e RENAVAM', 422);
        }

        // Tratar erros de foreign key
        if (strpos($errorMessage, 'foreign key constraint') !== false || strpos($errorMessage, 'FOREIGN KEY') !== false) {
            throw new Exception('Erro de referência: dados relacionados não encontrados', 422);
        }

        // Tratar erros de check constraint
        if (strpos($errorMessage, 'check constraint') !== false || strpos($errorMessage, 'CHECK') !== false) {
            throw new Exception('Dados inválidos: valores não atendem às regras do sistema', 422);
        }

        // Tratar erros de campos obrigatórios
        if (strpos($errorMessage, 'not null constraint') !== false || strpos($errorMessage, 'cannot be null') !== false) {
            throw new Exception('Campos obrigatórios não podem estar vazios', 422);
        }

        // Tratar erros de conexão
        if (strpos($errorMessage, 'Connection refused') !== false || strpos($errorMessage, 'server has gone away') !== false) {
            throw new Exception('Erro de conexão com o banco de dados. Tente novamente em alguns instantes', 503);
        }

        // Tratar erros de timeout
        if (strpos($errorMessage, 'timeout') !== false || strpos($errorMessage, 'Lock wait timeout') !== false) {
            throw new Exception('Operação demorou muito para ser executada. Tente novamente', 408);
        }

        // Tratar erros de sintaxe SQL (não deveria acontecer em produção)
        if (strpos($errorMessage, 'syntax error') !== false || strpos($errorMessage, 'SQL syntax') !== false) {
            throw new Exception('Erro interno de processamento. Entre em contato com o suporte', 500);
        }

        // Tratar erros de permissão
        if (strpos($errorMessage, 'Access denied') !== false || strpos($errorMessage, 'permission denied') !== false) {
            throw new Exception('Erro de permissão no banco de dados. Entre em contato com o suporte', 500);
        }

        // Outros erros de banco de dados
        throw new Exception('Erro interno ao processar os dados. Tente novamente ou entre em contato com o suporte', 500);
    }

    public static function getTranslatedMessage(string $originalMessage): string
    {
        $translations = [
            'Duplicate entry' => 'Dados duplicados',
            'foreign key constraint' => 'Referência inválida',
            'check constraint' => 'Dados inválidos',
            'not null constraint' => 'Campo obrigatório vazio',
            'Connection refused' => 'Erro de conexão',
            'server has gone away' => 'Conexão perdida',
            'timeout' => 'Operação demorou muito',
            'syntax error' => 'Erro de sintaxe',
            'Access denied' => 'Acesso negado',
        ];

        foreach ($translations as $original => $translated) {
            if (strpos($originalMessage, $original) !== false) {
                return $translated;
            }
        }

        return 'Erro interno do sistema';
    }
}
