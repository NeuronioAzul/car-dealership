<?php

declare(strict_types=1);

namespace App\Presentation\Exceptions;

/**
 * Exceção para conflitos HTTP (409)
 */
class ConflictException extends HttpException
{
    public function __construct(string $message = 'Conflict', int $code = 409, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int
    {
        return 409;
    }
}
