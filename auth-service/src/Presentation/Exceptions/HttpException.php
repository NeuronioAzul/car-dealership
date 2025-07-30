<?php

declare(strict_types=1);

namespace App\Presentation\Exceptions;

use Exception;

/**
 * Base exception para HTTP responses
 */
abstract class HttpException extends Exception
{
    public function __construct(string $message, int $code = 500, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    abstract public function getStatusCode(): int;
    
    public function toArray(): array
    {
        return [
            'error' => true,
            'message' => $this->getMessage(),
            'code' => $this->getCode()
        ];
    }
}
